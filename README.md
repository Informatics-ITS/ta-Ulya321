# 🏁 Tugas Akhir (TA) - Final Project

**Nama Mahasiswa**: Najma Ulya Agustina

**NRP**: 5025211239

**Judul TA**: Rancang Bangun myIF-Kantin Aplikasi Pemesanan dan Pengantaran Makanan di Kantin Informatika ITS Menggunakan Flutter dan Laravel

**Dosen Pembimbing**: Prof. Dr. Ir. Umi Laili Yuhana, S.Kom., M.Sc.
                    
**Dosen Ko-pembimbing**:   Bintang Nuralamsyah, S.Kom., M.Kom.

---

## 📺 Demo Aplikasi  
Embed video demo di bawah ini (ganti `VIDEO_ID` dengan ID video YouTube Anda):  

[![Demo Aplikasi](https://i.ytimg.com/vi/zIfRMTxRaIs/maxresdefault.jpg)](https://www.youtube.com/watch?v=VIDEO_ID)  
*Klik gambar di atas untuk menonton demo*

---

*Konten selanjutnya hanya merupakan contoh awalan yang baik. Anda dapat berimprovisasi bila diperlukan.*

## 🛠 Panduan Instalasi & Menjalankan Software  

### Prasyarat  
- Daftar dependensi (contoh):
  - Flutter SDK 3.32.4
  - Dart Version 3.8.1
  - MySQL 8.0
  - XAMPP
  

### Langkah-langkah  
1. **Clone Repository**  
   ```bash
   git clone https://github.com/Informatics-ITS/TA.git
   ```
2. **Instalasi Dependensi**
   ```bash

   backend
   akses ke url myifkantin.my.id
   backend(manual)
   nyalakan mysql dan apache di XAMPP
   buka di vscode
   cd proyek
   php artisan migrate:fresh --seed
   php artisan serve 
   
   frontend (manual)
   flutter pub get
   download emulator di android studio (android 13-tiramisu API 33)
   cd [folder-proyek]
   pilih device
   flutter run
   ```
3. **Konfigurasi**
- Salin/rename file .env.example menjadi .env
- Isi variabel lingkungan sesuai kebutuhan (database, API key, dll.)
4. **Jalankan Aplikasi**
   ```bash
   python main.py  # Contoh untuk Python
   npm start      # Contoh untuk Node.js
   ```
5. Buka browser dan kunjungi: `http://localhost:3000` (sesuaikan dengan port proyek Anda)

---

## 📚 Dokumentasi Tambahan

- [![Dokumentasi API]](docs/api.md)
- [![Diagram Arsitektur]](docs/architecture.png)
- [![Struktur Basis Data]](docs/database_schema.sql)

---

## ✅ Validasi

Pastikan proyek memenuhi kriteria berikut sebelum submit:
- Source code dapat di-build/run tanpa error
- Video demo jelas menampilkan fitur utama
- README lengkap dan terupdate
- Tidak ada data sensitif (password, API key) yang ter-expose

---

## ⁉️ Pertanyaan?

Hubungi:
- Penulis: [najmaulya01@gmail.com]
- Pembimbing Utama: [yuhana@if.its.ac.id]
