# Product Requirements Document (PRD)

## Sistem Informasi Bengkel & Penjadwalan Servis Rutin dengan Fuzzy Tsukamoto

**Document Version:** 1.0  
**Status:** Locked Baseline  
**Project Type:** Tugas Akhir D3  
**Primary Stack:** Laravel, MySQL, Livewire, Filament, Tailwind CSS  
**Primary Users:** Customer dan Admin Bengkel  

---

## 1. Ringkasan Produk

Produk ini adalah sistem informasi bengkel berbasis web yang membantu customer memantau kebutuhan servis rutin kendaraan, mendapatkan rekomendasi waktu servis, membuat booking servis, serta melihat riwayat servis kendaraan.

Keunikan utama sistem adalah penerapan **Fuzzy Tsukamoto** untuk menghasilkan skor urgensi servis berdasarkan kondisi penggunaan kendaraan.

Sistem tidak hanya menampilkan jadwal servis secara statis, tetapi mengolah:

1. progress kilometer sejak servis terakhir,
2. progress waktu sejak servis terakhir,
3. intensitas penggunaan kendaraan,

untuk menghasilkan:

- skor urgensi servis 0–100,
- status rekomendasi,
- rentang waktu tindakan,
- tanggal servis yang paling direkomendasikan.

Aplikasi terdiri dari dua area utama:

- **Customer Area** menggunakan Livewire + Tailwind CSS.
- **Admin Area** menggunakan Filament.

---

## 2. Tujuan Produk

### 2.1 Tujuan Utama

Membangun sistem bengkel yang:

- membantu customer mengetahui kapan kendaraannya perlu servis,
- membantu bengkel mengelola booking dan riwayat servis,
- menyediakan rekomendasi servis yang lebih adaptif daripada jadwal statis,
- menerapkan metode Fuzzy Tsukamoto secara transparan dan dapat dipertanggungjawabkan,
- tetap sederhana untuk dipahami dan didemonstrasikan pada sidang tugas akhir.

### 2.2 Tujuan Akademik

Sistem harus dapat menunjukkan proses Fuzzy Tsukamoto secara jelas:

1. input,
2. fuzzifikasi,
3. evaluasi rule,
4. alpha-predicate,
5. nilai `z`,
6. defuzzifikasi weighted average,
7. skor crisp,
8. interpretasi skor menjadi rekomendasi servis.

---

## 3. Non-Goals

Versi MVP tidak mencakup:

- inventory spare part,
- supplier,
- pembelian barang,
- payroll teknisi,
- multi-cabang bengkel,
- multi-tenant SaaS,
- role teknisi,
- akuntansi lengkap,
- CRM kompleks,
- payment gateway,
- integrasi WhatsApp API,
- mobile native app,
- IoT kendaraan,
- machine learning,
- AI assistant,
- marketplace bengkel.

Role hanya:

- Customer
- Admin Bengkel

---

## 4. Persona

### 4.1 Customer

Pemilik kendaraan yang ingin:

- menyimpan data kendaraan,
- mencatat odometer,
- mengetahui kebutuhan servis,
- mendapatkan rekomendasi servis,
- membuat booking,
- menerima reminder,
- melihat histori servis.

### 4.2 Admin Bengkel

Pengelola bengkel yang ingin:

- melihat booking,
- mengelola jadwal operasional,
- mengelola customer dan kendaraan,
- memproses servis,
- mencatat hasil servis,
- mengelola service profile,
- melihat konfigurasi fuzzy,
- melihat laporan.

---

## 5. Core Product Loop

```text
Customer Register/Login
        ↓
Tambah Kendaraan
        ↓
Masukkan Baseline Servis
        ↓
Update Odometer
        ↓
Sistem Hitung Fuzzy
        ↓
Tampilkan Rekomendasi
        ↓
Customer Booking Servis
        ↓
Admin Konfirmasi
        ↓
Servis Dimulai
        ↓
Admin Selesaikan Servis
        ↓
Service History Dibuat
        ↓
Baseline Kendaraan Diperbarui
        ↓
Fuzzy Reset ke Siklus Baru
```

---

## 6. Fuzzy Tsukamoto Specification

### 6.1 Tujuan

Fuzzy digunakan untuk menjawab:

> Seberapa mendesak kendaraan perlu melakukan servis rutin?

Output matematis:

```text
Urgency Score = 0–100
```

---

### 6.2 Input 1 — Progress Kilometer

```text
km_since_service =
current_odometer - baseline_odometer

progress_km =
(km_since_service / service_interval_km) × 100
```

Himpunan:

- Aman
- Mendekati
- Kritis

Baseline membership:

- `<=70%` dominan Aman
- `70–90%` transisi Aman → Mendekati
- `90–100%` transisi Mendekati → Kritis
- `>=100%` dominan Kritis

---

### 6.3 Input 2 — Progress Waktu

```text
progress_time =
(days_since_service / service_interval_days) × 100
```

Himpunan:

- Aman
- Mendekati
- Kritis

Membership menggunakan struktur yang sama dengan progress kilometer.

---

### 6.4 Input 3 — Intensitas Penggunaan

Sistem menghitung average daily mileage dari odometer history.

```text
average_daily_km =
odometer_delta / day_delta
```

Baseline penggunaan:

```text
baseline_daily_usage =
service_interval_km / service_interval_days
```

Intensitas:

```text
usage_intensity =
(average_daily_km / baseline_daily_usage) × 100
```

Himpunan:

- Normal
- Intensif

Baseline membership:

- `<=80%` dominan Normal
- `80–120%` transisi Normal → Intensif
- `>=120%` dominan Intensif

---

### 6.5 Rule Base

Total kombinasi:

```text
3 × 3 × 2 = 18 rules
```

| Rule | Progress KM | Progress Waktu | Penggunaan | Consequent |
|---|---|---|---|---|
| R01 | Aman | Aman | Normal | Tidak Mendesak |
| R02 | Aman | Aman | Intensif | Tidak Mendesak |
| R03 | Aman | Mendekati | Normal | Tidak Mendesak |
| R04 | Aman | Mendekati | Intensif | Mendesak |
| R05 | Aman | Kritis | Normal | Mendesak |
| R06 | Aman | Kritis | Intensif | Mendesak |
| R07 | Mendekati | Aman | Normal | Tidak Mendesak |
| R08 | Mendekati | Aman | Intensif | Mendesak |
| R09 | Mendekati | Mendekati | Normal | Mendesak |
| R10 | Mendekati | Mendekati | Intensif | Mendesak |
| R11 | Mendekati | Kritis | Normal | Mendesak |
| R12 | Mendekati | Kritis | Intensif | Mendesak |
| R13 | Kritis | Aman | Normal | Mendesak |
| R14 | Kritis | Aman | Intensif | Mendesak |
| R15 | Kritis | Mendekati | Normal | Mendesak |
| R16 | Kritis | Mendekati | Intensif | Mendesak |
| R17 | Kritis | Kritis | Normal | Mendesak |
| R18 | Kritis | Kritis | Intensif | Mendesak |

---

### 6.6 Alpha Predicate

Setiap rule menggunakan operator AND:

```text
α = MIN(membership_1, membership_2, membership_3)
```

---

### 6.7 Defuzzifikasi

Setiap rule menghasilkan nilai `z`.

Fungsi keanggotaan output monoton untuk invers Tsukamoto didefinisikan pada rentang berikut:

```text
not_urgent: μ(z) = (40 - z) / 40, 0 ≤ z ≤ 40
urgent:     μ(z) = (z - 40) / 60, 40 ≤ z ≤ 100
```

Dengan firing strength `α`, nilai inversnya adalah `z = 40 × (1 - α)` untuk `not_urgent` dan `z = 40 + 60 × α` untuk `urgent`. Skor crisp tetap diinterpretasikan dengan batas status pada bagian 6.8.

Semua hasil digabungkan:

```text
Z = Σ(α × z) / Σα
```

Output akhir berupa crisp score 0–100.

---

### 6.8 Status Aplikasi

Skor crisp diterjemahkan menjadi:

| Score | Status | Rentang Tindakan |
|---:|---|---|
| 0 – <40 | Belum Perlu Servis | >30 hari |
| 40 – <70 | Servis Mendekat | 8–30 hari |
| 70 – 100 | Segera Servis | 0–7 hari |

Status UI merupakan interpretasi business layer dan bukan consequent fuzzy internal.

---

### 6.9 Recommended Service Date

Tanggal rekomendasi dihitung dari dua proyeksi:

#### Kilometer

```text
remaining_km =
service_interval_km - km_since_service

estimated_days_by_km =
remaining_km / average_daily_km
```

#### Waktu

```text
estimated_due_by_time =
baseline_service_date + interval_days
```

Sistem memilih estimasi yang lebih cepat sebagai base due date.

---

### 6.10 Recommendation Guard

Jika proyeksi aktual lebih mendesak daripada status fuzzy, sistem dapat menaikkan **final recommendation status** tanpa mengubah fuzzy score.

Contoh:

```text
fuzzy_score = 68.40
fuzzy_status = approaching

estimated_due_days = 5

final_status = urgent
guard_applied = true
```

Fuzzy score tetap disimpan apa adanya untuk menjaga integritas metode penelitian.

---

## 7. Customer Features

### 7.1 Authentication

- Register
- Login
- Logout
- Forgot Password
- Reset Password

Admin tidak dapat register melalui public registration.

---

### 7.2 Customer Dashboard

Dashboard harus menjawab:

> Apakah kendaraan saya perlu servis sekarang?

Informasi utama:

- active vehicle selector,
- vehicle identity,
- urgency score,
- recommendation status,
- recommended service date,
- service window,
- current odometer,
- progress kilometer,
- progress waktu,
- average usage,
- active booking,
- CTA booking servis.

Tidak menggunakan dashboard stat-card berlebihan.

---

### 7.3 Vehicle Management

Customer dapat:

- melihat daftar kendaraan,
- menambah kendaraan,
- melihat detail kendaraan,
- mengedit data identitas kendaraan.

Field utama:

- vehicle name,
- brand,
- model,
- year,
- plate number,
- current odometer,
- last service date,
- last service odometer.

Customer tidak boleh bebas mengubah baseline servis setelah data sudah menjadi bagian dari history resmi.

---

### 7.4 Odometer

Customer dapat:

- update odometer,
- melihat odometer sebelumnya,
- melihat histori odometer.

Validation:

```text
new_odometer >= latest_odometer
```

Update odometer akan memicu recalculation recommendation.

---

### 7.5 Service Recommendation

Customer dapat melihat:

- score 0–100,
- recommendation status,
- service window,
- recommended service date,
- progress kilometer,
- progress waktu,
- usage intensity,
- explanation singkat.

CTA:

- Buat Jadwal Servis
- Lihat Detail Perhitungan

---

### 7.6 Detail Perhitungan Fuzzy

Halaman ini menampilkan:

#### Input

- service interval,
- current odometer,
- baseline odometer,
- km since service,
- progress kilometer,
- days since service,
- progress waktu,
- average daily usage,
- usage intensity.

#### Fuzzification

Membership values untuk:

- Aman,
- Mendekati,
- Kritis,
- Normal,
- Intensif.

#### Active Rules

Untuk setiap active rule:

- rule code,
- antecedent,
- consequent,
- alpha,
- z value.

#### Defuzzification

```text
Z = Σ(α × z) / Σα
```

#### Result

- fuzzy score,
- fuzzy status,
- final status,
- recommendation date.

---

### 7.7 Booking

Customer dapat:

- memilih kendaraan,
- memilih tanggal,
- memilih slot waktu,
- melihat availability,
- menambahkan keluhan/catatan,
- membuat booking,
- melihat booking aktif,
- melihat booking history,
- membatalkan booking jika status masih memungkinkan.

Status:

- Pending
- Confirmed
- In Service
- Completed
- Cancelled

---

### 7.8 Service History

Customer dapat melihat:

- tanggal servis,
- kendaraan,
- odometer,
- jenis servis,
- keluhan,
- pekerjaan dilakukan,
- catatan bengkel,
- total biaya.

---

### 7.9 Notifications

Notification event:

- status berubah menjadi Servis Mendekat,
- status berubah menjadi Segera Servis,
- booking dikonfirmasi,
- booking dijadwalkan ulang,
- reminder H-1,
- servis selesai.

Channel MVP:

- in-app database notification,
- email.

---

### 7.10 Profile

Customer dapat mengubah:

- name,
- email,
- phone,
- password.

---

## 8. Admin Features

Admin area menggunakan Filament.

---

### 8.1 Admin Dashboard

Menampilkan informasi operasional:

- booking hari ini,
- booking menunggu konfirmasi,
- servis sedang berjalan,
- kendaraan yang segera servis,
- daftar booking hari ini.

Fokus pada pekerjaan yang perlu dilakukan, bukan vanity metrics.

---

### 8.2 Booking Management

Admin dapat:

- melihat seluruh booking,
- filter booking,
- melihat detail booking,
- confirm booking,
- reschedule,
- cancel,
- start service,
- complete service.

---

### 8.3 Customer Management

Admin dapat:

- search customer,
- melihat customer detail,
- melihat kendaraan customer,
- melihat booking aktif,
- melihat service history.

---

### 8.4 Vehicle Management

Admin dapat:

- melihat seluruh kendaraan,
- filter berdasarkan recommendation status,
- search plate number,
- melihat odometer history,
- melihat fuzzy recommendation,
- melihat service history,
- mengganti service profile,
- melakukan koreksi odometer dengan alasan.

---

### 8.5 Service Management

Admin dapat:

- melihat service history,
- membuka detail service,
- complete service,
- melakukan koreksi terkontrol.

Service completion menghasilkan:

- service record,
- odometer log,
- baseline baru,
- booking completed,
- fuzzy recalculation.

---

### 8.6 Service Profile

Admin dapat membuat dan mengubah:

- profile name,
- interval kilometer,
- interval hari,
- description,
- active status.

Customer hanya melihat service profile yang diterapkan pada kendaraannya.

---

### 8.7 Workshop Scheduling

Admin dapat mengatur:

- opening hours per weekday,
- closing hours,
- slot duration,
- capacity per slot,
- holiday,
- schedule exception,
- partial operating hours.

---

### 8.8 Fuzzy Configuration

Admin hanya dapat mengubah parameter sederhana:

- progress safe end,
- approaching threshold,
- critical threshold,
- usage normal threshold,
- usage intensive threshold.

Admin dapat:

- preview membership configuration,
- melihat rule base,
- reset ke default.

Rule base read-only.

Admin tidak dapat:

- membuat rule baru,
- menghapus rule,
- mengubah consequent.

---

### 8.9 Reports

Filter berdasarkan periode.

Output utama:

- total booking,
- completed services,
- total service revenue,
- unique customers,
- service transaction list.

Optional export:

- CSV
- PDF

---

## 9. Booking Availability Rules

Availability ditentukan oleh:

```text
operating_hours
+
schedule_exceptions
+
slot_duration
+
slot_capacity
+
existing_bookings
```

Slot dihitung secara dinamis.

Tidak ada tabel pre-generated booking slots.

Server wajib melakukan availability check ulang saat booking disimpan untuk mencegah race condition dan overbooking.

---

## 10. Booking State Machine

Valid transitions:

```text
Pending
├── Confirmed
└── Cancelled

Confirmed
├── In Service
└── Cancelled

In Service
└── Completed
```

Invalid:

```text
Completed → Pending
Cancelled → In Service
```

Transition harus divalidasi di backend.

---

## 11. Database Domain

Core tables:

1. users
2. vehicles
3. service_profiles
4. odometer_logs
5. fuzzy_configs
6. fuzzy_rules
7. fuzzy_calculations
8. fuzzy_rule_results
9. bookings
10. booking_events
11. service_records
12. workshop_settings
13. operating_hours
14. schedule_exceptions

Technical Laravel tables dapat digunakan jika diperlukan:

- notifications
- jobs
- job_batches
- failed_jobs
- sessions
- password_reset_tokens

---

## 12. Architecture

### 12.1 Application Style

Monolithic Laravel Application.

```text
Browser
  ↓
Customer UI / Admin UI
  ↓
Application Actions
  ↓
Domain Services
  ↓
Eloquent
  ↓
MySQL
```

---

### 12.2 Presentation

Customer:

- Livewire
- Blade
- Tailwind CSS
- Alpine.js untuk interaction ringan

Admin:

- Filament

---

### 12.3 Application Layer

Action classes untuk workflow penting.

Contoh:

- CreateVehicleAction
- UpdateOdometerAction
- CreateBookingAction
- ConfirmBookingAction
- RescheduleBookingAction
- StartServiceAction
- CompleteServiceAction
- CancelBookingAction

---

### 12.4 Fuzzy Domain

```text
RecommendationService
    ├── ProgressCalculator
    ├── UsageCalculator
    ├── FuzzyConfigResolver
    ├── FuzzyTsukamotoEngine
    │   ├── MembershipCalculator
    │   ├── RuleEvaluator
    │   └── Defuzzifier
    ├── ServiceDueDateCalculator
    └── RecommendationGuard
```

Fuzzy engine harus pure calculation dan dapat diuji tanpa database.

---

### 12.5 Queue

Menggunakan Laravel Database Queue.

Digunakan untuk:

- email,
- reminder,
- background notification jika diperlukan.

Redis tidak menjadi dependency MVP.

---

### 12.6 Scheduler

Laravel Scheduler digunakan untuk:

- daily recommendation recalculation,
- service reminder,
- booking H-1 reminder.

---

### 12.7 Testing

Menggunakan Pest.

Prioritas test:

- Fuzzy unit tests
- Authorization
- Booking availability
- Booking state transitions
- Service completion transaction
- Recommendation recalculation

---

## 13. Design System — Locked

### 13.1 Design Direction

Nama internal:

**Modern Workshop Editorial**

Inspirasi:

- Claude → visual calmness
- Linear → information hierarchy
- Rivian → vehicle information
- Notion Calendar → scheduling
- Tesla Service → service workflow
- Resend → micro-interactions and form polish

Website **tidak boleh memiliki AI-slop aesthetic**.

---

### 13.2 Anti AI-Slop Rules

Dilarang:

- purple-blue gradient,
- aurora gradient,
- glow orb,
- glassmorphism,
- excessive blur,
- decorative sparkles,
- robot/AI iconography,
- excessive oversized cards,
- arbitrary floating cards,
- gradients hanya untuk terlihat “modern”,
- excessive pills,
- rounded-3xl untuk semua komponen,
- fake dashboard charts,
- meaningless statistics,
- excessive shadows,
- generic copy seperti “Experience the future of...”.

Gunakan:

- whitespace,
- typography,
- divider,
- subtle border,
- purposeful grouping,
- clear hierarchy,
- restrained color,
- functional motion.

---

### 13.3 Color Tokens

Base palette:

```text
--canvas:          #F5F4F0
--surface:         #FFFFFF
--surface-muted:   #EFEEE9

--text-primary:    #191918
--text-secondary:  #6F6E69
--text-muted:      #96938B

--border:          #E3E1DB
--border-strong:   #CAC7BF

--brand:           #A84F32
--brand-hover:     #93452D
```

Primary actions use dark neutral:

```text
--button-primary: #191918
--button-primary-text: #FFFFFF
```

Brand copper digunakan sebagai accent, bukan seluruh UI.

---

### 13.4 Semantic Colors

```text
--success: #3E7453
--warning: #A56A19
--danger:  #B8493F
--neutral: #77746D
```

Recommendation mapping:

- Belum Perlu → success
- Servis Mendekat → warning
- Segera Servis → danger
- Unavailable → neutral

Semantic colors digunakan hemat.

---

### 13.5 Typography

Primary UI:

**Geist**

Fallback:

```text
Inter, ui-sans-serif, system-ui, sans-serif
```

Optional landing editorial accent:

**Instrument Serif**

Serif tidak digunakan pada dashboard/form/table.

Recommended hierarchy:

```text
Display: 48–56px
H1: 32px
H2: 24px
H3: 18px
Body: 15–16px
Small: 13–14px
Caption: 12px
```

---

### 13.6 Spacing

Base spacing unit:

```text
4px
```

Common scale:

```text
4
8
12
16
20
24
32
40
48
64
```

Whitespace digunakan untuk hierarchy dan tidak digantikan oleh card.

---

### 13.7 Radius

```text
Inputs / Buttons: 8px
Cards: 10–12px
Modal: 14px
Badge: pill only where semantically appropriate
```

Tidak menggunakan large rounded container secara berlebihan.

---

### 13.8 Shadows

Default card:

```text
no shadow
```

Gunakan border + surface contrast.

Shadow hanya untuk:

- dropdown,
- popover,
- modal,
- floating contextual menu.

---

### 13.9 Card Philosophy

Card hanya digunakan ketika informasi membutuhkan grouping yang jelas.

Dilarang mengubah setiap metric menjadi card.

Prioritaskan:

- typography,
- alignment,
- spacing,
- divider,
- sections.

---

### 13.10 Navigation

Customer desktop:

- sidebar 220–240px,
- content width terbatas,
- active state subtle,
- tidak menggunakan colorful navigation.

Admin mengikuti Filament navigation dengan branding yang sama.

---

### 13.11 Responsive

Customer UI wajib mobile responsive.

Prioritas:

1. mobile,
2. tablet,
3. desktop.

Booking, dashboard, vehicle detail, recommendation, dan service history harus tetap usable pada mobile.

Admin Filament minimal usable pada tablet/desktop.

---

### 13.12 Motion

Animation hanya untuk:

- state transition,
- modal,
- dropdown,
- loading,
- progressive disclosure.

Durasi pendek:

```text
150–250ms
```

Tidak ada decorative looping animations.

---

## 14. Empty, Loading, and Error States

Wajib disediakan untuk:

- no vehicle,
- recommendation unavailable,
- no booking,
- no service history,
- no notifications,
- no search result,
- no available slots,
- calculation error.

Error fuzzy tidak boleh fallback ke score palsu.

Jika perhitungan gagal:

```text
Recommendation unavailable
```

dengan opsi retry.

---

## 15. Security Requirements

- Password hashing native Laravel.
- CSRF protection.
- Authorization Policies.
- Customer hanya mengakses resource miliknya.
- Admin panel hanya dapat diakses admin.
- Server-side validation.
- Booking availability divalidasi ulang di server.
- Historical service records tidak dihapus secara normal.
- Critical multi-step workflows menggunakan database transaction.

---

## 16. Performance Requirements

Target MVP:

- dashboard dapat dimuat tanpa N+1 query,
- list menggunakan pagination,
- expensive report query dibatasi berdasarkan periode,
- fuzzy calculation tidak dijalankan pada setiap page refresh,
- maksimal satu scheduled calculation per vehicle per day,
- database indexes pada foreign key dan common lookup fields.

---

## 17. Accessibility Requirements

Target minimal:

- semantic HTML,
- keyboard accessible navigation,
- visible focus states,
- label terhubung dengan input,
- error message programmatically associated,
- sufficient color contrast,
- status tidak hanya bergantung pada warna,
- modal memiliki focus management,
- interactive target cukup besar untuk mobile.

---

## 18. Key Acceptance Criteria

### Vehicle

- Customer dapat menambah kendaraan.
- Odometer baru tidak boleh lebih rendah dari histori terakhir.
- Customer tidak dapat mengakses kendaraan milik user lain.

### Fuzzy

- Input yang sama menghasilkan score yang sama.
- Score selalu 0–100.
- Tidak terjadi division by zero.
- Rule evaluation sesuai 18 rule locked.
- Calculation history dapat direproduksi.
- Detail fuzzy menampilkan alpha dan z rule aktif.

### Booking

- Slot penuh tidak dapat dibooking.
- Hari tutup tidak dapat dipilih.
- Cancelled booking tidak memakai slot capacity.
- Booking status hanya dapat berpindah melalui transition valid.

### Service

Saat service selesai:

- service record dibuat,
- booking completed,
- odometer log dibuat,
- vehicle baseline diperbarui,
- recommendation dihitung ulang.

Semua terjadi dalam transaction.

### Notifications

- Customer tidak menerima reminder status yang sama setiap hari.
- Notification dikirim saat status berubah atau event penting terjadi.

---

## 19. Testing Strategy

### Unit Test

Prioritas:

- MembershipCalculator
- RuleEvaluator
- Defuzzifier
- FuzzyTsukamotoEngine
- RecommendationGuard
- ServiceDueDateCalculator

### Feature Test

- auth
- vehicle CRUD
- ownership
- odometer
- booking
- slot capacity
- admin actions
- service completion
- notification trigger

### Fuzzy Scenario Matrix

Wajib menguji kondisi:

```text
low km / low time / normal usage
low km / low time / intensive usage
approaching km / low time / normal usage
approaching km / low time / intensive usage
approaching km / approaching time / normal usage
approaching km / approaching time / intensive usage
critical km / low time / normal usage
low km / critical time / normal usage
critical km / critical time / intensive usage
```

---

## 20. Development Priorities

Urutan implementasi:

### Phase 1 — Foundation

- Laravel project setup
- environment
- MySQL
- Livewire
- Filament
- Tailwind
- authentication
- roles
- base design tokens

### Phase 2 — Database

- migrations
- models
- relationships
- enums
- factories
- seeders

### Phase 3 — Fuzzy Engine

- membership calculator
- rules
- rule evaluator
- defuzzifier
- fuzzy tests
- recommendation service
- projection
- guard

### Phase 4 — Vehicle & Odometer

- vehicle management
- baseline
- odometer update
- odometer history
- automatic recalculation

### Phase 5 — Customer Recommendation UI

- dashboard
- recommendation overview
- recommendation detail
- fuzzy calculation detail

### Phase 6 — Booking

- operating hours
- schedule exceptions
- slot availability
- customer booking
- admin booking management
- booking timeline

### Phase 7 — Service Workflow

- start service
- complete service
- service record
- baseline reset
- service history

### Phase 8 — Notifications & Scheduler

- in-app notifications
- email
- queue
- scheduled recalculation
- reminder jobs

### Phase 9 — Admin Completion

- customer resource
- vehicle resource
- service profile
- fuzzy configuration
- reports

### Phase 10 — Quality Assurance

- authorization audit
- fuzzy validation
- responsive audit
- accessibility audit
- performance audit
- regression test
- final demo scenario

---

## 21. Definition of Done

Project dianggap selesai ketika:

- seluruh core loop dapat dijalankan end-to-end,
- fuzzy calculation dapat dijelaskan dan direproduksi,
- customer dan admin authorization aman,
- customer UI responsive,
- admin workflow dapat digunakan melalui Filament,
- booking tidak dapat overbook,
- service completion menjaga data consistency,
- notification bekerja,
- scheduler bekerja,
- seluruh critical automated tests lulus,
- tidak terdapat AI-slop visual pattern,
- hasil akhir cukup sederhana untuk dijelaskan oleh mahasiswa saat sidang.

---

## 22. Final Product Principle

Setiap keputusan fitur harus mendukung salah satu dari tiga hal:

1. **Membantu customer mengetahui kapan servis diperlukan.**
2. **Membantu customer dan bengkel menjalankan proses servis.**
3. **Membuat metode Fuzzy Tsukamoto transparan dan dapat dipertanggungjawabkan.**

Jika suatu fitur tidak mendukung ketiganya, fitur tersebut tidak termasuk MVP.
