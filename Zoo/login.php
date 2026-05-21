<?php
require __DIR__ . '/includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = db()->prepare('SELECT * FROM users WHERE login = ? AND password = ?');
    $stmt->execute([trim($_POST['login'] ?? ''), trim($_POST['password'] ?? '')]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        setcookie('last_login', $user['login'], time() + 86400 * 30, '/');
        redirect('/index.php');
    }
    flash('danger', 'Неверный логин или пароль.');
}
require __DIR__ . '/templates/header.php';
?>
<section class="container auth-page">
    <?php show_flash(); ?>
    <form method="post" class="auth-card">
        <h1>Вход</h1>
        <input class="form-control mb-3" name="login" placeholder="Логин" value="<?= e($_COOKIE['last_login'] ?? '') ?>" required>
        <input class="form-control mb-3" type="password" name="password" placeholder="Пароль" required>
        <button class="btn btn-accent w-100">Войти</button>
    </form>
</section>
<?php require __DIR__ . '/templates/footer.php'; ?>

