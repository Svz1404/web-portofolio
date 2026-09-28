<?php
require_once __DIR__ . '/auth_check.php';

$db = Database::getConnection();
$adminId = $_SESSION['admin_id'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password)) {
        setFlash('danger', 'Password lama dan Password baru wajib diisi.');
    } elseif ($new_password !== $confirm_password) {
        setFlash('danger', 'Konfirmasi password baru tidak cocok.');
    } elseif (strlen($new_password) < 6) {
        setFlash('danger', 'Password baru minimal harus 6 karakter.');
    } else {
        $stmt = $db->prepare("SELECT * FROM admins WHERE id = ? LIMIT 1");
        $stmt->execute([$adminId]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($current_password, $admin['password'])) {
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
            $updateStmt = $db->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $updateStmt->execute([$hashedPassword, $adminId]);
            setFlash('success', 'Password admin berhasil diperbarui! Silakan gunakan password baru ini saat login berikutnya.');
            redirect('MrSvz1404/settings.php');
        } else {
            setFlash('danger', 'Password saat ini yang Anda masukkan salah.');
        }
    }
}

$pageTitle = 'Pengaturan Akun & Password';
require_once __DIR__ . '/header.php';
?>

<div class="admin-card" style="max-width: 600px;">
  <div class="admin-card-header">
    <div>
      <h3 class="admin-card-title"><i class="fas fa-lock" style="color: var(--admin-primary); margin-right: 6px;"></i> Ganti Password Admin</h3>
      <p style="font-size: 0.85rem; color: #64748b; margin-top: 2px;">Perbarui kata sandi untuk mengamankan akses admin panel Anda.</p>
    </div>
  </div>

  <div class="admin-modal-body">
    <form action="<?= base_url('MrSvz1404/settings.php') ?>" method="POST">
      <div class="admin-input-group">
        <label class="admin-label" for="current_password">Password Saat Ini *</label>
        <input type="password" id="current_password" name="current_password" class="admin-input" required placeholder="Masukkan password lama">
      </div>

      <div class="admin-input-group">
        <label class="admin-label" for="new_password">Password Baru *</label>
        <input type="password" id="new_password" name="new_password" class="admin-input" required minlength="6" placeholder="Minimal 6 karakter">
      </div>

      <div class="admin-input-group">
        <label class="admin-label" for="confirm_password">Konfirmasi Password Baru *</label>
        <input type="password" id="confirm_password" name="confirm_password" class="admin-input" required minlength="6" placeholder="Ketik ulang password baru">
      </div>

      <div style="display: flex; justify-content: flex-end; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--admin-border);">
        <button type="submit" class="btn-admin-action">
          <i class="fas fa-key"></i> Simpan Password Baru
        </button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
