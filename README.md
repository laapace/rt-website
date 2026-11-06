# Website RT — Sistem Informasi RT 02 RW 03 Bendan Kergon (Laravel 11)

Website profil + layanan RT berbasis **Laravel 11 + MySQL 8** (Blade + Tailwind CSS + Breeze + Dompdf).
Dibangun dari dokumen spesifikasi v1.1 di `~/workspace/your_files/rt-website/` (PRD, arsitektur,
desain, rules, schema). Bahasa antarmuka: **Bahasa Indonesia**. Satu RT, iuran manual +
upload bukti (tanpa payment gateway di MVP), surat online → PDF setelah disetujui.

## Data RT (asli, dari pengguna 5 Nov 2026)

- **RT 02 RW 03**, Kelurahan **Bendan Kergon**, Kecamatan **Pekalongan Barat**,
  Kota **Pekalongan**, alamat **Bendan Kergon Gg.1 No. 45**, telepon **0812-3345-6677**
- **Pengurus:** Ketua **Abdullah**, Sekretaris **Rina**, Bendahara **Ayu**
  (hanya nama depan, sesuai yang dikirim pengguna — jangan tambahkan nama belakang karangan)
- **No. HP Ketua terpisah tidak ada** — kontak memakai telepon RT di atas
- **Tarif:** Iuran Bulanan **Rp30.000/bulan per KK** + Iuran Sampah **Rp10.000/bulan per KK**,
  keduanya aktif, jatuh tempo **tanggal 5** setiap bulan
- **Jenis surat:** 5 default (domisili, tidak_mampu, usaha, kelahiran, kematian)
- **Deploy & domain:** belum diputuskan pengguna — disiapkan untuk Railway (demo) +
  shared hosting, tanpa domain sendiri
- **Kota Pekalongan adalah asumsi** dari Kecamatan Pekalongan Barat (pengguna tidak
  menyebut kota secara eksplisit); kalau salah, perbaiki `pengaturan_rt` / seeder.
- **Data warga di seed tetap FIKTIF** (nama, NIK/KK fiktif diawali 3273, alamat gang) —
  bukan data warga asli. Jangan kirim NIK/KK asli lewat chat; pengurus menginput
  data asli lewat halaman Admin setelah deploy.

## Cara kerja tagihan dengan 2 tarif

`IuranService::generateTagihan($periode)` membuat **1 tagihan per KK per tarif aktif**
per periode (unique index `kartu_keluarga_id, tarif_id, periode`). Dengan 10 KK dan
2 tarif aktif, satu kali generate menghasilkan **20 tagihan** (10 × Iuran Bulanan +
10 × Iuran Sampah). Generate yang sama dipanggil 2 kali tidak ganda: panggilan kedua
mengembalikan `dibuat: 0, sudah_ada: 20` (dijamin `firstOrCreate` + unique index).
Jatuh tempo selalu **tanggal 5** bulan periode tersebut. Tiap tagihan dibayar dan
diverifikasi **terpisah per tarif** (upload bukti sendiri, verifikasi Bendahara
sendiri → kas masuk + tagihan lunas per tarif). Menambah tarif baru lewat halaman
Admin → Iuran (form "Tambah Tarif") otomatis ikut ter-generate bulan berikutnya
selama `is_active = true`. Halaman warga menampilkan 1 kartu tagihan per tarif
untuk bulan berjalan, dan dashboard warga menampilkan total kedua tarif + status
gabungan (Belum Bayar mengalahkan Menunggu Verifikasi, yang mengalahkan Lunas).

## Fitur (FR-01–FR-16, MVP)

- **Publik:** beranda, profil RT, pengumuman, agenda, kontak (`/`, `/profil`, `/pengumuman`, `/agenda`, `/kontak`)
- **Warga (login):** dashboard, iuran bulan berjalan + upload bukti (JPG/PNG/PDF ≤ 2 MB),
  ajukan surat (5 jenis), lapor (laporan warga), jadwal ronda
- **Pengurus:** Ketua, Sekretaris, Bendahara, Admin
  - Sekretaris: aktivasi akun warga baru (akun tidak aktif sampai diaktifkan), kelola pengumuman/agenda
  - Bendahara: tarif + generate tagihan bulanan (anti-ganda per periode), verifikasi/tolak pembayaran
    (otomatis: pembayaran + kas masuk + tagihan lunas + notifikasi, dalam `DB::transaction`)
  - Admin: CRUD KK + anggota warga, kas + ekspor CSV, antrean surat + unduh PDF bernomor
    (`001/RT.02/RW.03/XI/2026`, counter terkunci `lockForUpdate`), laporan warga, jadwal ronda, pengguna

## Keamanan

- Middleware `role:*` (`EnsureHasRole`) di **semua** route admin — tidak ada route admin tanpa peran.
- Policy per model (TagihanIuran, Pembayaran, Kas, SuratPengajuan, LaporanWarga, Warga) +
  `scopeUntukUser` di setiap query halaman warga (tidak ada `Model::all()` tanpa scope).
- NIK warga ditampilkan tersamar (`****1234`) via accessor `Warga::nik_tersamar`.
- MySQL tidak punya RLS — keamanan ditegakkan di lapisan aplikasi (middleware + policy + scope).

## Menjalankan lokal

### Prasyarat

PHP 8.3 + ekstensi (cli, mbstring, xml, curl, zip, gd, bcmath, intl, sqlite3, mysql),
Composer, Node 20+, dan MySQL 8 / MariaDB 10.11.

```bash
# Ubuntu 24.04
sudo apt install php8.3 php8.3-cli php8.3-mbstring php8.3-xml php8.3-curl \
  php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl php8.3-sqlite3 php8.3-mysql \
  php8.3-dom unzip mariadb-server
sudo service mariadb start
sudo mysql -e "CREATE DATABASE rt_website CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  CREATE USER 'rt'@'127.0.0.1' IDENTIFIED BY 'rt1234';
  GRANT ALL PRIVILEGES ON rt_website.* TO 'rt'@'127.0.0.1';"
```

### Instalasi & jalan

```bash
cd rt-website-app
composer install
npm install
npm run build
cp .env.example .env
php artisan key:generate
# Isi DB_* di .env (lihat .env.example) — default: MySQL rt_website / rt / rt1234
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve          # http://127.0.0.1:8000
```

**Mode SQLite (fallback lokal):** kalau MySQL tidak tersedia, set di `.env`
`DB_CONNECTION=sqlite` dan buat file `database/database.sqlite`, lalu
`php artisan migrate:fresh --seed`. Migrasi ditulis kompatibel MySQL
(enum, bigInteger, uuid, json, timestamp); **produksi tetap memakai MySQL**.

## Akun demo (password semua: `password123`)

| Peran | Email | Nama |
|---|---|---|
| Ketua | `ketua@rt02.example` | Abdullah |
| Sekretaris | `sekretaris@rt02.example` | Rina |
| Bendahara | `bendahara@rt02.example` | Ayu |
| Admin | `admin@rt02.example` | Admin Sistem |
| Warga (kepala KK 1) | `warga1@rt02.example` | Abdullah (orang yang sama dengan Ketua — akun kedua khusus peran warga) |

> Akun demo hanya untuk pengujian lokal — **bukan** kata sandi asli pengurus RT.

## Pengujian

```bash
php artisan test                 # 41 test, 174 assertion (per 5 Nov 2026)
./vendor/bin/pint --test app routes database tests/Feature/RtFlowTest.php \
  tests/Feature/RtCrawlTest.php tests/Feature/Auth/RegistrationTest.php
```

Cakupan test: halaman publik 200, login 4 peran + dashboard 200, warga dilarang
masuk halaman admin (403), tamu diarahkan dari `/dashboard`, alur bayar iuran +
verifikasi bendahara end-to-end, upload > 2 MB / format salah ditolak, generate
tagihan 2× tidak ganda (20 dibuat, lalu 0 dibuat / 20 sudah ada), alur surat sampai PDF
(`%PDF`, nomor sesuai pola `^\d{3}/RT\.02/RW\.03/[IVX]+/\d{4}$`), warga hanya melihat
tagihan KK sendiri (4: 2 periode × 2 tarif), NIK tersamar, CRUD warga + ekspor kas,
register warga baru tidak langsung aktif, dan crawl semua halaman publik/warga/admin (200).

Test berjalan terhadap **MySQL** maupun SQLite (test default memakai SQLite `:memory:`,
jadi tidak menghapus database lokal; jalankan dengan `DB_*` MySQL untuk menguji di
MySQL — database `rt_website_test`). Terverifikasi: `migrate:fresh --seed` di MariaDB 10.11 —
10 KK, 32 warga, 40 tagihan, 31 pembayaran, view `v_iuran_periode`
(2026-11: 20 tagihan / 10 lunas / 4 menunggu / 6 belum bayar / terkumpul Rp200.000;
2026-10: 20 / 12 / 4 / 4 / Rp240.000), saldo kas `v_kas_saldo` +Rp260.000
(masuk Rp440.000 − keluar Rp180.000).

## Struktur penting

```
app/Http/Controllers/            # Public, Dashboard (warga), Admin, Auth (Breeze, disesuaikan)
app/Http/Requests/               # 11 Form Request, pesan validasi Bahasa Indonesia
app/Models/                      # 18 model (uuid PK, scopeUntukUser, nik_tersamar)
app/Policies/                    # 6 policy
app/Services/                    # IuranService, SuratService, KasService (DB::transaction)
app/Http/Middleware/EnsureHasRole.php
database/migrations/             # 22 migrasi (18 tabel + view v_kas_saldo, v_kas_bulanan,
                                 #   v_iuran_periode, v_kas_ringkas)
database/seeders/RtSeeder.php    # Data RT asli (pengguna 5 Nov 2026); warga tetap fiktif
resources/views/                 # layout publik + dashboard (sidebar), komponen x-card/x-badge/
                                 #   x-stat-card, surat/pdf.blade.php (kop RT 02, Dompdf)
routes/web.php                   # 88 route
```

## Deploy

Panduan lengkap mengikuti `architecture.md` §6 dari dokumen spesifikasi.

**Railway (demo):** buat proyek → tambahkan service MySQL → deploy dari repo Git
(`NIXPACKS_PHP_VERSION=8.3`, build: `composer install --no-dev && npm ci && npm run build`,
start: `php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT`),
isi env `APP_KEY`, `DB_*` dari MySQL Railway, `APP_URL` domain Railway.
**Shared hosting (jangka panjang):** upload isi `public/` ke `public_html/`, sisanya di
luar web root, arahkan `public_html` ke folder `public` Laravel, buat database MySQL
via cPanel, isi `.env`, jalankan `php artisan migrate --force --seed` sekali via SSH/cron,
dan `php artisan storage:link`. **Jangan** unggah `.env` ke repo; isi lewat panel hosting.
SMTP produksi belum diputuskan (TBD) — saat ini `MAIL_MAILER=log`.

## Yang masih butuh keputusan pengguna

- **Deploy utama:** Railway (demo) atau shared hosting — belum diputuskan.
- **Domain & SMTP produksi:** belum diputuskan (belum ada domain).
- **Data warga asli:** tetap fiktif di seed — pengurus menginputnya lewat halaman Admin
  setelah deploy; jangan kirim NIK/KK asli lewat chat.
- **Asumsi yang perlu dikonfirmasi:** Kota Pekalongan (diasumsikan dari Kecamatan
  Pekalongan Barat).
