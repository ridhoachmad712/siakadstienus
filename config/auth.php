<?php
require_once __DIR__ . '/security.php';
function siakad_mulai_sesi() { if (session_status() === PHP_SESSION_NONE) session_start(); }
function siakad_alihkan($url) {
    if (!headers_sent()) header('Location: ' . $url);
    else echo '<script>window.location=' . json_encode($url, JSON_HEX_TAG | JSON_HEX_AMP) . ';</script>';
    exit;
}
function siakad_hapus_sesi() {
    $_SESSION = array();
    if (session_status() === PHP_SESSION_ACTIVE) session_destroy();
}
function siakad_user_sesi($db) {
    // Sesi lama harus login ulang; sesi baru tidak menyimpan password.
    if (empty($_SESSION['login']) || empty($_SESSION['id_user']) || empty($_SESSION['auth_version'])) return null;
    $user = siakad_baris($db, 'SELECT id_user,username,kode_prodi,level,password FROM user WHERE id_user=?', 'i', array($_SESSION['id_user']));
    if (!$user || !hash_equals(siakad_auth_version($user['password']), (string) $_SESSION['auth_version'])) return null;
    unset($user['password']);
    return $user;
}
function siakad_wajib_login($db, $levels=null, $login='login', $ditolak='dashboard') {
    siakad_mulai_sesi();
    $user = siakad_user_sesi($db);
    if (!$user) { siakad_hapus_sesi(); siakad_alihkan($login); }
    siakad_otorisasi_request($db, $user, $levels);
    foreach (array('username','level','kode_prodi') as $key) $_SESSION[$key]=$user[$key];
    unset($_SESSION['password']);
    return $user;
}
function siakad_wajib_login_ajax($db, $levels=null) {
    siakad_mulai_sesi();
    $user=siakad_user_sesi($db);
    if (!$user) siakad_tolak('Sesi berakhir. Silakan login ulang.',401);
    siakad_otorisasi_request($db,$user,$levels);
    foreach (array('username','level','kode_prodi') as $key) $_SESSION[$key]=$user[$key];
    unset($_SESSION['password']);
    return $user;
}
