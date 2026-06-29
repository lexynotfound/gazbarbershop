# Jawaban Demo CRM GAZ Barbershop

## 1. Booking Online dan CRM

Booking online menangani reservasi: layanan, capster, jadwal, pembayaran, dan status. Bagian CRM menggunakan riwayat transaksi untuk mengelompokkan pelanggan Aktif, Repeat, dan Loyal; menghitung layanan terlaris dan capster favorit; menampilkan riwayat komunikasi; serta menyiapkan promo personal untuk pelanggan loyal.

## 2. Acquire, Enhance, dan Retain

- **Acquire:** homepage, registrasi, katalog layanan/capster, dan booking online untuk mengubah pengunjung menjadi pelanggan.
- **Enhance:** multi-layanan, pengecekan jadwal, konfirmasi, check-in, pembayaran, serta review untuk meningkatkan pengalaman layanan.
- **Retain:** segmentasi CRM, riwayat booking/notifikasi, promo personal, rating, dan booking ulang untuk mendorong kunjungan kembali.

## 3. Segmentasi Pelanggan

- **Aktif:** minimal satu booking `COMPLETED` atau `REVIEWED` pada periode laporan.
- **Repeat:** aktif pada periode dan memiliki tepat dua booking selesai sampai akhir periode.
- **Loyal:** aktif pada periode dan memiliki minimal tiga booking selesai sampai akhir periode.

Jika pelanggan booking dua kali dalam satu bulan dan keduanya selesai, pelanggan menjadi Repeat. Setelah total booking selesai mencapai tiga, pelanggan menjadi Loyal.

## 4. Capster dan Layanan Terlaris

Capster favorit ditentukan dari jumlah booking selesai terbanyak pada periode. Rating rata-rata menjadi pemecah seri, kemudian nama. Pendapatan tidak menjadi bobot. Layanan terlaris dihitung dari jumlah baris `booking_items` yang terhubung ke booking selesai pada periode.

## 5. Alasan `booking_items`

Satu booking dapat memiliki beberapa layanan. `booking_items` menyimpan relasi setiap layanan beserta snapshot harga dan durasinya. Tanpa tabel detail, layanan harus disimpan sebagai daftar teks/JSON sehingga relasi, total, histori harga, dan laporan layanan terlaris sulit dijaga secara konsisten.

## 6. Alur Status Booking

`PENDING → WAITING_CUSTOMER_CONFIRMATION → CONFIRMED → CHECKED_IN → COMPLETED → REVIEWED`.

- `PENDING`: booking baru dibuat.
- `WAITING_CUSTOMER_CONFIRMATION`: admin menandai pesan WhatsApp sudah dikirim; deadline 15 menit dibuat.
- `CONFIRMED`: pelanggan menyatakan jadi datang.
- `CHECKED_IN`: pelanggan hadir.
- `COMPLETED`: layanan selesai.
- `REVIEWED`: pelanggan mengirim review.
- `CANCELLED`: dibatalkan manual oleh admin.
- `AUTO_CANCELLED`: tidak ada balasan konfirmasi selama 15 menit.
- `LATE_CANCELLED`: belum check-in 15 menit setelah jadwal.

Status pembayaran disimpan terpisah pada tabel `payments`.

## 7. Pencegahan Double Booking

Slot diperiksa berdasarkan overlap `booking_start < slot_end` dan `booking_end > slot_start`. Saat menyimpan, sistem membuka transaksi database, mengunci jadwal capster dengan `lockForUpdate()`, lalu memeriksa ulang ketersediaan sebelum insert. Request kedua harus menunggu dan akan ditolak jika request pertama sudah mengisi slot.

## 8. Auto Cancel dan Notifikasi

Laravel Scheduler menjalankan `app:cancel-late-bookings` setiap menit dengan `withoutOverlapping()`. Proses lokal lengkap dijalankan melalui `composer run dev`, yang menyalakan server, queue worker, scheduler, dan Vite.

Setelah auto-cancel, `BookingCancelledNotification` dikirim melalui kanal mail dan database. Tabel `notifications` menyimpan ID booking, kode booking, judul, pesan, alasan, jadwal sebelumnya, action URL, waktu dibuat, serta waktu dibaca. User dapat melihatnya pada Dashboard User dan memilih Booking Ulang.

## 9. WhatsApp Manual dan Otomatis

WhatsApp bukan notifikasi otomatis. Sistem otomatis membentuk nomor, isi pesan, dan link `wa.me`; admin tetap membuka WhatsApp dan menekan kirim. Bagian yang otomatis adalah deadline konfirmasi, auto-cancel, email, dan database notification.

## 10. Promo Personal

Promo hanya tersedia untuk segmen Loyal. Pesan dibuat berdasarkan jumlah kunjungan, layanan favorit, capster favorit, dan total transaksi lunas. Pengiriman WhatsApp tetap manual karena sistem tidak memakai WhatsApp Business API.

## 11. Agile/Scrum

Bukti backlog, perubahan kebutuhan, hasil sprint, commit, dan acceptance test dicatat pada `AGILE_SCRUM_EVIDENCE.md`. Agile dipilih karena kebutuhan berubah selama evaluasi—misalnya CRM, hero, settings, jam operasional, segmentasi, dan notifikasi—dan setiap perubahan dapat dikembangkan serta diuji per increment tanpa menunggu seluruh sistem selesai.

