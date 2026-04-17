# Sistem Informasi Pemesanan Tiket Event (EventTiket)

Sistem web-based untuk pemesanan tiket event dengan fitur lengkap mulai dari manajemen event, pemesanan tiket, voucher diskon, check-in, hingga laporan.

## Teknologi
- **Backend:** PHP Native
- **Database:** MySQL
- **Frontend:** Bootstrap 5 + Bootstrap Icons

## Struktur Database

### Tabel-tabel:
1. **users** - Data pengguna (admin, petugas, user)
2. **venue** - Data lokasi event
3. **event** - Data event yang tersedia
4. **tiket** - Data tiket untuk setiap event
5. **voucher** - Data voucher diskon
6. **orders** - Data pesanan/transaksi
7. **order_detail** - Detail pesanan (tiket, qty, subtotal)
8. **attendee** - Data tiket yang digenerate dengan kode unik

## Fitur Sistem

### Bagian A: Database
- [x] Database `event_tiket` dengan 8 tabel
- [x] Primary Key dan Foreign Key sesuai ERD
- [x] Sample data untuk testing

### Bagian B: Sistem Login
- [x] Login dengan email & password
- [x] Role: admin dan user
- [x] Redirect sesuai role
- [x] Session management
- [x] Logout

### Bagian C: CRUD Master Data (Admin)
- [x] CRUD Venue (Tambah, Edit, Hapus, Tampil)
- [x] CRUD Event (Relasi dengan venue, input tanggal)
- [x] CRUD Tiket (Relasi ke event, input harga & kuota)
- [x] CRUD Voucher (Kode, potongan harga, status)

### Bagian D: Pemesanan Tiket (User)
- [x] Halaman daftar event
- [x] Halaman detail tiket
- [x] Form pemesanan dengan qty
- [x] Simpan ke orders dan order_detail

### Bagian E: Voucher & Pembayaran
- [x] Input kode voucher
- [x] Validasi voucher (aktif & kuota)
- [x] Perhitungan diskon
- [x] Update total pembayaran

### Bagian F: Generate Tiket (Attendee)
- [x] Generate kode tiket unik (TKT-XXXX)
- [x] Simpan ke tabel attendee
- [x] Jumlah tiket sesuai qty pembelian

### Bagian G: Check-in Tiket
- [x] Halaman check-in dengan input kode
- [x] Validasi kode tiket
- [x] Update status_checkin = "sudah"
- [x] Simpan waktu_checkin

### Bagian H: Dashboard & Laporan
- [x] Dashboard admin (Total user, order, pendapatan)
- [x] Dashboard user (Ringkasan pesanan)
- [x] Laporan transaksi
- [x] Laporan tiket terjual per event

### Bagian I: UI
- [x] Responsive design dengan Bootstrap 5
- [x] Layout card untuk event
- [x] Tabel untuk data admin
- [x] Modern UI dengan icons

### Bagian J: HOTS (Analisis)
- [x] Validasi kuota tiket sebelum pemesanan
- [x] Query tiket terjual per event
- [x] Riwayat pembelian user
- [x] Analisis voucher dengan kuota

## Cara Install

1. **Import Database:**
   ```
   Buka phpMyAdmin
   Import file: database.sql
   ```

2. **Konfigurasi:**
   - Edit `config.php` jika perlu mengubah koneksi database
   - Default: localhost, root, (no password), event_tiket

3. **Akses Aplikasi:**
   ```
   http://localhost/event_tiket/
   ```

## Akun Demo

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@event.com | 123456 |
| User | user@event.com | 123456 |
| Petugas | petugas@event.com | 123456 |

## Struktur File

```
event_tiket/
├── config.php          # Koneksi database
├── database.sql        # Dump database
├── header.php         # Template header + navbar
├── footer.php         # Template footer
├── index.php          # Halaman utama (daftar event)
├── login.php          # Halaman login
├── logout.php         # Proses logout
├──
├── dashboard_admin.php    # Dashboard admin
├── dashboard_user.php     # Dashboard user
├──
├── venue.php              # Tampil data venue
├── venue_tambah.php       # Tambah venue
├── venue_edit.php         # Edit venue
├── venue_hapus.php        # Hapus venue
├──
├── event_tampil.php       # Tampil data event
├── event_tambah.php       # Tambah event
├── event_edit.php         # Edit event
├──
├── tiket_tampil.php       # Tampil data tiket
├── tiket_tambah.php       # Tambah tiket
├── tiket_edit.php         # Edit tiket
├──
├── voucher_tampil.php     # Tampil data voucher
├── voucher_tambah.php     # Tambah voucher
├── voucher_edit.php       # Edit voucher
├──
├── detail_tiket.php       # Detail event & tiket
├── tiket.php              # Form pemesanan tiket
├── pesan.php              # Proses pemesanan
├── riwayat.php            # Riwayat pembelian user
├── checkin.php            # Halaman check-in tiket
└── laporan.php            # Laporan admin
```

## Fitur Keamanan
- Password di-hash dengan MD5
- Session validation untuk halaman terproteksi
- SQL injection prevention dengan `mysqli_real_escape_string`
- XSS prevention dengan `htmlspecialchars`
- Input validation untuk kuota tiket

## Query Analisis (HOTS)

### 1. Mencegah Pembelian Melebihi Kuota
```php
$terjual = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(qty) as total FROM order_detail WHERE id_tiket = $id_tiket"))['total'];
$tersedia = $tiket['kuota'] - $terjual;
if ($qty > $tersedia) { ... }
```

### 2. Total Tiket Terjual per Event
```sql
SELECT e.nama_event, SUM(od.qty) as total_tiket_terjual
FROM event e
LEFT JOIN tiket t ON e.id_event = t.id_event
LEFT JOIN order_detail od ON t.id_tiket = od.id_tiket
LEFT JOIN orders o ON od.id_order = o.id_order AND o.status = 'paid'
GROUP BY e.id_event
```

### 3. Analisis Voucher
Jika voucher tidak dibatasi kuota, maka voucher bisa digunakan berulang kali tanpa batas, yang dapat merugikan bisnis. Oleh karena itu, sistem ini menggunakan field `kuota` untuk membatasi penggunaan voucher.
