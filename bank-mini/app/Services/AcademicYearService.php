<?php

namespace App\Services;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AcademicYearService
{
    /**
     * Get all academic years with pagination or full list.
     */
    public function getAllAcademicYears(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = AcademicYear::withCount(['customers', 'classHistories'])
            ->orderBy('is_active', 'desc')
            ->orderBy('start_date', 'desc');

        if (! empty($search)) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get list of all academic years for dropdowns.
     */
    public function getAcademicYearList(): Collection
    {
        return AcademicYear::orderBy('start_date', 'desc')->get();
    }

    /**
     * Get currently active academic year.
     */
    public function getActiveAcademicYear(): ?AcademicYear
    {
        return AcademicYear::where('is_active', true)->first();
    }

    /**
     * Create a new academic year.
     * Ensures only one academic year is active if is_active is checked.
     * Does NOT modify customer classes.
     */
    public function createAcademicYear(array $data): AcademicYear
    {
        return DB::transaction(function () use ($data) {
            $isActive = ! empty($data['is_active']);

            if ($isActive) {
                AcademicYear::where('is_active', true)->update(['is_active' => false]);
            }

            return AcademicYear::create([
                'name' => trim($data['name']),
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'is_active' => $isActive,
            ]);
        });
    }

    /**
     * Set specific academic year as active.
     * Deactivates all other academic years.
     * Does NOT automatically alter student classes.
     */
    public function activateAcademicYear(AcademicYear $academicYear): bool
    {
        return DB::transaction(function () use ($academicYear) {
            AcademicYear::where('id', '!=', $academicYear->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            return $academicYear->update(['is_active' => true]);
        });
    }

    /**
     * Get previous academic years (inactive ones).
     */
    public function getPreviousAcademicYears(): Collection
    {
        return AcademicYear::where('is_active', false)
            ->orderBy('end_date', 'desc')
            ->get();
    }
}

