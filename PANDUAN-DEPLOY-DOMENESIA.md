# Panduan Deploy Apotek Naraya ke DomaiNesia — via Git Clone

| Item | Nilai |
|---|---|
| **Domain produksi** | `https://apotek-naraya.com` |
| **Repository** | `https://github.com/sitakkang/apoteknaraya.git` |
| **Branch** | `main` |
| **Framework** | CodeIgniter 3.1.13 (PHP 7.4) |
| **Database aplikasi** | `db_narayaapotek` (27 tabel) |
| **File dump untuk server** | `C:\xampp\mysql\backup\db_narayaapotek_20260917.sql` (1,93 MB) |

> Cara kerja singkat: **kode tidak di-upload manual**, tetapi di-`git clone` langsung di server dari GitHub.
> Update berikutnya cukup `git push` dari komputer → klik **Deploy** di cPanel.

```mermaid
flowchart LR
    A[PC lokal<br>edit kode] -->|git push| B[GitHub<br>repo private]
    B -->|cPanel Git Version Control<br>Update from Remote| C[Server DomaiNesia<br>clone di server]
    C -->|Deploy HEAD Commit| D[public_html<br>apotek-naraya.com]
    D --> E[(Database<br>DB_apotek)]
```

---

## 1. ⚠️ WAJIB DIBACA DULU: Amankan repo GitHub

Repo `sitakkang/apoteknaraya` saat ini masih **PUBLIC** dan riwayat commit-nya masih menyimpan file sensitif
(`db_apoteknaraya.sql` = data pasien, `generate_password.php`, `sys_backup/`).
Artinya: siapa pun yang tahu URL repo bisa mengunduh data pasien dari riwayat commit, **walaupun file itu sudah tidak dilacak lagi**.

Urutan yang benar: **jadikan private → bersihkan riwayat → baru clone ke server.**

### 1.1 Jadikan repo private (30 detik, lakukan sekarang)

1. Buka `https://github.com/sitakkang/apoteknaraya/settings`
2. Scroll paling bawah → **Danger Zone** → **Change repository visibility** → **Change to private** → ketik nama repo untuk konfirmasi.

### 1.2 Bersihkan riwayat commit (pilih salah satu)

#### Opsi A — hapus file dari seluruh riwayat (riwayat lama tetap ada)

```powershell
# sekali saja: pip install git-filter-repo
cd C:\xampp\htdocs\apoteknaraya

git filter-repo --invert-paths `
  --path db_apoteknaraya.sql `
  --path db_core_v4.sql `
  --path "INITIATE PROJECT TRUNCATE TABLE.sql" `
  --path db_narayaapotek.sql `
  --path generate_password.php `
  --path sys_backup `
  --path app/config/database.php

git remote add origin https://github.com/sitakkang/apoteknaraya.git   # filter-repo menghapus remote
git push origin main --force
```

#### Opsi B — mulai riwayat baru (paling cepat & paling pasti bersih)

```powershell
cd C:\xampp\htdocs\apoteknaraya

git checkout --orphan clean-main       # buat branch baru tanpa riwayat
git add -A                             # .gitignore sudah menghalangi file sensitif
git commit -m "Initial commit - Apotek Naraya (riwayat bersih)"
git branch -D main
git branch -m main
git push origin main --force
```

> Karena GitHub masih bisa menyajikan commit lama lewat cache/URL commit, untuk kepastian 100%:
> **Settings → Delete this repository**, lalu buat repo baru dengan nama sama (mode **Private**), dan `git push -u origin main`.
> Data lokal Anda tidak terpengaruh — yang dihapus hanya salinan di GitHub.

### 1.3 Ganti kredensial yang sudah pernah bocor

- Password database MySQL cPanel.
- Password user aplikasi: `admin`, `adminapotek`, `ahmad` (password awal `adminutama`).
- Token/kunci yang pernah ditulis ke file di dalam repo.

---

## 2. Commit & push perubahan dari komputer

Semua perubahan yang sudah disiapkan di workspace ini (`.gitignore`, `.htaccess`, `index.php`, `app/config/config.php`,
`app/config/database.sample.php`, penghapusan file sensitif dari repo) belum di-commit. Jalankan:

```powershell
cd C:\xampp\htdocs\apoteknaraya

git add -A
git commit -m "chore: keluarkan file sensitif dari repo + siapkan deploy git (private + .cpanel) "
git push origin main
```

Yang **tidak boleh** ikut ter-commit (sudah otomatis diabaikan `.gitignore`):

| File / folder | Alasan |
|---|---|
| `app/config/database.php` | kredensial database beda tiap server |
| `environment.php` | penanda environment (production/development) |
| `.cpanel.yml` | path akun hosting beda tiap server |
| `*.sql`, `generate_password.php`, `sys_backup/`, `*.zip` | data sensitif / arsip |
| `app/sessions/*` (kecuali `index.html`) | file sesi pengguna |
| `app/logs/**`, `img/temp-qrcode/**` | file sementara |

---

## 3. Siapkan akses GitHub dari server

Repo private → server butuh kunci akses. Pilih sesuai fasilitas akun DomaiNesia Anda:

| Kondisi akun | Cara clone yang bisa dipakai |
|---|---|
| Ada **cPanel » Advanced » Terminal** atau akses SSH | **Deploy key** (Bagian 3.1) — paling aman & disarankan |
| Tidak ada Terminal/SSH | 1) minta support DomaiNesia mengaktifkan SSH/Terminal (sarankan), atau 2) URL HTTPS + token (Bagian 3.2), atau 3) jadikan repo **public** setelah riwayat dibersihkan dulu (Bagian 1), lalu pakai URL HTTPS biasa |

> Catatan resmi cPanel: tanpa akses shell/Terminal, cPanel hanya bisa *create, clone, delete, view* repository.
> Untuk repo **private** cPanel mensyaratkan fitur **SSH Access & Terminal** (generate SSH key + deploy key).

### 3.1 Deploy Key (rekomendasi, read-only)

1. cPanel → **SSH Access** → **Manage SSH Keys** → **Generate a New Key** (nama bebas, password kosongkan).
2. Klik **View/Download** pada *Public Key* → copy isinya.
3. GitHub → repo → **Settings → Deploy keys → Add deploy key** → paste → beri nama `domenesia-server` → **biarkan "Allow write access" tidak dicentang** → Add.
4. Untuk URL clone pakai bentuk SSH: `git@github.com:sitakkang/apoteknaraya.git`

### 3.2 Personal Access Token (alternatif, kalau SSH tidak tersedia)

1. GitHub → foto profil → **Settings → Developer settings → Personal access tokens → Fine-grained tokens → Generate new token**.
2. Repository access: **Only select repositories → apoteknaraya** → Permissions: **Contents = Read-only** → Generate.
3. URL clone menjadi: `https://USERNAME_GITHUB:TOKEN@github.com/sitakkang/apoteknaraya.git`

> Token disimpan di `~/repositories/.../.git/config` — jangan dibagikan, dan cabut (revoke) bila tidak dipakai lagi.

---

## 4. CARA A — Clone langsung ke `public_html` (paling singkat)

Syarat dari cPanel: **direktori tujuan harus kosong** (kalau belum ada, cPanel akan membuatnya).

1. Backup dulu isi `public_html` bila sudah ada file (cPanel → **File Manager** → pilih semua → **Compress** → download).
2. `public_html` biasanya sudah berisi folder `cgi-bin` (dan kadang `.well-known`). cPanel **menolak** clone
   bila dua folder itu masih ada → pindahkan sementara ke `/home/USERCPANEL/sementara/`.
   Setelah clone selesai, `cgi-bin` boleh dikembalikan (`.well-known` dibiarkan di lokasi aslinya).
3. cPanel → **Files → Git™ Version Control** → **Create**.
4. Isi form:

   | Field | Isi |
   |---|---|
   | **Clone a Repository** | ON (toggle aktif) |
   | **Clone URL** | `git@github.com:sitakkang/apoteknaraya.git` (SSH + deploy key) atau URL HTTPS + token |
   | **Repository Path** | `public_html` |
   | **Repository Name** | `apoteknaraya` |

5. Klik **Create**. Kalau muncul **SSH host key verification**, klik **Save and Continue** (menyimpan host key `github.com`).
   cPanel men-clone seluruh isi repo ke `public_html`.
6. Verifikasi lewat File Manager: `public_html/index.php`, `public_html/app/`, `.htaccess` sudah ada.
7. Sudah otomatis aktif di `https://apotek-naraya.com` — lanjut ke **Bagian 7** (setup wajib).

> Kalau cPanel menolak (`The repository path must be empty` / kredensial di URL tidak diterima),
> gunakan **Cara B** di bawah — hasil akhirnya sama, dan tetap **tanpa upload file**.
>
> ⚠️ Di Cara A, jangan klik **Deploy HEAD Commit** kecuali Anda memang sudah membuat `.cpanel.yml`
> (lihat Cara B), karena tombol itu menjalankan file tersebut.
> Untuk menarik perubahan berikutnya cukup klik **Update from Remote**.

---

## 5. CARA B — Clone ke luar `public_html`, deploy ke `public_html` (lebih aman & disarankan)

Kode tersimpan di luar document root, sehingga `.git` tidak pernah ada di area web.
Sekaligus membuat `database.php` / `environment.php` **tidak pernah tertimpa** saat deploy.

1. cPanel → **Git™ Version Control** → **Create**
   - **Clone URL**: `git@github.com:sitakkang/apoteknaraya.git`
   - **Repository Path**: `repositories/apoteknaraya`
   - **Repository Name**: `apoteknaraya`
2. Buat file **`.cpanel.yml`** di dalam repo tersebut pada server
   (cPanel → **File Manager** → masuk `repositories/apoteknaraya` → **+ File** → nama `.cpanel.yml`) dan isi dengan
   (ganti `USERCPANEL` dengan username cPanel Anda, mis. `apotekna`):

   ```yaml
   ---
   deployment:
     tasks:
       - export DEPLOYPATH=/home/USERCPANEL/public_html
       - /bin/mkdir -p $DEPLOYPATH/app $DEPLOYPATH/img $DEPLOYPATH/lib $DEPLOYPATH/src $DEPLOYPATH/sys
       - /bin/cp -Rf app/. $DEPLOYPATH/app/
       - /bin/cp -Rf img/. $DEPLOYPATH/img/
       - /bin/cp -Rf lib/. $DEPLOYPATH/lib/
       - /bin/cp -Rf src/. $DEPLOYPATH/src/
       - /bin/cp -Rf sys/. $DEPLOYPATH/sys/
       - /bin/cp -f index.php $DEPLOYPATH/index.php
       - /bin/cp -f .htaccess $DEPLOYPATH/.htaccess
   ```

   > `.cpanel.yml` sengaja **tidak** ada di repo (masuk `.gitignore`) karena path akun tiap server berbeda.
3. cPanel → Git™ Version Control → klik repo → **Update from Remote**, lalu **Deploy HEAD Commit**.
   Log deploy muncul di panel bawah — pastikan tidak ada `error`.
4. File aplikasi sekarang ada di `public_html`, dan `public_html/app/config/database.php` milik server **tidak tersentuh**.

> Catatan: deploy hanya menyalin/menimpa, **tidak menghapus** file yang sudah dihapus dari repo.
> Jika perlu bersih total, hapus isi `public_html` (kecuali `database.php`, `environment.php`, `app/logs`, `app/sessions`)
> lalu klik **Deploy HEAD Commit** lagi.

---

## 6. CARA C — Clone via SSH/Terminal langsung ke `public_html` (yang paling cepat)

Semua perintah dijalankan di server lewat **cPanel » Advanced » Terminal** atau SSH.
Contoh di panduan ini memakai username `apotekn1` dan server `sierra` — sesuaikan dengan milik Anda.

### 6.1 Prasyarat: kirim kode terbaru ke GitHub

Repo GitHub harus sudah memuat commit terbaru dari komputer (`git push origin main`), kalau tidak
server akan men-clone versi lama yang masih berisi file `.sql` sensitif.

### 6.2 Siapkan deploy key (repo private)

```bash
# 1) buat kunci khusus repo ini (tanpa passphrase agar bisa dipakai git otomatis)
ssh-keygen -t ed25519 -C "apotekn1@apotek-naraya.com" -f ~/.ssh/apoteknaraya -N ""

# 2) tampilkan public key → copy semuanya (mulai dari ssh-ed25519 sampai akhir)
cat ~/.ssh/apoteknaraya.pub

# 3) daftarkan ke GitHub: repo → Settings → Deploy keys → Add deploy key
#    paste kunci di atas, JANGAN centang "Allow write access"

# 4) tes kunci langsung ke GitHub (dijawab "yes" saat ditanya fingerprint)
ssh -i ~/.ssh/apoteknaraya -o IdentitiesOnly=yes -T git@github.com
#    jawaban benar: "Hi sitakkang/apoteknaraya! You've successfully authenticated..."
```

> Kalau muncul `Permission denied (publickey)` → public key belum ter-paste di Deploy keys GitHub.
> Kalau muncul `Could not resolve hostname` → jangan pakai alias (`github-apoteknaraya`);
> pakai bentuk `git@github.com:...` seperti pada Bagian 6.4.

> Alternatif tanpa deploy key — pakai token pada URL HTTPS:
> `git clone https://USERNAME_GITHUB:TOKEN@github.com/sitakkang/apoteknaraya.git .`
> (token akan tersimpan di `.git/config` server; revoke bila tidak dipakai lagi).

### 6.3 Kosongkan `public_html`

Direktori tujuan **harus kosong** supaya bisa clone langsung ke situ.

```bash
cd ~
mkdir -p ~/backup_public_html_default
mv ~/public_html/* ~/backup_public_html_default/ 2>/dev/null
mv ~/public_html/.[!.]* ~/backup_public_html_default/ 2>/dev/null   # file tersembunyi (.htaccess, .well-known)
mv ~/public_html/..?* ~/backup_public_html_default/ 2>/dev/null
ls -la ~/public_html          # harus kosong (hanya . dan ..)
```

> `php.ini` di `public_html` dibuat oleh MultiPHP INI Editor; setelah clone selesai boleh
> dikembalikan: `mv ~/backup_public_html_default/php.ini ~/public_html/`.
> Kalau ada folder `cgi-bin`, pindahkan juga dulu.

> **Penting — `.well-known` tidak bisa dipindahkan.** Folder itu dibuat oleh AutoSSL dan
> pemiliknya `root`, jadi `mv` gagal walaupun `public_html` milik Anda. **Jangan dihapus**
> (dipakai untuk perpanjangan SSL). Karena itu `git clone <url> .` tetap ditolak
> (*destination path '.' already exists and is not an empty directory*).
> Pakai cara ini — clone ke folder sementara, lalu pindahkan isinya ke `public_html`:

```bash
cd ~
git clone https://github.com/sitakkang/apoteknaraya.git ~/apoteknaraya-clone
cd ~/apoteknaraya-clone
shopt -s dotglob          # supaya .git, .htaccess, .gitignore ikut terpindah
mv * ~/public_html/
shopt -u dotglob
cd ~ && rmdir ~/apoteknaraya-clone

cd ~/public_html
git status --short        # tanpa output = repo sudah aktif di public_html
ls -la                    # index.php, app/, .htaccess, .git, .well-known
```

Hasil akhirnya identik dengan clone langsung; `.well-known` tetap di tempatnya.

### 6.4 Clone ke `public_html`

```bash
cd ~/public_html
GIT_SSH_COMMAND="ssh -i ~/.ssh/apoteknaraya -o IdentitiesOnly=yes" \
  git clone git@github.com:sitakkang/apoteknaraya.git .

# simpan perintah ssh ke konfigurasi repo, supaya `git pull` berikutnya jalan tanpa opsi tambahan
git config core.sshCommand "ssh -i ~/.ssh/apoteknaraya -o IdentitiesOnly=yes"

# verifikasi
ls -la
ls -a                 # .git harus ada
```

> Kalau repo masih **public**, tidak perlu kunci sama sekali:
> `git clone https://github.com/sitakkang/apoteknaraya.git .`
> (isinya sama, cuma riwayat kode bisa dilihat siapa pun — lihat Bagian 1).

> Tidak memakai alias `Host github-apoteknaraya` lagi supaya tidak bergantung pada `~/.ssh/config`;
> kalau tetap ingin alias, pastikan file itu dibuat di `~` (bukan di dalam `public_html`) dan
> permissionnya `chmod 600 ~/.ssh/config`.

Struktur hasil clone: `index.php`, `.htaccess`, `app/`, `img/`, `lib/`, `src/`, `sys/`, `.git/`.

### 6.5 Update rutin (tanpa cPanel GUI)

```bash
cd ~/public_html
git pull origin main
```

Karena `app/config/database.php`, `environment.php`, dan `.cpanel.yml` tidak ada di repo,
`git pull` tidak akan pernah menimpa konfigurasi server.

> Kalau `git pull` menolak karena ada file repo yang diedit di server:
> `git stash` → `git pull` → `git stash pop`, atau buang edit server dengan `git checkout -- <file>`.

### 6.6 (Opsional) daftarkan ke cPanel Git™ Version Control

Kalau nanti ingin tombol **Pull or Deploy** di cPanel: cPanel → Git™ Version Control → **Create** →
*Clone a Repository* dimatikan → **Repository Path** `public_html` → nama bebas → **Create**
(cPanel akan mengenali repo yang sudah ada, tidak men-clone ulang).

---

## 7. Setup wajib setelah clone (berlaku untuk semua cara)

### 7.1 Versi PHP & ekstensi

cPanel → **Software → Select PHP Version** / **MultiPHP Manager**:

- PHP **7.4**
- Ekstensi wajib: `mysqli`, `pdo_mysql`, `gd`, `mbstring`, `curl`, `openssl`, `json`, `iconv`, `zip`, `fileinfo`, `zip`
  (tanpa `gd`, gambar QR code pada SKBS/SKS/SKMB tidak akan terbentuk)

### 7.2 Buat database & user

cPanel → **Databases → MySQL® Databases**:

1. Buat database: `apotek` → jadi **`USERCPANEL_apotek`** (mis. `apotekna_apotek`).
2. Buat user + password kuat (min. 12 karakter, campur simbol).
3. **Add User To Database** → pilih user & database → centang **ALL PRIVILEGES** → Make Changes.

### 7.3 File konfigurasi database

Template sudah disiapkan di repo: `app/config/database.sample.php`.

- **Cara A** (repo langsung di `public_html`): cPanel → File Manager → `public_html/app/config` → copy `database.sample.php` → rename menjadi `database.php` → Edit.
- **Cara B/C**: buat file `public_html/app/config/database.php` di server (file ini tidak ada di repo).

Ubah 3 baris:

```php
'hostname' => 'localhost',
'username' => 'USERCPANEL_userdb',
'password' => 'PASSWORD_DB_YANG_KUAT',
'database' => 'USERCPANEL_apotek',
```

> Host di cPanel hampir selalu `localhost` (bukan IP).
> Karena `database.php` tidak ada di repo, file ini **aman** dari `git pull`/deploy berikutnya.

### 7.4 Tandai environment produksi

Buat file `environment.php` **sejajar dengan `index.php`** di `public_html`:

```php
<?php
define('APP_ENV', 'production');                      // matikan tampilan error
define('APP_BASE_URL', 'https://apotek-naraya.com/');  // opsional, paksa base URL
```

Efeknya: tampilan error dimatikan, `db_debug` mati, cookie sesi otomatis **hanya lewat HTTPS** (`cookie_secure = TRUE`).
File ini juga **tidak ada di repo**, jadi tidak tertimpa saat update.
Berisi nilai selain `development`/`testing`/`production` akan memunculkan error *"The application environment is not set correctly"*.

### 7.5 Import database

1. File Manager: upload `db_narayaapotek_20260917.sql` ke **`/home/USERCPANEL/`** (JANGAN ke `public_html`).
2. cPanel → **Databases → phpMyAdmin** → pilih database `USERCPANEL_apotek` → tab **Import** → pilih file → **Go**.
   (Kalau gagal karena ukuran: minta DomaiNesia import via support, atau pecah dump.)
3. **Hapus** file `.sql` dari server setelah import selesai.
4. Verifikasi cepat: tabel `conf_users`, `conf_menu`, `trans_obat`, `patient`, `skbs`, `sks`, `skmb` ada dan berisi baris.

### 7.6 Folder yang harus bisa ditulis

Pastikan ada dan permissionnya **755** (owner = akun cPanel Anda):

```
public_html/app/sessions      ← wajib (folder sesi, sudah ikut repo sebagai folder)
public_html/app/logs          ← buat manual (kosong di repo)
public_html/app/cache         ← biasanya sudah ada
public_html/img/avatar
public_html/img/temp-qrcode   ← wajib ada, tempat QR code ditulis
```

Bila perlu: cPanel → File Manager → pilih folder → **Change Permissions** → 755 (dicoba dulu), naikkan ke 775 hanya bila masih error.

### 7.7 SSL & paksa HTTPS

1. cPanel → **Security → SSL/TLS Status** → pastikan `apotek-naraya.com` berstatus **Active (AutoSSL/Let's Encrypt)**; kalau belum → **Run AutoSSL**.
2. Aktifkan **Force HTTPS Redirect** (cPanel → Domains → `apotek-naraya.com` → toggle *Force HTTPS Redirect*).

   > Pakai toggle cPanel, **jangan** menambah baris redirect di `.htaccess`, karena `.htaccess` ikut ter-overwrite tiap deploy.
3. Uji `http://apotek-naraya.com` harus otomatis pindah ke `https://`.
4. Cek juga **`.git` tidak bisa diakses**: buka `https://apotek-naraya.com/.git/config` → harus muncul **404/403**
   (sudah ditangani `.htaccess` di repo: `RedirectMatch 404 /\.git`).

### 7.8 Login pertama

| Username | Level | Nama | Password awal |
|---|---|---|---|
| `admin` | 1 — Superadmin | Superadmin | `adminutama` |
| `adminapotek` | 2 — Admin | Admin Apotek | `adminutama` |
| `ahmad` | 3 — Dokter | dr. Ahmad Nabani, S.Ked | `adminutama` |

Login di `https://apotek-naraya.com`, lalu **segera ganti ketiga password** (menu Pengguna).

### 7.9 Ringkasan langkah 7.2–7.6 versi terminal (khusus Cara C)

Dijalankan di server setelah clone (ganti `apotekn1_apotek` / `apotekn1_userdb` sesuai cPanel Anda):

```bash
cd ~/public_html

# 1) konfigurasi database
cp app/config/database.sample.php app/config/database.php
nano app/config/database.php      # isi username, password, database (host tetap 'localhost')

# 2) tandai production
cat > environment.php <<'EOF'
<?php
define('APP_ENV', 'production');
define('APP_BASE_URL', 'https://apotek-naraya.com/');
EOF

# 3) folder yang harus ada & writable
mkdir -p app/logs app/sessions app/cache img/temp-qrcode img/avatar
chmod 755 app/logs app/sessions app/cache img/temp-qrcode img/avatar

# 4) import database (file .sql diupload dulu ke luar public_html, mis. ~/)
mysql -u apotekn1_userdb -p apotekn1_apotek < ~/db_narayaapotek_20260917.sql
rm -f ~/db_narayaapotek_20260917.sql      # hapus dump dari server setelah import

# 5) cek cepat
php -v                                    # pastikan PHP 7.4
php -m | grep -E 'mysqli|gd|mbstring'      # ekstensi wajib
curl -I https://apotek-naraya.com          # harus HTTP/2 200 (atau 302 ke /auth)
```

Upload dump dari komputer (PowerShell) — pakai host SSH dari cPanel DomaiNesia:

```powershell
scp C:\xampp\mysql\backup\db_narayaapotek_20260917.sql apotekn1@sierra.domenesia.com:~/
```

---

## 8. Alur update aplikasi (rutin)

```
1. Komputer  : edit kode → git add -A → git commit -m "..." → git push origin main
2. cPanel    : Files → Git™ Version Control → pilih repo
3.           : klik "Update from Remote"
4a. Cara A   : klik "Pull or Deploy" (atau git pull via tombol Update)
4b. Cara B   : klik "Deploy HEAD Commit"  ← menjalankan .cpanel.yml
5. Uji       : buka https://apotek-naraya.com
```

Hal yang **tidak** boleh diedit langsung di server karena tertimpa deploy:
`index.php`, `.htaccess`, `app/config/config.php`, dan semua file di `app/`, `src/`, `lib/`, `sys/`, `img/`.
Setting khusus server harus lewat file yang tidak ada di repo: `database.php`, `environment.php`, `.cpanel.yml`.

---

## 9. Troubleshooting

| Gejala | Penyebab & solusi |
|---|---|
| `Update from Remote` gagal / minta login | Deploy key belum ditambahkan atau token salah/expired. Ulangi Bagian 3. |
| `git pull` gagal: *Your local changes would be overwritten* | Ada file repo yang diedit di server. Jalankan `git checkout -- <nama file>` (buang edit server) atau pindahkan setting ke `environment.php`/`database.php`. |
| `fatal: destination path '.' already exists and is not an empty directory` | `public_html` masih berisi file (termasuk file tersembunyi). Kosongkan dulu — Bagian 6.3 — atau clone ke nama folder lain: `git clone <url> apoteknaraya` lalu pindahkan isinya. |
| Sama, padahal `ls` sudah tidak menampilkan apa pun | Sisa file tersembunyi: `ls -la` (biasanya `.well-known` milik `root` dari AutoSSL). Jangan dihapus → pakai clone ke folder sementara lalu `mv` isinya (Bagian 6.3). |
| `ssh: Could not resolve hostname github-apoteknaraya` | Alias SSH tidak terbaca (file `~/.ssh/config` belum ada/salah lokasi). Pakai URL asli `git@github.com:sitakkang/apoteknaraya.git` + `GIT_SSH_COMMAND`/`core.sshCommand` seperti Bagian 6.4. |
| `Permission denied (publickey)` | Public key belum ter-paste di GitHub **Deploy keys**, atau kunci salah. Uji: `ssh -i ~/.ssh/apoteknaraya -o IdentitiesOnly=yes -T git@github.com`. |
| `~/.ssh/config` tidak berpengaruh di shell cPanel | Shell cPanel bisa berjalan di jail. Solusi paling pasti: `git config core.sshCommand "ssh -i ~/.ssh/apoteknaraya -o IdentitiesOnly=yes"` di dalam repo. |
| Blank/putih setelah login | Cek `public_html/app/logs/log-*.php`. Sementara ubah `environment.php` jadi `define('APP_ENV','development')` untuk melihat pesan error, lalu balikkan ke `production`. |
| Semua URL kecuali beranda → 404 | Rewrite tidak jalan: pastikan `.htaccess` ada di `public_html`. Jika hosting mematikan `AllowOverride`, set `$config['index_page'] = 'index.php';` di `app/config/config.php` (URL jadi `index.php/...`). |
| Login sukses tapi langsung logout / "session error" | Folder `app/sessions` tidak writable, atau `cookie_secure=TRUE` padahal diakses lewat `http://`. Perbaiki permission atau sementara set `cookie_secure` `FALSE`. |
| QR code pada SKBS/SKS/SKMB tidak muncul | Ekstensi `gd` mati, atau `img/temp-qrcode` tidak writable. |
| `Unable to connect to your database server` | `database.php` salah: cek nama user/database berprefix `USERCPANEL_`, password, host `localhost`. |
| Import phpMyAdmin gagal (file terlalu besar) | Upload ke folder luar `public_html`, atau minta support DomaiNesia, atau pecah file. |
| cPanel menolak `public_html` sebagai Repository Path | Gunakan Cara B (clone ke `repositories/apoteknaraya`). |
| Error 500 tepat setelah deploy | Biasanya `database.php` belum dibuat (Bagian 7.3). |
| `.htaccess` berisi baris lama `RewriteCond %{HTTP_REFERER} localhost` | Sudah dibersihkan di versi repo terbaru; pastikan deploy memakai commit terakhir. |

---

## 10. Checklist Go-Live

- [ ] Repo GitHub **private** dan riwayat commit **tidak lagi** memuat `*.sql` / `database.php` / `generate_password.php` / `sys_backup/`
- [ ] `git status` di komputer bersih dan sudah `git push`
- [ ] Clone di server sukses; aplikasi terbuka di `https://apotek-naraya.com`
- [ ] `app/config/database.php` dibuat di server (bukan dari repo)
- [ ] `environment.php` = `production` (+ `APP_BASE_URL`)
- [ ] Database terimport, jumlah data sesuai (cek menu pasien/kunjungan)
- [ ] AutoSSL aktif + **Force HTTPS Redirect** menyala
- [ ] `https://apotek-naraya.com/.git/config` → 404/403
- [ ] File dump `.sql` sudah dihapus dari server
- [ ] Password `admin`, `adminapotek`, `ahmad` sudah diganti
- [ ] Folder `app/sessions`, `app/logs`, `app/cache`, `img/temp-qrcode`, `img/avatar` writable (755)
- [ ] Backup otomatis diaktifkan (cPanel → Backup Wizard / Backup DomaiNesia)
- [ ] Uji cetak dokumen: SKS, SKBS, SKMB (nama & NIP dokter tampil benar), QR code terlihat
- [ ] Uji input: ANAMNESA (6 kolom aksi + modal), dokter pemeriksaan (diagnosa/obat/SKS/SKBS/SKMB manual obat)

---

## 11. Lampiran — Struktur repo & folder penting

```
public_html/
├── index.php               ← entry point (mendukung environment.php)
├── .htaccess               ← rewrite CI + blokir .git, *.sql, *.md, *.zip
├── app/
│   ├── config/
│   │   ├── config.php             (dilacak repo; base_url otomatis, cookie unik)
│   │   ├── database.sample.php    (dilacak repo; template)
│   │   └── database.php           (TIDAK di repo → dibuat di server)
│   ├── controllers/ models/ views/ libraries/
│   ├── sessions/           ← harus writable
│   ├── cache/              ← harus writable
│   └── logs/               ← harus ada & writable (buat manual)
├── environment.php         (TIDAK di repo → dibuat di server)
├── img/
│   ├── avatar/             ← writable
│   └── temp-qrcode/        ← writable (tempat QR code dibuat)
├── lib/  src/  sys/
└── .git/                   ← (Cara A) dijaga .htaccess agar tidak bisa diakses
```

**Catatan penting soal `sys/` dan `sys_backup/`:** folder `sys/` dipakai aplikasi (framework) dan wajib ikut deploy.
Folder `sys_backup/` hanya salinan lama framework → sudah dikeluarkan dari repo agar tidak memperberat clone.
Salinan lokalnya tetap ada di komputer Anda; data aplikasi tidak terpengaruh.
