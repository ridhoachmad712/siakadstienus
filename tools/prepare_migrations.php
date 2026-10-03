<?php
// Hanya menyiapkan skrip; perubahan database dilakukan saat skrip SSH dijalankan.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
ini_set('display_errors','0');
umask(0077);
$temporary=null;
$prepared=false;
$stage='memeriksa cadangan';
try {
    $home=getenv('HOME') ?: getenv('USERPROFILE');
    if (!$home) throw new RuntimeException();
    $directory=realpath($home.'/backup-siakad');
    if (!$directory || !is_writable($directory)) throw new RuntimeException();
    $backups=glob($directory.'/database-*.sql');
    if (!$backups || !array_filter($backups,fn($file)=>is_file($file) && filesize($file)>0)) throw new RuntimeException();
    $root=dirname(__DIR__);
    $files=['2026-10-03_integritas_akademik.sql','2026-10-03_krs_semester.sql'];
    foreach ($files as $file) if (!is_file($root.'/database/migrations/'.$file)) throw new RuntimeException();
    $stage='membaca konfigurasi';
    $config=require $root.'/config/db_config.php';
    if (!is_array($config)) throw new RuntimeException();
    foreach (['host','user','password','database'] as $key) if (!isset($config[$key]) || !is_string($config[$key])) throw new RuntimeException();
    $stage='menyiapkan skrip';
    // Berkas biasa dibuat eksklusif; hindari pembersihan tempnam oleh hosting.
    $temporary=$directory.'/mysql-migration-'.bin2hex(random_bytes(12)).'.cnf';
    $handle=fopen($temporary,'x');
    if (!$handle) throw new RuntimeException();
    fclose($handle);
    if (!chmod($temporary,0600)) throw new RuntimeException();
    $options=['host'=>$config['host'],'user'=>$config['user'],'password'=>$config['password'],'default-character-set'=>'utf8mb4'];
    if (preg_match('/^([^:]+):(\d+)$/D',$config['host'],$parts)) { $options['host']=$parts[1]; $options['port']=$parts[2]; }
    if (isset($config['port'])) $options['port']=(string)$config['port'];
    $ini="[client]\n";
    foreach ($options as $key=>$value) $ini.=$key.'="'.str_replace(["\\",'"',"\n","\r"],["\\\\",'\\"','\\n','\\r'],$value)."\"\n";
    if (file_put_contents($temporary,$ini)===false) throw new RuntimeException();
    $quote=fn($value)=>"'".str_replace("'","'\\''",$value)."'";
    $script="#!/usr/bin/env bash\nset -eu\numask 077\ncredentials=".$quote($temporary)."\n";
    $script.="cleanup() { rm -f -- \"\$credentials\"; }\ntrap cleanup EXIT\n";
    $script.='php '.$quote($root.'/tools/check_database.php')."\n";
    $script.="if [ ! -r \"\$credentials\" ]; then printf 'File koneksi migrasi tidak tersedia. Jalankan prepare_migrations.php kembali.\\n'; exit 1; fi\n";
    $client=implode(' ',array_map($quote,['mysql','--defaults-extra-file='.$temporary,'--database='.$config['database']]));
    foreach ($files as $file) {
        $script.="printf '%s\\n' ".$quote('Menjalankan '.$file)."\n";
        $script.=$client.' < '.$quote($root.'/database/migrations/'.$file)."\n";
    }
    $script.='php '.$quote($root.'/tools/check_database.php')."\n";
    $script.="printf 'MIGRASI DATABASE BERHASIL\\n'\n";
    $launcher=$directory.'/jalankan-migrasi-siakad.sh';
    if (file_put_contents($launcher,$script)===false || !chmod($launcher,0700)) throw new RuntimeException();
    $prepared=true;
    echo "PERSIAPAN MIGRASI BERHASIL\nJalankan: bash ".$quote($launcher)."\nDatabase belum diubah.\n";
} catch (Throwable $error) {
    fwrite(STDERR,'Persiapan gagal pada tahap: '.$stage.". Database belum diubah.\n");
} finally {
    if (!$prepared && $temporary && is_file($temporary)) unlink($temporary);
}
exit($prepared?0:1);
