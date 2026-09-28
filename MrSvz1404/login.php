<?php
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    redirect('MrSvz1404/index.php');
}

$error = '';
$flash = getFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Username dan Password wajib diisi.';
    } else {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_name'] = $admin['name'];

            setFlash('success', 'Selamat datang kembali, ' . sanitize($admin['name']) . '!');
            redirect('MrSvz1404/index.php');
        } else {
            $error = 'Username atau Password salah. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Admin Control | Mas Putra Portfolio</title>
  
  <link rel="icon" type="image/png" href="<?= base_url('assets/images/avatar.png') ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
</head>
<body class="admin-body">

  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <div class="login-logo">
          <i class="fas fa-user-shield"></i>
        </div>
        <h2>Admin Control</h2>
        <p>Masuk untuk mengelola portofolio & CV Anda</p>
      </div>

      <?php if ($flash): ?>
        <div class="alert-banner <?= $flash['type'] ?>">
          <i class="fas fa-info-circle"></i>
          <span><?= $flash['message'] ?></span>
        </div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="alert-banner danger">
          <i class="fas fa-exclamation-triangle"></i>
          <span><?= $error ?></span>
        </div>
      <?php endif; ?>

      <form action="<?= base_url('MrSvz1404/login.php') ?>" method="POST">
        <div class="admin-input-group">
          <label class="admin-label" for="username">Username</label>
          <input type="text" id="username" name="username" class="admin-input" placeholder="Masukkan username" required autofocus value="admin">
        </div>

        <div class="admin-input-group">
          <label class="admin-label" for="password">Password</label>
          <input type="password" id="password" name="password" class="admin-input" placeholder="Masukkan password" required value="admin123">
        </div>

        <button type="submit" class="btn-admin-action" style="width: 100%; justify-content: center; padding: 0.85rem; font-size: 1rem; margin-top: 0.5rem;">
          <i class="fas fa-sign-in-alt"></i> Masuk Sekarang
        </button>
      </form>

      <div style="margin-top: 1.75rem; padding-top: 1.25rem; border-top: 1px solid #f1f5f9; text-align: center;">
        <p style="font-size: 0.8rem; color: #64748b;">
          Akses Default: Username <code>admin</code> | Password <code>admin123</code>
        </p>
        <p style="margin-top: 0.75rem;">
          <a href="<?= base_url() ?>" style="font-size: 0.85rem; color: var(--admin-primary); font-weight: 600; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Kembali ke Website Portofolio
          </a>
        </p>
      </div>
    </div>
  </div>

</body>
</html>
