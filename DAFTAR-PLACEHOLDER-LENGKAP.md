# Daftar Lengkap Placeholder Template Form Cuti

## 📋 Semua Placeholder yang Tersedia

### Header & Tanggal
- `${tanggal}` - Tanggal surat (contoh: "9 Desember 2025")
- `${bulan_cuti}` - Bulan cuti dalam romawi (contoh: "XII")
- `${tahun_cuti}` - Tahun cuti (contoh: "2025")

**Format nomor surat:** `    /KPN.W32.U4/KP5.3/${bulan_cuti}/${tahun_cuti}`
(ada 4 spasi di awal untuk diisi manual)

### I. Data Pegawai
- `${nama}` - Nama pegawai
- `${nip}` - NIP pegawai
- `${jabatan}` - Jabatan pegawai
- `${masa_kerja}` - Masa kerja (contoh: "5 Tahun 0 Bulan")
- `${unit_kerja}` - Unit kerja (default: "Pengadilan Negeri Natuna")

### II. Jenis Cuti yang Diambil
**Auto checklist √ atau - sesuai pilihan:**
- `${cuti_tahunan}` - Cuti Tahunan
- `${cuti_besar}` - Cuti Besar
- `${cuti_sakit}` - Cuti Sakit
- `${cuti_melahirkan}` - Cuti Melahirkan
- `${cuti_alasan_penting}` - Cuti Karena Alasan Penting
- `${cuti_luar_tanggungan}` - Cuti di Luar Tanggungan Negara

### III. Alasan Cuti
- `${alasan}` - Alasan cuti

### IV. Lamanya Cuti
- `${lama_hari}` - Jumlah hari (angka)
- `${tanggal_mulai}` - Tanggal mulai (contoh: "15 Desember 2025")
- `${tanggal_selesai}` - Tanggal selesai (contoh: "2 Januari 2026")

### V. Catatan Cuti

#### Baris N-2 (2 tahun lalu)
- `${tahun_n2}` - Tahun N-2
- `${sisa_n2}` - Sisa cuti N-2
- `${keterangan_n2}` - Keterangan N-2 (contoh: "Sisa 0")

#### Baris N-1 (tahun lalu)
- `${tahun_n1}` - Tahun N-1
- `${sisa_n1}` - Sisa cuti N-1
- `${keterangan_n1}` - Keterangan N-1 (contoh: "Sisa 0")

#### Baris N (tahun ini)
- `${tahun_n}` - Tahun N
- `${sisa_n}` - Sisa cuti N
- `${keterangan_n}` - Keterangan N (contoh: "Sisa 6")

### VI. Alamat Selama Menjalankan Cuti
- `${alamat}` - Alamat lengkap
- `${telepon}` - Nomor telepon
- `${nama_pemohon}` - Nama pemohon (untuk tanda tangan)
- `${nip_pemohon}` - NIP pemohon (untuk tanda tangan)

### VII. Pertimbangan Atasan Langsung
- `${jabatan_atasan}` - Jabatan atasan (contoh: "Sekretaris Pengadilan Negeri Natuna")
- `${nama_atasan}` - Nama atasan
- `${nip_atasan}` - NIP atasan

### VIII. Keputusan Pejabat yang Berwenang
- `${jabatan_pejabat}` - Jabatan pejabat (contoh: "Ketua Pengadilan Negeri Natuna")
- `${nama_pejabat}` - Nama pejabat
- `${nip_pejabat}` - NIP pejabat

---

## 🎯 Total: 37 Placeholder

## ✅ Cara Kerja

1. **Placeholder statis** - langsung diganti dengan nilai (nama, NIP, tanggal, dll)
2. **Placeholder checkbox** - otomatis √ atau - sesuai jenis cuti yang dipilih
3. **Placeholder dinamis tahun** - otomatis hitung N-2, N-1, N berdasarkan tahun pengajuan
4. **Placeholder jabatan** - otomatis sesuai role atasan/pejabat yang approve

## 📝 Status Edit Template

Cek file: `TEMPLATE-EDIT-INSTRUCTIONS.md` untuk panduan lengkap edit template di Word.

Setelah semua placeholder diganti, template akan 100% dinamis dan format tetap sama dengan aslinya!
