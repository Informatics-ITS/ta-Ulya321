# 🏁 Tugas Akhir (TA) - Final Project

**Nama Mahasiswa**: Najma Ulya Agustina

**NRP**: 5025211239

**Judul TA**: Rancang Bangun myIF-Kantin Aplikasi Pemesanan dan Pengantaran Makanan di Kantin Informatika ITS Menggunakan Flutter dan Laravel

**Dosen Pembimbing**: Prof. Dr. Ir. Umi Laili Yuhana, S.Kom., M.Sc.
                    
**Dosen Ko-pembimbing**:   Bintang Nuralamsyah, S.Kom., M.Kom.

---

## 📺 Demo Aplikasi  

[![Demo Aplikasi](https://i.pinimg.com/736x/85/c9/86/85c986f9151044729ac97c8e6bdc89cc.jpg)](https://youtu.be/wYGlKiERNR0)  
*Klik gambar di atas untuk menonton demo*

---

*Konten selanjutnya hanya merupakan contoh awalan yang baik. Anda dapat berimprovisasi bila diperlukan.*

## 🛠 Panduan Instalasi & Menjalankan Software  

### Prasyarat  
- Daftar dependensi (contoh):
  - Flutter SDK 3.32.4
  - Dart Version 3.8.1
  - MySQL 8.0
  - XAMPP 8.2.12/PHP 8.2.12
  

### Langkah-langkah  
1. **Clone Repository**  
   ```bash
   git clone https://github.com/Informatics-ITS/ta-Ulya321.git
   ```
2. **Instalasi Dependensi**
   ```bash

   mobile(frontend)
   
   flutter pub get
   
3. **Konfigurasi**
- Pada .env ubah DB_DATABASE sesuai dengan nama database yang ingin/sudah dibuat
- Isi variabel lingkungan sesuai kebutuhan (database, dll.)
  
4. **Jalankan Aplikasi**
   ```bash
   (backend)
   php artisan migrate:fresh --seed #membuat migration di database
   php artisan serve --host=0.0.0.0 #sesuaikan dengan ip jaringan anda

   (mobile)
   flutter run
   ```
5. Buka browser dan kunjungi: `http://192.168.100.38:3000` (sesuaikan dengan IP jaringan dan port proyek Anda)

---

## 📚 Dokumentasi Tambahan

- [![Dokumentasi API]](docs/api.md)
- [![Diagram Arsitektur]](docs/architecture.png)
- [![User Manual]](docs/database_schema.sql)

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
- Penulis: najmaulya01@gmail.com
- Pembimbing Utama: yuhana@if.its.ac.id
