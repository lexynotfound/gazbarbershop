# Bukti Agile/Scrum GAZ Barbershop

## Product Backlog dan Hasil Increment

| ID | Backlog | Acceptance Criteria | Status/Bukti |
|---|---|---|---|
| PB-01 | Booking multi-layanan | User memilih layanan, capster, dan slot tanpa bentrok | `BookingFlowTest`, `booking_items` |
| PB-02 | Operasional admin | Admin mengonfirmasi, check-in, menyelesaikan, dan membatalkan booking | `BookingFlowTest` |
| PB-03 | Pembayaran dan laporan | Pembayaran tercatat dan laporan dapat difilter/diekspor | `TransactionReportTest` |
| PB-04 | CRM pelanggan | Aktif, Repeat, Loyal, layanan dan capster favorit tampil per bulan | `AdminDashboardCrmTest` |
| PB-05 | Retention | Promo loyal dipersonalisasi dari histori pelanggan | `CustomerManagementTest` |
| PB-06 | Reliability | Double booking dicegah secara atomic dan overlap durasi dihitung | `BookingFlowTest` |
| PB-07 | Auto-cancel | Scheduler membatalkan booking dan mengirim email/database notification | `BookingNotificationTest` |
| PB-08 | Pengaturan dan jadwal | Admin dapat mengubah profil serta jadwal 10:00–22:00 | `AdminSettingsTest`, `ScheduleManagementTest` |

## Ringkasan Sprint/Increment

| Sprint | Fokus | Hasil Review | Bukti Git |
|---|---|---|---|
| 1 | Fondasi aplikasi | Auth, model, layanan, capster, dan struktur booking | `921d8d3`, `b2db321` |
| 2 | Alur booking | Booking, validasi slot, status, dan halaman admin/user | `018e0e9`, `0b29309` |
| 3 | Profil dan stabilisasi | Profil user dan perbaikan route | `05e6f1d`, `ba58147` |
| 4 | CRM dan UI | Dashboard CRM, laporan pelanggan, dan hero homepage | `a504bcc` |
| 5 | Operasional admin | Pengaturan admin dan perbaikan jadwal capster | `27bfe64` |
| 6 | Hardening CRM | Segmentasi Aktif/Repeat/Loyal, locking booking, dan notifikasi auto-cancel | Acceptance test pada feature test terkait |

## Perubahan Kebutuhan

- Dashboard reservasi diperluas menjadi laporan CRM tanpa menghapus statistik dan konfirmasi.
- Definisi pelanggan dipisahkan menjadi Aktif, Repeat, dan Loyal.
- Jam operasional capster dinormalisasi menjadi 10:00–22:00.
- Menu Pengaturan yang semula kembali ke dashboard dibuat menjadi halaman fungsional.
- Pemeriksaan slot ditingkatkan dari validasi biasa menjadi transaksi dengan database lock.
- Auto-cancel diperluas dengan email, database notification, dan booking ulang.

## Sprint Review dan Definition of Done

Setiap increment dinyatakan selesai jika:

1. Feature test yang relevan lulus.
2. Seluruh test suite lulus tanpa regresi.
3. Pint menghasilkan format bersih.
4. Build frontend berhasil.
5. Route dan migration baru dapat dijalankan.
6. Perubahan perilaku dapat didemonstrasikan melalui UI dan didukung bukti test.

Agile lebih sesuai daripada Waterfall karena requirement berkembang setelah demo dan evaluasi. Increment kecil memungkinkan perubahan CRM, UI, operasional, dan reliability diterapkan tanpa mendesain ulang seluruh sistem sekaligus.

