# Login Page Background - Gedung Natuna Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Ganti background panel kiri halaman login dari gradient hijau polos menjadi foto gedung Pengadilan Negeri Natuna dengan dark green overlay 55%.

**Architecture:** Edit CSS di `login.blade.php` — ubah `.login-banner` dari `background: linear-gradient(...)` menjadi `background-image: url(...)` + pseudo-element overlay. Copy foto `gedung.png` ke `public/`.

**Tech Stack:** Laravel Blade, CSS (vanilla), foto PNG

---

### Task 1: Copy foto gedung ke public/

**Files:**
- Create: `public/gedung.png` (copy dari `C:\Users\faris\Documents\gedung.png`)

**Step 1: Copy file**

```bash
cp /c/Users/faris/Documents/gedung.png /c/Users/faris/sihealing/public/gedung.png
```

**Step 2: Verify**

```bash
ls /c/Users/faris/sihealing/public/gedung.png
```

Expected: file ada, ukuran > 0

---

### Task 2: Update CSS `.login-banner` di login.blade.php

**Files:**
- Modify: `resources/views/auth/login.blade.php`

Cari blok CSS ini (sekitar baris 25-35):

```css
/* Left panel - decorative */
.login-banner {
    display: none;
    width: 50%;
    background: linear-gradient(160deg, #0a2818 0%, #0d3320 30%, #14532d 65%, #166534 100%);
    position: relative;
    overflow: hidden;
    padding: 3rem;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}
```

Ganti dengan:

```css
/* Left panel - decorative */
.login-banner {
    display: none;
    width: 50%;
    background-image: url('/gedung.png');
    background-size: cover;
    background-position: center center;
    position: relative;
    overflow: hidden;
    padding: 3rem;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}
```

---

### Task 3: Ubah `::before` pseudo-element jadi overlay gelap

**Files:**
- Modify: `resources/views/auth/login.blade.php`

Cari blok ini (sekitar baris 38-46):

```css
/* Subtle radial glows */
.login-banner::before {
    content: '';
    position: absolute;
    top: -10%;
    right: -15%;
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, rgba(184,134,11,0.10) 0%, transparent 65%);
    border-radius: 50%;
}
```

Ganti dengan overlay solid yang menutupi seluruh panel:

```css
/* Dark overlay di atas foto gedung */
.login-banner::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(10, 40, 24, 0.55);
    z-index: 0;
}
```

---

### Task 4: Pastikan `.banner-content` z-index di atas overlay

**Files:**
- Modify: `resources/views/auth/login.blade.php`

Cari:

```css
.banner-content {
    position: relative;
    z-index: 1;
```

Pastikan `z-index: 1` sudah ada (sudah ada di kode asli — tidak perlu diubah). ✅

---

### Task 5: Test di browser

**Step 1:** Jalankan Laravel dev server

```bash
cd /c/Users/faris/sihealing
php artisan serve
```

**Step 2:** Buka `http://localhost:8000/login`

**Expected:**
- Panel kiri: foto gedung kelihatan, ada overlay hijau gelap, teks putih & gold terbaca jelas
- Panel kanan: form login tidak berubah sama sekali
- Mobile: tidak ada perbedaan (panel kiri hidden di mobile)

**Step 3:** Jika foto terlalu gelap → naikkan opacity overlay dari `0.55` → `0.45`
**Step 4:** Jika foto terlalu terang → turunkan dari `0.55` → `0.65`

---

### Task 6: Commit

```bash
cd /c/Users/faris/sihealing
rtk git add public/gedung.png resources/views/auth/login.blade.php
rtk git commit -m "feat(login): ganti background panel kiri dengan foto gedung PN Natuna"
```
