<?php
require __DIR__ . '/../includes/mail.php';
require_role('Клиент');
$user = current_user();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    send_app_mail('admin@zoo-course.local', 'Сообщение клиента', $user['full_name'] . "\n\n" . trim($_POST['message']));
    flash('success', 'Сообщение отправлено администрации.');
    redirect('/client/messages.php');
}
require __DIR__ . '/../templates/header.php';
?>
<section class="container py-5">
    <?php show_flash(); ?>
    <h1 class="section-title">Сообщение администрации</h1>
    <form method="post" class="app-panel">
        <textarea class="form-control mb-3" name="message" rows="6" required></textarea>
        <button class="btn btn-accent">Отправить</button>
    </form>
</section>
<?php require __DIR__ . '/../templates/footer.php'; ?>

