<?php
require __DIR__ . '/../includes/functions.php';
require_role('Сотрудник');
$user = current_user();
$stmt = db()->prepare('SELECT id FROM employees WHERE user_id=?');
$stmt->execute([$user['id']]);
$employeeId = (int)$stmt->fetchColumn();
$stmt = db()->prepare('SELECT * FROM view_excursions_full WHERE employee_id=? ORDER BY start_time');
$stmt->execute([$employeeId]);
require __DIR__ . '/../templates/header.php';
?>
<section class="container py-5">
    <h1 class="section-title">Мои экскурсии</h1>
    <div class="row g-3">
        <?php foreach ($stmt as $ex): ?><div class="col-md-6"><div class="app-card"><h2><?= e($ex['title']) ?></h2><p class="text-secondary"><?= e($ex['description']) ?></p><div><?= e($ex['start_time']) ?> · <?= e((string)$ex['duration_minutes']) ?> мин.</div></div></div><?php endforeach; ?>
    </div>
</section>
<?php require __DIR__ . '/../templates/footer.php'; ?>

