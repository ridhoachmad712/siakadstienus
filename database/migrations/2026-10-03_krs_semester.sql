-- Jalankan pada database tujuan setelah backup. Tidak mengubah KRS/KHS lama.
CREATE TABLE IF NOT EXISTS krs_semester_mahasiswa (
    nim_npm VARCHAR(20) NOT NULL,
    id_thn_akademik INT NOT NULL,
    kode_prodi VARCHAR(20) NOT NULL,
    semester INT NOT NULL,
    alasan VARCHAR(500) NOT NULL,
    id_pemberi INT NOT NULL,
    diperbarui_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (nim_npm,id_thn_akademik),
    FOREIGN KEY (nim_npm) REFERENCES mahasiswa(nim_npm) ON DELETE RESTRICT,
    FOREIGN KEY (id_thn_akademik) REFERENCES thn_akademik(id_thn_akademik) ON DELETE RESTRICT,
    FOREIGN KEY (kode_prodi) REFERENCES prodi(kode_prodi) ON DELETE RESTRICT,
    FOREIGN KEY (id_pemberi) REFERENCES user(id_user) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS krs_izin_matkul (
    nim_npm VARCHAR(20) NOT NULL,
    id_thn_akademik INT NOT NULL,
    kode_prodi VARCHAR(20) NOT NULL,
    kode_matkul VARCHAR(20) NOT NULL,
    jenis VARCHAR(20) NOT NULL,
    alasan VARCHAR(500) NOT NULL,
    aktif TINYINT NOT NULL DEFAULT 1,
    id_pemberi INT NOT NULL,
    diperbarui_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (nim_npm,id_thn_akademik,kode_matkul),
    FOREIGN KEY (nim_npm) REFERENCES mahasiswa(nim_npm) ON DELETE RESTRICT,
    FOREIGN KEY (id_thn_akademik) REFERENCES thn_akademik(id_thn_akademik) ON DELETE RESTRICT,
    FOREIGN KEY (kode_matkul) REFERENCES mata_kuliah(kode_matkul) ON DELETE RESTRICT,
    FOREIGN KEY (kode_prodi) REFERENCES prodi(kode_prodi) ON DELETE RESTRICT,
    FOREIGN KEY (id_pemberi) REFERENCES user(id_user) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS krs_kebijakan_log (
    id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nim_npm VARCHAR(20) NOT NULL,
    id_thn_akademik INT NOT NULL,
    id_pemberi INT NOT NULL,
    aksi VARCHAR(30) NOT NULL,
    rincian TEXT NOT NULL,
    dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_krs_log_mahasiswa (nim_npm,id_thn_akademik),
    FOREIGN KEY (nim_npm) REFERENCES mahasiswa(nim_npm) ON DELETE RESTRICT,
    FOREIGN KEY (id_thn_akademik) REFERENCES thn_akademik(id_thn_akademik) ON DELETE RESTRICT,
    FOREIGN KEY (id_pemberi) REFERENCES user(id_user) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
