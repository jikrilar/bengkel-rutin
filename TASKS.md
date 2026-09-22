# Development Tasks

## Sistem Informasi Bengkel & Penjadwalan Servis Rutin dengan Fuzzy Tsukamoto

**Document Version:** 1.1  
**Status:** Execution Plan  
**Primary Goal:** Menyelesaikan project secepat mungkin tanpa mengorbankan correctness pada fuzzy, booking, service workflow, security, dan UI quality.  
**Execution Style:** Large grouped tasks untuk Codex CLI, dikerjakan berurutan per task besar.

Dokumen acuan yang harus dibaca sebelum mulai:

- `PRD.md`
- `DATABASE.md`
- `ARCHITECTURE.md`
- `DESIGN_SYSTEM.md`

---

# 1. Strategi Pengerjaan

Project sengaja dibagi menjadi **9 task besar**, bukan puluhan task kecil.

Tujuannya:

- mengurangi context switching,
- memungkinkan Codex menyelesaikan satu vertical slice dalam satu proses,
- membuat hasil setiap task langsung dapat dites,
- mengurangi pekerjaan integrasi ulang,
- memprioritaskan core system sebelum polish,
- menjaga waktu pengerjaan tetap cepat.

Urutan:

```text
T02 Docker & Runtime Foundation
    ↓
T03 Project Foundation, Auth, Design & Filament
    ↓
T04 Database & Domain Foundation
    ↓
T05 Fuzzy & Recommendation Engine
    ↓
T06 Customer Core Experience
    ↓
T07 Booking & Workshop Scheduling
    ↓
T08 Service Workflow + Admin Operations
    ↓
T09 Notifications, Scheduler, Reports & Final Admin
    ↓
T09 Full QA, Polish & Release Readiness
```

Task berikutnya hanya dimulai jika task sebelumnya telah memenuhi acceptance criteria.

---

# 2. Global Rules untuk Codex

Setiap task wajib mengikuti aturan berikut.

## 2.1 Baca Dokumen Sebelum Mengubah Code

Sebelum mengimplementasikan task:

1. baca seluruh dokumen development yang relevan,
2. inspect project state,
3. jangan mengubah keputusan produk yang sudah locked tanpa alasan teknis kuat,
4. jika terdapat konflik antar dokumen, prioritaskan:
   - PRD untuk product behavior,
   - Database untuk schema,
   - Architecture untuk code structure,
   - Design System untuk UI.

---

## 2.2 Jangan Overengineering

Dilarang menambahkan tanpa kebutuhan:

- React/Vue SPA,
- Redis,
- microservice,
- repository pattern generik,
- CQRS,
- event sourcing,
- service container abstraction berlapis,
- permission package besar,
- state machine package,
- API layer terpisah untuk customer UI.

Gunakan Laravel conventions.

---

### Docker Runtime Rule

Setelah T01 selesai:

- development command dijalankan melalui Docker,
- setup native tidak menjadi requirement,
- app/queue/scheduler menggunakan application image yang konsisten,
- development-only service tidak menjadi production dependency,
- MySQL menggunakan persistent storage,
- perubahan runtime dependency harus diikuti update Dockerfile/Compose dan dokumentasi.

---

## 2.3 Jangan Mengubah Locked Scope

MVP hanya:

```text
Customer
Admin Bengkel
```

Tidak menambahkan:

- teknisi,
- inventory,
- supplier,
- spare parts management,
- payment gateway,
- multi workshop,
- AI assistant,
- mobile app.

---

## 2.4 UI Wajib Mengikuti Design System

Tidak boleh ada:

- purple/blue gradient,
- glassmorphism,
- glow,
- excessive rounded cards,
- fake charts,
- generic SaaS layout,
- decorative AI elements,
- visual AI-slop.

Gunakan theme:

```text
Modern Workshop Editorial
```

---

## 2.5 Testing Tidak Boleh Ditunda Sampai Akhir

Setiap task harus menambahkan test untuk behavior yang dibuat.

Minimal jalankan:

```bash
php artisan test
```

serta tool quality check yang tersedia di project.

Jangan meninggalkan test failing untuk task berikutnya.

---

## 2.6 Jangan Merusak Historical Data

Historical entity tidak boleh dibuat destructive tanpa alasan:

- odometer logs,
- fuzzy calculations,
- fuzzy rule results,
- bookings,
- booking events,
- service records.

---

## 2.7 Commit Scope

Satu task besar sebaiknya menghasilkan satu logical implementation batch.

Jika menggunakan Git:

```text
feat: complete Txx <task name>
```

Tidak wajib satu commit literal, tetapi perubahan harus tetap dapat direview sebagai satu scope.

---

# 3. T01 — Docker & Runtime Foundation

## Objective

Menjadikan Docker + Docker Compose sebagai **canonical development runtime** sejak awal project, sehingga setup di laptop developer/client dan deployment nantinya konsisten.

Task ini dikerjakan pertama agar seluruh task setelahnya berjalan di environment yang sama dan tidak bergantung pada instalasi native PHP, Composer, MySQL, Nginx, atau Node.

---

## Scope

### Docker Architecture

Implement container stack:

```text
nginx
app
queue
scheduler
mysql
node
mailpit
```

Catatan:

```text
app
queue
scheduler
```

harus menggunakan application image yang sama.

Development boleh menggunakan bind mount untuk source code.

Production build harus mendukung immutable image.

---

### Dockerfile

Buat `Dockerfile` untuk Laravel/PHP-FPM.

Minimum PHP extensions:

```text
pdo_mysql
mbstring
intl
bcmath
pcntl
zip
```

Tambahkan extension lain hanya jika dependency project benar-benar membutuhkan.

Composer harus tersedia pada build stage/runtime yang diperlukan.

Gunakan multi-stage build bila membantu:

```text
composer stage
node build stage
php runtime stage
```

Production image tidak boleh membutuhkan Vite dev server untuk menjalankan aplikasi.

---

### Docker Compose

Buat:

```text
compose.yaml
```

dan jika diperlukan:

```text
compose.override.yaml
compose.prod.yaml
```

Development services minimum:

```text
nginx
app
mysql
node
mailpit
queue
scheduler
```

Gunakan named volume untuk MySQL:

```text
mysql_data
```

---

### Nginx

Buat konfigurasi:

```text
docker/nginx/default.conf
```

Responsibilities:

- expose application HTTP port,
- serve static assets,
- forward PHP requests ke PHP-FPM app container.

---

### PHP Configuration

Jika dibutuhkan, buat:

```text
docker/php/php.ini
```

Atur hanya setting yang memang dibutuhkan project.

Jangan memasukkan secret ke image.

---

### Docker Networking

Laravel harus mengakses MySQL melalui service name:

```text
DB_HOST=mysql
```

Mail development:

```text
MAIL_HOST=mailpit
```

MySQL tidak boleh diasumsikan berada di `localhost` dari dalam app container.

---

### Environment Files

Update:

```text
.env.example
```

agar cocok dengan Docker development.

Minimum:

```text
APP_*
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...

QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
```

Jangan commit `.env`.

---

### Health / Readiness

Tambahkan readiness/health strategy sederhana untuk:

```text
mysql
app
nginx
```

Pastikan app tidak gagal hanya karena MySQL container sudah `started` tetapi belum siap menerima koneksi.

---

### Development Workflow

Target first setup:

```bash
docker compose up -d --build
docker compose exec app php artisan migrate --seed
```

Jika project belum ter-bootstrap saat task ini dimulai, bootstrap Laravel dilakukan melalui container agar environment native tidak menjadi requirement.

Tambahkan helper script/command hanya jika benar-benar menyederhanakan setup.

Contoh optional:

```text
make setup
make up
make test
```

Jangan menjadikan Makefile requirement jika tidak diperlukan.

---

### Queue Container

Service `queue` menjalankan:

```bash
php artisan queue:work --tries=3
```

Parameter dapat disesuaikan kemudian.

Pastikan container restart policy masuk akal untuk development/deployment.

---

### Scheduler Container

Service `scheduler` menjalankan:

```bash
php artisan schedule:work
```

atau strategi Laravel scheduler yang setara dan stabil.

---

### Mailpit

Development-only:

```text
SMTP port 1025
Web UI port sesuai compose
```

Gunakan untuk verifikasi email tanpa mengirim email sungguhan.

---

### Node / Vite

Development:

- Vite dapat berjalan melalui node container,
- HMR harus dapat diakses dari browser host.

Production:

- asset dibuild saat image build,
- node dev server tidak dijalankan.

---

### Persistent Data

Wajib:

```text
MySQL named volume
```

Jangan menyimpan database hanya pada writable layer container.

Dokumentasikan cara:

```text
start
stop
rebuild
reset development database
```

Reset destructive harus eksplisit dan tidak menjadi default command.

---

### Docker Ignore

Buat `.dockerignore` agar build context tidak memuat file tidak perlu seperti:

```text
.git
node_modules
vendor (jika dibuild di image)
logs
local env files
test artifacts
```

sesuai strategy final.

---

### Production Readiness Baseline

Pastikan arsitektur dapat dipakai untuk deployment single VPS:

```text
nginx
app
queue
scheduler
mysql
```

Development-only service seperti:

```text
mailpit
node dev server
```

tidak ikut production runtime.

Production settings minimum:

```text
APP_ENV=production
APP_DEBUG=false
```

---

### Documentation

Tambahkan/update README setup section:

```text
Requirements
First setup
Start containers
Run migrations
Run tests
Run frontend build
Queue
Scheduler
Mailpit
Stop containers
Reset development environment
Production notes
```

Targetnya client cukup mengetahui:

```text
install Docker
clone/copy project
copy .env
docker compose up -d --build
run migration/seed
```

---

## Deliverables

- Dockerfile,
- Docker Compose configuration,
- Nginx config,
- PHP config jika dibutuhkan,
- MySQL persistent volume,
- app/queue/scheduler runtime,
- Vite development runtime,
- Mailpit development service,
- Docker-ready `.env.example`,
- setup documentation,
- verified development workflow.

---

## Acceptance Criteria

```text
[ ] docker compose config valid
[ ] docker compose up -d --build berhasil
[ ] Nginx dapat membuka Laravel app
[ ] app dapat terhubung ke MySQL melalui service name
[ ] MySQL data persisten setelah container recreation
[ ] queue container berjalan
[ ] scheduler container berjalan
[ ] Mailpit dapat menerima development email
[ ] Vite development workflow bekerja
[ ] production asset build tidak membutuhkan Vite dev server runtime
[ ] .env tidak masuk image/Git
[ ] Docker setup terdokumentasi
[ ] laptop baru tidak membutuhkan native PHP/Composer/MySQL/Node untuk menjalankan project
```

---

# 4. T02 — Project Foundation, Authentication, Design Foundation & Filament Bootstrap

## Objective

Membuat project Laravel siap dikembangkan dengan stack final dan fondasi visual/auth yang sudah usable **di atas Docker runtime yang telah disiapkan pada T01**.

Task ini harus menyelesaikan Laravel/application foundation sekaligus agar task berikutnya langsung fokus ke business domain.

Seluruh command development pada task ini dijalankan melalui container kecuali ada alasan khusus yang terdokumentasi.

---

## Scope

### Project Setup

Gunakan Docker runtime dari T01 sebagai environment development utama.

Siapkan:

- Laravel project,
- MySQL connection,
- Livewire,
- Filament,
- Tailwind CSS,
- Alpine.js sesuai stack Laravel/Livewire,
- Vite,
- Pest,
- database queue,
- Laravel notifications,
- scheduler baseline.

Pastikan versi package saling kompatibel.

---

### Environment

Siapkan:

```text
.env.example
```

dengan variable minimum:

```text
APP_*
DB_*
MAIL_*
QUEUE_CONNECTION=database
SESSION_*
CACHE_*
```

Development email dapat diarahkan ke Mailpit.

---

### Authentication

Implement:

- register customer,
- login,
- logout,
- forgot password,
- reset password,
- authenticated redirect,
- admin access restriction.

Public registration hanya menghasilkan:

```text
role = customer
```

Admin tidak bisa dibuat melalui public registration.

---

### Role Foundation

Implement:

```php
UserRole
```

dengan:

```text
customer
admin
```

Setup access control untuk Filament:

```text
/admin
```

Customer harus ditolak dari admin panel.

---

### Customer Layout

Buat application shell responsive:

```text
Desktop:
sidebar + content

Mobile:
compact navigation
```

Navigation:

```text
Dashboard
Kendaraan
Rekomendasi Servis
Booking
Riwayat Servis
Notifikasi
Profil
```

Pada tahap ini link boleh menuju placeholder page yang rapi.

---

### Design Tokens

Implement design tokens dari Design System:

```text
canvas
surface
text
border
brand
success
warning
danger
neutral
```

Set up:

- Geist sebagai primary font jika dependency/source memungkinkan,
- fallback system font tetap baik,
- Instrument Serif hanya optional untuk landing.

Implement primitive Blade components:

```text
Button
Input
Textarea
Select
StatusBadge
Alert
PageHeader
SectionHeader
EmptyState
Modal
ProgressBar
```

Jangan membuat component library terlalu besar.

---

### Landing Page

Buat landing page minimal tetapi polished:

```text
Header
Hero
Cara Kerja
Manfaat Utama
Informasi Bengkel placeholder/config-based
CTA
Footer
```

Tidak perlu seluruh konten marketing kompleks.

---

### Filament Theme

Apply:

- font,
- brand accent,
- semantic colors,
- radius sederhana,
- logo/name placeholder,
- admin dashboard shell.

Jangan redesign Filament ekstrem.

---

## Deliverables

- project dapat dijalankan,
- auth bekerja,
- customer/admin separated,
- customer app shell tersedia,
- Filament tersedia,
- design tokens tersedia,
- primitive components tersedia,
- landing page tersedia,
- test foundation tersedia.

---

## Acceptance Criteria

```text
[ ] Application berjalan melalui Docker runtime T01
[ ] Customer dapat register/login/logout
[ ] Customer tidak dapat mengakses /admin
[ ] Admin dapat login ke Filament
[ ] Customer layout responsive
[ ] Design tokens digunakan, bukan hex acak di banyak file
[ ] Tidak ada AI-slop visual
[ ] Landing page mengikuti design direction
[ ] php artisan test lulus
[ ] Build frontend lulus
```

---

# 5. T03 — Complete Database, Models, Enums, Relationships, Seeders, Factories & Policies

## Objective

Mengimplementasikan seluruh domain schema sekaligus agar business feature berikutnya tidak perlu melakukan migration improvisasi berulang.

---

## Scope

Implement seluruh domain tables dari Database Specification:

```text
users
service_profiles
vehicles
odometer_logs
fuzzy_configs
fuzzy_rules
fuzzy_calculations
fuzzy_rule_results
bookings
booking_events
service_records
workshop_settings
operating_hours
schedule_exceptions
```

Tambahkan Laravel technical tables yang dibutuhkan:

```text
notifications
jobs
job_batches
failed_jobs
sessions
password_reset_tokens
```

---

### Enums

Implement:

```text
UserRole
BookingStatus
BookingEventType
OdometerSource
BaselineSource
RecommendationStatus
CalculationTrigger
```

Gunakan model casts.

---

### Models & Relationships

Implement seluruh Eloquent relationships sesuai Database document.

Pastikan:

- latest odometer relation/helper tersedia,
- latest fuzzy calculation relation/helper tersedia,
- active booking query mudah digunakan,
- ownership chain jelas.

---

### Seeders

Implement:

```text
AdminUserSeeder
ServiceProfileSeeder
FuzzyConfigSeeder
FuzzyRuleSeeder
WorkshopSettingSeeder
OperatingHourSeeder
```

Fuzzy rules harus menghasilkan tepat **18 canonical rules** sesuai dokumen.

Seeder harus aman dijalankan ulang jika memungkinkan.

---

### Factories

Implement factory minimum:

```text
UserFactory
VehicleFactory
ServiceProfileFactory
OdometerLogFactory
FuzzyConfigFactory
FuzzyCalculationFactory
BookingFactory
ServiceRecordFactory
```

---

### Policies

Implement minimum:

```text
VehiclePolicy
BookingPolicy
ServiceRecordPolicy
FuzzyCalculationPolicy
```

Customer hanya bisa mengakses data miliknya.

Admin memperoleh access sesuai kebutuhan admin area.

---

### Database Integrity

Tambahkan:

- indexes,
- unique constraints,
- foreign keys,
- soft deletes sesuai spec.

Handle circular relationship:

```text
vehicles.baseline_service_record_id
```

dengan migration order yang aman.

---

## Deliverables

- database schema lengkap,
- migration fresh berhasil,
- models lengkap,
- enums lengkap,
- factories dan seeders,
- policies,
- domain relationships.

---

## Acceptance Criteria

Jalankan dari database kosong:

```bash
php artisan migrate:fresh --seed
```

Harus berhasil.

Validasi:

```text
[ ] 18 fuzzy rules tersedia
[ ] 1 active fuzzy config tersedia
[ ] admin seed tersedia
[ ] service profile default tersedia
[ ] operating hours tersedia
[ ] foreign key valid
[ ] plate number normalized memiliki unique constraint
[ ] policy ownership memiliki tests
[ ] php artisan test lulus
```

---

# 6. T04 — Full Fuzzy Tsukamoto & Recommendation Engine

## Objective

Menyelesaikan seluruh mesin fuzzy dan recommendation layer sebelum membuat customer feature lebih jauh.

Ini adalah task teknis paling penting dalam project.

Setelah task ini selesai, algoritma harus dianggap production-ready untuk scope TA.

---

## Scope

### DTOs

Implement:

```text
FuzzyInput
MembershipResult
RuleResult
FuzzyResult
RecommendationResult
```

Gunakan typed properties/value yang jelas.

---

### Fuzzy Services

Implement:

```text
FuzzyConfigResolver
MembershipCalculator
RuleEvaluator
Defuzzifier
FuzzyTsukamotoEngine
```

Engine tidak boleh bergantung langsung pada Livewire/Filament.

Sebisa mungkin calculation core pure PHP.

---

### Membership Functions

Implement locked fuzzy inputs:

```text
Progress Kilometer:
Aman
Mendekati
Kritis

Progress Waktu:
Aman
Mendekati
Kritis

Usage:
Normal
Intensif
```

Default boundaries berasal dari active fuzzy config:

```text
70
90
100
80
120
```

Pastikan overlap continuous dan boundary tidak menghasilkan gap.

---

### Rule Evaluation

Load/evaluate 18 canonical rules.

Alpha:

```text
MIN(all antecedent memberships)
```

Consequent internal:

```text
not_urgent
urgent
```

Hanya active rule `alpha > 0` yang perlu dipersist.

---

### Defuzzification

Implement:

```text
Z = Σ(alpha × z) / Σ alpha
```

Handle denominator 0 dengan explicit domain exception, bukan return random score.

Score harus:

```text
0 <= score <= 100
```

---

### Recommendation Services

Implement:

```text
ProgressCalculator
UsageCalculator
ServiceDueDateCalculator
RecommendationGuard
RecommendationService
```

RecommendationService melakukan:

```text
load baseline
load latest odometer
calculate progress
calculate usage
resolve config
run fuzzy
calculate due date
map fuzzy status
apply guard
derive service window
derive recommended date
persist calculation
persist active rule results
```

---

### Insufficient Usage Data

Tentukan fallback deterministic.

Recommended implementation:

Jika belum ada cukup histori odometer untuk menghitung actual usage:

```text
average_daily_km = baseline_daily_usage
usage_intensity = 100%
```

Reason:

- neutral midpoint,
- deterministic,
- tidak menganggap kendaraan ringan atau ekstrem,
- memungkinkan recommendation berjalan sejak onboarding.

Simpan/mark bahwa usage berasal dari fallback jika perlu pada service/result layer.

Jika implementasi membutuhkan field tambahan untuk transparansi, tambahkan secara minimal dan update database docs kemudian.

---

### Recommendation Status

Map:

```text
0 – <40   → not_needed
40 – <70  → approaching
70 – 100  → urgent
```

---

### Recommendation Guard

Jika projected due date lebih cepat daripada fuzzy service window, final status boleh dinaikkan.

Fuzzy score tidak pernah diubah.

---

### Calculation Persistence

Simpan:

- service profile snapshot,
- input snapshot,
- membership snapshot,
- score,
- fuzzy status,
- due projections,
- final status,
- guard metadata,
- active rule results.

---

### Tests

Buat extensive unit tests.

Minimum scenarios:

```text
20 / 20 / normal
20 / 20 / intensive
75 / 40 / normal
75 / 40 / intensive
80 / 80 / normal
80 / 80 / intensive
95 / 50 / normal
50 / 95 / normal
100 / 30 / normal
30 / 100 / normal
110 / 110 / intensive
boundary 70
boundary 90
boundary 100
usage boundary 80
usage boundary 120
```

Test:

- membership,
- alpha,
- z,
- score range,
- deterministic result,
- guard behavior,
- due-date projection,
- history persistence.

---

## Deliverables

- fuzzy engine lengkap,
- recommendation engine lengkap,
- persistence,
- unit tests,
- feature tests dasar.

---

## Acceptance Criteria

```text
[ ] Core fuzzy dapat dijalankan tanpa UI
[ ] Input sama menghasilkan output sama
[ ] Tidak ada division by zero
[ ] Semua 18 rule dapat dievaluasi
[ ] Membership continuous pada boundary
[ ] Score selalu 0–100
[ ] Fuzzy score tidak dimodifikasi oleh RecommendationGuard
[ ] Calculation snapshot tersimpan
[ ] Active rule result tersimpan
[ ] Unit tests fuzzy lengkap dan lulus
[ ] php artisan test lulus
```

---

# 7. T05 — Complete Customer Core: Dashboard, Vehicles, Odometer & Recommendation UI

## Objective

Menyelesaikan seluruh vertical slice customer dari kendaraan sampai rekomendasi dalam satu task.

Setelah task ini, customer sudah dapat menggunakan core value aplikasi walaupun booking belum tersedia.

---

## Scope

### Dashboard

Implement:

- vehicle selector,
- recommendation summary,
- score,
- status,
- recommended date,
- service window,
- current odometer,
- km progress,
- time progress,
- usage,
- booking placeholder jika belum ada,
- CTA yang relevan.

Empty states:

```text
no vehicle
baseline unavailable
recommendation unavailable
```

---

### Vehicle List

Implement:

```text
/vehicles
```

- list kendaraan,
- status recommendation,
- current odometer,
- detail link,
- add vehicle CTA.

Gunakan rows/list, bukan excessive cards.

---

### Create Vehicle

Implement form:

```text
name
brand
model
year
plate number
current odometer
last service known?
last service date
last service odometer
```

Service profile dipilih/ditetapkan sesuai rule product yang ada.

Creation flow:

```text
vehicle
initial odometer
initial recommendation if possible
```

dalam action/transaction yang tepat.

---

### Vehicle Detail

Implement sections:

- identity,
- current odometer,
- service profile,
- service baseline,
- recommendation,
- odometer history,
- service history preview.

---

### Vehicle Edit

Customer hanya dapat mengedit identity fields.

Customer tidak boleh bebas mengedit service baseline resmi.

---

### Update Odometer

Implement modal/page:

```text
latest odometer
new odometer
recorded date
```

Validation:

```text
new >= latest
```

Setelah berhasil:

- log dibuat,
- recommendation recalculated,
- UI refresh,
- success message,
- score/status terbaru ditampilkan.

---

### Odometer History

Implement chronological list/table.

---

### Recommendation Overview

Implement:

```text
/recommendations
```

menampilkan semua kendaraan dengan:

- score,
- status,
- recommended date.

---

### Recommendation Detail

Implement:

- score,
- status,
- service window,
- recommended date,
- explanation,
- progress km,
- progress time,
- usage intensity.

---

### Fuzzy Calculation Detail

Implement customer-readable technical page:

```text
Input
Membership
Active Rules
Alpha
Z
Weighted values
Defuzzification
Fuzzy score
Final recommendation
```

Default tampilkan active rules.

Optional:

```text
Lihat semua rule
```

jika memang berguna.

---

### Profile

Implement customer profile dasar:

```text
name
email
phone
password change
```

---

### UI Requirements

Wajib mengikuti design system:

- warm-neutral,
- no AI slop,
- responsive,
- clear hierarchy,
- no excessive cards,
- semantic status.

---

## Deliverables

Customer dapat:

```text
register
add vehicle
view vehicle
update odometer
see history
receive fuzzy recommendation
understand calculation
edit profile
```

---

## Acceptance Criteria

```text
[ ] Customer hanya melihat kendaraan sendiri
[ ] Create vehicle menghasilkan initial odometer
[ ] Recommendation muncul jika baseline lengkap
[ ] Missing baseline menghasilkan state yang benar
[ ] Update odometer recalculates recommendation
[ ] Invalid lower odometer ditolak
[ ] Fuzzy detail sesuai persisted calculation
[ ] Mobile 360/390px usable
[ ] Tidak ada horizontal overflow utama
[ ] UI sesuai design system
[ ] php artisan test lulus
[ ] frontend build lulus
```

---

# 8. T06 — Complete Booking & Workshop Scheduling

## Objective

Menyelesaikan seluruh booking end-to-end: jadwal bengkel, slot availability, booking customer, dan booking management dasar admin.

---

## Scope

### Workshop Settings

Filament:

- workshop name,
- address,
- phone,
- email,
- timezone,
- slot duration,
- slot capacity.

---

### Operating Hours

Admin dapat mengatur:

```text
Monday–Sunday
open/closed
open time
close time
```

---

### Schedule Exceptions

Admin dapat:

- menutup tanggal tertentu,
- menentukan jam khusus,
- memberi alasan.

---

### WorkshopScheduleService

Implement:

- resolve normal hours,
- apply exception,
- generate dynamic time slots.

---

### BookingAvailabilityService

Implement availability berdasarkan:

```text
schedule
exception
slot duration
capacity
existing active bookings
```

---

### Customer Booking Flow

Implement:

```text
Vehicle
Date
Time Slot
Complaint
Confirmation
```

Jika masuk dari recommendation detail:

- vehicle preselected,
- recommended date diberi indicator.

Customer boleh memilih tanggal di luar recommendation tetapi mendapat warning, bukan hard block.

---

### Booking List

Customer:

```text
Active
History
```

Tampilkan status dan tanggal.

---

### Booking Detail

Tampilkan:

- vehicle,
- schedule,
- status,
- complaint,
- timeline,
- cancel action jika valid.

---

### CreateBookingAction

Wajib:

- validate ownership,
- validate future date,
- re-check availability server-side,
- transaction,
- create booking,
- create event.

---

### Booking State Foundation

Implement:

```text
Pending
Confirmed
InService
Completed
Cancelled
```

dengan transition validation.

---

### Admin Booking Resource

Filament:

- list,
- search,
- filter status/date/customer/vehicle,
- detail,
- confirm,
- reschedule,
- cancel.

`Start Service` boleh disiapkan sekarang dan dipakai penuh di T07.

---

### Booking Events

Persist:

```text
created
confirmed
rescheduled
cancelled
started where used
```

---

### Race Condition Protection

Saat create/reschedule:

- availability selalu dicek ulang di backend,
- jangan percaya UI state.

Gunakan strategi locking/transaction yang cukup untuk mencegah overbooking pada scope MySQL/Laravel project.

---

## Deliverables

Customer dan admin dapat menjalankan booking lifecycle sampai confirmed/cancelled/rescheduled.

---

## Acceptance Criteria

```text
[ ] Closed day tidak bisa dibooking
[ ] Schedule exception bekerja
[ ] Full slot ditolak
[ ] Capacity tepat
[ ] Pending/confirmed memakai capacity
[ ] Cancelled melepas capacity
[ ] Customer tidak dapat booking kendaraan user lain
[ ] Server re-validates slot saat submit
[ ] Admin dapat confirm/reschedule/cancel
[ ] Timeline tersimpan
[ ] Responsive booking flow usable
[ ] php artisan test lulus
```

---

# 9. T07 — Complete Service Workflow, History & Core Admin Operations

## Objective

Menyelesaikan core loop aplikasi dari booking sampai servis selesai, baseline reset, recommendation baru, dan seluruh admin operational resources utama.

---

## Scope

### Start Service

Admin:

```text
Confirmed → In Service
```

Gunakan action dan valid transition.

Create:

```text
BookingEvent(started)
```

---

### CompleteServiceAction

Implement transaction lengkap:

```text
validate booking = in_service
validate odometer

create service record
create odometer log

update vehicle baseline:
    baseline_service_date
    baseline_odometer
    baseline_source
    baseline_service_record_id

update booking = completed
create booking event = completed

calculate recommendation baru
persist fuzzy calculation
persist active rule results
```

Commit baru boleh diikuti side effects.

---

### Admin Service Completion Form

Field:

```text
service date
odometer
service type
complaint
work performed
notes
total cost
```

Complaint dapat diprefill dari booking.

---

### Customer Service History

Implement:

```text
/service-history
/service-history/{record}
```

List:

- date,
- vehicle,
- odometer,
- service type,
- total.

Detail:

- complaint,
- work performed,
- notes,
- cost,
- baseline explanation if useful.

---

### Admin Customer Resource

Implement:

- search,
- customer detail,
- vehicles,
- active booking,
- service history.

---

### Admin Vehicle Resource

Implement:

- search plate/customer,
- recommendation filter,
- service profile,
- odometer history,
- fuzzy result,
- service history,
- controlled odometer correction.

---

### Odometer Correction

Admin correction:

```text
current value
corrected value
reason
```

Jangan menghapus audit trail.

---

### Service Record Resource

Admin:

- list,
- filter,
- detail,
- controlled correction metadata if needed.

Tidak menyediakan hard-delete routine.

---

### Service Profile Resource

Admin:

- create,
- update,
- deactivate,
- assign/use profile.

Validate:

```text
interval_km > 0
interval_days > 0
```

Perubahan service profile kendaraan harus memicu recommendation recalculation.

---

## Deliverables

Core product loop selesai:

```text
Vehicle
→ Recommendation
→ Booking
→ Service
→ Service History
→ New Baseline
→ New Recommendation
```

---

## Acceptance Criteria

```text
[ ] Invalid booking transition ditolak
[ ] Complete service hanya dari In Service
[ ] Service record dibuat
[ ] Service odometer log dibuat
[ ] Vehicle baseline diperbarui
[ ] Booking menjadi Completed
[ ] Booking event dibuat
[ ] Recommendation dihitung ulang
[ ] Fuzzy progress kembali mendekati awal siklus
[ ] Semua critical write dalam transaction
[ ] Rollback bekerja jika failure disimulasikan
[ ] Customer service history benar
[ ] Admin resources usable
[ ] php artisan test lulus
```

---

# 10. T08 — Notifications, Scheduler, Fuzzy Admin, Reports & Application Completion

## Objective

Menyelesaikan fitur pendukung yang membuat aplikasi benar-benar operasional tanpa menambah scope besar.

---

## Scope

### In-App Notifications

Implement:

```text
ServiceApproachingNotification
ServiceUrgentNotification
BookingConfirmedNotification
BookingRescheduledNotification
UpcomingBookingNotification
ServiceCompletedNotification
```

Gunakan Laravel database notification.

---

### Email Notifications

Event penting juga dikirim email.

Gunakan queue.

Pastikan failure email tidak membatalkan business transaction.

---

### Database Queue

Setup:

```text
QUEUE_CONNECTION=database
```

Pastikan jobs dapat diproses dengan:

```bash
php artisan queue:work
```

---

### Scheduler

Implement command/job untuk:

```text
daily recommendation recalculation
service status reminder
booking H-1 reminder
```

Batasi scheduled calculation:

```text
max 1 daily_scheduler calculation per vehicle per day
```

---

### Notification Deduplication

Jangan spam.

Status reminder utama hanya saat transition:

```text
not_needed → approaching
approaching → urgent
```

Reminder tanggal tertentu boleh menggunakan deduplication key/rule sederhana.

---

### Notification Customer UI

Implement:

```text
/notifications
```

Features:

- list,
- read/unread,
- open target,
- mark read.

---

### Fuzzy Config Admin

Filament:

- view current config,
- edit via new version,
- preview values,
- reset default,
- view config history if simple.

Rule base:

```text
read-only
```

Tidak boleh edit/add/delete rules.

---

### Fuzzy Rule Viewer

Admin dapat melihat 18 rule human-readable.

---

### Recalculation on Config Change

Setelah config baru aktif:

- historical calculations tetap utuh,
- active vehicles dapat direcalculate melalui job/batch sederhana.

---

### Reports

Filament custom page.

Filter:

```text
date range
```

Show:

```text
total bookings
completed services
service revenue
unique customers
transaction/service list
```

Optional jika cepat dan stabil:

```text
CSV export
PDF export
```

Jangan menunda release hanya untuk PDF jika fitur inti sudah lengkap.

---

### Admin Dashboard Final

Tampilkan hanya data actionable:

```text
Booking Hari Ini
Menunggu Konfirmasi
Sedang Servis
Kendaraan Segera Servis
Today's bookings
Urgent vehicles
```

Tidak perlu chart dekoratif.

---

## Deliverables

- in-app notifications,
- email queue,
- scheduler,
- fuzzy admin configuration,
- reports,
- final admin dashboard.

---

## Acceptance Criteria

```text
[ ] In-app notification bekerja
[ ] Email queued, bukan blocking request utama
[ ] Scheduler command dapat dijalankan
[ ] Daily recalculation tidak duplicate berlebihan
[ ] Status reminder tidak spam setiap hari
[ ] Admin config fuzzy versioned
[ ] 18 rule read-only
[ ] Config change tidak merusak historical calculations
[ ] Report period filter benar
[ ] Admin dashboard actionable
[ ] php artisan test lulus
```

---

# 11. T09 — Full Regression, Security, Responsive, Accessibility, Performance & Release Readiness

## Objective

Melakukan audit menyeluruh dan memperbaiki seluruh issue sebelum project dianggap selesai.

Task ini bukan sekadar "testing"; Codex harus **menemukan dan memperbaiki** issue sampai release gate lulus.

---

## Scope

### Full Regression

Test seluruh journey:

```text
Register
Login
Create Vehicle
View Recommendation
Update Odometer
Recommendation Changes
Create Booking
Admin Confirm
Admin Start Service
Admin Complete Service
Customer See History
Baseline Reset
New Recommendation
```

---

### Fuzzy Audit

Verifikasi:

- membership boundaries,
- 18 rules,
- alpha,
- z,
- weighted average,
- score,
- status mapping,
- guard,
- due date.

Bandingkan beberapa scenario dengan manual calculation.

---

### Security Audit

Test:

```text
cross-user vehicle access
cross-user booking access
cross-user service history access
cross-user fuzzy calculation access
customer admin access
mass assignment
tampered IDs
CSRF assumptions
unsafe direct object access
```

---

### Database Audit

Check:

- migration fresh,
- seed,
- FK,
- indexes,
- no orphan data,
- transaction rollback.

---

### Responsive Audit

Minimum widths:

```text
360
390
768
1024
1280
1440
```

Audit:

- dashboard,
- vehicle pages,
- fuzzy detail,
- booking calendar,
- booking detail,
- service history,
- profile,
- auth,
- landing page.

---

### Accessibility Audit

Check:

- keyboard navigation,
- visible focus,
- form labels,
- validation association,
- modal focus trap,
- Escape,
- focus return,
- touch target,
- contrast,
- status not color-only.

Fix medium/high issues before completion.

---

### Visual / Anti-AI-Slop Audit

Review every major page.

Fail jika terdapat:

- excessive card layout,
- gradient/glow,
- glassmorphism,
- poor hierarchy,
- inconsistent radius,
- random colors,
- oversized dashboard widgets,
- generic SaaS visuals.

---

### Performance Audit

Check:

- N+1,
- heavy dashboard query,
- recommendation list,
- booking availability,
- admin tables,
- report query,
- page render.

Use eager loading/pagination/indexes where needed.

---

### Queue & Scheduler Audit

Verify:

```text
queue works
failed jobs visible
notifications work
scheduler commands work
no duplicate spam
```

---

### Docker & Deployment Audit

Verifikasi development stack dari kondisi bersih:

```bash
docker compose down
docker compose up -d --build
```

Check:

```text
nginx reachable
Laravel healthy
MySQL ready
queue running
scheduler running
Mailpit available in development
Vite/build works
persistent database volume works
```

Verifikasi production-oriented build/config:

```text
no Mailpit dependency
no Vite dev server dependency
APP_DEBUG=false supported
queue/scheduler use same app image
only required ports exposed
```

Simulasikan setup seperti laptop client baru berdasarkan README dan perbaiki dokumentasi jika masih ada langkah manual yang tidak perlu.

---

### Production Build

Run project quality checks.

Typical melalui Docker runtime:

```bash
docker compose exec app php artisan test
docker compose exec node npm run build
```

atau command production build yang sesuai Dockerfile final.

dan static/lint tooling yang tersedia.

---

### Demo Seeder / Demo Scenario

Siapkan development/demo data yang membantu presentasi:

```text
1 admin
2–3 customers
3–5 vehicles
different recommendation statuses
booking examples
service history examples
```

Demo data tidak aktif otomatis pada production seeding.

---

### Documentation Sync

Jika implementation final berbeda secara legitimate dari docs:

- update PRD,
- DATABASE,
- ARCHITECTURE,
- DESIGN SYSTEM,
- TASKS completion status,

agar dokumentasi sesuai sistem final.

Jangan membiarkan docs menjelaskan behavior yang tidak ada.

---

## Release Gate

Project hanya dinyatakan selesai jika:

```text
[ ] Full automated tests PASS
[ ] Production frontend build PASS
[ ] Critical user journey PASS
[ ] Fuzzy scenario audit PASS
[ ] No Critical security issue
[ ] No High security issue
[ ] No known data-loss bug
[ ] No booking overcapacity bug
[ ] No broken service transaction
[ ] Responsive core pages PASS
[ ] Accessibility tidak memiliki Critical/High issue
[ ] No obvious AI-slop violation
[ ] Queue works
[ ] Scheduler works
[ ] Docker clean-start PASS
[ ] Client setup instructions PASS
[ ] Production-oriented Docker build/config PASS
[ ] MySQL persistence verified
[ ] Demo scenario ready
[ ] Docs synchronized
```

---

# 12. Dependency Matrix

| Task | Depends On | Can Start When |
|---|---|---|
| T01 | None | Immediately |
| T02 | T01 | Docker runtime verified |
| T03 | T02 | Laravel foundation stable |
| T04 | T03 | Schema + rules ready |
| T05 | T04 | Recommendation engine ready |
| T06 | T03 + T05 | Vehicle/customer foundation ready |
| T07 | T06 | Booking workflow ready |
| T08 | T07 | Core product loop complete |
| T09 | T01–T08 | All features complete |
---

# 13. Recommended Execution Order for Speed

Gunakan satu Codex session/process per task besar.

Recommended:

```text
Run 1 → T01 Docker & Runtime
Run 2 → T02 Laravel Foundation
Run 3 → T03 Database & Domain
Run 4 → T04 Fuzzy Engine
Run 5 → T05 Customer Core
Run 6 → T06 Booking
Run 7 → T07 Service/Admin
Run 8 → T08 Notifications/Reports
Run 9 → T09 Final QA
```

Jangan meminta Codex mengerjakan seluruh project dalam satu giant prompt.

Alasannya:

- context terlalu besar,
- mudah melewatkan requirement,
- testing sulit dikontrol,
- regression lebih sulit dilacak.

**9 grouped runs** masih cepat, dan Docker sudah dikunci sejak run pertama sehingga environment tidak berubah-ubah sepanjang development.
---

# 14. Codex Execution Protocol

Untuk setiap task, gunakan pola:

```text
1. Read all relevant project docs.
2. Inspect current implementation.
3. Make a short execution plan internally.
4. Implement the entire requested task.
5. Add/update automated tests.
6. Run tests and build checks.
7. Fix failures.
8. Review for regressions.
9. Summarize:
   - changes
   - files/modules added
   - tests run
   - results
   - remaining blockers
```

Codex tidak perlu berhenti meminta approval untuk keputusan kecil yang sudah dijelaskan oleh docs.

Jika menemui ambiguity:

- gunakan locked docs,
- pilih implementasi paling sederhana,
- jangan memperluas scope.

---

# 15. Task Completion Report Format

Setelah setiap task, Codex harus mengembalikan:

```text
TASK: Txx — Name
STATUS: PASS / PASS WITH NOTES / BLOCKED

IMPLEMENTED
- ...

TESTS
- command
- result

VALIDATION
- acceptance criterion 1: PASS
- acceptance criterion 2: PASS

ISSUES
- none
atau
- issue + severity + impact

NEXT
- ready for Txx
```

Tujuannya agar progress mudah dilacak tanpa membaca seluruh Git diff.

---

# 16. Prioritas Jika Waktu Sangat Terbatas

Jika deadline semakin dekat, jangan memotong core logic.

Prioritas:

## Tier 1 — Tidak Boleh Dipotong

```text
Docker runtime/setup
Auth
Database correctness
Vehicle
Odometer
Fuzzy engine
Recommendation
Booking
Admin confirmation
Service completion
Service history
Authorization
Critical testing
```

## Tier 2 — Penting

```text
Notifications
Scheduler
Fuzzy config admin
Admin dashboard
Responsive polish
Accessibility
```

## Tier 3 — Bisa Disederhanakan

```text
PDF report export
advanced report chart
email styling
extra landing sections
advanced filtering
demo visual extras
```

Jangan mengorbankan fuzzy correctness demi visual tambahan.

---

# 17. MVP Completion Definition

MVP dianggap benar-benar selesai saat scenario ini dapat dilakukan tanpa manual database editing:

```text
1. Customer register.
2. Customer menambah kendaraan.
3. Recommendation pertama muncul.
4. Customer update odometer.
5. Fuzzy score/recommendation berubah.
6. Customer melihat detail perhitungan.
7. Customer membuat booking.
8. Admin mengonfirmasi.
9. Admin memulai servis.
10. Admin menyelesaikan servis.
11. Service history muncul.
12. Baseline kendaraan diperbarui.
13. Recommendation siklus baru muncul.
14. Customer menerima notification relevan.
15. Admin dapat melihat laporan operasional.
```

---

# 18. Final Task Philosophy

Project harus selesai cepat dengan prinsip:

```text
Large coherent tasks
>
many tiny tasks
```

Tetapi setiap large task harus tetap:

```text
implement
+
test
+
validate
+
finish
```

sebelum berpindah.

Target bukan menghasilkan code sebanyak mungkin dalam satu prompt.

Targetnya adalah menghasilkan **vertical slices yang selesai**, sehingga setelah setiap Codex run project semakin dekat ke kondisi production/demo-ready dan bukan sekadar semakin banyak file.
