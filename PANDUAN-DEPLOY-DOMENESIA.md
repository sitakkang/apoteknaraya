# PANDUAN DEPLOY KE HOSTING DOMAINESIA

Aplikasi: **Apotek Naraya** — CodeIgniter **3.1.13**, PHP + MySQL/MariaDB
Target: hosting cPanel DomaiNesia (`public_html`), sampai aplikasi siap dipakai.

> ⚠️ **Sebelum upload ke `public_html`, jangan sertakan file/folder ini:**
> `db_apoteknaraya.sql`, `db_core_v4.sql`, `INITIATE PROJECT TRUNCATE TABLE.sql`,
> `generate_password.php`, folder `sys_backup/`, folder `.git/`, file zip, dan panduan ini.
> File `.sql` bisa diunduh siapa saja lewat browser — isinya seluruh data pasien.

---

## 0. Yang sudah disiapkan & info yang perlu Anda kumpulkan

**Sudah disiapkan di komputer Anda:**

| Item | Lokasi |
|---|---|
| Dump database (1,93 MB, 27 tabel, sudah ada `DROP TABLE` + `CREATE TABLE`) | `c:\xampp\mysql\backup\db_narayaapotek_20260917.sql` |
| Script reset data transaksi (untuk serah terima bersih) | `INITIATE PROJECT TRUNCATE TABLE.sql` |

**Yang perlu dicatat dari cPanel DomaiNesia nanti:**

- [ ] Nama domain yang dipakai
- [ ] Nama database (biasanya berprefix akun, contoh `userabc_apotek`)
- [ ] Username database
- [ ] Password database
- [ ] Host database → **`localhost`** (di cPanel DomaiNesia hampir selalu `localhost`)

**Kebutuhan server (sudah dicek dari kode aplikasi):**

| Kebutuhan | Dipakai oleh |
|---|---|
| PHP 5.6–7.4 (**disarankan 7.4**) | CodeIgniter 3.1.13 |
| Ekstensi `mysqli` | koneksi database |
| Ekstensi `gd` (fungsi `ImagePng`) | QR Code di SKS/SKBS/SKMB/SKKB |
| Ekstensi `mbstring`, `json` | helper & library CI |
| Folder yang bisa ditulis PHP | `app/sessions/`, `app/cache/`, `app/logs/`, `img/temp-qrcode/`, `img/avatar/` |

---

## 1. Isolasi sesi (SUDAH saya terapkan — pembeda dari project asal)

Karena aplikasi ini clone, dua hal ini **wajib berbeda** dari project asal, kalau tidak
login di aplikasi satu akan menendang sesi aplikasi lain (browser mengirim cookie sesi
yang sama ke keduanya). Sudah diubah di `app/config/config.php`:

```php
$config['sess_cookie_name'] = 'narayaapotek_sess';   // project asal: imip_core4
$config['sess_save_path']   = APPPATH.'sessions';    // project asal: sys_get_temp_dir()
$config['encryption_key']   = 'A7F3C1D9E4B8062A5C7D1E9F3B6A8C40';  // unik per aplikasi
```

Bukti sudah jalan (dites di lokal): file sesi terbentuk di `app/sessions/narayaapotek_sess...`.

> Kalau nanti Anda clone lagi untuk klien lain, ganti 3 nilai di atas (nama cookie,
> folder sesi, encryption key) supaya tidak saling bertabrakan.

---

## 2. Persiapan di komputer lokal

1. Buat folder kerja baru, misalnya `Kirim-Domainesia/`, lalu salin isi project ke dalamnya.

2. **Keluarkan** item berikut dari folder kiriman:
   - `sys_backup/` (salinan CI lama, tidak dipakai aplikasi)
   - `.git/` dan `.gitignore`
   - `db_apoteknaraya.sql`, `db_core_v4.sql`, `INITIATE PROJECT TRUNCATE TABLE.sql`, `generate_password.php`
   - `PANDUAN-DEPLOY-DOMENESIA.md`

3. Isi folder kiriman seharusnya seperti ini:

```
index.php
.htaccess
app/        (config, controllers, models, views, libraries, sessions, cache, logs)
img/        (avatar, temp-qrcode, noimage.png, loading.gif)
lib/        (bootstrap, datatables, jquery, fontawesome, dll)
src/        (css, js, json/main_menu.json, sub_menu.json)
sys/        (core CodeIgniter — JANGAN diubah/dibuang)
```

4. Sebelum di-zip, cek 3 file konfigurasi ini (detailnya di bagian 6–8):
   - `index.php` → `ENVIRONMENT`
   - `app/config/database.php` → kredensial DB
   - `app/config/config.php` → sudah beres (bagian 1)

5. Zip seluruh isi folder kiriman menjadi **`apoteknaraya.zip`**.

---

## 3. Login cPanel & upload project

**Login cPanel:** `https://namadomain.com/cpanel` (atau dari MyDomaiNesia → menu Hosting → Login cPanel).

Pilih salah satu cara:

**Cara A — File Manager (paling mudah)**
1. cPanel → **File Manager** → masuk folder `public_html`.
2. Klik **Upload** → pilih `apoteknaraya.zip` → tunggu sampai 100% → **Go Back**.
3. Klik kanan `apoteknaraya.zip` → **Extract** → hasilnya menaruh `index.php`, `app/`, `lib/`, `src/`, `sys/`, `img/` langsung di `public_html`.
4. Hapus `apoteknaraya.zip`.

> Kalau `public_html` sudah berisi situs lain, buat subfolder (mis. `public_html/apotek`)
> atau pakai subdomain, lalu tambahkan `RewriteBase` (lihat bagian 8).

**Cara B — SSH** (kalau paket Anda ada SSH): `scp apoteknaraya.zip user@server:~/` → `unzip` → pindahkan isi ke `public_html`.

**Cara C — Jasa migrasi DomaiNesia**: DomaiNesia menyediakan migrasi gratis; bisa dipakai kalau Anda tidak mau upload manual.

Verifikasi struktur lewat File Manager: `public_html/index.php` harus ada, dan ada folder `app`.

---

## 4. Buat database & user-nya (cPanel → MySQL® Databases)

1. **Create New Database** → nama: `apotek` → cPanel menambah prefix akun → jadi mis. `userabc_apotek`. **Catat nama lengkapnya.**
2. **Add New User** → username: `apotek` → cPanel jadikan `userabc_apotek` → password: pakai yang kuat → **Catat.**
3. **Add User To Database** → pilih user + database di atas → **ALL PRIVILEGES** → Make Changes.
4. Host database: **`localhost`**.

---

## 5. Import database

1. cPanel → **phpMyAdmin** → klik nama database `userabc_apotek` di kiri.
2. Tab **Import** → **Choose File** → pilih `db_narayaapotek_20260917.sql` → **Go**.
3. Tunggu sampai muncul "Import has been successfully finished".
4. Cek: di kiri harus muncul 27 tabel, termasuk `conf_users`, `conf_level`, `conf_menu`, `ms_diagnosa`, `trans_visit`, `sks`, `skbs`, `skmb`.

Kalau phpMyAdmin menolak karena ukuran:
- Kompres dulu jadi `.zip`/`.gz` (phpMyAdmin menerima file terkompres), **atau**
- Import per bagian (potong per ~2 MB), **atau**
- Minta bantuan support DomaiNesia (live chat 24 jam).

> **Untuk serah terima ke klien dengan data kosong:** setelah import berhasil, buka tab
> **SQL** di phpMyAdmin dan jalankan isi `INITIATE PROJECT TRUNCATE TABLE.sql`
> (10 tabel transaksi dikosongkan; master data seperti user, level, menu, diagnosa, wilayah tetap ada).

---

## 6. Update akses database di aplikasi

Buka **File Manager** → `public_html/app/config/database.php` → **Edit** → ubah 4 baris ini:

```php
$db['default'] = array(
	'dsn'	=> '',
	'hostname' => 'localhost',                 // biarkan localhost
	'username' => 'userabc_apotek',            // ← username database dari cPanel
	'password' => 'PasswordKuatAnda',          // ← password database dari cPanel
	'database' => 'userabc_apotek',            // ← nama database lengkap (dengan prefix)
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),   // otomatis FALSE saat production
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
```

Simpan. Tidak ada baris lain yang perlu diubah — `base_url` di `app/config/config.php`
sudah otomatis mengikuti domain yang dipakai, jadi tidak perlu ditulis manual.

---

## 7. Set mode production (matikan tampilan error)

Buka `public_html/index.php` → cari baris (sekitar baris 57):

```php
define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');
```

Ubah menjadi:

```php
define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'production');
```

Efeknya: pesan error PHP tidak tampil ke pengunjung, log disimpan di `app/logs/`,
dan `db_debug` otomatis mati (pesan error SQL tidak bocor ke halaman).

**Alternatif tanpa mengubah `index.php`** — tambahkan di `.htaccess`:

```apache
SetEnv CI_ENV production
```

---

## 8. Versi PHP, ekstensi, permission, dan `.htaccess`

**a. Versi PHP** — cPanel → **MultiPHP Manager** (atau **Select PHP Version**) → pilih domain → **PHP 7.4**
(kalau hosting hanya menyediakan 8.x, aplikasi masih bisa jalan tapi akan banyak
peringatan *deprecated* dari CI 3.1.13; lebih aman minta support mengaktifkan 7.4).

**b. Ekstensi** — di halaman Select PHP Version → **Extensions**, pastikan tercentang:
`mysqli`, `gd`, `mbstring`, `json`, `zip`, `curl`.

**c. Permission folder** — File Manager → klik kanan folder → **Change Permissions**:

| Folder | Permission |
|---|---|
| `app/sessions` | 755 (kalau error, 775) |
| `app/cache` | 755 |
| `app/logs` | 755 |
| `img/temp-qrcode` | 755 |
| `img/avatar` | 755 |

File lain biarkan 644. Tidak perlu 777.

**d. `.htaccess`** — file `.htaccess` bawaan project sudah benar untuk domain utama
(`RewriteRule ^(.*)$ ./index.php/$1 [L,QSA]`). Tambahan yang disarankan:

```apache
# Hapus bila bukan di subfolder
# RewriteBase /

# Opsional: buang 2 baris sisa percobaan hotlink yang mengarah ke localhost
# RewriteCond %{HTTP_REFERER} !^$
# RewriteCond %{HTTP_REFERER} !^http://(www\.)?localhost/.*$ [NC]
```

Kalau aplikasi diletakkan di **subfolder** (mis. `public_html/apotek`), tambahkan
`RewriteBase /apotek/` dan `DirectoryIndex index.php`.

---

## 9. Uji coba sampai "siap pakai" (checklist)

1. [ ] Buka `https://namadomain.com/` → muncul **halaman login** (bukan error 500 / halaman kosong)
2. [ ] Login dengan akun dari tabel `conf_users` (mis. `adminutama` / Superadmin)
3. [ ] **Dashboard** tampil + kartu SKS / SKBS / SKMB bulan ini muncul
4. [ ] Menu **Pengguna** → daftar user tampil
5. [ ] Menu **Pendaftaran** → daftarkan 1 pasien uji
6. [ ] Menu **Anamnesa** → kolom Dokter / Diagnosa / Obat / SKS / SKBS / SKMB (tombol modal) bisa dibuka & simpan data
7. [ ] Menu **Dokter** → halaman pemeriksaan pasien bisa dibuka
8. [ ] **Cetak SKS / SKBS / SKMB** → QR Code muncul (bagian ini butuh ekstensi GD + folder `img/temp-qrcode` writable)
9. [ ] Scan QR dari HP → halaman verifikasi (`sks_validate/verify/...`) terbuka & menampilkan data pasien
10. [ ] Ganti password user → berhasil, lalu login ulang
11. [ ] Cek `app/logs/` → kalau ada file log berisi error, kirim ke saya

Kalau semua tercentang, aplikasi **siap pakai**.

---

## 10. Keamanan — wajib dilakukan sebelum diserahkan

1. **Hapus file sensitif dari `public_html`:**
   - `db_apoteknaraya.sql`, `db_core_v4.sql` → berisi SELURUH data pasien, bisa diunduh bebas
   - `INITIATE PROJECT TRUNCATE TABLE.sql` → memperlihatkan struktur tabel
   - `generate_password.php` → memperlihatkan cara hash password + password default (`adminutama`)
   - `sys_backup/` → tidak dipakai, buang supaya tidak ada 2 versi framework
   - `.git/` → riwayat kode bisa diunduh orang lain
   - `PANDUAN-DEPLOY-DOMENESIA.md` dan file zip sisaupload

2. **Aktifkan SSL**: cPanel → **SSL/TLS Status** → **Run AutoSSL** → pastikan `https://` aktif
   (DomaiNesia menyediakan SSL gratis). Setelah SSL stabil, di `app/config/config.php` boleh diubah:

   ```php
   $config['cookie_secure'] = TRUE;   // hanya kirim cookie lewat HTTPS
   ```

   ⚠️ Jangan diaktifkan sebelum SSL benar-benar jalan, nanti login gagal.

3. **Ganti password bawaan** semua akun di menu Pengguna, dan hapus akun yang tidak dipakai.

4. **Blokir akses file yang tidak perlu** (opsional, tambahkan di `.htaccess`:

   ```apache
   RedirectMatch 403 ^/.*\.(sql|md|log|zip)$
   ```

5. **Backup rutin**: cPanel → **Backup** (Home Directory + Databases), atau minta
   DomaiNesia menjadwalkan backup harian (biasanya sudah termasuk di paket).

---

## 11. Troubleshooting

| Gejala | Penyebab paling mungkin & solusinya |
|---|---|
| **HTTP 500** di semua halaman | Lihat `app/logs/`. Umumnya: versi PHP tidak cocok, ekstensi kurang, atau `.htaccess` tidak didukung. Cek juga permission folder. |
| Halaman **putih/kosong** | `ENVIRONMENT` masih `development` tapi `display_errors` mati, atau ada fatal error. Cek log di `app/logs/`. |
| **"Unable to connect to your database"** | Username/password/nama database salah, atau user belum di-*Add User To Database*, atau hostname bukan `localhost`. |
| **Login berhasil tapi kembali ke halaman login terus** | Folder `app/sessions` tidak writable (set 755/775), **atau** nama cookie sesi sama dengan aplikasi lain di domain yang sama. |
| Bisa login lalu **tiba-tiba logout sendiri** | `sess_expiration` = 3600 (1 jam) — memang begitu; jika mengganggu naikkan nilainya. |
| **404 di semua menu** (URL tanpa `index.php` gagal) | `mod_rewrite`/`.htaccess` tidak jalan. Solusi cepat: set `$config['index_page'] = 'index.php';` di `app/config/config.php` (URL jadi memuat `index.php`). |
| **QR Code tidak muncul** saat cetak | Ekstensi `gd` belum aktif, atau `img/temp-qrcode/` tidak writable. |
| **Import SQL gagal** | Kompres ke `.gz`, atau potong jadi beberapa bagian, atau hubungi support DomaiNesia. |
| Preview cetak **terpotong/miring** | Bukan masalah hosting — atur ukuran kertas A4 & margin saat print (Ctrl+P). |
| Kapasitas hosting penuh | Bersihkan `app/logs/`, `app/cache/`, `img/temp-qrcode/` (isi QR lama boleh dihapus). |

---

## 12. Kalau nanti clone lagi untuk klien lain

Ubah 3 hal ini di `app/config/config.php`, dan buat database baru:

```php
$config['sess_cookie_name'] = 'narayaapotek2_sess';   // nama unik
$config['sess_save_path']   = APPPATH.'sessions';     // sudah otomatis khusus aplikasi ini
$config['encryption_key']   = '<32 karakter hex unik>';
```

Lalu ulangi bagian 3–9 dengan nama database baru. Dengan begitu, aplikasi-aplikasi
klien Anda bisa berjalan di satu hosting yang sama tanpa saling mengganggu sesi.
