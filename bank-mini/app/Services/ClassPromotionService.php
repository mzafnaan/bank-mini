<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Customer;
use App\Models\CustomerClassHistory;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ClassPromotionService
{
    /**
     * Get candidate customers for promotion with filters & recommendations.
     */
    public function getPromotionCandidates(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = Customer::with(['academicYear', 'customerAccount', 'classHistories'])
            ->whereIn('status', ['active', 'aktif'])
            ->orderBy('class', 'asc')
            ->orderBy('name', 'asc');

        // Filter by source academic year
        if (! empty($filters['source_academic_year_id'])) {
            $query->where('academic_year_id', $filters['source_academic_year_id']);
        }

        // Filter by class level (X, XI, XII)
        if (! empty($filters['class_level'])) {
            $query->where('class', $filters['class_level']);
        }

        // Search by name or NIS
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('class_group', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Calculate promotion summary statistics for a given source academic year.
     */
    public function getCandidateSummary(?int $sourceAcademicYearId = null): array
    {
        $query = Customer::whereIn('status', ['active', 'aktif']);

        if ($sourceAcademicYearId) {
            $query->where('academic_year_id', $sourceAcademicYearId);
        }

        $all = $query->get(['id', 'class']);

        $countX = $all->where('class', 'X')->count();
        $countXI = $all->where('class', 'XI')->count();
        $countXII = $all->where('class', 'XII')->count();
        $countOther = $all->whereNotIn('class', ['X', 'XI', 'XII'])->count();

        return [
            'total' => $all->count(),
            'count_x' => $countX,
            'count_xi' => $countXI,
            'count_xii' => $countXII,
            'count_other' => $countOther,
            'recommended_promote' => $countX + $countXI,
            'recommended_graduate' => $countXII,
            'recommended_stay' => 0,
        ];
    }

    /**
     * Process Class Promotion in a single Atomic Database Transaction.
     * Prevents duplicate processing on the same academic year.
     * Handles student graduation, login deactivation, and class histories.
     *
     * @param  AcademicYear  $targetYear
     * @param  array<int, array{action: string, target_class_group: ?string}>  $decisions
     * @param  int|null  $sourceYearId
     * @return array{total: int, promoted: int, stayed: int, graduated: int}
     *
     * @throws \Exception
     */
    public function processPromotion(AcademicYear $targetYear, array $decisions, ?int $sourceYearId = null): array
    {
        return DB::transaction(function () use ($targetYear, $decisions, $sourceYearId) {
            $counters = [
                'total' => 0,
                'promoted' => 0,
                'stayed' => 0,
                'graduated' => 0,
            ];

            $customerIds = array_keys($decisions);
            $customers = Customer::with(['customerAccount', 'classHistories'])
                ->whereIn('id', $customerIds)
                ->get()
                ->keyBy('id');

            foreach ($decisions as $customerId => $decision) {
                if (! isset($customers[$customerId])) {
                    throw new \Exception("Data nasabah ID {$customerId} tidak ditemukan dalam sistem.");
                }

                /** @var Customer $customer */
                $customer = $customers[$customerId];

                // 1. Prevent double processing: Check if already in target academic year history
                $alreadyInTarget = $customer->classHistories
                    ->where('academic_year_id', $targetYear->id)
                    ->isNotEmpty();

                if ($alreadyInTarget) {
                    throw new \Exception(
                        "Nasabah '{$customer->name}' (NIS: {$customer->nis}) sudah pernah diproses untuk tahun ajaran {$targetYear->name}. Pemrosesan ganda tidak diizinkan."
                    );
                }

                // Check if already inactive / graduated
                if (! $customer->isActive()) {
                    throw new \Exception(
                        "Nasabah '{$customer->name}' (NIS: {$customer->nis}) berstatus nonaktif/lulus dan tidak dapat diproses lagi."
                    );
                }

                $action = $decision['action'] ?? $decision['decision'] ?? 'promote';
                $targetGroup = ! empty($decision['target_class_group'])
                    ? trim($decision['target_class_group'])
                    : $customer->class_group;

                // 2. Ensure historical record for previous academic year exists
                $effectiveSourceYearId = $sourceYearId ?: $customer->academic_year_id;
                if ($effectiveSourceYearId) {
                    $hasSourceHistory = $customer->classHistories
                        ->where('academic_year_id', $effectiveSourceYearId)
                        ->isNotEmpty();

                    if (! $hasSourceHistory) {
                        CustomerClassHistory::create([
                            'customer_id' => $customer->id,
                            'academic_year_id' => $effectiveSourceYearId,
                            'class' => $customer->class,
                            'class_group' => $customer->class_group,
                        ]);
                    }
                }

                // 3. Apply Decision
                if ($action === 'promote') {
                    // X -> XI, XI -> XII
                    $currentLevel = strtoupper(trim($customer->class));
                    $newClass = match ($currentLevel) {
                        'X' => 'XI',
                        'XI' => 'XII',
                        default => throw new \Exception(
                            "Nasabah '{$customer->name}' di kelas {$currentLevel} tidak dapat dinaikkan kelas. Kelas XII harus memilih opsi Lulus atau Tinggal Kelas."
                        ),
                    };

                    // Update Customer
                    $customer->update([
                        'class' => $newClass,
                        'class_group' => $targetGroup,
                        'academic_year_id' => $targetYear->id,
                        'status' => 'active',
                    ]);

                    // Record new history in target academic year
                    CustomerClassHistory::create([
                        'customer_id' => $customer->id,
                        'academic_year_id' => $targetYear->id,
                        'class' => $newClass,
                        'class_group' => $targetGroup,
                    ]);

                    $counters['promoted']++;

                } elseif ($action === 'stay') {
                    // X -> X, XI -> XI, XII -> XII
                    $newClass = strtoupper(trim($customer->class));

                    // Update Customer
                    $customer->update([
                        'class' => $newClass,
                        'class_group' => $targetGroup,
                        'academic_year_id' => $targetYear->id,
                        'status' => 'active',
                    ]);

                    // Record stay history in target academic year
                    CustomerClassHistory::create([
                        'customer_id' => $customer->id,
                        'academic_year_id' => $targetYear->id,
                        'class' => $newClass,
                        'class_group' => $targetGroup,
                    ]);

                    $counters['stayed']++;

                } elseif ($action === 'graduate') {
                    // XII -> Lulus (Nonaktif)
                    // Aturan:
                    // 1. Ubah customers.status menjadi nonaktif.
                    // 2. Nonaktifkan akun login customer_accounts & revoke tokens.
                    // 3. Jangan menghapus identitas nasabah.
                    // 4. Jangan menghapus atau mengubah rekening, saldo, transaksi, dan jurnal keuangan.
                    // 5. Jangan otomatis menonaktifkan rekening tanpa kebijakan penutupan rekening.
                    // 6. Siswa yang lulus tetap tercatat sebagai kelas XII pada tahun ajaran terakhirnya,
                    //    tetapi tidak mendapatkan catatan kelas baru sebagai siswa aktif.

                    $customer->update([
                        'status' => 'inactive',
                    ]);

                    if ($customer->customerAccount) {
                        $customer->customerAccount->update([
                            'status' => 'inactive',
                        ]);
                        $customer->customerAccount->tokens()->delete();
                    }

                    $counters['graduated']++;
                } else {
                    throw new \Exception("Aksi '{$action}' tidak dikenal dalam sistem.");
                }

                $counters['total']++;
            }

            return $counters;
        });
    }
}

