# IOTECHS — Deployment Guide for Hostinger

## 📁 File Structure

Upload all files to your Hostinger `public_html/` folder:

```
public_html/
├── index.html           ← Landing page (sudah siap)
├── vision.html          ← Vision page (sudah siap)
├── login.html           ← Login/signup page (calls API)
├── launch-app.html      ← Dashboard (auth-protected)
└── api/                 ← Backend PHP
    ├── config.php       ← ⚠️ EDIT DULU credential-nya!
    ├── send-otp.php     ← Kirim OTP ke email user
    ├── verify-otp.php   ← Verify OTP + create session
    ├── check-auth.php   ← Cek status login
    ├── logout.php       ← Logout
    └── .htaccess        ← Security rules
```

---

## 🚀 Setup Steps (5 menit)

### Step 1: Edit `api/config.php`

Buka file `api/config.php` di File Manager Hostinger. **Ganti 2 baris** ini:

```php
define('SMTP_USERNAME', 'noreply@iotechs.xyz');   // Email address kamu
define('SMTP_PASSWORD', 'GANTI_PASSWORD_DISINI'); // Password email BARU kamu
```

⚠️ **PENTING:** Gunakan password BARU (yang kamu sudah ganti di Hostinger), bukan password lama yang sempat bocor di chat.

### Step 2: Cek SMTP Settings

Default di `config.php`:
```php
SMTP_HOST     = 'smtp.titan.email'   // Titan email (default Hostinger baru)
SMTP_PORT     = 465
SMTP_ENCRYPTION = 'ssl'
```

**Kalau email kamu pakai Hostinger cPanel Email (bukan Titan):**
```php
SMTP_HOST = 'smtp.hostinger.com'
```

Cek jenis email di: hPanel → Emails → kalau pakai Titan akan ada label "Titan Mail", kalau standar = cPanel Email.

### Step 3: Upload Files

- Login ke hPanel → File Manager → `public_html/`
- Upload semua HTML files
- Buat folder `api/`, upload semua PHP + `.htaccess`

### Step 4: Test

1. Buka `https://iotechs.xyz/` di browser
2. Klik **Launch App** → muncul "Authentication required"
3. Klik **Sign in with Email** → ke halaman login
4. Pilih tab **Sign up** → masukkan email kamu yang beneran
5. Kamu akan dapat email OTP dari `noreply@iotechs.xyz` dalam 10-30 detik
6. Masukkan kode OTP → masuk ke dashboard

---

## 🐛 Troubleshooting

### Email OTP tidak terkirim

**Cek 1:** Buka File Manager → `api/send-otp.php` → klik **View/Open**. Kalau yang muncul adalah source code PHP (bukan executed), berarti PHP di-disable di folder itu. Solusi: chmod file 644.

**Cek 2:** hPanel → Emails → pilih `noreply@iotechs.xyz` → **Send Test Email**. Kalau gagal, ada masalah di email account, bukan di code.

**Cek 3:** Buka Error Logs di hPanel (Files → Error Logs). Cari pesan error dari PHP. Kalau ada "SMTP failed", berarti password salah atau host SMTP beda.

### "Failed to connect to server"

Berarti API URL salah. Cek:
- Pastikan folder `api/` ada di `public_html/api/`, bukan di root hosting
- Buka di browser: `https://iotechs.xyz/api/check-auth.php` — harus return JSON, bukan 404

### User diterima meski email palsu

PHP `mail()` function nggak selalu validate email tujuan secara real-time. Untuk validasi lebih ketat:
- Install **PHPMailer** (opsional upgrade) yang akan error langsung kalau email fake
- Atau pakai **Supabase Auth** yang sudah handle ini

---

## 🔐 Security Notes

1. **`config.php` aman** — `.htaccess` sudah block direct access ke file ini
2. **Password tidak pernah dikirim ke browser** — hanya ada di server
3. **OTP hashed** — disimpan pakai `password_hash()`, bukan plaintext
4. **Rate limited** — max 3 request OTP per 15 menit per email
5. **Session httpOnly** — cookie nggak bisa diakses JavaScript (anti XSS)

---

## 📈 Optional Upgrades

- **Database MySQL** — ganti `users.json` jadi MySQL table (Hostinger free)
- **PHPMailer** — lebih robust dari PHP `mail()` native, validate email real-time
- **reCAPTCHA** — tambahkan verifikasi human sebelum kirim OTP
- **Email log** — simpan log pengiriman email untuk audit

---

## 🎨 File Auth Flow

```
1. User klik "Launch App" di index.html
   ↓
2. launch-app.html loads → checkAuth() fetch ke api/check-auth.php
   ↓
3. Belum login? → show #authGate popup
   ↓
4. User klik "Sign in with Email" → ke login.html
   ↓
5. Email masuk → fetch POST ke api/send-otp.php
   - Server generate OTP 6-digit
   - Server kirim email via SMTP
   - Server simpan hash OTP di PHP session
   ↓
6. User buka email → copy kode → paste di OTP input
   ↓
7. Submit → fetch POST ke api/verify-otp.php
   - Server verify hash cocok
   - Server set $_SESSION['auth'] = authenticated
   - Server return redirect URL
   ↓
8. Redirect ke launch-app.html
   ↓
9. checkAuth() lagi → API returns authenticated=true → tampil dashboard ✓
```
