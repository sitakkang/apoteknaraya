<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| KONFIGURASI DATABASE — TEMPLATE (sengaja di-commit ke repo)
|--------------------------------------------------------------------------
| File `database.php` TIDAK di-commit (lihat .gitignore) supaya setiap server
| punya kredensial sendiri dan tidak bentrok saat git pull.
|
| Cara pakai di server setelah `git clone`:
|
|     cp app/config/database.sample.php app/config/database.php
|
| lalu ubah 3 nilai bertanda GANTI_* di bawah sesuai cPanel hosting.
| Di cPanel (termasuk DomaiNesia) hostname hampir selalu 'localhost'.
|--------------------------------------------------------------------------
*/

$active_group = 'default';
$query_builder = TRUE;

$db['default'] = array(
	'dsn'	=> '',
	'hostname' => 'localhost',
	'username' => 'GANTI_USER_DATABASE',
	'password' => 'GANTI_PASSWORD_DATABASE',
	'database' => 'GANTI_NAMA_DATABASE',
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);
