<?php
require __DIR__ . '/includes/mail.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roleId = db()->query("SELECT id FROM roles WHERE name='Клиент'")->fetchColumn();
    $stmt = db()->prepare('INSERT INTO users (role_id, login, password, full_name, email, phone) VALUES (?, ?, ?, ?, ?, ?)');
    try {
        $stmt->execute([$roleId, trim($_POST['login']), trim($_POST['password']), trim($_POST['full_name']), trim($_POST['email']), trim($_POST['phone'])]);
        send_app_mail(trim($_POST['email']), 'Регистрация в зоопарке', 'Вы успешно зарегистрировались в системе зоопарка.');
        flash('success', 'Регистрация выполнена. Теперь можно войти.');
        redirect('/login.php');
    } catch (Throwable $e) {
        flash('danger', 'Не удалось зарегистрироваться. Возможно, логин уже занят.');
    }
}
require __DIR__ . '/templates/header.php';
?>
<section class="container auth-page">
    <?php show_flash(); ?>
    <form method="post" class="auth-card">
        <h1>Регистрация</h1>
        <input class="form-control mb-3" name="full_name" placeholder="ФИО" required>
        <input class="form-control mb-3" name="login" placeholder="Логин" required>
        <input class="form-control mb-3" type="password" name="password" placeholder="Пароль" required>
        <input class="form-control mb-3" type="email" name="email" placeholder="Email" required>
        <input class="form-control mb-3" name="phone" placeholder="Телефон">
        <button class="btn btn-accent w-100">Создать аккаунт</button>
    </form>
</section>
<?php require __DIR__ . '/templates/footer.php'; ?>

