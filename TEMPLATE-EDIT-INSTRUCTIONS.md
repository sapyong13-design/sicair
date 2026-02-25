# Instruksi Edit Template DOCX

File template ada di: `storage/app/templates/form-permintaan-cuti-template.docx`

## Placeholder yang sudah otomatis diganti ✓
- `${nip}` - NIP pegawai (2 tempat)
- `${jabatan}` - Jabatan
- `${alasan}` - Alasan cuti
- `${tanggal_selesai}` - Tanggal selesai
- `${nama_atasan}` - Nama atasan
- `${nip_atasan}` - NIP atasan
- `${nama_pejabat}` - Nama pejabat
- `${nip_pejabat}` - NIP pejabat

## Yang perlu diedit MANUAL di Microsoft Word:

### 1. Buka file template di Word
```
storage/app/templates/form-permintaan-cuti-template.docx
```

### 2. Gunakan Find & Replace (Ctrl+H) untuk item berikut:

#### Header
| Cari | Ganti Dengan |
|------|--------------|
| `Ranai, 9 Desember 2025` | `Ranai, ${tanggal}` |
| `700/KPN.W32.U4/KP5.3/XII/2025` | `    /KPN.W32.U4/KP5.3/${bulan_cuti}/${tahun_cuti}` |

**Catatan nomor surat:**
- Ada 4 spasi di awal (untuk diisi manual)
- `${bulan_cuti}` = bulan romawi dari tanggal mulai cuti (contoh: XII)
- `${tahun_cuti}` = tahun dari tanggal mulai cuti (contoh: 2025)

#### Data Pegawai
| Cari | Ganti Dengan |
|------|--------------|
| `CANDRA FIRMANSYAH, S.I.Pust.` | `${nama}` |
| `5 Tahun 0 Bulan` | `${masa_kerja}` |

#### Jenis Cuti yang Diambil (Checkbox)

**PENTING:** Placeholder akan otomatis diganti dengan √ (jika cuti tersebut dipilih) atau - (jika tidak).

| Cari | Ganti Dengan |
|------|--------------|
| `√  Cuti Tahunan` ATAU `-  Cuti Tahunan` | `${cuti_tahunan}  Cuti Tahunan` |
| `-  Cuti Besar` | `${cuti_besar}  Cuti Besar` |
| `-  Cuti Sakit` | `${cuti_sakit}  Cuti Sakit` |
| `-  Cuti Melahirkan` | `${cuti_melahirkan}  Cuti Melahirkan` |
| `-  Cuti Karena Alasan Penting` | `${cuti_alasan_penting}  Cuti Karena Alasan Penting` |
| `-  Cuti di Luar Tanggungan Negara` | `${cuti_luar_tanggungan}  Cuti di Luar Tanggungan Negara` |

**Contoh hasil:** Jika user pilih Cuti Sakit, sistem akan tampilkan:
```
-  Cuti Tahunan        -  Cuti Besar
√  Cuti Sakit          -  Cuti Melahirkan
-  Cuti Karena Alasan Penting    -  Cuti di Luar Tanggungan Negara
```

#### Lamanya Cuti
| Cari | Ganti Dengan |
|------|--------------|
| `12` (di baris "Selama ... hari") | `${lama_hari}` |
| `15 Desember 2025` | `${tanggal_mulai}` |

#### Catatan Cuti (tabel V)

**Hati-hati:** Gunakan Find Next + Replace (bukan Replace All) untuk angka yang muncul berkali-kali.

| Cari | Ganti Dengan | Keterangan |
|------|--------------|------------|
| `2023` | `${tahun_n2}` | Tahun N-2 (kolom Tahun baris 1) |
| `2024` | `${tahun_n1}` | Tahun N-1 (kolom Tahun baris 2) |
| `2025` | `${tahun_n}` | Tahun N (kolom Tahun baris 3) |
| `0` (di baris 2023) | `${sisa_n2}` | Sisa N-2 (kolom Sisa baris 1) |
| `6` (di baris 2024) | `${sisa_n1}` | Sisa N-1 (kolom Sisa baris 2) |
| `12` (di baris 2025) | `${sisa_n}` | Sisa N (kolom Sisa baris 3) |
| `Sisa 0` (baris 2023) | `${keterangan_n2}` | Keterangan N-2 (kolom Keterangan baris 1) |
| `Sisa 0` (baris 2024) | `${keterangan_n1}` | Keterangan N-1 (kolom Keterangan baris 2) |
| `Sisa 6` (baris 2025) | `${keterangan_n}` | Keterangan N (kolom Keterangan baris 3) |

#### Alamat & Kontak
| Cari | Ganti Dengan |
|------|--------------|
| `Jalan LK 1 Pringsewu Utara RT/RW 005/002 Kelurahan Pringsewu Utara Kecamatan Pringsewu Kabupaten Pringsewu Propinsi Lampung` | `${alamat}` |
| `0813-7387-2683` | `${telepon}` |

#### Tanda Tangan Pemohon
Di bagian "Hormat Saya,":
- Hapus nama "CANDRA FIRMANSYAH, S.I.Pust."
- Ganti dengan: `${nama_pemohon}`
- Di baris bawahnya, "NIP. ..." sudah jadi `NIP. ${nip}` - biarkan saja atau ubah jadi `NIP. ${nip_pemohon}`

#### **PENTING: Jabatan Atasan dan Pejabat (BARU)**
Di bagian **VII. PERTIMBANGAN ATASAN LANGSUNG**:
| Cari | Ganti Dengan |
|------|--------------|
| `Sekretaris Pengadilan Negeri Natuna,` | `${jabatan_atasan},` |

Di bagian **VIII. KEPUTUSAN PEJABAT**:
| Cari | Ganti Dengan |
|------|--------------|
| `Ketua Pengadilan Negeri Natuna,` | `${jabatan_pejabat},` |

**Catatan**: Jabatan ini akan dinamis sesuai dengan siapa yang menyetujui cuti:
- Jika atasan adalah Sekretaris → akan tampil "Sekretaris Pengadilan Negeri Natuna"
- Jika atasan adalah Panitera → akan tampil "Panitera Pengadilan Negeri Natuna"
- Pejabat biasanya Ketua → akan tampil "Ketua Pengadilan Negeri Natuna"

### 3. Save file setelah semua diganti

### 4. Test
Setelah save, coba export form cuti dari aplikasi untuk memastikan semua placeholder terganti dengan benar.

## Tips:
- Hati-hati dengan angka yang muncul berkali-kali (seperti "12", "2025") - pastikan replace yang benar
- Gunakan "Replace All" hanya jika yakin tidak akan replace yang salah
- Jika ragu, gunakan "Find Next" dan "Replace" satu per satu

## Ukuran Halaman
✓ Sudah diset ke Legal (8.5 x 14 inch) secara otomatis
