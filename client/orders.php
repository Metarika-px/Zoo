<?php
require __DIR__ . '/../includes/functions.php';
require_role('Клиент');
$user = current_user();
$stmt = db()->prepare('SELECT * FROM ticket_orders WHERE user_id=? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
require __DIR__ . '/../templates/header.php';
?>
<section class="container py-5">
    <?php show_flash(); ?>
    <h1 class="section-title">Мои билеты</h1>
    <div class="table-responsive"><table class="table table-dark table-hover">
        <tr><th>Заказ</th><th>Дата посещения</th><th>Сумма</th><th>Статус</th><th>Создан</th></tr>
        <?php foreach ($stmt as $o): ?><tr><td>#<?= $o['id'] ?></td><td><?= e($o['visit_date']) ?></td><td><?= e((string)$o['total_price']) ?> ₽</td><td><?= e($o['status']) ?></td><td><?= e($o['created_at']) ?></td></tr><?php endforeach; ?>
    </table></div>
</section>
<?php require __DIR__ . '/../templates/footer.php'; ?>

