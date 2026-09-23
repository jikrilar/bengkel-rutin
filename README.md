# Sistem Jadwal Servis

Fondasi aplikasi Laravel untuk Sistem Informasi Bengkel dan Penjadwalan Servis Rutin. Docker Compose adalah runtime development canonical; host tidak perlu memasang PHP, Composer, MySQL, Nginx, atau Node.js.

## Requirements

- Docker Desktop, atau Docker Engine dengan Docker Compose v2.
- Port development default yang tersedia: `8080`, `5173`, `1025`, dan `8025`.

Semua command PHP, Composer, Artisan, dan npm dijalankan melalui container.

## First setup dari laptop baru

Salin repository/project, masuk ke direktorinya, lalu opsional salin environment example jika ingin mengubah nilai sebelum start:

```bash
cp .env.example .env
```

PowerShell:

```powershell
Copy-Item .env.example .env
```

Jika `.env` belum ada, container `app` akan membuatnya dari `.env.example` dan menghasilkan `APP_KEY` development secara otomatis.

Build dan start seluruh development stack:

```bash
docker compose up -d --build
```

Jalankan migration dan seeder:

```bash
docker compose exec app php artisan migrate --seed
```

Seeder membuat akun admin development dari variable berikut di `.env`:

```dotenv
ADMIN_NAME="Admin Bengkel"
ADMIN_EMAIL=admin@example.test
ADMIN_PHONE=081200000000
ADMIN_PASSWORD=password
```

Nilai contoh hanya untuk development. Gunakan password kuat yang berbeda sebelum menjalankan seeder pada environment lain.

Akses layanan:

| Layanan | URL / alamat |
|---|---|
| Laravel melalui Nginx | http://localhost:8080 |
| Vite HMR | http://localhost:5173 |
| Mailpit UI | http://localhost:8025 |
| Mailpit SMTP dari container | `mailpit:1025` |
| MySQL dari container | `mysql:3306` |

## Application foundation

Stack aplikasi saat ini:

- Laravel 13
- Livewire 3 dengan Alpine.js bawaan
- Filament 4 pada `/admin`
- Tailwind CSS 4 dan Vite
- Pest 5
- database queue, Laravel database notifications, dan scheduler baseline

URL utama:

| Area | URL |
|---|---|
| Landing page | http://localhost:8080 |
| Register customer | http://localhost:8080/register |
| Login customer | http://localhost:8080/login |
| Customer dashboard | http://localhost:8080/dashboard |
| Filament admin | http://localhost:8080/admin |

Public registration selalu membuat role `customer`. Akun `admin` hanya dibuat melalui seeder/configuration dan customer akan menerima HTTP 403 jika mencoba membuka panel Filament.

MySQL sengaja tidak dipublikasikan ke host. Gunakan `docker compose exec mysql mysql ...` atau tambahkan override lokal jika database client host benar-benar diperlukan.

## Command Laravel dan Composer

Contoh command canonical:

```bash
docker compose exec app php artisan about
docker compose exec app php artisan migrate
docker compose exec app php artisan test
docker compose exec app composer install
```

Quality checks untuk fondasi aplikasi:

```bash
docker compose exec app php artisan test
docker compose exec app ./vendor/bin/pint --test
docker compose exec node npm run build
```

Tidak ada langkah yang membutuhkan executable PHP atau Composer dari host.

## Frontend dan Vite

Service `node` memasang dependency ke named volume dan menjalankan Vite dengan HMR pada port `5173`.

Build frontend production secara manual:

```bash
docker compose exec node npm run build
```

Jika port host berbeda, ubah `VITE_PORT`, `VITE_HMR_HOST`, dan `VITE_HMR_CLIENT_PORT` di `.env`, lalu recreate service `node`.

## Queue dan scheduler

`queue` dan `scheduler` menggunakan image aplikasi yang sama dengan `app`.

- `queue` menjalankan `php artisan queue:work --sleep=3 --tries=3 --timeout=90`.
- `scheduler` menjalankan `php artisan schedule:work`.
- Worker queue menunggu migration Laravel tersedia pada first setup agar tidak crash-loop saat database masih kosong.
- Notification database dan email diproses setelah transaction commit melalui database queue.

Task operasional terjadwal:

```bash
docker compose exec app php artisan recommendations:recalculate-daily
docker compose exec app php artisan recommendations:send-reminders
docker compose exec app php artisan bookings:send-upcoming-reminders
docker compose exec app php artisan schedule:list
```

Periksa atau retry failed job dengan command Laravel standar:

```bash
docker compose exec app php artisan queue:failed
docker compose exec app php artisan queue:retry <uuid>
```

Status dan log:

```bash
docker compose ps
docker compose logs -f queue scheduler
```

## Mailpit

Laravel development menggunakan SMTP `mailpit:1025`. Email yang dikirim aplikasi dapat dilihat di http://localhost:8025 dan tidak diteruskan ke alamat sungguhan.

## Start, stop, dan rebuild

```bash
# Start / recreate
docker compose up -d

# Rebuild setelah runtime dependency berubah
docker compose up -d --build

# Stop tanpa menghapus data
docker compose down

# Lihat status dan health
docker compose ps
```

Database disimpan di named volume `mysql_data`, sehingga `docker compose down`, container recreation, dan rebuild image tidak menghapus data.

## Reset development environment

Perintah berikut destruktif dan menghapus database, dependency volumes, serta pesan Mailpit development:

```bash
docker compose down -v
```

Setelah reset, jalankan kembali:

```bash
docker compose up -d --build
docker compose exec app php artisan migrate --seed
```

Jangan gunakan `down -v` untuk stop rutin atau pada environment production.

## Production-oriented image

`compose.prod.yaml` adalah konfigurasi standalone untuk baseline deployment single VPS. Konfigurasi ini hanya menjalankan:

```text
nginx, app, queue, scheduler, mysql
```

Tidak ada service Mailpit atau Vite development server. Asset Vite dibangun pada multi-stage Docker build dan disalin ke image immutable.

Sebelum digunakan, siapkan `.env` production dengan minimum:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY` unik hasil `php artisan key:generate --show`
- credential database yang kuat dan berbeda dari contoh development
- SMTP production, bukan `mailpit`

Validasi dan build:

```bash
docker compose -f compose.prod.yaml config
docker compose -f compose.prod.yaml build
docker compose -f compose.prod.yaml up -d
docker compose -f compose.prod.yaml run --rm app php artisan migrate --force
```

Production image tidak berisi `.env`, tidak bind-mount source code, dan tidak bergantung pada Mailpit maupun Vite dev server. Atur TLS/reverse proxy publik dan backup volume MySQL sesuai server tujuan sebelum go-live.

## Troubleshooting singkat

```bash
docker compose ps
docker compose logs app nginx mysql node queue scheduler mailpit
docker compose exec app php artisan about
docker compose exec app php artisan config:clear
```

Jika Docker Desktop baru dinyalakan, tunggu health check MySQL selesai; Compose menahan startup aplikasi sampai MySQL siap menerima koneksi.
