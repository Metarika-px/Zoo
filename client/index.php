<?php
require __DIR__ . '/../includes/functions.php';
require_role('Клиент');
require __DIR__ . '/../templates/header.php';
$user = current_user();
?>
<section class="container py-5">
    
    <?php require __DIR__ . '/../templates/client_nav.php'; ?>
<h1 class="section-title">Личный кабинет</h1>
    <div class="row g-3">
        <div class="col-md-4"><div class="metric-card"><span><?= e($user['full_name']) ?></span><small>Клиент</small></div></div>
        <div class="col-md-4"><a class="app-card d-block" href="orders.php"><h2>Мои билеты</h2><p class="text-secondary mb-0">История заказов и посещений</p></a></div>
        <div class="col-md-4"><a class="app-card d-block" href="profile.php"><h2>Мои данные</h2><p class="text-secondary mb-0">Профиль и контакты</p></a></div>
        <div class="col-md-4"><a class="app-card d-block" href="messages.php"><h2>Сообщение</h2><p class="text-secondary mb-0">Связь с администрацией</p></a></div>
    </div>
</section>
<?php require __DIR__ . '/../templates/footer.php'; ?>
