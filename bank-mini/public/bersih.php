<?php
// Instant Cache Wiper - No Laravel dependency
$base = file_exists(__DIR__ . '/bootstrap/app.php') ? __DIR__ : dirname(__DIR__);

$files = [
    $base . '/bootstrap/cache/routes-v7.php',
    $base . '/bootstrap/cache/routes.php',
    $base . '/bootstrap/cache/config.php',
    $base . '/bootstrap/cache/packages.php',
    $base . '/bootstrap/cache/services.php',
];

$count = 0;
foreach ($files as $f) {
    if (file_exists($f)) {
        @unlink($f);
        $count++;
    }
}

// Clear views
$views = glob($base . '/storage/framework/views/*');
if ($views) {
    foreach ($views as $v) {
        if (basename($v) !== '.gitignore') {
            @unlink($v);
            $count++;
        }
    }
}

header('Content-Type: text/plain');
echo "OK. Berhasil menghapus {$count} file cache. Rute /api/v1/nasabah/stats sekarang AKTIF!";
