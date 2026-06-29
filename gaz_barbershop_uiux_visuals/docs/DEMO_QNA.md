# Demo Q&A — GAZ Barbershop CRM

---

## 1. Jelaskan perbedaan sistem booking online biasa dengan sistem CRM. Bagian mana dari sistem Kamu yang benar-benar CRM, bukan sekadar reservasi?

Booking online menangani reservasi: layanan, capster, jadwal, pembayaran, dan status. Itu saja sudah cukup sebagai sistem reservasi biasa.

Bagian yang menjadi CRM adalah penggunaan **riwayat transaksi untuk tindakan lanjutan**:
- Mengelompokkan pelanggan menjadi **Aktif, Repeat, dan Loyal** berdasarkan jumlah booking selesai.
- Menghitung **layanan terlaris** dan **capster favorit** per periode.
- Menyimpan riwayat notifikasi untuk tiap pelanggan.
- Menyiapkan **promo personal** khusus pelanggan Loyal berdasarkan data kunjungan dan preferensi mereka.

Jadi bukan sekadar "siapa yang booking hari ini", tapi "siapa pelanggan paling bernilai dan bagaimana mempertahankan mereka."

---

## 2. Tunjukkan fitur mana yang masuk tahap Acquire, Enhance, dan Retain. Mengapa fitur tersebut ditempatkan di tahap itu?

| Tahap | Fitur | Alasan |
|---|---|---|
| **Acquire** | Homepage, registrasi, katalog layanan & capster, booking online | Mengubah pengunjung baru menjadi pelanggan pertama kali |
| **Enhance** | Multi-layanan per booking, pengecekan ketersediaan jadwal, konfirmasi WhatsApp, check-in, pembayaran, review | Meningkatkan pengalaman selama dan sesudah layanan |
| **Retain** | Segmentasi CRM (Aktif/Repeat/Loyal), riwayat booking, notifikasi email & database, promo personal, rating capster, tombol booking ulang di dashboard | Mendorong pelanggan yang sudah ada untuk datang kembali |

---

## 3. Bagaimana sistem menentukan pelanggan aktif, pelanggan repeat order, dan pelanggan loyal? Rumus atau logikanya apa?

Segmentasi dihitung dari jumlah booking berstatus selesai (`COMPLETED` atau `REVIEWED`) yang dimiliki pelanggan **sejak awal hingga akhir periode laporan**, dengan syarat pelanggan tersebut **aktif di periode tersebut** (minimal satu booking selesai dalam rentang bulan yang dipilih).

| Segmen | Kondisi |
|---|---|
| **Aktif** | Minimal 1 booking selesai pada periode laporan |
| **Repeat** | Aktif pada periode + total booking selesai = 2 |
| **Loyal** | Aktif pada periode + total booking selesai ≥ 3 |

Logika ini ada di `app/Services/CustomerSegmentation.php`:
```php
$completedBookingsCount >= 3  → 'Loyal'
$completedBookingsCount >= 2  → 'Repeat'
default                       → 'Aktif'
```

---

## 4. Jika satu pelanggan melakukan booking dua kali dalam satu bulan, bagaimana sistem membedakan pelanggan aktif biasa dan repeat order?

Jika keduanya selesai (`COMPLETED`/`REVIEWED`), total booking selesai pelanggan tersebut menjadi 2 → sistem memberi label **Repeat Order**.

Pelanggan disebut **Aktif biasa** hanya jika total booking selesai (sepanjang waktu, sampai akhir periode) masih 1. Begitu mencapai 2, langsung naik ke Repeat. Begitu mencapai 3, naik ke Loyal.

---

## 5. Bagaimana cara sistem menentukan capster favorit? Berdasarkan jumlah booking, rating, pendapatan, atau kombinasi?

Urutan prioritas:
1. **Jumlah booking selesai terbanyak** pada periode (utama)
2. **Rating rata-rata tertinggi** sebagai pemecah seri
3. **Nama A–Z** jika masih seri

Pendapatan tidak digunakan sebagai bobot. Logika ini ada di `app/Services/CrmDashboardReport.php` metode `capsters()`.

---

## 6. Bagaimana cara sistem menentukan layanan terlaris? Apakah dari tabel bookings atau booking_details?

Dari tabel **`booking_items`** (bukan `bookings`). Sistem menghitung jumlah baris `booking_items` yang terhubung ke booking berstatus selesai dalam periode, dikelompokkan per layanan. Ini ada di metode `services()` pada `CrmDashboardReport.php`.

---

## 7. Mengapa Kamu membutuhkan tabel booking_details (booking_items)? Apa akibatnya jika tidak ada?

Satu booking bisa memilih **lebih dari satu layanan**. Tabel `booking_items` menyimpan relasi setiap layanan beserta **snapshot harga dan durasi** saat booking dibuat.

Tanpa tabel detail:
- Layanan harus disimpan sebagai teks/JSON di kolom booking → tidak bisa di-query atau di-join.
- Laporan layanan terlaris tidak bisa dihitung per baris transaksi.
- Jika harga layanan berubah, riwayat transaksi lama ikut berubah (tidak ada snapshot).
- Relasi ke tabel `services` tidak terjaga konsistensinya.

---

## 8. Jelaskan alur status booking dari awal sampai selesai. Kapan setiap status berubah?

```
PENDING
  → WAITING_CUSTOMER_CONFIRMATION  (admin kirim link WhatsApp)
  → CONFIRMED                      (pelanggan balas konfirmasi)
  → CHECKED_IN                     (pelanggan hadir, admin check-in)
  → COMPLETED                      (layanan selesai, admin complete)
  → REVIEWED                       (pelanggan kirim review)
```

Status pembatalan:
| Status | Kapan terjadi |
|---|---|
| `CANCELLED` | Dibatalkan manual oleh admin |
| `AUTO_CANCELLED` | Tidak ada balasan konfirmasi dalam 15 menit setelah admin kirim WhatsApp |
| `LATE_CANCELLED` | Sudah confirmed tetapi customer tidak hadir 15 menit setelah `booking_start` |

Status pembayaran disimpan **terpisah** di tabel `payments`, bukan di kolom status booking.

---

## 9. Bagaimana sistem mencegah dua pelanggan memilih capster dan jam yang sama secara bersamaan?

Pengecekan menggunakan **overlap interval**:
```
booking_start < slot_end  AND  booking_end > slot_start
```

Saat menyimpan booking, sistem:
1. Membuka **transaksi database**
2. Mengunci baris jadwal capster dengan `lockForUpdate()`
3. Memeriksa ulang ketersediaan sebelum insert

Request kedua harus menunggu sampai request pertama selesai. Jika slot sudah terisi, request kedua ditolak. Logika ini ada di `app/Services/BookingAvailability.php`.

---

## 10. Fitur auto cancel 15 menit berjalan di mana? Apakah pakai cron job, scheduler Laravel, atau dicek saat halaman dibuka?

Menggunakan **Laravel Scheduler**, bukan cron job OS maupun pengecekan saat halaman dibuka.

```php
// routes/console.php
Schedule::command('app:cancel-late-bookings')->everyMinute()->withoutOverlapping();
```

Command `app:cancel-late-bookings` menangani **dua skenario sekaligus**:

| Skenario | Kondisi | Status hasil |
|---|---|---|
| Tidak ada konfirmasi | `WAITING_CUSTOMER_CONFIRMATION` + `customer_response_deadline` terlewat | `AUTO_CANCELLED` |
| Terlambat hadir | Booking sudah `CONFIRMED` + belum check-in 15 menit setelah `booking_start` | `LATE_CANCELLED` |

Setelah dibatalkan, `BookingCancelledNotification` dikirim otomatis via email dan database notification.

Scheduler berjalan otomatis saat menjalankan `composer run dev` di lokal.

---

## 11. Jika admin mengirim konfirmasi melalui link WhatsApp manual, apakah itu bisa disebut notifikasi otomatis?

**Tidak sepenuhnya otomatis.** Ini semi-otomatis:

| Bagian | Otomatis / Manual |
|---|---|
| Membentuk nomor tujuan, isi pesan, dan link `wa.me` | **Otomatis** (sistem yang buat) |
| Membuka WhatsApp dan menekan kirim | **Manual** (admin yang lakukan) |
| Deadline 15 menit setelah klik tombol | **Otomatis** (sistem set `customer_response_deadline`) |
| Auto-cancel jika tidak ada balasan | **Otomatis** (scheduler) |
| Notifikasi email & database setelah cancel | **Otomatis** |

---

## 12. Bagaimana sistem mengirim notifikasi/email setelah auto cancel? Data apa yang disimpan di tabel notifikasi?

Setelah status booking berubah menjadi `AUTO_CANCELLED` atau `LATE_CANCELLED`, command langsung memanggil:
```php
$booking->user->notify(new BookingCancelledNotification($booking, $reason));
```

Notifikasi dikirim melalui dua kanal: **mail** (email) dan **database**.

Data yang disimpan di tabel `notifications`:
- ID & kode booking
- Judul dan isi pesan
- Alasan pembatalan (`NO_CONFIRMATION` atau `LATE_ARRIVAL`)
- Detail jadwal sebelumnya (tanggal, waktu, capster)
- Action URL (link booking ulang)
- Waktu dibuat (`created_at`) dan waktu dibaca (`read_at`)

User dapat melihat notifikasi di **Dashboard User** dan langsung memilih Booking Ulang dari sana.

---

## 13. Bagaimana promo/campaign personal dikirim ke pelanggan? Segmentasinya berdasarkan apa?

Promo hanya tersedia untuk segmen **Loyal** (3+ booking selesai). Pesan dibuat secara personal berdasarkan:
- **Jumlah kunjungan** total
- **Layanan favorit** (layanan yang paling sering di-booking)
- **Capster favorit**
- **Total transaksi lunas**

Pengiriman dilakukan via **WhatsApp manual** — admin membuka halaman promo pelanggan, sistem men-generate link `wa.me` dengan pesan yang sudah dipersonalisasi, admin tinggal klik kirim. Sistem tidak menggunakan WhatsApp Business API sehingga pengiriman masih semi-manual.

---

## 14. Kamu menggunakan Agile/Scrum. Mana buktinya? Mengapa lebih tepat dibanding Waterfall?

Bukti lengkap ada di `gaz_barbershop_uiux_visuals/docs/AGILE_SCRUM_EVIDENCE.md`, meliputi:
- Product backlog dan sprint backlog per iterasi
- Perubahan kebutuhan yang terjadi selama pengembangan (misalnya: CRM ditambah setelah evaluasi awal, notifikasi email ditambah setelah auto-cancel, hero section direvisi)
- Hasil tiap sprint (increment yang bisa didemonstrasikan)
- Commit history sebagai bukti iterasi

**Mengapa Agile lebih tepat dari Waterfall untuk GAZ Barbershop:**

Waterfall mengharuskan semua kebutuhan ditetapkan di awal. Faktanya, kebutuhan GAZ Barbershop berubah selama pengembangan — fitur CRM, segmentasi pelanggan, notifikasi, jam operasional, dan promo baru muncul setelah sistem dasar berjalan. Dengan Agile, setiap perubahan bisa dikembangkan dan diuji per increment tanpa menunggu seluruh sistem selesai terlebih dahulu.

---
