<?php
require_once __DIR__ . '/../config/database.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nama_lengkap = trim($_POST["nama_lengkap"] ?? "");
  $email = trim($_POST["email"] ?? "");
  $no_hp = trim($_POST["no_hp"] ?? "");
  $nisn = trim($_POST["nisn"] ?? "");
  $password = $_POST["password"] ?? "";
  $konfirmasi_password = $_POST["konfirmasi_password"] ?? "";

  if (empty($nama_lengkap) || empty($email) || empty($password) || empty($nisn) || empty($no_hp)) {
    $error = "Semua field wajib diisi.";
  } elseif (!preg_match('/^[0-9]{10}$/', $nisn)) {
    $error = "NISN harus berupa 10 digit angka.";
  } elseif (strlen($password) < 8) {
    $error = "Password minimal 8 karakter.";
  } elseif ($password !== $konfirmasi_password) {
    $error = "Konfirmasi password tidak cocok.";
  } else {
    try {
      $stmt = $pdo->prepare("SELECT usr_id FROM users WHERE usr_email = ?");
      $stmt->execute([$email]);
      if ($stmt->fetch()) {
        $error = "Email sudah terdaftar.";
      } else {
        $pdo->beginTransaction();

        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmtUser = $pdo->prepare("INSERT INTO users (usr_nama, usr_email, usr_pass, usr_role) VALUES (?, ?, ?, 'siswa')");
        $stmtUser->execute([$nama_lengkap, $email, $password_hash]);
        $usr_id = $pdo->lastInsertId();

        $stmtSiswa = $pdo->prepare("INSERT INTO siswa (usr_id, swa_nisn, swa_nama_lengkap, swa_kelamin, swa_no_hp) VALUES (?, ?, ?, 'L', ?)");
        $stmtSiswa->execute([$usr_id, $nisn, $nama_lengkap, $no_hp]);

        $pdo->commit();
        header("Location: login.php?registered=success");
        exit;
      }
    } catch (Exception $e) {
      if ($pdo->inTransaction()) {
        $pdo->rollBack();
      }
      $error = "Terjadi kesalahan pada database: " . $e->getMessage();
    }
  }
}
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Registrasi Siswa - Portal PPDB Online SMK Nusantara." />
  <title>Registrasi Siswa | PPDB Online SMK Nusantara</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet" />
  <style>
    :root {
      --bg-dark: #141b21;
      --bg-card: #1e222a;
      --text-light: #f5f5f5;
      --text-muted: #53607c;
      --text-muted-light: #8793a9;
      --accent-red: #c9333b;
      --border-card-light: #53607c;
      --font-heading: "Oswald", sans-serif;
      --font-body: "DM Sans", sans-serif;
      --font-size-h1: 3.5rem;
      --font-size-h5: 1.5rem;
      --font-size-body: 1.125rem;
      --spacing-md: 1rem;
      --spacing-lg: 2rem;
      --spacing-xl: 3rem;
    }

    body {
      font-family: var(--font-body);
      background-color: var(--bg-dark);
      color: var(--text-light);
      margin: 0;
      padding: 0;
      line-height: 1.6;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .container {
      max-width: 1400px;
      margin: 0 auto;
      padding: 0 var(--spacing-md);
    }

    h1,
    h2,
    h5 {
      font-family: var(--font-heading);
      font-weight: 700;
      letter-spacing: -0.02em;
      color: var(--text-light);
      margin: 0 0 var(--spacing-md) 0;
    }

    .navbar {
      background-color: var(--bg-dark);
      border-bottom: 1px solid var(--border-card-light);
      padding: var(--spacing-md);
    }

    .navbar-brand {
      font-family: var(--font-heading);
      font-size: var(--font-size-h5);
      color: var(--text-light);
      font-weight: 700;
      text-decoration: none;
    }

    .navbar-brand span {
      color: var(--accent-red);
      margin-left: 0.5rem;
    }

    .btn-back {
      font-size: 0.875rem;
      color: var(--text-muted-light);
      border: 1px solid var(--border-card-light);
      border-radius: 2px;
      padding: 0.4rem 1rem;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .btn-back:hover {
      color: var(--accent-red);
      border-color: var(--accent-red);
      background-color: var(--bg-card);
    }

    .auth-section {
      flex: 1;
      display: flex;
      align-items: stretch;
    }

    .left-panel {
      background-color: var(--bg-dark);
      border-left: 4px solid var(--accent-red);
      border-right: 1px solid var(--border-card-light);
      padding: var(--spacing-xl);
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .left-panel h1 {
      font-size: var(--font-size-h1);
      line-height: 1.1;
      margin-bottom: var(--spacing-md);
    }

    .left-panel p {
      color: var(--text-muted-light);
      font-size: var(--font-size-body);
      margin-bottom: var(--spacing-xl);
      max-width: 480px;
    }

    .bullet-item {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 1.25rem;
    }

    .bullet-square {
      width: 16px;
      height: 16px;
      flex: 0 0 16px;
      background-color: var(--accent-red);
    }

    .bullet-text {
      font-family: var(--font-heading);
      font-size: 1.25rem;
      font-weight: 600;
      color: var(--text-light);
    }

    .right-panel {
      background-color: var(--bg-dark);
      padding: var(--spacing-xl);
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .form-title {
      font-size: 2.25rem;
      letter-spacing: 1px;
      margin-bottom: var(--spacing-lg);
      text-align: left;
    }

    .form-label {
      font-family: var(--font-heading);
      font-weight: 600;
      color: var(--accent-red);
      margin-bottom: 0.35rem;
      font-size: 1.25rem;
    }

    .form-control {
      background-color: var(--bg-card);
      border: 1px solid var(--border-card-light);
      border-radius: 2px;
      color: var(--text-light);
      padding: 0.75rem 1rem;
      font-size: 0.95rem;
    }

    .form-control::placeholder {
      color: var(--text-muted);
    }

    .form-control:focus {
      background-color: var(--bg-card);
      border-color: var(--accent-red);
      color: var(--text-light);
      box-shadow: none;
    }

    .form-check-input {
      background-color: var(--bg-card);
      border: 1px solid var(--border-card-light);
      border-radius: 2px;
      width: 18px;
      height: 18px;
    }

    .form-check-input:checked {
      background-color: var(--accent-red);
      border-color: var(--accent-red);
    }

    .form-check-label {
      color: var(--text-muted-light);
      font-size: 0.85rem;
      line-height: 1.4;
    }

    .btn-submit {
      background-color: var(--accent-red);
      border: 1px solid var(--accent-red);
      color: white;
      font-family: var(--font-heading);
      font-size: 1.5rem;
      font-weight: 700;
      letter-spacing: 1px;
      padding: 0.85rem;
      border-radius: 2px;
      width: 100%;
      margin-top: var(--spacing-md);
      transition: all 0.3s ease;
    }

    .btn-submit:hover {
      background-color: white;
      border-color: var(--accent-red);
      color: var(--accent-red);
    }

    footer {
      background-color: var(--bg-dark);
      border-top: 1px solid var(--border-card-light);
      padding: 1.25rem 0;
    }
  </style>
</head>

<body>
  <nav class="navbar py-3">
    <div class="container d-flex justify-content-between align-items-center">
      <a class="navbar-brand d-flex align-items-center fw-bold" href="../index.html">
        SMK <span>Nusantara</span>
      </a>
      <a href="../index.html" class="btn-back">&larr; Kembali ke Beranda</a>
    </div>
  </nav>

  <section class="auth-section py-0">
    <div class="container mx-0 p-0 min-vw-100">
      <div class="row g-0 rounded-1 overflow-hidden">
        <div class="col-lg-6 left-panel d-none d-md-flex">
          <h1>MULAI LANGKAH<br />MENUJU MASA<br />DEPAN</h1>
          <p>Masuk ke akun PPDB Online SMK Nusantara untuk melanjutkan proses pendaftaran dan memantau status seleksi.</p>
          <div class="bullets-wrap mt-3">
            <div class="bullet-item"><span class="bullet-square"></span><span class="bullet-text">Pantau status pendaftaran</span></div>
            <div class="bullet-item"><span class="bullet-square"></span><span class="bullet-text">Perbarui biodata dan dokumen</span></div>
            <div class="bullet-item"><span class="bullet-square"></span><span class="bullet-text">Lihat informasi hasil seleksi</span></div>
          </div>
        </div>

        <div class="col-lg-6 right-panel">
          <h2 class="form-title">REGISTRASI SISWA</h2>

          <?php if (!empty($error)): ?>
            <div class="alert alert-danger mb-4 rounded-2" style="font-size: 0.95rem; background-color: #2c1619; border-color: var(--accent-red); color: #ff8b94;">
              <?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>

          <form action="register.php" method="POST">
            <div class="mb-3">
              <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
              <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" placeholder="Masukkan nama sesuai identitas" value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>" required />
            </div>
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label for="email" class="form-label">Email aktif</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="nama@mail.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required />
              </div>
              <div class="col-md-6">
                <label for="no_hp" class="form-label">Nomor HP / WhatsApp</label>
                <input type="tel" class="form-control" id="no_hp" name="no_hp" placeholder="08xxxxxx" value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>" required />
              </div>
            </div>
            <div class="mb-3">
              <label for="nisn" class="form-label">NISN</label>
              <input type="text" class="form-control" id="nisn" name="nisn" placeholder="Masukkan 10 digit NISN" maxlength="10" value="<?= htmlspecialchars($_POST['nisn'] ?? '') ?>" required />
            </div>
            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Minimal 8 karakter" required />
              </div>
              <div class="col-md-6">
                <label for="konfirmasi_password" class="form-label">Konfirmasi Password</label>
                <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" placeholder="Ulangi password" required />
              </div>
            </div>
            <div class="form-check mb-4 d-flex gap-2">
              <input class="form-check-input flex-shrink-0" type="checkbox" id="terms" required />
              <label class="form-check-label" for="terms">
                Saya menyatakan data yang diisi benar dan bersedia mengikuti ketentuan PPDB SMK Nusantara.
              </label>
            </div>
            <button type="submit" class="btn btn-submit">BUAT AKUN</button>
          </form>
        </div>
      </div>
    </div>
  </section>

  <footer>
    <div class="container text-center">
      <p class="small mb-0" style="color: var(--text-muted)">
        &copy; 2026 SMK Nusantara. PPDB Online Sistem Penerimaan Peserta Didik Baru.
      </p>
    </div>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>