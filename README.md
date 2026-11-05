# ZISWAF — Platform Digital Fundraising & Penyaluran

Aplikasi donasi **Zakat, Infaq, Sedekah & Wakaf** dengan checkout satu halaman, pembayaran Midtrans Snap (webhook idempoten), kalkulator zakat, kwitansi PDF + QR verifikasi, notifikasi WhatsApp (Fonnte), dan back-office Filament (kampanye, donasi, CRM donatur, mustahik, penyaluran).

- **Stack:** Laravel 12 (PHP 8.3), Filament v3, Blade + Tailwind CSS 4 + Alpine.js (Vite), PostgreSQL 16, Laravel Queue (database), Midtrans Snap.
- **Catatan versi:** semua rilis Laravel 11 diblokir Composer karena security advisory, jadi proyek memakai Laravel 12 (PHP 8.2+). Struktur kode sama.

## Daftar isi
1. [Arsitektur singkat](#arsitektur-singkat)
2. [Local setup (Docker)](#local-setup-docker)
3. [Menjalankan test](#menjalankan-test)
4. [Konfigurasi penting](#konfigurasi-penting)
5. [Deploy ke VPS Ubuntu](#deploy-ke-vps-ubuntu)
6. [Webhook Midtrans](#webhook-midtrans)
7. [Operasional: backup, update, troubleshooting](#operasional)
8. [Keamanan](#keamanan)

---

## Arsitektur singkat

```
app/
  Enums/                  status, kategori, asnaf
  Models/                 Campaign, Donor, Donation, Mustahik, Disbursement, Fundraiser, Setting, User
  Services/
    DonationService.php            buat donasi + proses webhook idempoten
    PaymentGatewayService.php      Midtrans Snap + verifikasi signature
    ZakatCalculationService.php    nishab & zakat (harga emas dari Setting -> .env)
    ReceiptService.php             PDF kwitansi + QR (signed URL)
    Notification/                  NotificationService, LogNotificationService, FonnteNotificationService
  Jobs/SendDonationReceiptJob.php
  Http/Controllers/                skinny controllers; Webhook/MidtransWebhookController
  Filament/                        Resources, Pages (Pengaturan Zakat), Widgets
docker/                   php ini, entrypoint, nginx (dev | http | https)
```

Alur pembayaran: `POST /donasi` → donasi `pending` + Snap token → popup Midtrans → Midtrans `POST /api/webhooks/midtrans` → (signature valid, `lockForUpdate`, cek nominal) → status `paid`, `collected_amount` & `total_collected` naik atomik → job kwitansi WhatsApp dikirim setelah commit. Webhook yang dikirim ulang tidak mengkredit dua kali.

## Local setup (Docker)

Prasyarat: Docker Desktop / Docker Engine + Compose v2, Git.

```bash
git clone <url-repo-anda> ziswaf && cd ziswaf
cp .env.example .env                      # lalu isi MIDTRANS_* (sandbox) bila ingin uji pembayaran

docker compose up -d --build              # app, webserver, database, queue, scheduler
docker compose exec app composer install  # source di-mount, jadi vendor dipasang di host volume
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link

# aset frontend (Tailwind + Alpine)
docker compose run --rm vite sh -c "npm install && npm run build"   # atau: docker compose --profile assets up -d  (HMR)
```

Buka:
- Situs publik: http://localhost:8080
- Admin: http://localhost:8080/admin — login dari seeder: `admin@ziswaf.test` / `password` (ubah lewat `ADMIN_EMAIL`/`ADMIN_PASSWORD` sebelum seed)

Seeder membuat: 1 admin, 3 kampanye (Zakat, Infaq, Wakaf), 8 contoh mustahik (satu per asnaf), dan 1 fundraiser (`RELAWAN01`, coba `/?ref=RELAWAN01`).

Mode log: `NOTIFICATION_DRIVER=log` menulis pesan WhatsApp ke `storage/logs/laravel.log` (tidak ada pesan nyata terkirim). Untuk uji webhook lokal gunakan tunnel (mis. ngrok/cloudflared) ke `http://localhost:8080` dan pasang URL-nya di dashboard Midtrans Sandbox.

Tanpa Docker (butuh PHP 8.2+ dengan `pdo_pgsql bcmath gd zip intl`, Composer, Node 20, PostgreSQL): `composer install && npm install && npm run build`, atur `DB_HOST=127.0.0.1`, lalu `php artisan migrate --seed && php artisan serve` dan `php artisan queue:work` di terminal lain.

## Menjalankan test

Test memakai SQLite in-memory dan tidak membutuhkan Postgres atau Midtrans asli.

```bash
docker compose exec app php artisan test        # atau: php artisan test
```

Cakupan: idempotensi webhook (replay, signature salah, nominal tidak cocok, expire setelah paid), batas saldo penyaluran, enkripsi NIK, kalkulasi zakat + override harga emas, checkout & referral, PDF kwitansi + signed URL, halaman admin.

## Konfigurasi penting

| Variabel | Keterangan |
|---|---|
| `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_IS_PRODUCTION` | Kunci Snap. Rahasia, hanya di `.env`. |
| `NOTIFICATION_DRIVER` | `log` (default) atau `fonnte`. Untuk Fonnte isi `FONNTE_TOKEN`. |
| `GOLD_PRICE_PER_GRAM` | **Fallback** harga emas. Nilai aktif diatur di **Admin → Pengaturan → Pengaturan Zakat**; kosongkan di sana untuk kembali ke fallback. |
| `DOMAIN`, `NGINX_MODE`, `RUN_MIGRATIONS` | Khusus deploy VPS. |

Menambah provider WhatsApp lain: implementasikan `App\Services\Notification\NotificationService` dan daftarkan di `AppServiceProvider`.

---

## Deploy ke VPS Ubuntu

Asumsi: Ubuntu 22.04/24.04, domain `domainanda.org`, akses SSH + sudo.

### 1. Siapkan server

```bash
ssh user@IP_VPS
sudo apt update && sudo apt -y upgrade
sudo apt -y install ca-certificates curl git ufw

# Docker Engine + Compose plugin (repo resmi Docker)
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo $VERSION_CODENAME) stable" | sudo tee /etc/apt/sources.list.d/docker.list
sudo apt update
sudo apt -y install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
sudo usermod -aG docker $USER && newgrp docker
docker compose version

# Firewall: hanya SSH, HTTP, HTTPS
sudo ufw allow OpenSSH && sudo ufw allow 80/tcp && sudo ufw allow 443/tcp && sudo ufw --force enable
```

### 2. DNS: A-record ke IP VPS

Di panel DNS domain Anda:

| Type | Name | Value | TTL |
|---|---|---|---|
| A | `@` | `IP_VPS` | 300 |
| A | `www` | `IP_VPS` | 300 |

Tunggu propagasi, lalu cek: `dig +short domainanda.org` harus menampilkan IP VPS. **Certbot gagal bila DNS belum mengarah ke server.**

### 3. Ambil kode & konfigurasi

```bash
git clone <url-repo-anda> /opt/ziswaf && cd /opt/ziswaf
cp .env.example .env
nano .env
```

Wajib diubah untuk produksi:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domainanda.org
SESSION_SECURE_COOKIE=true
LOG_LEVEL=warning
DOMAIN=domainanda.org
NGINX_MODE=http                  # tetap http dulu sampai sertifikat terbit
DB_PASSWORD=<password-acak-kuat>
MIDTRANS_SERVER_KEY=<production key>
MIDTRANS_CLIENT_KEY=<production key>
MIDTRANS_IS_PRODUCTION=true
NOTIFICATION_DRIVER=fonnte
FONNTE_TOKEN=<token>
ADMIN_EMAIL=<email-admin-anda>
ADMIN_PASSWORD=<password-kuat>   # dipakai seeder sekali; ganti setelah login
```

Buat `APP_KEY` dan jalankan:

```bash
chmod 600 .env
docker compose -f docker-compose.prod.yml build
docker compose -f docker-compose.prod.yml run --rm --no-deps -e SKIP_BOOTSTRAP=1 app php artisan key:generate --show
# salin hasilnya ke APP_KEY= di .env

docker compose -f docker-compose.prod.yml up -d
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec app php artisan db:seed --force   # sekali saja: membuat admin + kampanye contoh
```

Seeder juga membuat 3 kampanye contoh. Arsipkan atau hapus dari Admin sebelum go-live.

Tes HTTP: buka `http://domainanda.org`. Setelah itu pasang SSL.

### 4. SSL Let's Encrypt (Certbot) dengan Nginx

Nginx mode `http` sudah melayani `/.well-known/acme-challenge/`.

```bash
cd /opt/ziswaf
docker compose -f docker-compose.prod.yml --profile tools run --rm certbot certonly \
  --webroot -w /var/www/certbot \
  -d domainanda.org -d www.domainanda.org \
  --email admin@domainanda.org --agree-tos --no-eff-email

# beralih ke mode HTTPS
sed -i 's/^NGINX_MODE=.*/NGINX_MODE=https/' .env
docker compose -f docker-compose.prod.yml up -d webserver
curl -I https://domainanda.org
```

**Perpanjangan otomatis** (sertifikat berlaku 90 hari). Tambahkan ke crontab pada langkah 6.

### 5. Verifikasi layanan

```bash
docker compose -f docker-compose.prod.yml ps          # app, webserver, database, queue, scheduler = running
docker compose -f docker-compose.prod.yml logs -f queue
```

### 6. Crontab di VPS (queue, scheduler, SSL, backup)

`queue` dan `scheduler` sudah berjalan sebagai container (`restart: unless-stopped`, otomatis hidup lagi setelah reboot). Crontab host dipakai untuk **memastikan keduanya hidup**, memperpanjang SSL, dan backup database.

```bash
crontab -e
```

```cron
# Pastikan worker & scheduler hidup (idempotent; tidak mengganggu yang sudah jalan)
*/5 * * * *  cd /opt/ziswaf && docker compose -f docker-compose.prod.yml up -d queue scheduler >/dev/null 2>&1

# Perpanjang SSL tiap hari 03:30, lalu reload nginx bila ada pembaruan
30 3 * * *   cd /opt/ziswaf && docker compose -f docker-compose.prod.yml --profile tools run --rm certbot renew --webroot -w /var/www/certbot --quiet && docker compose -f docker-compose.prod.yml exec -T webserver nginx -s reload

# Backup database harian 02:00 (simpan 14 hari). Folder backups/ sudah di .gitignore
0 2 * * *    cd /opt/ziswaf && mkdir -p backups && docker compose -f docker-compose.prod.yml exec -T database pg_dump -U ziswaf ziswaf | gzip > backups/ziswaf-$(date +\%F).sql.gz && find backups -name '*.sql.gz' -mtime +14 -delete
```

**Alternatif tanpa container scheduler:** hapus service `scheduler` dan pakai cron host `* * * * * cd /opt/ziswaf && docker compose -f docker-compose.prod.yml exec -T app php artisan schedule:run >> /dev/null 2>&1`.
Pastikan `DB_USERNAME`/`DB_DATABASE` di perintah backup sesuai `.env` jika Anda mengubahnya.

### 7. Update aplikasi

```bash
cd /opt/ziswaf && git pull
docker compose -f docker-compose.prod.yml build
docker compose -f docker-compose.prod.yml up -d
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
docker compose -f docker-compose.prod.yml restart queue scheduler   # muat kode baru
```

---

## Webhook Midtrans

1. Login ke dashboard Midtrans (Sandbox untuk uji, Production untuk live).
2. **Settings → Configuration → Payment Notification URL**.
3. Isi **Payment Notification URL**: `https://domainanda.org/api/webhooks/midtrans` (HTTPS wajib untuk production).
4. Simpan. Opsional: isi **Finish Redirect URL** dengan `https://domainanda.org`.
5. Uji: lakukan donasi sandbox, atau gunakan tombol *Send Test Notification* bila tersedia, lalu cek `docker compose -f docker-compose.prod.yml logs queue` dan status donasi di Admin.

Perilaku endpoint:
- Signature SHA-512 diverifikasi (`order_id + status_code + gross_amount + ServerKey`); tidak valid → `403`.
- Respons `200` untuk notifikasi valid (termasuk pengiriman ulang), sehingga Midtrans berhenti retry.
- `paid` bersifat final: event `expire`/`cancel` yang datang terlambat tidak menurunkan status.
- Nominal `gross_amount` harus sama dengan nominal donasi, jika tidak donasi tidak dikredit.

Kunci Sandbox dan Production berbeda. Pastikan `MIDTRANS_IS_PRODUCTION` sesuai kunci yang dipakai.

## Operasional

- **Restore backup:** `gunzip -c backups/ziswaf-YYYY-MM-DD.sql.gz | docker compose -f docker-compose.prod.yml exec -T database psql -U ziswaf ziswaf`
- **Log aplikasi:** `docker compose -f docker-compose.prod.yml exec app tail -f storage/logs/laravel.log`
- **Job gagal:** `docker compose ... exec app php artisan queue:failed` dan `queue:retry all`.
- **Gambar kampanye tidak tampil:** pastikan volume `public_storage` terpasang di `app` dan `webserver` (sudah diatur di compose prod).
- **Certbot gagal (404/timeout):** cek DNS, port 80 terbuka (`ufw status`), dan `NGINX_MODE=http` saat meminta sertifikat.
- **502 Bad Gateway:** `docker compose ... logs app`; biasanya `APP_KEY` kosong atau `.env` salah.

## Keamanan

- `.env`, kunci Midtrans, token Fonnte, dump database, dan file `*.pdf` sudah di `.gitignore`. Jangan pernah commit; bila terlanjur, **rotasi kunci** karena riwayat git menyimpannya.
- PostgreSQL di produksi tidak mempublikasikan port ke luar container.
- NIK mustahik terenkripsi (`encrypted` cast) + hash HMAC untuk pencarian; tampil tersamar di admin.
- Kwitansi dan bukti penyaluran disimpan di disk privat; unduhan lewat signed URL (kwitansi berlaku 7 hari).
- Ganti password admin seeder segera setelah login pertama. Admin panel `/admin` sebaiknya dibatasi (mis. firewall/IP allowlist) bila memungkinkan.
