<?php
require __DIR__ . '/../includes/functions.php';
require_role(['Администратор', 'Директор']);
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startTime = str_replace('T', ' ', $_POST['start_time']);
    $pdo->prepare('CALL create_excursion(?,?,?,?,?,?,?)')->execute([$_POST['employee_id'], $_POST['title'], $_POST['description'], $startTime, $_POST['duration_minutes'], $_POST['max_people'], $_POST['price']]);
    flash('success', 'Экскурсия создана.');
    redirect('/admin/excursions.php');
}
if (isset($_GET['delete'])) {
    $pdo->prepare('DELETE FROM excursions WHERE id=?')->execute([(int)$_GET['delete']]);
    redirect('/admin/excursions.php');
}
require __DIR__ . '/../templates/header.php';
?>
<div class="admin-layout"><?php require __DIR__ . '/../templates/admin_sidebar.php'; ?><section class="admin-content">
<?php show_flash(); ?><h1 class="section-title">Экскурсии</h1>
<form method="post" class="app-panel mb-4"><div class="row g-2">
<div class="col-md-3"><input class="form-control" name="title" placeholder="Название" required></div>
<div class="col-md-2"><select class="form-select" name="employee_id"><?php foreach ($pdo->query('SELECT e.id, u.full_name FROM employees e JOIN users u ON u.id=e.user_id') as $emp): ?><option value="<?= $emp['id'] ?>"><?= e($emp['full_name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><input class="form-control" type="datetime-local" name="start_time" required></div>
<div class="col-md-1"><input class="form-control" type="number" name="duration_minutes" placeholder="Мин" required></div>
<div class="col-md-1"><input class="form-control" type="number" name="max_people" placeholder="Мест" required></div>
<div class="col-md-1"><input class="form-control" type="number" step="0.01" name="price" placeholder="Цена" required></div>
<div class="col-md-2"><button class="btn btn-accent w-100">Добавить</button></div>
<div class="col-12"><textarea class="form-control" name="description" placeholder="Описание"></textarea></div>
</div></form>
<div class="table-responsive"><table class="table table-dark table-hover"><tr><th>ID</th><th>Название</th><th>Сотрудник</th><th>Дата</th><th>Цена</th><th></th></tr><?php foreach ($pdo->query('SELECT * FROM view_excursions_full ORDER BY start_time DESC') as $ex): ?><tr><td><?= $ex['id'] ?></td><td><?= e($ex['title']) ?></td><td><?= e($ex['employee_name']) ?></td><td><?= e($ex['start_time']) ?></td><td><?= e((string)$ex['price']) ?></td><td><a class="btn btn-sm btn-outline-danger js-confirm" href="?delete=<?= $ex['id'] ?>">Удалить</a></td></tr><?php endforeach; ?></table></div>
</section></div><?php require __DIR__ . '/../templates/footer.php'; ?>
