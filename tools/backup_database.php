<?php
// Backup dari CLI saja. Tidak mengubah database atau menampilkan kredensial.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
ini_set('display_errors', '0');
umask(0077);
$temporary = null;
$output = null;
$success = false;
try {
    $configFile = dirname(__DIR__).'/config/db_config.php';
    if (!is_file($configFile)) throw new RuntimeException('Konfigurasi database belum tersedia.');
    $config = require $configFile;
    foreach (['host','user','password','database'] as $key) {
        if (!isset($config[$key]) || !is_string($config[$key])) throw new RuntimeException('Format konfigurasi database tidak sesuai.');
    }
    $home = getenv('HOME') ?: getenv('USERPROFILE');
    if (!$home) throw new RuntimeException('Folder home tidak ditemukan.');
    $directory = $home.'/backup-siakad';
    if (!is_dir($directory) && !mkdir($directory, 0700, true)) throw new RuntimeException('Folder cadangan tidak dapat dibuat.');
    $directory = realpath($directory);
    if (!$directory || !is_writable($directory)) throw new RuntimeException('Folder cadangan tidak dapat ditulis.');
    $output = $directory.'/database-'.date('Ymd-His').'-'.bin2hex(random_bytes(3)).'.sql';
    // File kredensial sementara berizin 0600; password tidak menjadi argumen proses.
    $temporary = tempnam($directory, '.mysql-client-');
    if (!$temporary) throw new RuntimeException('Konfigurasi sementara tidak dapat dibuat.');
    if (!chmod($temporary, 0600)) throw new RuntimeException('Izin konfigurasi sementara tidak dapat diatur.');
    $options = ['host'=>$config['host'],'user'=>$config['user'],'password'=>$config['password'],'default-character-set'=>'utf8mb4'];
    if (preg_match('/^([^:]+):(\d+)$/D', $config['host'], $parts)) { $options['host']=$parts[1]; $options['port']=$parts[2]; }
    if (isset($config['port'])) $options['port']=(string)$config['port'];
    $text = "[client]\n";
    foreach ($options as $key=>$value) $text .= $key.'="'.str_replace(["\\",'"',"\n","\r"],["\\\\",'\\"','\\n','\\r'], $value)."\"\n";
    if (file_put_contents($temporary, $text) === false) throw new RuntimeException('Konfigurasi sementara tidak dapat ditulis.');
    $binary = getenv('SIAKAD_MYSQLDUMP') ?: 'mysqldump';
    $arguments = [$binary,'--defaults-extra-file='.$temporary,'--single-transaction','--quick','--skip-lock-tables','--no-tablespaces','--routines','--events','--triggers','--result-file='.$output,'--databases',$config['database']];
    $command = implode(' ',array_map('escapeshellarg',$arguments));
    passthru($command,$status);
    clearstatcache(true,$output);
    if ($status !== 0 || !is_file($output) || filesize($output) === 0) throw new RuntimeException('Backup gagal. Jangan lanjutkan migrasi; kirim pesan error tanpa kredensial.');
    if (!chmod($output,0600)) throw new RuntimeException('Izin cadangan tidak dapat diatur.');
    $success = true;
    echo "BACKUP DATABASE BERHASIL\n".$output."\nUkuran: ".number_format(filesize($output))." byte\nSHA-256: ".hash_file('sha256',$output)."\n";
} catch (Throwable $error) {
    fwrite(STDERR,"Backup tidak selesai. Database tetap tidak diubah.\n");
} finally {
    if ($temporary && is_file($temporary)) unlink($temporary);
    if (!$success && $output && is_file($output)) unlink($output);
}
exit($success ? 0 : 1);
