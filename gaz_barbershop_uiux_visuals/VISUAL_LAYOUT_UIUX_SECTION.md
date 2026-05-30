---

## 29. Visual Layout UI/UX

Bagian ini berisi referensi visual untuk implementasi UI/UX website **GAZ Barbershop Booking System**. Visual ini dibuat agar proses slicing frontend lebih jelas, baik untuk tampilan desktop, mobile, maupun dashboard admin.

### 29.1 Konsep UI/UX

Konsep desain yang digunakan adalah **premium dark barbershop interface** dengan aksen warna emas. Tujuannya agar website terlihat profesional, maskulin, modern, dan tetap mudah digunakan oleh pelanggan maupun admin.

Prinsip desain:

* **Dark premium layout** untuk memberi kesan eksklusif.
* **Gold accent** untuk tombol utama, rating, badge, dan highlight harga.
* **Card-based layout** untuk layanan, capster, booking, dan status.
* **Mobile-first responsive** agar user mudah booking dari handphone.
* **Booking wizard** agar user tidak bingung saat memilih layanan, capster, jadwal, dan ringkasan harga.
* **Admin dashboard compact** agar admin cepat melihat booking masuk dan melakukan konfirmasi WhatsApp.

---

### 29.2 Visual Blueprint Layout

![Visual Layout Blueprint](docs/images/15-visual-layout-blueprint.png)

Blueprint ini menjelaskan struktur utama UI/UX:

* Landing page desktop.
* Landing page mobile.
* Booking flow user.
* Dashboard admin.
* Komponen penting seperti service card, capster card, status booking, review, email notification, dan WhatsApp confirmation.

---

### 29.3 Preview UI/UX Full Overview

![Full UI UX Overview](docs/images/00-ui-ux-full-overview.png)

---

### 29.4 Tampilan Branding dan Ringkasan Project

![Branding and Project Summary](docs/images/01-branding-and-project-summary.png)

Bagian ini digunakan sebagai referensi header dokumentasi, identitas aplikasi, dan ringkasan fungsi utama website.

---

### 29.5 Tampilan Tech Stack

![Tech Stack Card](docs/images/02-tech-stack-card.png)

Bagian ini menjelaskan stack utama yang digunakan, yaitu Laravel, MySQL, Tailwind CSS, Alpine.js, dan JavaScript.

---

### 29.6 Tampilan Scope Fitur User, Admin, dan Sistem Booking

![Feature Scope](docs/images/03-feature-scope-user-admin-system.png)

Visual ini memisahkan kebutuhan sistem menjadi tiga bagian:

* Dari sisi user.
* Dari sisi admin.
* Dari sisi sistem booking.

---

### 29.7 Tampilan Benefit Utama

![Key Benefits](docs/images/04-key-benefits-row.png)

Benefit utama yang ditampilkan:

* Booking mudah.
* Konfirmasi cepat via WhatsApp.
* Notifikasi otomatis via email.
* Review dan rating capster.

---

### 29.8 Tampilan Desktop User Homepage

![Desktop User Homepage](docs/images/05-desktop-user-homepage.png)

Struktur desktop homepage:

* Navbar berisi logo, beranda, layanan, capster, booking saya, dan profil user.
* Hero section dengan headline dan CTA booking.
* Section layanan.
* Section capster terbaik.
* Card harga dan rating.

---

### 29.9 Tampilan Mobile Homepage

![Mobile Homepage](docs/images/06-mobile-homepage.png)

Struktur mobile homepage:

* Header compact dengan hamburger menu.
* Hero section pendek.
* List layanan berbentuk card vertikal.
* List capster terbaik.
* Bottom navigation untuk akses cepat.

---

### 29.10 Tampilan Dashboard Admin

![Admin Dashboard](docs/images/07-admin-dashboard.png)

Dashboard admin menampilkan:

* Sidebar menu.
* Statistik total booking.
* Booking hari ini.
* Total capster.
* Total pelanggan.
* Chart booking terbaru.
* Daftar booking yang menunggu konfirmasi.
* Tombol konfirmasi WhatsApp.

---

### 29.11 Tampilan Alur Booking Desktop

![Desktop Booking Flow](docs/images/08-desktop-booking-flow.png)

Alur booking desktop menggunakan model wizard:

1. Pilih layanan.
2. Pilih capster.
3. Pilih jadwal.
4. Lihat ringkasan dan total harga.
5. Klik tombol booking sekarang.

---

### 29.12 Tampilan Alur Booking Mobile

![Mobile Booking Flow](docs/images/09-mobile-booking-flow.png)

Alur booking mobile dibuat per step agar tampilan tidak terlalu padat di layar kecil.

Urutan screen mobile:

1. Layanan.
2. Capster.
3. Jadwal.
4. Ringkasan.

---

### 29.13 Tampilan Notifikasi Email Reschedule

![Email Reschedule Notification](docs/images/10-email-reschedule-notification.png)

Email ini dikirim ketika booking batal otomatis karena user terlambat datang atau tidak melakukan check-in sesuai batas waktu.

---

### 29.14 Tampilan Halaman Review

![Review Page](docs/images/11-review-page.png)

Halaman review muncul setelah:

* Layanan selesai.
* Pembayaran sudah dilakukan.
* Booking sudah berstatus completed atau paid.

---

### 29.15 Tampilan Status Booking User

![User Booking Status](docs/images/12-user-booking-status.png)

Halaman ini digunakan user untuk melihat status booking:

* Akan datang.
* Selesai.
* Dibatalkan.

---

### 29.16 Tampilan Konfirmasi WhatsApp Admin

![Admin WhatsApp Confirmation](docs/images/13-admin-whatsapp-confirmation.png)

Visual ini menjadi referensi alur ketika admin menghubungi user melalui WhatsApp untuk konfirmasi jadi datang atau tidak jadi datang.

---

### 29.17 Tampilan Kelola Capster Admin

![Admin Manage Capster](docs/images/14-admin-manage-capster.png)

Halaman ini digunakan admin untuk:

* Melihat daftar capster.
* Menambah capster.
* Mengedit capster.
* Menghapus capster.
* Mengatur harga jasa dan rating capster.

---

### 29.18 Rekomendasi Struktur Halaman UI

```text
resources/views/
├── layouts/
│   ├── app.blade.php
│   ├── guest.blade.php
│   └── admin.blade.php
├── pages/
│   ├── home.blade.php
│   ├── services.blade.php
│   └── capsters.blade.php
├── member/
│   ├── dashboard.blade.php
│   ├── bookings/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   ├── show.blade.php
│   │   └── review.blade.php
└── admin/
    ├── dashboard.blade.php
    ├── bookings/
    │   ├── index.blade.php
    │   └── show.blade.php
    ├── capsters/
    │   ├── index.blade.php
    │   ├── create.blade.php
    │   └── edit.blade.php
    ├── services/
    │   ├── index.blade.php
    │   ├── create.blade.php
    │   └── edit.blade.php
    └── schedules/
        ├── index.blade.php
        ├── create.blade.php
        └── edit.blade.php
```

---

### 29.19 Komponen UI yang Dibutuhkan

| Komponen | Digunakan Pada | Keterangan |
|---|---|---|
| Navbar | User homepage | Navigasi utama website |
| Hero Section | Landing page | Headline dan CTA booking |
| Service Card | Homepage dan booking | Menampilkan layanan dan harga |
| Capster Card | Homepage dan booking | Menampilkan foto, rating, dan harga jasa capster |
| Booking Stepper | Booking page | Step layanan, capster, jadwal, ringkasan |
| Status Badge | Booking list | Menampilkan status booking |
| WhatsApp Button | Admin booking | Redirect konfirmasi ke WhatsApp |
| Review Form | User review | Rating dan komentar capster |
| Admin Sidebar | Dashboard admin | Navigasi halaman admin |
| Data Table | Admin CRUD | Kelola capster, layanan, booking |
| Email Template | Notification | Notifikasi reschedule dan pembatalan |

---

### 29.20 Catatan Implementasi Responsive

Untuk tampilan mobile:

* Gunakan layout satu kolom.
* Service card dibuat full width.
* Booking wizard dipisah per step.
* Bottom navigation dapat digunakan untuk menu utama member.
* Tabel admin sebaiknya dibuat responsive dengan horizontal scroll.
* Tombol utama harus mudah ditekan di layar kecil.

Untuk tampilan desktop:

* Gunakan container besar dengan grid 2 sampai 4 kolom.
* Homepage dapat menggunakan hero image besar.
* Dashboard admin menggunakan sidebar tetap.
* Booking wizard dapat ditampilkan dalam beberapa kolom agar proses booking terlihat lengkap.
