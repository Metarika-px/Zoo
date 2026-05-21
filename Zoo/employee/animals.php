<?php
require __DIR__ . '/../includes/functions.php';
require_role('Сотрудник');
$user = current_user();
$stmt = db()->prepare('SELECT id FROM employees WHERE user_id=?');
$stmt->execute([$user['id']]);
$employeeId = (int)$stmt->fetchColumn();
$stmt = db()->prepare('SELECT animal_name, responsibility, assigned_at FROM view_animal_responsibilities WHERE employee_id = ? ORDER BY animal_name');
$stmt->execute([$employeeId]);
require __DIR__ . '/../templates/header.php';
?>
<section class="container py-5">
    <h1 class="section-title">Мои животные</h1>
    <div class="table-responsive"><table class="table table-dark table-hover"><tr><th>Животное</th><th>Обязанность</th><th>Назначено</th></tr>
        <?php foreach ($stmt as $row): ?><tr><td><?= e($row['animal_name']) ?></td><td><?= e($row['responsibility']) ?></td><td><?= e($row['assigned_at']) ?></td></tr><?php endforeach; ?>
    </table></div>
</section>
<?php require __DIR__ . '/../templates/footer.php'; ?>
