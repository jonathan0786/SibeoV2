# Dokumentasi Sistem Informasi Bengkel Kampus

## 1. Deskripsi Sistem

Sistem Informasi Bengkel Kampus adalah aplikasi berbasis web yang dirancang untuk membantu pengelolaan operasional bengkel kendaraan di lingkungan kampus. Sistem ini mendukung proses pendaftaran pelanggan secara online maupun walk-in, pengelolaan servis kendaraan, penggunaan sparepart, peminjaman alat, serta evaluasi kinerja mekanik mahasiswa.

Sistem dikembangkan menggunakan:

- PHP
- HTML
- CSS
- JavaScript
- Bootstrap
- MySQL/MariaDB

Sebagai implementasi konsep Basis Data Lanjut, sistem memanfaatkan:

- Stored Procedure (SP)
- User Defined Function (UDF)
- Trigger

---

# 2. Tujuan Sistem

Tujuan pengembangan sistem ini adalah:

1. Memudahkan pelanggan melakukan booking servis kendaraan.
2. Membantu admin dalam mengelola jadwal dan pekerjaan bengkel.
3. Mempermudah mekanik dalam mencatat aktivitas servis.
4. Mengontrol penggunaan sparepart dan alat bengkel.
5. Menyediakan laporan operasional dan penilaian kinerja mekanik.
6. Mengimplementasikan fitur basis data tingkat lanjut pada MySQL/MariaDB.

---

# 3. Aktor Sistem

## 3.1 Pelanggan

Hak akses:

- Registrasi akun
- Login
- Mengelola data kendaraan
- Melakukan booking servis
- Melihat status servis

---

## 3.2 Admin

Hak akses:

- Mengelola data master
- Mengelola booking
- Menugaskan mekanik dan stall
- Mengelola sparepart
- Mengelola alat
- Melihat laporan

---

## 3.3 Mekanik

Hak akses:

- Melihat pekerjaan servis
- Melakukan pengerjaan servis
- Mencatat penggunaan sparepart
- Melakukan peminjaman alat
- Menyelesaikan pekerjaan servis

---

# 4. Master Data

Sistem memiliki 8 master data utama.

## 4.1 Master Pelanggan

Menyimpan informasi pelanggan.

### Data yang disimpan

- ID Pelanggan
- Nama Pelanggan
- Alamat
- Nomor Telepon
- Email

---

## 4.2 Master Kendaraan

Menyimpan data kendaraan pelanggan.

### Data yang disimpan

- ID Kendaraan
- ID Pelanggan
- Nomor Polisi
- Merk
- Tipe Kendaraan
- Tahun Kendaraan

---

## 4.3 Master Mekanik

Menyimpan data mekanik (mahasiswa).

### Data yang disimpan

- ID Mekanik
- NIM
- Nama Mekanik
- Program Studi
- Status Aktif

---

## 4.4 Master Stall

Menyimpan data area pengerjaan servis.

### Data yang disimpan

- ID Stall
- Nama Stall
- Status Stall

### Status Stall

- Tersedia
- Digunakan
- Maintenance

---

## 4.5 Master Alat

Menyimpan data alat kerja bengkel.

### Data yang disimpan

- ID Alat
- Nama Alat
- Jumlah
- Kondisi

---

## 4.6 Master Sparepart

Menyimpan data sparepart yang tersedia.

### Data yang disimpan

- ID Sparepart
- Nama Sparepart
- Harga
- Stok

---

## 4.7 Master Jenis Servis

Menyimpan daftar layanan servis.

### Data yang disimpan

- ID Servis
- Nama Servis
- Tarif Servis
- Estimasi Waktu

### Contoh

- Ganti Oli
- Tune Up
- Servis Ringan
- Servis Berkala

---

## 4.8 Master Shift

Menyimpan jadwal kerja mekanik.

### Data yang disimpan

- ID Shift
- Nama Shift
- Jam Masuk
- Jam Keluar

### Contoh Shift

#### Shift Pagi

- Jam Masuk: 08:00
- Jam Keluar: 12:00

#### Shift Siang

- Jam Masuk: 13:00
- Jam Keluar: 17:00

---

# 5. Transaksi Sistem

Sistem memiliki 4 transaksi utama.

## 5.1 Transaksi Booking

Digunakan pelanggan untuk melakukan pemesanan servis.

### Data yang disimpan

- ID Booking
- ID Pelanggan
- ID Kendaraan
- Tanggal Booking
- Keluhan
- Status

### Status Booking

- Menunggu
- Diterima
- Ditolak
- Selesai

---

## 5.2 Transaksi Pengerjaan Servis

Digunakan untuk mencatat proses servis.

### Data yang disimpan

- ID Pengerjaan
- ID Booking
- ID Mekanik
- ID Shift
- ID Stall
- ID Servis
- Tanggal Mulai
- Tanggal Selesai
- Status

### Status Pengerjaan

- Terjadwal
- Dikerjakan
- Selesai

---

## 5.3 Transaksi Penggunaan Sparepart

Digunakan untuk mencatat sparepart yang digunakan selama servis.

### Data yang disimpan

- ID Detail Sparepart
- ID Pengerjaan
- ID Sparepart
- Qty
- Subtotal

---

## 5.4 Transaksi Peminjaman Alat

Digunakan untuk mencatat peminjaman alat oleh mekanik.

### Data yang disimpan

- ID Pinjam
- ID Pengerjaan
- ID Alat
- Tanggal Pinjam
- Tanggal Kembali
- Kondisi Kembali

### Kondisi Alat

- Baik
- Rusak Ringan
- Rusak Berat

---

# 6. Alur Proses Bisnis

## 6.1 Registrasi dan Login

1. Pelanggan melakukan registrasi akun.
2. Sistem menyimpan data pelanggan.
3. Pelanggan login ke sistem.

---

## 6.2 Pengelolaan Kendaraan

1. Pelanggan login.
2. Pelanggan menambahkan data kendaraan.
3. Sistem menyimpan data kendaraan.

---

## 6.3 Booking Servis

1. Pelanggan memilih kendaraan.
2. Pelanggan mengisi keluhan.
3. Pelanggan memilih tanggal servis.
4. Sistem membuat data booking.
5. Status booking menjadi **Menunggu**.

---

## 6.4 Konfirmasi Booking

1. Admin memeriksa booking.
2. Admin menerima atau menolak booking.

Jika diterima:

- Status menjadi **Diterima**

Jika ditolak:

- Status menjadi **Ditolak**

---

## 6.5 Penugasan Servis

1. Admin menentukan:
   - Mekanik
   - Shift
   - Stall
   - Jenis Servis

2. Sistem membuat data pengerjaan servis.

---

## 6.6 Peminjaman Alat

1. Mekanik memilih alat yang digunakan.
2. Sistem mencatat data peminjaman alat.
3. Alat digunakan selama proses servis.

---

## 6.7 Penggunaan Sparepart

1. Mekanik memilih sparepart yang digunakan.
2. Sistem mencatat penggunaan sparepart.
3. Stok sparepart berkurang secara otomatis.

---

## 6.8 Penyelesaian Servis

1. Mekanik menyelesaikan pekerjaan.
2. Mekanik mengembalikan alat.
3. Admin melakukan verifikasi.
4. Status pekerjaan menjadi **Selesai**.

---

# 7. Aturan Bisnis

## BR-01

Nomor telepon pelanggan harus unik.

---

## BR-02

Satu pelanggan dapat memiliki lebih dari satu kendaraan.

---

## BR-03

Satu kendaraan hanya dapat dimiliki oleh satu pelanggan.

---

## BR-04

Stall hanya dapat digunakan apabila statusnya "Tersedia".

---

## BR-05

Mekanik hanya dapat menerima pekerjaan sesuai shift yang aktif.

---

## BR-06

Sparepart tidak dapat digunakan jika stok tidak mencukupi.

---

## BR-07

Setiap penggunaan sparepart harus tercatat pada transaksi servis.

---

## BR-08

Peminjaman alat harus dilakukan sebelum alat digunakan.

---

## BR-09

Status stall kembali menjadi "Tersedia" setelah servis selesai.

---

## BR-10

Setiap pekerjaan servis harus memiliki mekanik dan stall.

---

# 8. Laporan Sistem

Sistem menyediakan 4 laporan utama.

## 8.1 Laporan Servis Kendaraan

Informasi yang ditampilkan:

- Tanggal Servis
- Pelanggan
- Kendaraan
- Jenis Servis
- Mekanik
- Status

---

## 8.2 Laporan Penggunaan Sparepart

Informasi yang ditampilkan:

- Nama Sparepart
- Jumlah Digunakan
- Harga
- Sisa Stok

---

## 8.3 Laporan Peminjaman Alat

Informasi yang ditampilkan:

- Nama Alat
- Nama Mekanik
- Tanggal Pinjam
- Tanggal Kembali
- Kondisi Alat

---

## 8.4 Laporan Kinerja Mekanik

Informasi yang ditampilkan:

- Nama Mekanik
- Shift
- Jumlah Servis
- Durasi Pengerjaan
- Riwayat Kerusakan Alat

---

# 9. Implementasi Stored Procedure (SP)

## SP-01 : sp_tambah_booking()

Fungsi:

- Menambahkan data booking baru.
- Validasi pelanggan dan kendaraan.

---

## SP-02 : sp_penugasan_servis()

Fungsi:

- Menentukan mekanik.
- Menentukan shift.
- Menentukan stall.
- Membuat transaksi pengerjaan.

---

## SP-03 : sp_tambah_sparepart()

Fungsi:

- Menambahkan sparepart pada pengerjaan servis.
- Menghitung subtotal sparepart.

---

## SP-04 : sp_selesaikan_servis()

Fungsi:

- Menyelesaikan pekerjaan servis.
- Mengubah status servis.
- Mengubah status stall menjadi tersedia.

---

# 10. Implementasi User Defined Function (UDF)

## UDF-01 : fn_hitung_subtotal_sparepart()

Fungsi:

Menghitung subtotal penggunaan sparepart.

```text
Subtotal = Harga × Qty
```

---

## UDF-02 : fn_hitung_total_servis()

Fungsi:

Menghitung total biaya servis.

```text
Total Servis = Tarif Servis + Total Sparepart
```

---

## UDF-03 : fn_hitung_durasi_servis()

Fungsi:

Menghitung durasi pengerjaan servis.

```text
Durasi = Tanggal Selesai - Tanggal Mulai
```

---

## UDF-04 : fn_hitung_nilai_mekanik()

Fungsi:

Menghitung nilai kinerja mekanik.

```text
Nilai = (Jumlah Servis × 10) - (Jumlah Kerusakan Alat × 5)
```

---

# 11. Implementasi Trigger

## Trigger 1

### trg_kurangi_stok_sparepart

Dijalankan setelah transaksi penggunaan sparepart ditambahkan.

Fungsi:

- Mengurangi stok sparepart secara otomatis.

---

## Trigger 2

### trg_stall_digunakan

Dijalankan setelah transaksi pengerjaan servis dibuat.

Fungsi:

- Mengubah status stall menjadi "Digunakan".

---

## Trigger 3

### trg_stall_tersedia

Dijalankan setelah status servis menjadi selesai.

Fungsi:

- Mengubah status stall menjadi "Tersedia".

---

## Trigger 4

### trg_histori_pengembalian_alat

Dijalankan setelah data pengembalian alat diperbarui.

Fungsi:

- Mencatat histori kondisi alat setelah digunakan.

---

# 12. Kesimpulan

Sistem Informasi Bengkel Kampus terdiri dari:

## Master Data (8)

1. Pelanggan
2. Kendaraan
3. Mekanik
4. Stall
5. Alat
6. Sparepart
7. Jenis Servis
8. Shift

## Transaksi (4)

1. Booking
2. Pengerjaan Servis
3. Penggunaan Sparepart
4. Peminjaman Alat

## Laporan (4)

1. Laporan Servis Kendaraan
2. Laporan Penggunaan Sparepart
3. Laporan Peminjaman Alat
4. Laporan Kinerja Mekanik

## Fitur Basis Data Lanjut

### Stored Procedure

- sp_tambah_booking()
- sp_penugasan_servis()
- sp_tambah_sparepart()
- sp_selesaikan_servis()

### User Defined Function

- fn_hitung_subtotal_sparepart()
- fn_hitung_total_servis()
- fn_hitung_durasi_servis()
- fn_hitung_nilai_mekanik()

### Trigger

- trg_kurangi_stok_sparepart
- trg_stall_digunakan
- trg_stall_tersedia
- trg_histori_pengembalian_alat

Dokumentasi ini dapat digunakan sebagai dasar pembuatan ERD, DFD, BPMN, Use Case Diagram, Activity Diagram, serta implementasi aplikasi Sistem Informasi Bengkel Kampus berbasis PHP dan MySQL/MariaDB.