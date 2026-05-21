<?php
require __DIR__ . '/../includes/functions.php';
require_role('Клиент');
$user = current_user();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    db()->prepare('UPDATE users SET full_name=?, email=?, phone=? WHERE id=?')->execute([$_POST['full_name'], $_POST['email'], $_POST['phone'], $user['id']]);
    flash('success', 'Профиль обновлен.');
    redirect('/client/profile.php');
}
require __DIR__ . '/../templates/header.php';
?>
<section class="container py-5">
    
    <?php require __DIR__ . '/../templates/client_nav.php'; ?>

    <?php require __DIR__ . '/../templates/client_back.php'; ?>
<?php show_flash(); ?>
    <h1 class="section-title">Мои данные</h1>
    <form method="post" class="app-panel">
        <input class="form-control mb-3" name="full_name" value="<?= e($user['full_name']) ?>" required>
        <input class="form-control mb-3" type="email" name="email" value="<?= e($user['email']) ?>" required>
        <input class="form-control mb-3" name="phone" value="<?= e($user['phone']) ?>">
        <button class="btn btn-accent">Сохранить</button>
    </form>
</section>
<?php require __DIR__ . '/../templates/footer.php'; ?>

