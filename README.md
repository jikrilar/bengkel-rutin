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

Akses layanan:

| Layanan | URL / alamat |
|---|---|
| Laravel melalui Nginx | http://localhost:8080 |
| Vite HMR | http://localhost:5173 |
| Mailpit UI | http://localhost:8025 |
| Mailpit SMTP dari container | `mailpit:1025` |
| MySQL dari container | `mysql:3306` |

MySQL sengaja tidak dipublikasikan ke host. Gunakan `docker compose exec mysql mysql ...` atau tambahkan override lokal jika database client host benar-benar diperlukan.

## Command Laravel dan Composer

Contoh command canonical:

```bash
docker compose exec app php artisan about
docker compose exec app php artisan migrate
docker compose exec app php artisan test
docker compose exec app composer install
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
