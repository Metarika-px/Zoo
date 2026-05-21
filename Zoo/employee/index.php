<?php
require __DIR__ . '/../includes/functions.php';
require_role('Сотрудник');
$user = current_user();
$stmt = db()->prepare('SELECT e.* FROM employees e WHERE e.user_id=?');
$stmt->execute([$user['id']]);
$employee = $stmt->fetch();
require __DIR__ . '/../templates/header.php';
?>
<section class="container py-5">
    <h1 class="section-title">Панель сотрудника</h1>
    <div class="row g-3">
        <div class="col-md-4"><div class="metric-card"><span><?= e((string)active_salary((int)$employee['id'])) ?> ₽</span><small>Активная ставка</small></div></div>
        <div class="col-md-4"><a class="app-card d-block" href="animals.php"><h2>Мои животные</h2><p class="text-secondary mb-0">Закрепления и обязанности</p></a></div>
        <div class="col-md-4"><a class="app-card d-block" href="excursions.php"><h2>Мои экскурсии</h2><p class="text-secondary mb-0">Расписание проводимых экскурсий</p></a></div>
    </div>
</section>
<?php require __DIR__ . '/../templates/footer.php'; ?>

