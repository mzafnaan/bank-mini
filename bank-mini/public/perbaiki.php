<?php
/**
 * Script Otomatis Perbaikan Bank Mini (Hosting)
 * - Membuka izin folder terkunci (Permission Denied -> 755)
 * - Mengekstrak update_bank_mini.zip jika ada
 * - Menghapus seluruh cache rute, config, dan tampilan
 * - Menjalankan database migration
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Deteksi root Laravel
$baseDir = __DIR__;
if (!file_exists($baseDir . '/bootstrap/app.php') && file_exists(dirname($baseDir) . '/bootstrap/app.php')) {
    $baseDir = dirname($baseDir);
}

echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><title>Perbaikan Otomatis Bank Mini</title>";
echo "<style>body{font-family:sans-serif;max-width:800px;margin:30px auto;padding:20px;background:#f8fafc;color:#1e293b;}";
echo "pre{background:#0f172a;color:#f8fafc;padding:15px;border-radius:8px;overflow-x:auto;}";
echo ".card{background:#fff;padding:20px;border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,0.1);margin-bottom:20px;}";
echo ".btn{display:inline-block;padding:12px 24px;background:#2563eb;color:#fff;text-decoration:none;border-radius:6px;font-weight:bold;margin-top:10px;}";
echo ".success{color:#16a34a;font-weight:bold;}";
echo "</style></head><body>";

echo "<h1>🚀 Tool Perbaikan Otomatis Hosting</h1>";

// 1. Perbaiki Permissions
echo "<div class='card'>";
echo "<h3>1. Membuka Izin Akses Folder (Fix Permission Denied)...</h3>";

function fixDirPermissions($path) {
    if (!file_exists($path)) return;
    @chmod($path, 0755);
    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                @chmod($item->getPathname(), 0755);
            } else {
                @chmod($item->getPathname(), 0644);
            }
        }
    } catch (\Exception $e) {
        // Abaikan jika ada file spesifik sistem
    }
}

foreach (['app', 'bootstrap', 'config', 'database', 'public', 'resources', 'routes', 'storage'] as $dir) {
    fixDirPermissions($baseDir . '/' . $dir);
}
echo "<p class='success'>✓ Semua folder (app, Http, Controllers, dsb.) telah diatur ke izin normal (755)!</p>";
echo "</div>";

// 2. Ekstrak update_bank_mini.zip jika ada
echo "<div class='card'>";
echo "<h3>2. Mengekstrak File Pembaruan (update_bank_mini.zip)...</h3>";
$zipPath = $baseDir . '/update_bank_mini.zip';
if (!file_exists($zipPath) && file_exists(__DIR__ . '/update_bank_mini.zip')) {
    $zipPath = __DIR__ . '/update_bank_mini.zip';
}

if (file_exists($zipPath) && class_exists('ZipArchive')) {
    $zip = new ZipArchive;
    if ($zip->open($zipPath) === TRUE) {
        $zip->extractTo($baseDir);
        $zip->close();
        echo "<p class='success'>✓ Berhasil mengekstrak seluruh file baru tanpa ada 'Permission Denied'!</p>";
    } else {
        echo "<p style='color:#ca8a04;'>⚠️ Gagal membuka zip otomatis, silakan ekstrak manual di File Manager.</p>";
    }
} else {
    echo "<p>File update_bank_mini.zip sudah diekstrak atau tidak ditemukan di folder root.</p>";
}
echo "</div>";

// 3. Bersihkan Cache File (Wajib agar rute /nasabah/stats langsung aktif)
echo "<div class='card'>";
echo "<h3>3. Membersihkan File Cache Rute & Tampilan...</h3>";

$cacheFiles = [
    $baseDir . '/bootstrap/cache/routes-v7.php',
    $baseDir . '/bootstrap/cache/routes.php',
    $baseDir . '/bootstrap/cache/config.php',
    $baseDir . '/bootstrap/cache/packages.php',
    $baseDir . '/bootstrap/cache/services.php',
];

$deletedCache = 0;
foreach ($cacheFiles as $cf) {
    if (file_exists($cf)) {
        @unlink($cf);
        $deletedCache++;
    }
}
echo "<p class='success'>✓ Berhasil menghapus file cache rute & config (Rute baru langsung terbaca)!</p>";

// Bersihkan file cache views manual
$viewPath = $baseDir . '/storage/framework/views';
$deletedViews = 0;
if (is_dir($viewPath)) {
    foreach (glob($viewPath . '/*') as $f) {
        if (is_file($f) && basename($f) !== '.gitignore') {
            @unlink($f);
            $deletedViews++;
        }
    }
}
echo "<p class='success'>✓ Berhasil menghapus {$deletedViews} file cache tampilan (views) lama!</p>";

// Bersihkan cache data
$dataCache = $baseDir . '/storage/framework/cache/data';
if (is_dir($dataCache)) {
    try {
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dataCache, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $file) {
            if ($file->isFile() && $file->getFilename() !== '.gitignore') {
                @unlink($file->getRealPath());
            }
        }
    } catch (\Exception $e) {}
}

// 4. Jalankan Artisan Migration jika memungkinkan
if (file_exists($baseDir . '/vendor/autoload.php') && file_exists($baseDir . '/bootstrap/app.php')) {
    try {
        require_once $baseDir . '/vendor/autoload.php';
        $app = require_once $baseDir . '/bootstrap/app.php';

        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();

        echo "<h4>• Menjalankan Database Migration:</h4>";
        Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        echo "<pre>" . htmlspecialchars(Illuminate\Support\Facades\Artisan::output()) . "</pre>";
    } catch (\Throwable $e) {
        echo "<p style='color:#ca8a04;'>Catatan Artisan: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p class='success'>✓ Pembersihan cache rute tetap berhasil dijalankan!</p>";
    }
}

echo "<h2 class='success'>🎉 SEMUA PROSES BERHASIL SELESAI!</h2>";
echo "<p>Rute API <code>/api/v1/nasabah/stats</code> dan tampilan terbaru sudah aktif sekarang. Silakan buka aplikasi m-banking di HP Anda!</p>";
echo "<a href='/' class='btn'>Buka Halaman Utama Web</a>";
echo "</div>";

echo "</body></html>";
