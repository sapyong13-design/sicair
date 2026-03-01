# Panduan Template DOCX — Form Permintaan Cuti

File template: `storage/app/templates/form-permintaan-cuti-template.docx`
Ukuran halaman: **Legal (8.5 × 14 inch)** — sudah diset otomatis oleh sistem.

---

## Daftar Placeholder (37 total)

### Header & Nomor Surat
| Placeholder | Keterangan | Contoh |
|------------|-----------|--------|
| `${tanggal}` | Tanggal surat (dari `created_at`) | `9 April 2026` |
| `${bulan_cuti}` | Bulan pengajuan dalam Romawi | `IV` |
| `${tahun_cuti}` | Tahun pengajuan | `2026` |

**Format nomor surat:** `    /KPN.W32.U4/KP5.3/${bulan_cuti}/${tahun_cuti}`
(4 spasi di awal untuk diisi manual)

> **Catatan:** Bulan & tahun berdasarkan tanggal **mengajukan** (`created_at`), bukan tanggal mulai cuti.

---

### I. Data Pegawai
| Placeholder | Keterangan | Contoh |
|------------|-----------|--------|
| `${nama}` | Nama pegawai | `CANDRA FIRMANSYAH, S.I.Pust.` |
| `${nip}` | NIP pegawai | `199001012015031001` |
| `${jabatan}` | Jabatan | `Pustakawan Ahli Pertama` |
| `${masa_kerja}` | Masa kerja (format: X Tahun Y Bulan) | `5 Tahun 3 Bulan` |
| `${unit_kerja}` | Unit kerja | `Pengadilan Negeri Natuna` |

---

### II. Jenis Cuti (Checkbox Otomatis)
Sistem otomatis isi `√` (dipilih) atau `-` (tidak dipilih):

| Placeholder | Jenis Cuti |
|------------|-----------|
| `${cuti_tahunan}` | Cuti Tahunan |
| `${cuti_besar}` | Cuti Besar |
| `${cuti_sakit}` | Cuti Sakit |
| `${cuti_melahirkan}` | Cuti Melahirkan |
| `${cuti_alasan_penting}` | Cuti Karena Alasan Penting |
| `${cuti_luar_tanggungan}` | Cuti di Luar Tanggungan Negara |

---

### III. Alasan Cuti
| Placeholder | Keterangan |
|------------|-----------|
| `${alasan}` | Alasan cuti yang diinput pegawai |

---

### IV. Lamanya Cuti
| Placeholder | Keterangan | Contoh |
|------------|-----------|--------|
| `${lama_hari}` | Jumlah hari kerja (sudah include kata "hari") | `2 hari` |
| `${tanggal_mulai}` | Tanggal mulai cuti | `9 April 2026` |
| `${tanggal_selesai}` | Tanggal selesai cuti | `11 April 2026` |

---

### V. Catatan Cuti (3 Baris: N-2, N-1, N)

| Placeholder | Keterangan |
|------------|-----------|
| `${tahun_n2}` | Tahun N-2 |
| `${sisa_n2}` | Sisa hak cuti N-2 (selalu `0`) |
| `${keterangan_n2}` | Keterangan N-2 (selalu `Sisa 0`) |
| `${tahun_n1}` | Tahun N-1 |
| `${sisa_n1}` | Carry-over dari tahun lalu |
| `${keterangan_n1}` | Sisa carry-over **setelah** cuti ini dikurangi |
| `${tahun_n}` | Tahun berjalan |
| `${sisa_n}` | Hak cuti tahun berjalan (`hak_cuti`) |
| `${keterangan_n}` | Sisa tahun berjalan **setelah** cuti ini dikurangi |

#### Logika Cascade Deduction (Penting!)
Sistem mengurangi carry-over **terlebih dahulu** sebelum memotong tahun berjalan:

```
carry_over = sisa_n1 (misal: 6 hari)
hari_cuti  = lama_hari (misal: 2 hari)

deduct_n1 = min(carry_over, hari_cuti) = min(6, 2) = 2
deduct_n  = max(0, hari_cuti - deduct_n1) = max(0, 2-2) = 0

keterangan_n1 = Sisa (6 - 2) = Sisa 4
keterangan_n  = Sisa (12 - 0) = Sisa 12
```

Contoh jika hari_cuti > carry_over (misal 8 hari, carry_over 6):
```
deduct_n1 = 6 → keterangan_n1 = Sisa 0
deduct_n  = 2 → keterangan_n  = Sisa 10
```

---

### VI. Alamat Selama Cuti
| Placeholder | Keterangan |
|------------|-----------|
| `${alamat}` | Alamat selama menjalankan cuti |
| `${telepon}` | Nomor telepon yang bisa dihubungi |
| `${nama_pemohon}` | Nama untuk tanda tangan pemohon |
| `${nip_pemohon}` | NIP untuk tanda tangan pemohon |

---

### VII. Pertimbangan Atasan Langsung
| Placeholder | Keterangan | Contoh |
|------------|-----------|--------|
| `${jabatan_atasan}` | Jabatan atasan (dinamis) | `Sekretaris Pengadilan Negeri Natuna` |
| `${nama_atasan}` | Nama atasan | `BUDI SANTOSO, S.H.` |
| `${nip_atasan}` | NIP atasan | `198005012005011001` |

> Jika pegawai skipAtasanReview (langsung ke Ketua), bagian VII dikosongkan otomatis.

---

### VIII. Keputusan Pejabat Berwenang
| Placeholder | Keterangan | Contoh |
|------------|-----------|--------|
| `${jabatan_pejabat}` | Jabatan pejabat (dinamis) | `Ketua Pengadilan Negeri Natuna` |
| `${nama_pejabat}` | Nama pejabat | `Dr. AHMAD HAKIM, S.H., M.H.` |
| `${nip_pejabat}` | NIP pejabat | `197203012000031001` |

---

## Cara Edit Template di Microsoft Word

### 1. Buka file template
```
storage/app/templates/form-permintaan-cuti-template.docx
```

### 2. Gunakan Find & Replace (Ctrl+H)

> **Tips:** Untuk angka yang muncul berkali-kali (seperti `12`, `2024`), gunakan
> **Find Next → Replace** satu per satu, bukan Replace All.

#### Header
| Cari | Ganti dengan |
|------|-------------|
| `Ranai, 9 April 2026` | `Ranai, ${tanggal}` |
| `700/KPN.W32.U4/KP5.3/IV/2026` | `    /KPN.W32.U4/KP5.3/${bulan_cuti}/${tahun_cuti}` |

#### Data Pegawai
| Cari | Ganti dengan |
|------|-------------|
| `CANDRA FIRMANSYAH, S.I.Pust.` | `${nama}` |
| `5 Tahun 3 Bulan` | `${masa_kerja}` |

#### Jenis Cuti (Checkbox)
| Cari | Ganti dengan |
|------|-------------|
| `√  Cuti Tahunan` atau `-  Cuti Tahunan` | `${cuti_tahunan}  Cuti Tahunan` |
| `-  Cuti Besar` | `${cuti_besar}  Cuti Besar` |
| `-  Cuti Sakit` | `${cuti_sakit}  Cuti Sakit` |
| `-  Cuti Melahirkan` | `${cuti_melahirkan}  Cuti Melahirkan` |
| `-  Cuti Karena Alasan Penting` | `${cuti_alasan_penting}  Cuti Karena Alasan Penting` |
| `-  Cuti di Luar Tanggungan Negara` | `${cuti_luar_tanggungan}  Cuti di Luar Tanggungan Negara` |

#### Lamanya Cuti
| Cari | Ganti dengan |
|------|-------------|
| `2 (hari/...` (di baris "Selama") | `${lama_hari} (hari/...` |
| `9 April 2026` (tanggal mulai) | `${tanggal_mulai}` |

#### Catatan Cuti — Tabel V
| Cari | Ganti dengan | Keterangan |
|------|-------------|-----------|
| `2024` (kolom Tahun baris 1) | `${tahun_n2}` | N-2 |
| `2025` (kolom Tahun baris 2) | `${tahun_n1}` | N-1 |
| `2026` (kolom Tahun baris 3) | `${tahun_n}` | N |
| `0` (baris 2024) | `${sisa_n2}` | Sisa N-2 |
| `6` (baris 2025) | `${sisa_n1}` | Carry-over N-1 |
| `12` (baris 2026) | `${sisa_n}` | Hak N |
| `Sisa 0` (baris 2024) | `${keterangan_n2}` | Keterangan N-2 |
| `Sisa 4` (baris 2025) | `${keterangan_n1}` | Keterangan N-1 (cascade) |
| `Sisa 10` (baris 2026) | `${keterangan_n}` | Keterangan N (cascade) |

#### Alamat & Kontak
| Cari | Ganti dengan |
|------|-------------|
| *(alamat lengkap di template)* | `${alamat}` |
| *(nomor telepon di template)* | `${telepon}` |

#### Tanda Tangan Pemohon
| Cari | Ganti dengan |
|------|-------------|
| `CANDRA FIRMANSYAH, S.I.Pust.` (bagian "Hormat Saya") | `${nama_pemohon}` |
| `NIP. 199001012015031001` (bawah nama pemohon) | `NIP. ${nip_pemohon}` |

#### Pertimbangan Atasan (Bagian VII)
| Cari | Ganti dengan |
|------|-------------|
| `Sekretaris Pengadilan Negeri Natuna,` | `${jabatan_atasan},` |

#### Keputusan Pejabat (Bagian VIII)
| Cari | Ganti dengan |
|------|-------------|
| `Ketua Pengadilan Negeri Natuna,` | `${jabatan_pejabat},` |

### 3. Simpan file

### 4. Test
Export form cuti dari aplikasi dan verifikasi semua placeholder terganti dengan benar.
