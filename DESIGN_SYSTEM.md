# Design System Specification

## Sistem Informasi Bengkel & Penjadwalan Servis Rutin dengan Fuzzy Tsukamoto

**Document Version:** 1.0  
**Status:** Locked Baseline  
**Design Direction:** Modern Workshop Editorial  
**Customer UI:** Livewire + Blade + Tailwind CSS  
**Admin UI:** Filament  
**Related Documents:**  
- `PRD.md`
- `DATABASE.md`
- `ARCHITECTURE.md`

---

# 1. Tujuan Dokumen

Dokumen ini mendefinisikan sistem desain untuk aplikasi bengkel dan penjadwalan servis rutin.

Tujuan utamanya:

- menjaga konsistensi visual dan interaksi,
- mencegah tampilan generik atau AI-generated,
- membuat aplikasi customer terasa modern, tenang, dan jelas,
- membuat admin tetap padat informasi tetapi tidak berantakan,
- memastikan status servis mudah dipahami,
- memastikan UI tetap usable pada mobile,
- menjadi acuan implementasi Tailwind dan Filament.

---

# 2. Design Direction

Nama internal:

```text
Modern Workshop Editorial
```

Karakter utama:

- clean,
- warm,
- restrained,
- functional,
- editorial,
- automotive tanpa terlihat "racing",
- modern tanpa futuristik berlebihan,
- data-rich tetapi tetap tenang.

Inspirasi arah visual:

- Claude → calmness, whitespace, typography,
- Linear → hierarchy, dense-but-calm product UI,
- Rivian → vehicle information at a glance,
- Notion Calendar → scheduling clarity,
- Tesla Service → service flow,
- Resend → form and interaction polish.

Inspirasi digunakan sebagai prinsip, bukan copy layout.

---

# 3. Anti AI-Slop Rules

Bagian ini **locked**.

Website tidak boleh memiliki karakter visual yang identik dengan template AI SaaS generik.

## 3.1 Dilarang

- purple-blue gradient,
- aurora gradient,
- neon glow,
- gradient blob,
- glow orb,
- glassmorphism,
- excessive backdrop blur,
- decorative sparkles,
- robot iconography,
- magic-wand AI iconography,
- card di setiap elemen,
- floating card tanpa fungsi,
- oversized rounded cards,
- `rounded-3xl` sebagai default,
- excessive pill buttons,
- overly glossy UI,
- excessive drop shadow,
- fake analytics charts,
- decorative metric cards,
- meaningless statistics,
- gradient text,
- animated gradient background,
- generic stock illustration,
- unnecessary hero dashboard illustration,
- decorative 3D render kendaraan,
- copy seperti:
  - “Experience the future”
  - “Revolutionize your vehicle care”
  - “Powered by intelligent technology”
  - “Smart. Fast. Seamless.”
- visual treatment yang mencoba terlihat “AI” padahal produk bukan AI.

## 3.2 Gunakan

- whitespace,
- strong typography,
- alignment,
- meaningful section grouping,
- subtle borders,
- neutral surfaces,
- semantic color,
- readable hierarchy,
- concise copy,
- purposeful animation,
- restrained iconography,
- real product state,
- real data,
- clear action priority.

---

# 4. Product Personality

Produk harus terasa:

```text
Calm
Reliable
Practical
Modern
Human
Precise
```

Produk tidak boleh terasa:

```text
Flashy
Futuristic
Gamified
Aggressive
Luxury
Corporate-heavy
AI-first
```

---

# 5. Brand Tone

Voice:

- jelas,
- singkat,
- langsung,
- tidak terlalu teknis untuk customer,
- tidak infantil,
- tidak dramatis.

Contoh baik:

```text
Servis kendaraan mulai mendekat.
```

Contoh buruk:

```text
Kendaraan Anda membutuhkan perhatian segera dari sistem pintar kami!
```

Contoh baik:

```text
Disarankan servis dalam 8–30 hari.
```

Contoh buruk:

```text
AI mendeteksi kendaraan Anda mungkin harus segera diservis.
```

---

# 6. Color System

## 6.1 Core Neutral Palette

```css
--color-canvas:         #F5F4F0;
--color-surface:        #FFFFFF;
--color-surface-muted:  #EFEEE9;
--color-surface-subtle: #F8F7F4;

--color-text-primary:   #191918;
--color-text-secondary: #6F6E69;
--color-text-muted:     #96938B;
--color-text-inverse:   #FFFFFF;

--color-border:         #E3E1DB;
--color-border-strong:  #CAC7BF;
--color-border-dark:    #A9A69E;
```

---

## 6.2 Brand Accent

Brand accent:

```css
--color-brand:       #A84F32;
--color-brand-hover: #93452D;
--color-brand-soft:  #F1E3DD;
```

Brand color digunakan untuk:

- active navigation detail,
- selected element,
- minor accent,
- small highlight,
- chart/detail yang benar-benar membutuhkan brand identity.

Brand color **bukan default primary button color**.

---

## 6.3 Primary Action

Primary action menggunakan dark neutral:

```css
--color-action-primary:       #191918;
--color-action-primary-hover: #2A2A28;
--color-action-primary-text:  #FFFFFF;
```

Tujuan:

- CTA tetap jelas,
- brand accent tidak berlebihan,
- visual terasa lebih editorial dan mature.

---

## 6.4 Semantic Colors

```css
--color-success:        #3E7453;
--color-success-soft:   #E7EFEA;

--color-warning:        #A56A19;
--color-warning-soft:   #F5EEDC;

--color-danger:         #B8493F;
--color-danger-soft:    #F5E5E2;

--color-neutral:        #77746D;
--color-neutral-soft:   #ECEAE5;
```

Mapping:

```text
Belum Perlu Servis
→ success

Servis Mendekat
→ warning

Segera Servis
→ danger

Recommendation Unavailable
→ neutral
```

Status tidak boleh hanya dibedakan berdasarkan warna.

Gunakan:

```text
icon / dot
+
label
+
warna
```

---

# 7. Tailwind Token Direction

Implementasi Tailwind sebaiknya menggunakan semantic naming melalui theme config / CSS variables.

Contoh:

```text
bg-canvas
bg-surface
bg-surface-muted

text-primary
text-secondary
text-muted

border-default
border-strong

bg-brand
text-brand

text-success
text-warning
text-danger
```

Hindari penggunaan hex langsung tersebar di Blade.

---

# 8. Typography

## 8.1 Primary UI Font

Primary:

```text
Geist
```

Fallback:

```css
font-family:
  Geist,
  Inter,
  ui-sans-serif,
  system-ui,
  -apple-system,
  BlinkMacSystemFont,
  "Segoe UI",
  sans-serif;
```

Digunakan pada:

- navigation,
- body,
- forms,
- tables,
- labels,
- buttons,
- numerical data,
- admin UI.

---

## 8.2 Editorial Accent Font

Optional untuk landing page:

```text
Instrument Serif
```

Hanya digunakan pada:

- large marketing heading,
- selected editorial statement.

Tidak digunakan pada:

- dashboard,
- forms,
- tables,
- admin,
- fuzzy calculations,
- booking.

---

# 9. Type Scale

Recommended scale:

| Token | Size | Line Height | Weight | Use |
|---|---:|---:|---:|---|
| Display | 52px | 1.05 | 500 | Landing hero |
| H1 | 32px | 1.20 | 600 | Page title |
| H2 | 24px | 1.25 | 600 | Section |
| H3 | 18px | 1.35 | 600 | Subsection |
| Body Large | 17px | 1.60 | 400 | Important prose |
| Body | 15–16px | 1.60 | 400 | Default |
| Small | 13–14px | 1.50 | 400/500 | Supporting text |
| Caption | 12px | 1.40 | 500 | Metadata |

Numerical score:

```text
48–64px
font-weight: 500–600
letter-spacing: tight
```

Jangan gunakan bold berlebihan.

---

# 10. Typography Rules

- maksimal 2–3 font weight utama,
- hindari all caps untuk heading,
- all caps hanya untuk micro-label jika diperlukan,
- line length body maksimal sekitar 65–75 karakter,
- gunakan tabular numbers untuk data numerik bila tersedia,
- label form harus selalu terbaca jelas.

---

# 11. Spacing System

Base unit:

```text
4px
```

Scale:

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
80
96
```

Prinsip:

- whitespace adalah struktur,
- jangan menggantikan spacing dengan card,
- vertical rhythm harus konsisten.

---

# 12. Layout

## 12.1 Customer Desktop

Sidebar:

```text
220–240px
```

Content:

```text
max-width: 1180–1240px
```

Reading/detail pages:

```text
max-width: 880–960px
```

Forms:

```text
max-width: 640–720px
```

---

## 12.2 Customer Mobile

Mobile first.

Rules:

- single column,
- primary CTA mudah dijangkau,
- no horizontal scroll,
- cards tidak terlalu nested,
- tables berubah menjadi stacked rows jika diperlukan,
- booking slots minimal touch-friendly.

---

## 12.3 Admin

Filament layout dipertahankan.

Customization hanya:

- brand,
- font,
- semantic colors,
- spacing,
- radius,
- badges,
- widgets.

Tidak melakukan redesign ekstrem terhadap Filament.

---

# 13. Grid

Default content grid:

```text
12-column desktop
6-column tablet
1-column mobile
```

Tetapi implementation tidak wajib memakai CSS grid formal jika layout sederhana.

Prioritas:

```text
clarity > visual complexity
```

---

# 14. Border Radius

Locked:

```text
Input         8px
Button        8px
Small card    10px
Large card    12px
Modal         14px
Badge         pill only when appropriate
```

Tidak menggunakan radius besar secara default.

---

# 15. Borders

Default:

```css
1px solid var(--color-border)
```

Strong:

```css
1px solid var(--color-border-strong)
```

Divider:

```text
thin
neutral
low contrast
```

Border diprioritaskan dibanding shadow untuk grouping.

---

# 16. Shadows

Default card:

```text
none
```

Shadow hanya untuk:

- dropdown,
- popover,
- modal,
- floating menu,
- temporary overlay.

Shadow harus subtle.

Dilarang:

```text
large soft shadow on every card
```

---

# 17. Card Philosophy

Card hanya digunakan jika ada grouping nyata.

Gunakan card untuk:

- primary vehicle summary,
- booking summary,
- grouped service detail,
- focused recommendation result.

Jangan gunakan card untuk:

- setiap metric,
- setiap form field,
- setiap row,
- setiap label,
- setiap navigation item.

Prefer:

```text
section
+
divider
+
spacing
```

---

# 18. Button System

## 18.1 Primary

Visual:

```text
dark background
white text
8px radius
medium weight
```

Untuk:

- Simpan
- Buat Booking
- Konfirmasi
- Selesaikan Servis
- Tambah Kendaraan

Satu primary action utama per section.

---

## 18.2 Secondary

```text
white / transparent
border
dark text
```

Untuk:

- Edit
- Lihat Detail
- Kembali
- Preview

---

## 18.3 Tertiary

Text-only.

Untuk:

- Lihat semua
- Batal
- Open detail
- Secondary navigation

---

## 18.4 Destructive

Gunakan danger semantic.

Contoh:

```text
Batalkan Booking
```

Destructive action tidak boleh menggunakan primary dark button.

---

## 18.5 Button Sizes

Recommended:

```text
sm: 32–36px
md: 40px
lg: 44–48px
```

Mobile primary CTA minimal sekitar 44px tinggi.

---

# 19. Input System

Default input:

- 40–44px height,
- subtle border,
- white/surface background,
- 8px radius,
- visible focus ring.

Structure:

```text
Label
Input
Helper/Error
```

Jangan menggunakan floating labels.

---

# 20. Form Rules

- label selalu visible,
- placeholder bukan pengganti label,
- required state jelas,
- validation dekat dengan field,
- destructive/correction form diberi context,
- long forms dibagi per section,
- jangan gunakan wizard jika satu halaman masih masuk akal.

---

# 21. Focus State

Semua interactive element harus memiliki focus state yang terlihat.

Contoh:

```text
2px outline / ring
dark or brand-derived
high enough contrast
```

Jangan menghilangkan browser focus tanpa replacement.

---

# 22. Status Badge

Status badge digunakan untuk status yang benar-benar diskrit.

Contoh:

```text
● Belum Perlu Servis
● Servis Mendekat
● Segera Servis
```

Badge:

- subtle background,
- semantic text,
- small dot/icon,
- tidak terlalu rounded besar,
- tidak memenuhi halaman dengan warna.

Booking:

```text
Pending
Confirmed
In Service
Completed
Cancelled
```

Gunakan badge tetapi tetap tampilkan label teks penuh.

---

# 23. Recommendation Score

Skor 0–100 adalah elemen visual penting.

Contoh:

```text
64
───
100
```

atau:

```text
64 / 100
```

Rules:

- angka besar tetapi tidak seperti gamification,
- semantic status lebih penting daripada score,
- tidak menggunakan glowing circle gauge,
- tidak menggunakan speedometer 3D,
- tidak menggunakan gradient radial.

Recommended visual:

```text
large score
status label
simple linear progress
```

---

# 24. Progress Indicator

Untuk:

- progress kilometer,
- progress waktu.

Gunakan linear progress bar.

Contoh:

```text
Progress Kilometer     82%
████████░░
```

Rules:

- 6–8px height,
- neutral track,
- semantic/brand fill,
- no gradient,
- no glow,
- label tetap tampil.

---

# 25. Fuzzy Calculation UI

Customer-facing default:

```text
Mengapa kendaraan ini mulai perlu servis?

Progress kilometer     82%
Progress waktu         75%
Penggunaan             Intensif ringan
```

Advanced details berada di disclosure:

```text
Lihat perhitungan teknis
```

Advanced section:

- memberships,
- active rules,
- alpha,
- z,
- weighted average,
- final score.

Dilarang menjadikan dashboard utama seperti scientific calculator.

---

# 26. Table Design

Admin dan desktop list dapat menggunakan table.

Rules:

- header subtle,
- row height nyaman,
- horizontal divider,
- no zebra stripes kecuali dibutuhkan,
- no heavy outer border,
- action menu di kanan,
- status badge kecil,
- numeric alignment konsisten.

Mobile:

- ubah ke stacked row/card ringan jika table terlalu lebar.

---

# 27. List Design

Customer vehicle list lebih baik menggunakan rows daripada cards berlebihan.

Contoh:

```text
Honda Vario 160                         Servis Mendekat
F 1234 ABC
13.550 km                                      64 / 100
──────────────────────────────────────────────────────────
```

---

# 28. Navigation

## Customer Sidebar

Items:

```text
Dashboard
Kendaraan
Rekomendasi Servis
Booking
Riwayat Servis
Notifikasi
Profil
```

Rules:

- no colorful icons,
- active state subtle,
- icon monochrome,
- max 1 accent detail,
- divider hanya jika grouping perlu.

---

# 29. Mobile Navigation

Recommended:

- compact top bar,
- slide-over navigation / bottom navigation hanya jika terbukti lebih usable.

Jangan menambahkan bottom nav hanya karena mobile app pattern.

Jika digunakan, maksimal 4–5 primary items.

---

# 30. Icons

Gunakan satu icon set konsisten.

Recommended:

```text
Heroicons
```

atau icon set yang sudah terintegrasi dengan Filament.

Rules:

- outline icon,
- 16–20px common size,
- no mixed icon style,
- no decorative emoji sebagai icon produk,
- no AI sparkle icon.

---

# 31. Modal

Modal hanya untuk:

- destructive confirmation,
- quick edit,
- booking confirmation,
- correction reason,
- short contextual task.

Jangan memasukkan form panjang ke modal.

Modal harus:

- trap focus,
- close with Escape,
- return focus,
- accessible title.

---

# 32. Dropdown / Popover

Gunakan untuk:

- row actions,
- vehicle selector,
- contextual filters.

Popover tidak boleh menggantikan halaman detail penting.

---

# 33. Empty State

Empty state harus:

- menjelaskan kondisi,
- menawarkan next action,
- tidak berlebihan secara visual.

Contoh:

```text
Belum ada kendaraan

Tambahkan kendaraan pertama untuk mulai mendapatkan
rekomendasi servis.

[ Tambah Kendaraan ]
```

Tidak perlu ilustrasi besar.

---

# 34. Loading State

Gunakan:

- skeleton ringan,
- inline spinner,
- disabled state.

Jangan menggunakan:

- animated gradient shimmer berlebihan,
- fullscreen loading untuk operasi kecil.

Untuk Livewire:

- button loading state,
- section skeleton bila diperlukan.

---

# 35. Error State

Error harus menjelaskan:

1. apa yang gagal,
2. apakah user bisa retry,
3. apa yang bisa dilakukan selanjutnya.

Contoh:

```text
Rekomendasi belum dapat diperbarui.

Coba lagi beberapa saat.
[ Coba Lagi ]
```

Jangan menampilkan raw exception.

---

# 36. Success Feedback

Gunakan toast/inline confirmation yang singkat.

Contoh:

```text
Odometer berhasil diperbarui.
```

atau:

```text
Booking berhasil dibuat.
```

Tidak perlu confetti.

---

# 37. Notification UI

Notification list:

```text
Title
Supporting text
Time
Read/unread state
```

Unread state subtle:

- dot,
- slight background difference,
- medium title weight.

No social-app style notification overload.

---

# 38. Dashboard Customer

Prioritas:

1. kendaraan aktif,
2. status servis,
3. score,
4. recommended date,
5. CTA booking,
6. progress.

Recommended structure:

```text
Page Greeting

Vehicle Summary
────────────────────────────────────

Honda Vario 160
F 1234 ABC

Servis Mendekat

64 / 100

Disarankan servis
1 Oktober 2026

[ Jadwalkan Servis ]

────────────────────────────────────

Progress Kilometer       82%
Progress Waktu           75%
Penggunaan Rata-rata     35 km/hari
```

Dilarang membuat 6–8 stat card di bagian atas.

---

# 39. Vehicle Detail

Sections:

```text
Vehicle Identity

Current Status

Service Profile

Odometer

Recommendation

Odometer History

Service History Preview
```

Gunakan sections dan divider.

Tidak semua section harus berupa card.

---

# 40. Booking UI

Booking harus terasa sederhana.

Flow:

```text
Vehicle
  ↓
Date
  ↓
Time Slot
  ↓
Complaint
  ↓
Confirm
```

Recommended one-page progressive form jika feasible.

---

# 41. Calendar

Calendar styling:

- neutral,
- selected date clear,
- unavailable date visibly disabled,
- recommended date punya subtle accent,
- today indicator distinct,
- no gradient.

Status:

```text
Available
Limited
Full
Closed
Recommended
```

Gunakan label/dot selain warna.

---

# 42. Time Slot

Slot button states:

### Available

```text
border
white surface
```

### Selected

```text
dark surface
white text
```

### Limited

```text
warning detail
```

### Full

```text
disabled
muted
```

Touch target minimum sekitar 44px.

---

# 43. Booking Timeline

Gunakan timeline linear sederhana.

Contoh:

```text
Booking dibuat
21 Sep, 09:20

│
Confirmed
21 Sep, 10:10

│
Menunggu servis
```

Tidak perlu complex stepper animation.

---

# 44. Service History

Gunakan row/list.

Contoh:

```text
1 Oktober 2026
Servis Rutin
13.800 km                                  Rp185.000
```

Detail service menggunakan sections:

- vehicle,
- odometer,
- complaint,
- work performed,
- notes,
- cost.

---

# 45. Admin Filament Design Rules

Admin boleh lebih dense daripada customer.

Gunakan:

- table,
- filters,
- search,
- concise widgets,
- grouped forms.

Dashboard admin hanya metric yang actionable:

```text
Booking Hari Ini
Pending Confirmation
In Service
Urgent Vehicles
```

Jangan menambahkan chart hanya agar dashboard terlihat penuh.

---

# 46. Admin Dashboard Widgets

Maksimal sekitar 4 primary stats.

Setelah itu:

- today's bookings table,
- urgent vehicles,
- pending actions.

Jika chart tidak menjawab pertanyaan operasional, tidak perlu dibuat.

---

# 47. Landing Page

Landing page bukan SaaS marketing template.

Structure recommended:

```text
Header

Hero
- clear promise
- short supporting copy
- login/register CTA

How It Works

Service Recommendation Explanation

Booking Convenience

Workshop Information

Footer
```

Hero tidak memakai floating dashboard cards.

No decorative gradient.

---

# 48. Landing Hero

Contoh direction:

```text
Rawat kendaraan
tepat sebelum terlambat.

Pantau kebutuhan servis, lihat rekomendasi,
dan jadwalkan kunjungan bengkel dari satu tempat.

[ Mulai Sekarang ]  [ Masuk ]
```

Typography menjadi visual utama.

---

# 49. Responsive Breakpoints

Gunakan Tailwind breakpoints sebagai baseline.

```text
sm
md
lg
xl
```

Prioritas:

```text
Mobile
Tablet
Desktop
```

Bukan desktop-only lalu dipaksa mengecil.

---

# 50. Mobile Rules

- sidebar berubah menjadi navigation compact,
- CTA full-width bila tepat,
- score tidak terlalu besar,
- no horizontal table scroll untuk customer utama,
- forms single-column,
- date/time selection tetap touch-friendly,
- spacing diperkecil secara konsisten,
- sticky CTA boleh digunakan jika membantu booking.

---

# 51. Accessibility

Minimum target:

- semantic HTML,
- keyboard accessible,
- visible focus,
- form label,
- error association,
- contrast memadai,
- status tidak hanya warna,
- modal focus trap,
- Escape close,
- focus return,
- touch target minimal sekitar 44px,
- icon-only button punya accessible label.

---

# 52. Copywriting Rules

Gunakan Bahasa Indonesia sederhana.

Prefer:

```text
Tambah Kendaraan
Update Odometer
Buat Jadwal Servis
Riwayat Servis
Lihat Perhitungan
```

Hindari:

```text
Kelola Armada Anda
Optimalkan Perawatan
Smart Maintenance
Vehicle Intelligence
```

kecuali memang konteksnya perlu.

---

# 53. Number Formatting

Kilometer:

```text
13.550 km
```

Currency:

```text
Rp185.000
```

Score:

```text
64 / 100
```

Date:

```text
1 Oktober 2026
```

Time:

```text
10.00
```

Gunakan format konsisten di seluruh aplikasi.

---

# 54. Status Terminology — Locked

Recommendation:

```text
Belum Perlu Servis
Servis Mendekat
Segera Servis
Rekomendasi Belum Tersedia
```

Booking:

```text
Menunggu Konfirmasi
Dikonfirmasi
Sedang Servis
Selesai
Dibatalkan
```

Jangan menggunakan sinonim berbeda pada halaman berbeda.

---

# 55. Motion

Allowed:

- dropdown,
- modal,
- accordion,
- state transitions,
- toast,
- progress update.

Duration:

```text
150–250ms
```

Easing sederhana.

Dilarang:

- looping decoration,
- floating background,
- continuous pulse,
- bouncing CTA,
- animated gradient.

---

# 56. Data Visualization

Gunakan visualisasi hanya jika benar-benar membantu.

Allowed:

- simple line chart untuk laporan jika dibutuhkan,
- bar chart untuk jumlah servis per periode,
- linear progress untuk status kendaraan.

Avoid:

- donut chart untuk semua metric,
- radial gauge,
- pseudo-3D chart,
- gradients,
- decorative analytics.

---

# 57. Tables vs Cards

Rule:

```text
Repeated structured data
→ Table/List

Single grouped entity
→ Section/Card

Metric
→ Text first, card only if grouping needed
```

---

# 58. Component Inventory

Customer component minimum:

```text
Button
Input
Textarea
Select
Checkbox
Radio
Badge
StatusBadge
Alert
Toast
Modal
Dropdown
Tabs
PageHeader
SectionHeader
EmptyState
Skeleton
ProgressBar
VehicleSelector
VehicleSummary
RecommendationSummary
BookingSlot
BookingTimeline
ServiceHistoryRow
NotificationRow
Pagination
```

---

# 59. Component: Button

Variants:

```text
primary
secondary
tertiary
danger
```

States:

```text
default
hover
focus
active
disabled
loading
```

Icon placement:

- leading or trailing sesuai fungsi,
- jangan icon-only jika label lebih jelas.

---

# 60. Component: StatusBadge

Props concept:

```text
label
tone
icon/dot
```

Tones:

```text
success
warning
danger
neutral
info optional
```

---

# 61. Component: Alert

Gunakan untuk:

- service window warning,
- booking outside recommendation,
- baseline unavailable,
- correction notice.

Alert bukan untuk informasi biasa.

---

# 62. Component: PageHeader

Structure:

```text
Breadcrumb optional
Title
Supporting description optional
Primary action
Secondary action optional
```

Jangan membuat header menjadi hero besar di setiap dashboard page.

---

# 63. Component: SectionHeader

Structure:

```text
Title
Description optional
Small action optional
```

Dipakai untuk mengurangi card usage.

---

# 64. Component: VehicleSummary

Content:

```text
Vehicle name
Plate
Recommendation status
Score
Recommended date
Primary CTA
```

Harus menjadi reusable pattern antara dashboard dan recommendation.

---

# 65. Component: RecommendationSummary

Content:

```text
status
score
service window
recommended date
short explanation
```

No decorative gauge.

---

# 66. Component: BookingSlot

States:

```text
available
selected
limited
full
closed
```

Slot selalu menunjukkan jam sebagai teks.

---

# 67. Component: EmptyState

Props:

```text
title
description
action optional
secondary action optional
```

Icon optional dan kecil.

Tidak ada large illustration dependency.

---

# 68. Component: Skeleton

Gunakan shape yang merepresentasikan layout asli.

Dilarang full-page fake shimmer decoration.

---

# 69. Filament Theme Alignment

Admin theme harus menggunakan:

```text
Geist
warm neutral background
copper accent
same semantic colors
similar radius
```

Tetapi tetap menghormati usability default Filament.

Filament tidak harus identik dengan customer UI.

---

# 70. Dark Mode

Dark mode **tidak menjadi requirement MVP**.

Alasan:

- menambah scope design/testing,
- bukan kebutuhan inti TA,
- customer app sudah memiliki visual direction kuat pada light warm-neutral theme.

Dark mode dapat dipertimbangkan setelah core product selesai.

---

# 71. Visual QA Checklist

Setiap halaman harus diperiksa:

### Hierarchy

- apakah satu hal paling penting terlihat dulu?
- apakah CTA utama jelas?
- apakah terlalu banyak card?

### Color

- apakah brand color terlalu banyak?
- apakah semantic color sesuai?
- apakah status masih dapat dibaca tanpa warna?

### Typography

- apakah heading terlalu besar?
- apakah terlalu banyak bold?
- apakah body readable?

### Spacing

- apakah sections bernapas?
- apakah spacing konsisten?
- apakah ada elemen terlalu rapat?

### Decoration

- apakah ada shadow/gradient yang tidak punya fungsi?
- apakah ada AI-slop pattern?

---

# 72. Anti-AI-Slop Review Checklist

Sebelum UI dianggap selesai, pastikan:

```text
[ ] Tidak ada purple-blue gradient
[ ] Tidak ada glow orb
[ ] Tidak ada glassmorphism
[ ] Tidak ada sparkles/AI icon
[ ] Tidak ada excessive rounded cards
[ ] Tidak ada card untuk setiap metric
[ ] Tidak ada fake chart
[ ] Tidak ada generic SaaS headline
[ ] Tidak ada decorative animation
[ ] Tidak ada excessive pill
[ ] Tidak ada gradient text
[ ] Tidak ada huge shadow
[ ] Tidak ada dashboard hero illustration tanpa fungsi
```

Jika salah satu muncul, harus ada alasan produk yang kuat.

---

# 73. Customer Screen Priority

Urutan visual:

## Dashboard

```text
1. Active Vehicle
2. Recommendation Status
3. Score
4. Recommended Date
5. Booking CTA
6. Progress
7. Secondary Information
```

## Vehicle

```text
1. Identity
2. Current Odometer
3. Recommendation
4. Service Baseline
5. Odometer History
6. Service History
```

## Booking

```text
1. Vehicle
2. Date
3. Slot
4. Complaint
5. Confirmation
```

---

# 74. Admin Screen Priority

## Dashboard

```text
1. Today
2. Pending actions
3. In-service work
4. Urgent vehicles
```

## Booking Detail

```text
1. Schedule
2. Customer
3. Vehicle
4. Status
5. Complaint
6. Recommendation context
7. Available action
```

## Service Completion

```text
1. Odometer
2. Service type
3. Work performed
4. Notes
5. Cost
6. Complete action
```

---

# 75. Design Tokens Example

Recommended CSS variables:

```css
:root {
  --canvas: #F5F4F0;
  --surface: #FFFFFF;
  --surface-muted: #EFEEE9;
  --surface-subtle: #F8F7F4;

  --text-primary: #191918;
  --text-secondary: #6F6E69;
  --text-muted: #96938B;
  --text-inverse: #FFFFFF;

  --border: #E3E1DB;
  --border-strong: #CAC7BF;

  --brand: #A84F32;
  --brand-hover: #93452D;
  --brand-soft: #F1E3DD;

  --success: #3E7453;
  --success-soft: #E7EFEA;

  --warning: #A56A19;
  --warning-soft: #F5EEDC;

  --danger: #B8493F;
  --danger-soft: #F5E5E2;

  --neutral: #77746D;
  --neutral-soft: #ECEAE5;

  --action-primary: #191918;
  --action-primary-hover: #2A2A28;
}
```

---

# 76. Tailwind Implementation Guidance

Recommended abstraction:

```text
resources/css/app.css
```

berisi semantic variables.

Tailwind utility digunakan untuk layout dan component implementation.

Hindari:

```text
hex values tersebar di dozens Blade files
```

Gunakan reusable Blade component untuk primitive yang sering dipakai.

---

# 77. Customer Page Shell

Desktop:

```text
┌─────────────────────────────────────────────────────────────┐
│ Sidebar              Main Content                           │
│                                                             │
│ Dashboard            Page Header                            │
│ Kendaraan            ───────────────────────────────────    │
│ Rekomendasi                                                │
│ Booking              Page content                           │
│ Riwayat                                                    │
│ Notifikasi                                                  │
│ Profil                                                      │
└─────────────────────────────────────────────────────────────┘
```

Main content tidak harus memenuhi seluruh viewport.

---

# 78. Detail Page Shell

Recommended:

```text
Back / breadcrumb

Title
Metadata / status
Primary action

────────────────────

Section 1

────────────────────

Section 2

────────────────────

Section 3
```

Prioritaskan vertical flow daripada nested tabs jika konten tidak terlalu banyak.

---

# 79. Form Page Shell

Recommended:

```text
Page Header

Section:
Vehicle Information

Form fields

Section:
Service Baseline

Form fields

Actions
```

Form action:

```text
Primary
Secondary cancel
```

Tidak menggunakan sticky save button kecuali form sangat panjang.

---

# 80. Landing Page Theme

Landing page boleh sedikit lebih editorial daripada aplikasi.

Allowed:

- serif accent heading,
- larger whitespace,
- photography bengkel/kendaraan jika tersedia dan relevan,
- restrained brand color.

Not allowed:

- generic 3D dashboard mockup,
- floating cards,
- gradient mesh,
- purple glow.

---

# 81. Image Usage

Jika menggunakan foto:

- gunakan foto bengkel/kendaraan nyata,
- natural lighting,
- documentary/editorial feel,
- tidak terlalu glossy,
- tidak stock-photo corporate.

Jika tidak ada foto berkualitas, lebih baik tanpa hero image.

---

# 82. Fuzzy Visual Explanation

Jika perlu diagram:

Gunakan flat diagram sederhana:

```text
Input
↓
Fuzzification
↓
Rules
↓
Weighted Average
↓
Score
```

No futuristic AI diagram.

No neural network visual.

Fuzzy Tsukamoto bukan AI/ML dan tidak boleh divisualkan seperti AI.

---

# 83. Accessibility Contrast Rule

Semua text normal harus memiliki contrast yang memadai terhadap background.

Semantic soft backgrounds tetap harus menggunakan darker semantic foreground.

Contoh:

```text
warning-soft background
+
warning dark text
```

Tidak gunakan yellow text on white.

---

# 84. Destructive UX

Untuk:

- cancel booking,
- admin correction,
- reset fuzzy config.

Gunakan confirmation.

Confirmation copy harus menyebut efek.

Contoh:

```text
Batalkan booking?

Jadwal 1 Oktober 2026 pukul 10.00 akan dilepas
dan slot kembali tersedia.
```

---

# 85. Correction UX

Admin odometer correction harus menampilkan:

```text
Nilai saat ini
Nilai koreksi
Alasan
```

Jangan hanya form input baru tanpa context.

---

# 86. Notification Tone

Good:

```text
Booking dikonfirmasi
Jadwal servis Anda telah dikonfirmasi untuk 1 Oktober pukul 10.00.
```

Bad:

```text
Great news! 🎉 Booking kamu sukses banget!
```

Tone tetap profesional dan ringan.

---

# 87. Consistency Rules

Locked:

- terminology sama di semua halaman,
- satu icon style,
- satu status color system,
- satu spacing scale,
- satu radius system,
- satu button hierarchy,
- satu form pattern.

Jangan membuat component style baru tanpa alasan.

---

# 88. Design Review Gate

Sebuah feature belum dianggap selesai jika hanya functional.

Minimum visual review:

```text
Desktop
Tablet
Mobile
Empty
Loading
Error
Long content
Keyboard focus
```

---

# 89. Responsive QA

Wajib audit minimal:

```text
360px
390px
768px
1024px
1280px
1440px
```

Periksa:

- overflow,
- clipped text,
- button wrapping,
- table behavior,
- sidebar,
- booking calendar,
- fuzzy detail,
- modal.

---

# 90. Content Edge Cases

Test dengan:

- nama customer panjang,
- nama kendaraan panjang,
- nomor polisi panjang,
- biaya besar,
- score desimal,
- banyak booking,
- banyak service history,
- error text panjang,
- empty state,
- unavailable recommendation.

---

# 91. Design Definition of Done

Design system dianggap diterapkan jika:

- UI konsisten,
- customer UI responsive,
- primary action selalu jelas,
- tidak ada AI-slop pattern,
- semantic status konsisten,
- accessibility baseline terpenuhi,
- loading/empty/error states tersedia,
- card usage tidak berlebihan,
- admin tetap efisien,
- fuzzy detail dapat dibaca tanpa terasa teknis berlebihan,
- mobile booking nyaman digunakan.

---

# 92. Final Design Principle

Prinsip utama:

> Jangan mencoba membuat website terlihat “modern” dengan dekorasi.  
> Buat website terasa modern melalui hierarchy, typography, spacing, clarity, dan interaction quality.

Untuk project ini:

```text
Claude-like calmness
+
Linear-like precision
+
Rivian-like vehicle clarity
+
Notion Calendar-like scheduling simplicity
```

tanpa kehilangan identitas sebagai sistem bengkel.

Jika sebuah elemen visual tidak membantu pengguna:

- memahami kendaraan,
- memahami rekomendasi,
- membuat booking,
- menyelesaikan pekerjaan admin,

maka elemen tersebut tidak perlu ada.
