<?php
// Backup dari CLI saja. Tidak mengubah database atau menampilkan kredensial.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
ini_set('display_errors', '0');
umask(0077);
$temporary = null;
$output = null;
$success = false;
$prepared = false;
$stage = 'membaca konfigurasi';
$problem = null;
function backup_problem($message) { global $problem; $problem=$message; throw new RuntimeException('Backup dihentikan.'); }
try {
    $configFile = dirname(__DIR__).'/config/db_config.php';
    if (!is_file($configFile)) backup_problem('Konfigurasi database belum tersedia.');
    $config = require $configFile;
    if (!is_array($config)) backup_problem('db_config.php harus mengembalikan array konfigurasi.');
    foreach (['host','user','password','database'] as $key) {
        if (!isset($config[$key]) || !is_string($config[$key])) backup_problem('Konfigurasi memerlukan kolom teks: '.$key.'.');
    }
    $stage = 'menyiapkan folder cadangan';
    $home = getenv('HOME') ?: getenv('USERPROFILE');
    if (!$home) backup_problem('Variabel HOME tidak tersedia untuk PHP CLI.');
    $directory = $home.'/backup-siakad';
    if (!is_dir($directory) && !mkdir($directory, 0700, true)) backup_problem('Folder cadangan tidak dapat dibuat.');
    $directory = realpath($directory);
    if (!$directory || !is_writable($directory)) backup_problem('Folder cadangan tidak dapat ditulis.');
    $output = $directory.'/database-'.date('Ymd-His').'-'.bin2hex(random_bytes(3)).'.sql';
    $stage = 'menyiapkan konfigurasi sementara';
    // File kredensial sementara berizin 0600; password tidak menjadi argumen proses.
    $temporary = tempnam($directory, '.mysql-client-');
    if (!$temporary) backup_problem('Konfigurasi sementara tidak dapat dibuat.');
    if (!chmod($temporary, 0600)) backup_problem('Izin konfigurasi sementara tidak dapat diatur.');
    $options = ['host'=>$config['host'],'user'=>$config['user'],'password'=>$config['password'],'default-character-set'=>'utf8mb4'];
    if (preg_match('/^([^:]+):(\d+)$/D', $config['host'], $parts)) { $options['host']=$parts[1]; $options['port']=$parts[2]; }
    if (isset($config['port'])) $options['port']=(string)$config['port'];
    $text = "[client]\n";
    foreach ($options as $key=>$value) $text .= $key.'="'.str_replace(["\\",'"',"\n","\r"],["\\\\",'\\"','\\n','\\r'], $value)."\"\n";
    if (file_put_contents($temporary, $text) === false) backup_problem('Konfigurasi sementara tidak dapat ditulis.');
    $binary = getenv('SIAKAD_MYSQLDUMP') ?: 'mysqldump';
    $arguments = [$binary,'--defaults-extra-file='.$temporary,'--single-transaction','--quick','--skip-lock-tables','--no-tablespaces','--routines','--events','--triggers','--result-file='.$output,'--databases',$config['database']];
    if (in_array('--prepare',$argv,true)) {
        $stage = 'menyiapkan skrip SSH';
        $quote = fn($value)=>"'".str_replace("'","'\\''",$value)."'";
        $shellCommand=implode(' ',array_map($quote,$arguments));
        $launcher=$directory.'/jalankan-backup-database.sh';
        $script="#!/usr/bin/env bash\nset -eu\numask 077\n";
        $script.='credentials='.$quote($temporary)."\noutput=".$quote($output)."\n";
        $script.="if [ -e \"\$output\" ]; then printf 'File cadangan sudah ada; tidak ditimpa.\\n'; exit 1; fi\n";
        $script.="success=0\ncleanup() { rm -f -- \"\$credentials\"; if [ \"\$success\" -ne 1 ]; then rm -f -- \"\$output\"; fi; }\ntrap cleanup EXIT\n";
        $script.=$shellCommand."\n";
        $script.="test -s \"\$output\"\nchmod 600 \"\$output\"\nsuccess=1\nprintf 'BACKUP DATABASE BERHASIL\\n%s\\n' \"\$output\"\nwc -c < \"\$output\"\nsha256sum -- \"\$output\"\n";
        if (file_put_contents($launcher,$script)===false || !chmod($launcher,0700)) backup_problem('Skrip backup tidak dapat disimpan.');
        $prepared=true;
        echo "PERSIAPAN BACKUP BERHASIL\nJalankan: bash ". $quote($launcher)."\n";
    } else {
    $stage = 'menjalankan mysqldump';
    if (!function_exists('passthru')) backup_problem('Hosting menonaktifkan passthru. Gunakan mode --prepare dan jalankan skrip melalui SSH.');
    $command = implode(' ',array_map('escapeshellarg',$arguments));
    passthru($command,$status);
    clearstatcache(true,$output);
    if ($status !== 0 || !is_file($output) || filesize($output) === 0) backup_problem('Backup gagal. Jangan lanjutkan migrasi; kirim pesan error tanpa kredensial.');
    if (!chmod($output,0600)) backup_problem('Izin cadangan tidak dapat diatur.');
    $success = true;
    echo "BACKUP DATABASE BERHASIL\n".$output."\nUkuran: ".number_format(filesize($output))." byte\nSHA-256: ".hash_file('sha256',$output)."\n";
    }
} catch (Throwable $error) {
    fwrite(STDERR,"Backup tidak selesai pada tahap: ".$stage.".\n".($problem?$problem."\n":'')."Database tetap tidak diubah.\n");
} finally {
    if (!$prepared && $temporary && is_file($temporary)) unlink($temporary);
    if (!$success && $output && is_file($output)) unlink($output);
}
exit($success || $prepared ? 0 : 1);
