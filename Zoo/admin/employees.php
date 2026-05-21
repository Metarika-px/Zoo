<?php
require __DIR__ . '/../includes/functions.php';
require_role(['Администратор', 'Директор']);
require __DIR__ . '/../templates/header.php';
?>
<div class="admin-layout"><?php require __DIR__ . '/../templates/admin_sidebar.php'; ?><section class="admin-content">
<h1 class="section-title">Сотрудники</h1>
<div class="table-responsive"><table class="table table-dark table-hover">
<tr><th>ID</th><th>ФИО</th><th>Должности</th><th>Зарплата</th><th>Медкнижка</th><th>Активен</th></tr>
<?php foreach (db()->query('SELECT * FROM view_employees_full ORDER BY id DESC') as $emp): ?>
<tr><td><?= $emp['id'] ?></td><td><?= e($emp['full_name']) ?></td><td><?= e($emp['positions']) ?></td><td><?= e((string)active_salary((int)$emp['id'])) ?> ₽</td><td><?= e($emp['medical_book_expire_date']) ?></td><td><?= $emp['is_active'] ? 'Да' : 'Нет' ?></td></tr>
<?php endforeach; ?>
</table></div>
<p class="text-secondary">Для курсовой здесь показан управленческий список. Пользователи и назначения редактируются через разделы пользователей, справочников и животных.</p>
</section></div><?php require __DIR__ . '/../templates/footer.php'; ?>

