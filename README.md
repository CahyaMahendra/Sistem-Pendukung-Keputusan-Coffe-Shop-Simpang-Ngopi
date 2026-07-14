# SPK Seleksi Karyawan - Coffee Shop "Simpang Ngopi"
### Metode: AHP (pembobotan kriteria) + TOPSIS (perangkingan alternatif)

## Struktur Folder
```
spk-simpangngopi/
├── database.sql          # skema + data contoh
├── config.php             # koneksi database (PDO)
├── functions.php          # fungsi perhitungan AHP & TOPSIS
├── index.php              # dashboard
├── kriteria.php           # CRUD kriteria penilaian
├── alternatif.php         # CRUD calon karyawan
├── penilaian.php          # input nilai tiap calon karyawan per kriteria (skala 1-5)
├── ahp.php                # input matriks perbandingan berpasangan + hitung bobot AHP
├── topsis.php             # hitung & tampilkan ranking akhir TOPSIS
├── includes/
│   ├── header.php
│   └── footer.php
└── assets/
    └── style.css
```

## Cara Instalasi (XAMPP / Laragon / sejenisnya)

1. **Salin folder** `spk-simpangngopi` ke dalam folder web server, misalnya:
   - XAMPP: `C:\xampp\htdocs\spk-simpangngopi`
   - Laragon: `C:\laragon\www\spk-simpangngopi`

2. **Buat database**. Buka phpMyAdmin, lalu import file `database.sql`
   (ini akan otomatis membuat database `spk_simpangngopi`, tabel, dan data contoh).

3. **Sesuaikan koneksi database** di `config.php` bila perlu (user/password MySQL Anda):
   ```php
   $DB_HOST = 'localhost';
   $DB_NAME = 'spk_simpangngopi';
   $DB_USER = 'root';
   $DB_PASS = '';
   ```

4. **Jalankan** dengan mengakses `http://localhost/spk-simpangngopi/` di browser.

## Alur Penggunaan Sistem

1. **Kriteria** — Kelola kriteria penilaian (contoh default: Pengalaman Kerja, Pendidikan Terakhir,
   Hasil Wawancara, Sikap/Attitude, Kemampuan Komunikasi). Setiap kriteria bisa diatur sebagai
   *benefit* (semakin besar semakin baik) atau *cost* (semakin kecil semakin baik).
2. **Calon Karyawan** — Kelola daftar alternatif/calon karyawan yang akan diseleksi.
3. **Penilaian** — Input nilai (skala 1–5) untuk tiap calon karyawan pada tiap kriteria.
4. **AHP** — Isi matriks perbandingan berpasangan antar kriteria (skala Saaty 1–9), sistem
   otomatis menghitung:
   - Normalisasi matriks & bobot prioritas tiap kriteria
   - λ max, Consistency Index (CI), Random Index (RI), dan Consistency Ratio (CR)
   - Jika CR ≤ 0.1 → matriks dinyatakan **konsisten** dan bobot dapat digunakan.
5. **TOPSIS** — Menggunakan bobot AHP untuk menghitung:
   - Matriks ternormalisasi (normalisasi vektor)
   - Matriks ternormalisasi terbobot
   - Solusi ideal positif (A+) dan negatif (A-)
   - Jarak tiap alternatif ke A+ (D+) dan A- (D-)
   - Nilai preferensi **V = D- / (D+ + D-)**
   - Perangkingan akhir calon karyawan (nilai V tertinggi = peringkat 1 = paling direkomendasikan)

## Catatan
- Data kriteria & contoh nilai pada `database.sql` merupakan **contoh default** untuk kasus
  seleksi karyawan Coffee Shop Simpang Ngopi (barista/kasir). Silakan sesuaikan kriteria,
  bobot pairwise, dan data calon karyawan sesuai kondisi riil melalui antarmuka website.
- Sistem ini dibangun dengan PHP native (PDO MySQL) + Bootstrap 5, tanpa framework, sehingga
  mudah dipelajari dan dimodifikasi untuk keperluan laporan/tugas akademik.
