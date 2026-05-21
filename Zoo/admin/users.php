<?php
require __DIR__ . '/../includes/functions.php';
require_role(['Администратор', 'Директор']);
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $stmt = $pdo->prepare('UPDATE users SET role_id=?, login=?, password=?, full_name=?, email=?, phone=? WHERE id=?');
        $stmt->execute([$_POST['role_id'], $_POST['login'], $_POST['password'], $_POST['full_name'], $_POST['email'], $_POST['phone'], $id]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO users (role_id, login, password, full_name, email, phone) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$_POST['role_id'], $_POST['login'], $_POST['password'], $_POST['full_name'], $_POST['email'], $_POST['phone']]);
    }
    flash('success', 'Пользователь сохранен.');
    redirect('/admin/users.php');
}
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    if ($deleteId === (int)($_SESSION['user_id'] ?? 0)) {
        flash('danger', 'Нельзя удалить текущую учетную запись.');
        redirect('/admin/users.php');
    }
    $stmt = $pdo->prepare('CALL delete_user_with_relations(?)');
    $stmt->execute([$deleteId]);
    $stmt->closeCursor();
    flash('success', 'Пользователь и связанные данные удалены вручную через процедуру.');
    redirect('/admin/users.php');
}
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id=?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}
require __DIR__ . '/../templates/header.php';
?>
<div class="admin-layout"><?php require __DIR__ . '/../templates/admin_sidebar.php'; ?><section class="admin-content">
<?php show_flash(); ?><h1 class="section-title">Пользователи</h1>
<form method="post" class="app-panel mb-4"><input type="hidden" name="id" value="<?= e((string)($edit['id'] ?? 0)) ?>"><div class="row g-2">
<div class="col-md-2"><select name="role_id" class="form-select"><?php foreach ($pdo->query('SELECT * FROM roles') as $r): ?><option value="<?= $r['id'] ?>" <?= (($edit['role_id'] ?? 0)==$r['id'])?'selected':'' ?>><?= e($r['name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><input class="form-control" name="login" placeholder="Логин" value="<?= e($edit['login'] ?? '') ?>" required></div>
<div class="col-md-2"><input class="form-control" name="password" placeholder="Пароль" value="<?= e($edit['password'] ?? '') ?>" required></div>
<div class="col-md-3"><input class="form-control" name="full_name" placeholder="ФИО" value="<?= e($edit['full_name'] ?? '') ?>" required></div>
<div class="col-md-2"><input class="form-control" name="email" placeholder="Email" value="<?= e($edit['email'] ?? '') ?>" required></div>
<div class="col-md-1"><input class="form-control" name="phone" placeholder="Телефон" value="<?= e($edit['phone'] ?? '') ?>"></div>
</div><button class="btn btn-accent mt-3">Сохранить</button></form>
<div class="table-responsive"><table class="table table-dark table-hover"><tr><th>ID</th><th>Роль</th><th>Логин</th><th>ФИО</th><th>Email</th><th></th></tr>
<?php foreach ($pdo->query('SELECT * FROM view_users_full ORDER BY id DESC') as $u): ?><tr><td><?= $u['id'] ?></td><td><?= e($u['role_name']) ?></td><td><?= e($u['login']) ?></td><td><?= e($u['full_name']) ?></td><td><?= e($u['email']) ?></td><td><a class="btn btn-sm btn-outline-light" href="?edit=<?= $u['id'] ?>">Редактировать</a> <a class="btn btn-sm btn-outline-danger js-confirm" href="?delete=<?= $u['id'] ?>">Удалить</a></td></tr><?php endforeach; ?>
</table></div></section></div><?php require __DIR__ . '/../templates/footer.php'; ?>

