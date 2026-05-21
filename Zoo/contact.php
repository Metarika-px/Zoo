<?php
require __DIR__ . '/includes/mail.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    send_app_mail('admin@zoo-course.local', 'Сообщение с сайта', "$name <$email>\n\n$message");
    flash('success', 'Сообщение отправлено.');
    redirect('/contact.php');
}
require __DIR__ . '/templates/header.php';
?>
<section class="container py-5">
    <?php show_flash(); ?>
    <h1 class="section-title">Контакты</h1>
    <form method="post" class="app-panel">
        <input class="form-control mb-3" name="name" placeholder="Ваше имя" required>
        <input class="form-control mb-3" type="email" name="email" placeholder="Email" required>
        <textarea class="form-control mb-3" name="message" rows="5" placeholder="Сообщение" required></textarea>
        <button class="btn btn-accent">Отправить</button>
    </form>
</section>
<?php require __DIR__ . '/templates/footer.php'; ?>

