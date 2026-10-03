<?php
/**
 * Contoh konfigurasi koneksi database.
 * ------------------------------------------------------------------
 * SALIN file ini menjadi config/db_config.php lalu isi dengan data asli.
 * config/db_config.php sengaja tidak ikut ke dalam git (lihat .gitignore)
 * supaya kredensial database tidak tersimpan di repository.
 *
 * Saat deploy ke hosting, file config/db_config.php harus ikut diupload
 * (lewat FTP / File Manager), karena file ini tidak ada di repository.
 */
return array(
	'host'     => 'localhost',
	'user'     => 'nama_user_database',
	'password' => 'password_database',
	'database' => 'nama_database',
);
