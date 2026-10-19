<?php
// Simpan file ini di: /htdoc/projeksaya/public/clear.php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// Atur timezone agar timestamp sesuai dengan waktu lokal Anda (WIB)
date_default_timezone_set('Asia/Jakarta');
$timestamp = date('Y-m-d H:i:s');

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$status = $kernel->handle(
    $input = new Symfony\Component\Console\Input\ArrayInput(['command' => 'optimize:clear']),
    $output = new Symfony\Component\Console\Output\BufferedOutput
);

$commandOutput = $output->fetch();

// 1. Tulis pesan log ke sistem log Laravel (storage/logs/laravel.log)
Log::info("Optimize clear executed via web script.", [
    'timestamp' => $timestamp,
    'output'    => trim(strip_tags($commandOutput))
]);

// 2. Tampilkan hasil ke browser
echo "<h3>Proses Selesai!</h3>";
echo "<p><strong>Waktu Eksekusi:</strong> {$timestamp} WIB</p>";
echo "<hr>";
echo "<h4>Log Output:</h4>";
echo "<pre>" . $commandOutput . "</pre>";
echo "<hr>";
echo "<p style='color: red;'><strong>PENTING:</strong> Segera hapus file <code>clear.php</code> ini demi keamanan website Anda!</p>";
