<?php
require __DIR__ . '/../includes/functions.php';
require_role(['Администратор', 'Директор']);
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $pdo->prepare('UPDATE ticket_types SET name=?, price=?, description=? WHERE id=?')->execute([$_POST['name'], $_POST['price'], $_POST['description'], $id]);
    } else {
        $pdo->prepare('INSERT INTO ticket_types (name, price, description) VALUES (?,?,?)')->execute([$_POST['name'], $_POST['price'], $_POST['description']]);
    }
    redirect('/admin/tickets.php');
}
if (isset($_GET['delete'])) {
    $pdo->prepare('DELETE FROM ticket_types WHERE id=?')->execute([(int)$_GET['delete']]);
    redirect('/admin/tickets.php');
}
require __DIR__ . '/../templates/header.php';
?>
<div class="admin-layout"><?php require __DIR__ . '/../templates/admin_sidebar.php'; ?><section class="admin-content">
<h1 class="section-title">Билеты</h1>
<form method="post" class="app-panel mb-4"><div class="row g-2"><div class="col-md-3"><input class="form-control" name="name" placeholder="Название" required></div><div class="col-md-2"><input class="form-control" type="number" step="0.01" name="price" placeholder="Цена" required></div><div class="col-md-5"><input class="form-control" name="description" placeholder="Описание"></div><div class="col-md-2"><button class="btn btn-accent w-100">Добавить</button></div></div></form>
<div class="table-responsive"><table class="table table-dark table-hover"><tr><th>ID</th><th>Тип</th><th>Цена</th><th>Описание</th><th></th></tr><?php foreach ($pdo->query('SELECT * FROM ticket_types') as $t): ?><tr><td><?= $t['id'] ?></td><td><?= e($t['name']) ?></td><td><?= e((string)$t['price']) ?></td><td><?= e($t['description']) ?></td><td><a class="btn btn-sm btn-outline-danger js-confirm" href="?delete=<?= $t['id'] ?>">Удалить</a></td></tr><?php endforeach; ?></table></div>
<h2 class="mt-4">Заказы</h2>
<div class="table-responsive"><table class="table table-dark table-hover"><tr><th>Заказ</th><th>Клиент</th><th>Дата</th><th>Сумма</th><th>Статус</th></tr><?php foreach ($pdo->query('SELECT DISTINCT order_id, full_name, visit_date, total_price, status FROM view_tickets_full ORDER BY order_id DESC') as $o): ?><tr><td>#<?= $o['order_id'] ?></td><td><?= e($o['full_name']) ?></td><td><?= e($o['visit_date']) ?></td><td><?= e((string)$o['total_price']) ?> ₽</td><td><?= e($o['status']) ?></td></tr><?php endforeach; ?></table></div>
</section></div><?php require __DIR__ . '/../templates/footer.php'; ?>

