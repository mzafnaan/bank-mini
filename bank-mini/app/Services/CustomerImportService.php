<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Customer;
use App\Models\CustomerClassHistory;
use Exception;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class CustomerImportService
{
    public function __construct(
        protected CustomerService $customerService
    ) {}

    /**
     * Parse uploaded file (CSV or XLSX) into a structured array of student data.
     *
     * @param string $filePath Absolute path to the uploaded temporary file
     * @param string $originalExtension File extension (e.g. 'csv', 'xlsx', 'txt')
     * @return array Array of parsed student records
     */
    public function parseFile(string $filePath, string $originalExtension): array
    {
        $ext = strtolower($originalExtension);

        if ($ext === 'xlsx') {
            $rows = $this->readXlsxRows($filePath);
        } else {
            $rows = $this->readCsvRows($filePath);
        }

        if (empty($rows)) {
            return [];
        }

        // Auto-detect format: School Attendance Sheet vs Standard Tabular
        if ($this->isSchoolAttendanceFormat($rows)) {
            return $this->parseSchoolAttendanceFormat($rows);
        }

        return $this->parseStandardTabularFormat($rows);
    }

    /**
     * Check if the document matches the School Attendance Sheet format (DAFTAR HADIR SISWA / KELAS : ...).
     */
    protected function isSchoolAttendanceFormat(array $rows): bool
    {
        foreach ($rows as $row) {
            $line = implode(' ', $row);
            if (preg_match('/DAFTAR\s+HADIR\s+SISWA/i', $line) || preg_match('/KELAS\s*:\s*[A-Z0-9]/i', $line)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Parse School Attendance Sheet format (supports multiple class sections in one file).
     */
    protected function parseSchoolAttendanceFormat(array $rows): array
    {
        $students = [];
        $currentClass = null;
        $currentClassGroup = null;
        $currentAcademicYearName = null;

        foreach ($rows as $row) {
            $line = implode(',', $row);

            // 1. Detect Class header: "KELAS : X RPL 1" or "KELAS : XI BD" or "KELAS : XII TKJ 1"
            if (preg_match('/KELAS\s*:\s*([A-Za-z0-9\s]+)/i', $line, $matches)) {
                $rawClass = trim(explode(',', $matches[1])[0]);
                if (preg_match('/^(XII|XI|X)\s*(.*)$/i', $rawClass, $cMatches)) {
                    $currentClass = strtoupper(trim($cMatches[1]));
                    $currentClassGroup = trim($cMatches[2]) ?: null;
                } else {
                    $currentClass = $rawClass;
                    $currentClassGroup = null;
                }
                continue;
            }

            // 2. Detect Academic Year: "TAHUN PELAJARAN 2026 - 2027"
            if (preg_match('/TAHUN\s+PELAJARAN\s*([0-9\s\-–\/]+)/i', $line, $matches)) {
                $currentAcademicYearName = trim(explode(',', $matches[1])[0]);
                continue;
            }

            // 3. Detect Student row: Col 0 is number, Col 1 is NIS, Col 2 is Name
            if (count($row) < 3) {
                continue;
            }

            $col0 = trim($row[0] ?? '');
            $col1 = trim($row[1] ?? '');
            $col2 = trim($row[2] ?? '');

            // Check if Col 0 is numeric order (1, 2, 3...) and Col 1 is numeric NIS (4-15 digits)
            if (is_numeric($col0) && (int)$col0 > 0 && preg_match('/^[0-9]{3,20}$/', $col1) && !empty($col2) && preg_match('/[a-zA-Z]/', $col2)) {
                $cleanName = trim(rtrim($col2, '* '));
                $gender = !empty($row[3]) ? strtoupper(trim($row[3])) : null;
                if ($gender !== 'L' && $gender !== 'P') {
                    $gender = null;
                }

                $students[] = [
                    'nis' => $col1,
                    'name' => $cleanName,
                    'class' => $currentClass ?? 'X',
                    'class_group' => $currentClassGroup,
                    'gender' => $gender,
                    'academic_year_name' => $currentAcademicYearName,
                ];
            }
        }

        return $students;
    }

    /**
     * Parse Standard Tabular format (Header row: NIS, Nama, Kelas, dsb.).
     */
    protected function parseStandardTabularFormat(array $rows): array
    {
        $students = [];
        $headerIndex = -1;
        $colMap = [];

        // Find header row
        foreach ($rows as $idx => $row) {
            $lowerRow = array_map(fn($c) => strtolower(trim((string)$c)), $row);
            $hasNis = false;
            $hasName = false;

            foreach ($lowerRow as $cIdx => $cell) {
                if (in_array($cell, ['nis', 'nomor_induk', 'no_induk', 'nisn'], true)) {
                    $hasNis = true;
                    $colMap['nis'] = $cIdx;
                } elseif (in_array($cell, ['nama', 'name', 'nama_siswa', 'nama_nasabah', 'nama_lengkap'], true)) {
                    $hasName = true;
                    $colMap['name'] = $cIdx;
                } elseif (in_array($cell, ['kelas', 'class', 'tingkat'], true)) {
                    $colMap['class'] = $cIdx;
                } elseif (in_array($cell, ['rombel', 'jurusan', 'class_group', 'kelompok'], true)) {
                    $colMap['class_group'] = $cIdx;
                } elseif (in_array($cell, ['telepon', 'phone', 'hp', 'no_hp', 'no_telepon'], true)) {
                    $colMap['phone'] = $cIdx;
                } elseif (in_array($cell, ['email', 'surel'], true)) {
                    $colMap['email'] = $cIdx;
                }
            }

            if ($hasNis && $hasName) {
                $headerIndex = $idx;
                break;
            }
        }

        // If no explicit header found, default col 0 = NIS, col 1 = Name, col 2 = Class
        if ($headerIndex === -1) {
            $headerIndex = -1;
            $colMap = ['nis' => 0, 'name' => 1, 'class' => 2];
        }

        // Process data rows
        for ($i = $headerIndex + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $nis = isset($colMap['nis']) ? trim($row[$colMap['nis']] ?? '') : '';
            $name = isset($colMap['name']) ? trim($row[$colMap['name']] ?? '') : '';

            // Clean NIS and validate
            $nis = preg_replace('/[^0-9]/', '', $nis);
            if (empty($nis) || empty($name)) {
                continue;
            }

            $rawClass = isset($colMap['class']) ? trim($row[$colMap['class']] ?? 'X') : 'X';
            $classGroup = isset($colMap['class_group']) ? trim($row[$colMap['class_group']] ?? '') : null;

            if (!$classGroup && preg_match('/^(XII|XI|X)\s+(.+)$/i', $rawClass, $matches)) {
                $class = strtoupper(trim($matches[1]));
                $classGroup = trim($matches[2]);
            } else {
                $class = strtoupper($rawClass);
            }

            $phone = isset($colMap['phone']) ? trim($row[$colMap['phone']] ?? '') : null;
            $email = isset($colMap['email']) ? strtolower(trim($row[$colMap['email']] ?? '')) : null;

            $students[] = [
                'nis' => $nis,
                'name' => trim(rtrim($name, '* ')),
                'class' => $class,
                'class_group' => $classGroup ?: null,
                'phone' => $phone ?: null,
                'email' => $email ?: null,
            ];
        }

        return $students;
    }

    /**
     * Import parsed student data into the database.
     *
     * @param array $students Parsed student records
     * @param bool $autoActivate Whether to automatically open bank accounts and login accounts (default: false)
     * @param int|null $academicYearId Override academic year ID
     * @return array Summary of import operation
     */
    public function import(array $students, bool $autoActivate = false, ?int $academicYearId = null): array
    {
        if (empty($students)) {
            return [
                'total' => 0,
                'imported' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => ['File tidak memuat data siswa yang valid.'],
            ];
        }

        $activeYearId = $academicYearId ?? AcademicYear::where('is_active', true)->value('id') ?? AcademicYear::orderBy('id', 'desc')->value('id');

        $importedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($students as $idx => $s) {
                $rowNum = $idx + 1;
                $nis = trim($s['nis'] ?? '');
                $name = trim($s['name'] ?? '');
                $class = trim($s['class'] ?? 'X');
                $classGroup = !empty($s['class_group']) ? trim($s['class_group']) : null;
                $phone = !empty($s['phone']) ? trim($s['phone']) : null;
                $email = !empty($s['email']) ? trim($s['email']) : null;

                if (empty($nis) || empty($name)) {
                    $skippedCount++;
                    continue;
                }

                // Check existing customer
                $existing = Customer::where('nis', $nis)->first();

                if ($existing) {
                    // Update identity if changed
                    $existing->update([
                        'name' => $name,
                        'class' => $class,
                        'class_group' => $classGroup,
                        'academic_year_id' => $existing->academic_year_id ?: $activeYearId,
                        'phone' => $phone ?: $existing->phone,
                    ]);

                    // Ensure class history exists for this academic year
                    if ($activeYearId) {
                        CustomerClassHistory::firstOrCreate([
                            'customer_id' => $existing->id,
                            'academic_year_id' => $activeYearId,
                        ], [
                            'class' => $existing->class,
                            'class_group' => $existing->class_group,
                        ]);
                    }

                    // If auto-activate requested and customer has no bank account yet
                    if ($autoActivate && !$existing->bankAccount) {
                        $this->customerService->openAccountForCustomer($existing, ['initial_deposit' => 0]);
                    }

                    $updatedCount++;
                } else {
                    // Create new Customer: Saldo 0, belum memiliki rekening, belum bisa login
                    $customer = Customer::create([
                        'nis' => $nis,
                        'name' => $name,
                        'class' => $class,
                        'class_group' => $classGroup,
                        'academic_year_id' => $activeYearId,
                        'status' => 'active',
                        'phone' => $phone,
                        'email' => null,
                        'pending_email' => $email,
                    ]);

                    if ($activeYearId) {
                        CustomerClassHistory::create([
                            'customer_id' => $customer->id,
                            'academic_year_id' => $activeYearId,
                            'class' => $customer->class,
                            'class_group' => $customer->class_group,
                        ]);
                    }

                    // If auto-activate requested:
                    if ($autoActivate) {
                        $this->customerService->openAccountForCustomer($customer, ['initial_deposit' => 0]);
                    }

                    $importedCount++;
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'total' => count($students),
                'imported' => 0,
                'updated' => 0,
                'skipped' => count($students),
                'errors' => ['Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()],
            ];
        }

        return [
            'total' => count($students),
            'imported' => $importedCount,
            'updated' => $updatedCount,
            'skipped' => $skippedCount,
            'errors' => $errors,
        ];
    }

    /**
     * Read lines from CSV file (supports comma, semicolon, tab).
     */
    protected function readCsvRows(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (! $handle) {
            return [];
        }

        $rows = [];
        $delimiter = ',';

        // Peek first line to detect delimiter (; or ,)
        $firstLine = fgets($handle);
        if ($firstLine !== false) {
            if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
                $delimiter = ';';
            } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
                $delimiter = "\t";
            }
            rewind($handle);
        }

        while (($data = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
            // Strip BOM on first cell if any
            if (!empty($data)) {
                $data[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string)$data[0]);
            }
            $rows[] = $data;
        }

        fclose($handle);
        return $rows;
    }

    /**
     * Read rows from OpenXML Excel file (.xlsx) using ZipArchive without third-party packages.
     */
    protected function readXlsxRows(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        // 1. Read shared strings if present
        $sharedStrings = [];
        $ssXmlContent = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXmlContent !== false) {
            $xml = simplexml_load_string($ssXmlContent);
            if ($xml) {
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string)$si->t;
                    } elseif (isset($si->r)) {
                        $text = '';
                        foreach ($si->r as $r) {
                            $text .= (string)$r->t;
                        }
                        $sharedStrings[] = $text;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // 2. Discover and parse all worksheets in the workbook in order (supports multi-sheet X, XI, XII)
        $sheetFiles = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (preg_match('#^xl/worksheets/sheet\d+\.xml$#', $name)) {
                $sheetFiles[] = $name;
            }
        }
        natsort($sheetFiles);

        if (empty($sheetFiles)) {
            // Fallback: check if sheet1.xml exists
            if ($zip->getFromName('xl/worksheets/sheet1.xml') !== false) {
                $sheetFiles = ['xl/worksheets/sheet1.xml'];
            }
        }

        $allRows = [];
        foreach ($sheetFiles as $sheetName) {
            $sheetXmlContent = $zip->getFromName($sheetName);
            if ($sheetXmlContent === false) {
                continue;
            }

            $xml = simplexml_load_string($sheetXmlContent);
            if (! $xml || ! isset($xml->sheetData)) {
                continue;
            }

            foreach ($xml->sheetData->row as $row) {
                $rowCells = [];
                foreach ($row->c as $c) {
                    $cellRef = (string) $c['r'];
                    $type = (string) $c['t'];
                    $val = '';

                    if ($type === 's') {
                        $idx = (int) $c->v;
                        $val = $sharedStrings[$idx] ?? '';
                    } elseif ($type === 'inlineStr') {
                        $val = (string) $c->is->t;
                    } elseif (isset($c->v)) {
                        $val = (string) $c->v;
                    }

                    // Column letter to index: A->0, B->1, etc.
                    preg_match('/^([A-Z]+)(\d+)$/', $cellRef, $matches);
                    $colLetters = $matches[1] ?? 'A';
                    $colIdx = 0;
                    for ($l = 0; $l < strlen($colLetters); $l++) {
                        $colIdx = $colIdx * 26 + (ord($colLetters[$l]) - 64);
                    }
                    $rowCells[$colIdx - 1] = trim($val);
                }

                if (! empty($rowCells)) {
                    $maxKey = max(array_keys($rowCells));
                    $cleanRow = [];
                    for ($k = 0; $k <= $maxKey; $k++) {
                        $cleanRow[$k] = $rowCells[$k] ?? '';
                    }
                    $allRows[] = $cleanRow;
                }
            }
        }

        $zip->close();
        return $allRows;
    }

    /**
     * Generate template CSV content for user download (Format minimalis: NIS, Nama, Kelas, Rombel).
     */
    public function generateTemplateCsv(): string
    {
        $rows = [
            ['NIS', 'NAMA_SISWA', 'KELAS', 'ROMBEL'],
            ['18910', 'ALLIF MUKHLIS SETYAWAN', 'X', 'RPL 1'],
            ['18911', 'ALUB MASSYAFANY TABA PRASAJA', 'X', 'RPL 1'],
            ['18982', 'ABIMANYU EKA PAMBUDI', 'X', 'TKJ 1'],
            ['19126', 'ADINDA ZAHRA PRAMITA WULANDANI', 'X', 'AK 1'],
            ['17782', 'MUHAMMAD ZAWAATUL AFNAAN', 'XII', 'RPL 1'],
        ];

        $output = fopen('php://temp', 'r+');
        foreach ($rows as $r) {
            fputcsv($output, $r);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}

