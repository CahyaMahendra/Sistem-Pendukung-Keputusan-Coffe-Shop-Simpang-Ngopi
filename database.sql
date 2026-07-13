
CREATE DATABASE IF NOT EXISTS spk_simpangngopi;
USE spk_simpangngopi;


CREATE TABLE IF NOT EXISTS kriteria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(10) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    jenis ENUM('benefit','cost') NOT NULL DEFAULT 'benefit',
    bobot_ahp DECIMAL(10,6) DEFAULT 0,
    urutan INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS alternatif (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(10) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    keterangan VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS penilaian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alternatif_id INT NOT NULL,
    kriteria_id INT NOT NULL,
    nilai DECIMAL(10,4) NOT NULL DEFAULT 0,
    FOREIGN KEY (alternatif_id) REFERENCES alternatif(id) ON DELETE CASCADE,
    FOREIGN KEY (kriteria_id) REFERENCES kriteria(id) ON DELETE CASCADE,
    UNIQUE KEY unik_nilai (alternatif_id, kriteria_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS perbandingan_ahp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kriteria_baris_id INT NOT NULL,
    kriteria_kolom_id INT NOT NULL,
    nilai DECIMAL(10,6) NOT NULL DEFAULT 1,
    FOREIGN KEY (kriteria_baris_id) REFERENCES kriteria(id) ON DELETE CASCADE,
    FOREIGN KEY (kriteria_kolom_id) REFERENCES kriteria(id) ON DELETE CASCADE,
    UNIQUE KEY unik_pair (kriteria_baris_id, kriteria_kolom_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS hasil_topsis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alternatif_id INT NOT NULL,
    nilai_preferensi DECIMAL(10,6) NOT NULL,
    peringkat INT NOT NULL,
    tanggal_hitung DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (alternatif_id) REFERENCES alternatif(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


INSERT INTO kriteria (kode, nama, jenis, urutan) VALUES
('C1', 'Pengalaman Kerja', 'benefit', 1),
('C2', 'Pendidikan Terakhir', 'benefit', 2),
('C3', 'Hasil Wawancara', 'benefit', 3),
('C4', 'Sikap / Attitude', 'benefit', 4),
('C5', 'Kemampuan Komunikasi', 'benefit', 5);

INSERT INTO alternatif (kode, nama, keterangan) VALUES
('A1', 'Andi', 'Pelamar Barista'),
('A2', 'Budi', 'Pelamar Barista'),
('A3', 'Citra', 'Pelamar Barista'),
('A4', 'Dimas', 'Pelamar Barista'),
('A5', 'Eka', 'Pelamar Barista');


INSERT INTO penilaian (alternatif_id, kriteria_id, nilai) VALUES
(1,1,4),(1,2,3),(1,3,4),(1,4,5),(1,5,4),
(2,1,3),(2,2,4),(2,3,3),(2,4,4),(2,5,3),
(3,1,5),(3,2,3),(3,3,4),(3,4,3),(3,5,4),
(4,1,3),(4,2,3),(4,3,5),(4,4,4),(4,5,5),
(5,1,4),(5,2,4),(5,3,3),(5,4,4),(5,5,3);


INSERT INTO perbandingan_ahp (kriteria_baris_id, kriteria_kolom_id, nilai) VALUES
(1,1,1),(1,2,3),(1,3,2),(1,4,1),(1,5,3),
(2,1,0.3333),(2,2,1),(2,3,0.5),(2,4,0.3333),(2,5,1),
(3,1,0.5),(3,2,2),(3,3,1),(3,4,0.5),(3,5,2),
(4,1,1),(4,2,3),(4,3,2),(4,4,1),(4,5,3),
(5,1,0.3333),(5,2,1),(5,3,0.5),(5,4,0.3333),(5,5,1);
