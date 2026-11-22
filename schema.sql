CREATE DATABASE ppdb_online  CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

USE ppdb_online;
CREATE TABLE users (
	usr_id 			INT AUTO_INCREMENT PRIMARY KEY, 
    usr_nama		VARCHAR(100) NOT NULL,
    usr_email		VARCHAR(100) UNIQUE NOT NULL,
    usr_pass		VARCHAR(255) NOT NULL,
    usr_role		ENUM('admin','siswa') NOT NULL,
    usr_created_at 	TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE siswa (
	swa_id				INT AUTO_INCREMENT PRIMARY KEY,
    usr_id				INT NOT NULL, 
    swa_nisn			VARCHAR(10) UNIQUE NOT NULL,
    swa_nama_lengkap	VARCHAR(100) NOT NULL,
    swa_kelamin			ENUM('L','P') NOT NULL,
    swa_tmpt_lahir		VARCHAR(50) ,
    swa_tgl_lahir		DATE ,
    swa_alamat			TEXT ,
    swa_asal_sekolah	VARCHAR(100) ,
    swa_no_hp			VARCHAR(20) NOT NULL,
    
    FOREIGN KEY (usr_id) REFERENCES users(usr_id)
    ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE jurusan (
	jrs_id				INT AUTO_INCREMENT PRIMARY KEY,
    jrs_kode			VARCHAR(20) UNIQUE NOT NULL,
    jrs_nama			VARCHAR(100) NOT NULL,
    jrs_kuota			INT NOT NULL,
    jrs_deskripsi		TEXT NOT NULL,
    jrs_status			TINYINT NOT NULL 
						-- 0: tidak aktif, 1: aktif
);

CREATE TABLE pendaftaran (
	pdt_id				INT AUTO_INCREMENT PRIMARY KEY,
    swa_id				INT NOT NULL,
    jrs_id				INT NOT NULL,
    pdt_no				VARCHAR(30) UNIQUE NOT NULL,
    pdt_thn_ajar		VARCHAR(9) NOT NULL,
    pdt_tgl_daftar		TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    pdt_status			ENUM('menunggu', 'diverifikasi', 'diterima', 'ditolak') NOT NULL DEFAULT 'menunggu',
    pdt_catatan			TEXT,
    
    FOREIGN KEY (swa_id) REFERENCES siswa(swa_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (jrs_id) REFERENCES jurusan(jrs_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE berkas(
	brk_id				INT AUTO_INCREMENT PRIMARY KEY,
    pdt_id				INT NOT NULL,
    brk_jenis			ENUM('kartu_keluarga','akta_kelahiran','rapor','pas_foto', 'lainnya') NOT NULL DEFAULT 'lainnya',
    brk_nama_file		VARCHAR(255) NOT NULL,
    brk_ukuran_file		INT NOT NULL,
    brk_tgl_up			TIMESTAMP DEFAULt CURRENT_TIMESTAMP,
    
    FOREIGN KEY (pdt_id) REFERENCES pendaftaran(pdt_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE riwayat_status (
	rs_id				INT AUTO_INCREMENT PRIMARY KEY,
    pdt_id				INT NOT NULL,
    admin_id			INT NOT NULL,
    rs_status_sblm		VARCHAR(20),
    rs_status_baru		VARCHAR(20),
    rs_catatan			TEXT,
    rs_tgl_ubah			TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pdt_id) REFERENCES pendaftaran(pdt_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(usr_id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- admin
INSERT INTO users (usr_nama, usr_email, usr_pass, usr_role)
VALUE ('admin', 'admin@gmail.com' ,'$2y$10$L67MkKvGPWfCMA.B2tu0Der6msj1i8qRBwNwB.dnw00GO1RsuFnlG', 'admin');	