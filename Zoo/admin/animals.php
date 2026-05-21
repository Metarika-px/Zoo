<?php
require __DIR__ . '/../includes/functions.php';
require_role(['Администратор', 'Директор']);
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['bulk_delete'])) {
        $ids = array_map('intval', $_POST['ids'] ?? []);
        $stmt = $pdo->prepare('CALL delete_animal_with_relations(?)');
        foreach ($ids as $id) {
            $stmt->execute([$id]);
            $stmt->closeCursor();
        }
        flash('success', 'Выбранные животные удалены.');
        redirect('/admin/animals.php');
    }
    $photo = upload_image($_FILES['photo'] ?? []);
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare('CALL update_animal(?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([$id, $_POST['species_id'], $_POST['enclosure_id'], $_POST['name'], $_POST['gender'], $_POST['birth_date'] ?: null, $_POST['arrival_date'], $_POST['description'], $photo, isset($_POST['is_active']) ? 1 : 0]);
        $stmt->closeCursor();
    } else {
        $stmt = $pdo->prepare('CALL add_animal(?,?,?,?,?,?,?,?)');
        $stmt->execute([$_POST['species_id'], $_POST['enclosure_id'], $_POST['name'], $_POST['gender'], $_POST['birth_date'] ?: null, $_POST['arrival_date'], $_POST['description'], $photo]);
        $stmt->closeCursor();
    }
    flash('success', 'Данные животного сохранены.');
    redirect('/admin/animals.php');
}
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('CALL delete_animal_with_relations(?)');
    $stmt->execute([(int)$_GET['delete']]);
    $stmt->closeCursor();
    flash('success', 'Животное удалено.');
    redirect('/admin/animals.php');
}
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM animals WHERE id=?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}
require __DIR__ . '/../templates/header.php';
?>
<div class="admin-layout">
<?php require __DIR__ . '/../templates/admin_sidebar.php'; ?>
<section class="admin-content">
<?php show_flash(); ?><h1 class="section-title">Животные</h1>
<form method="post" enctype="multipart/form-data" class="app-panel mb-4">
    <input type="hidden" name="id" value="<?= e((string)($edit['id'] ?? 0)) ?>">
    <div class="row g-3">
        <div class="col-md-4"><input class="form-control" name="name" placeholder="Имя" value="<?= e($edit['name'] ?? '') ?>" required></div>
        <div class="col-md-4"><select class="form-select" name="species_id" required><?php foreach ($pdo->query('SELECT * FROM species') as $s): ?><option value="<?= $s['id'] ?>" <?= (($edit['species_id'] ?? 0)==$s['id'])?'selected':'' ?>><?= e($s['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-4"><select class="form-select" name="enclosure_id" required><?php foreach ($pdo->query('SELECT * FROM enclosures') as $en): ?><option value="<?= $en['id'] ?>" <?= (($edit['enclosure_id'] ?? 0)==$en['id'])?'selected':'' ?>><?= e($en['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3">
            <label class="form-label d-block">Пол</label>
            <div class="d-flex gap-3 flex-wrap">
                <label class="form-check-label"><input class="form-check-input me-1" type="radio" name="gender" value="male" <?= (($edit["gender"] ?? "unknown") === "male") ? "checked" : "" ?>> Самец</label>
                <label class="form-check-label"><input class="form-check-input me-1" type="radio" name="gender" value="female" <?= (($edit["gender"] ?? "unknown") === "female") ? "checked" : "" ?>> Самка</label>
                <label class="form-check-label"><input class="form-check-input me-1" type="radio" name="gender" value="unknown" <?= (($edit["gender"] ?? "unknown") === "unknown") ? "checked" : "" ?>> Неизвестно</label>
            </div>
        </div>
        <div class="col-md-3"><input class="form-control" type="date" name="birth_date" value="<?= e($edit['birth_date'] ?? '') ?>"></div>
        <div class="col-md-3"><input class="form-control" type="date" name="arrival_date" value="<?= e($edit['arrival_date'] ?? date('Y-m-d')) ?>" required></div>
        <div class="col-md-3"><input class="form-control" type="file" name="photo" accept="image/*"></div>
        <div class="col-12"><textarea class="form-control" name="description" placeholder="Описание"><?= e($edit['description'] ?? '') ?></textarea></div>
        <div class="col-md-3 form-check ms-2"><input class="form-check-input" type="checkbox" name="is_active" checked> <label class="form-check-label">Активно</label></div>
    </div>
    <button class="btn btn-accent mt-3">Сохранить</button>
</form>
<form method="post">
<button class="btn btn-outline-danger mb-2 js-confirm" name="bulk_delete" value="1">Удалить выбранные</button>
<div class="table-responsive"><table class="table table-dark table-hover align-middle">
<tr><th></th><th>ID</th><th>Имя</th><th>Вид</th><th>Вольер</th><th></th></tr>
<?php foreach ($pdo->query('SELECT * FROM view_animals_full ORDER BY id DESC') as $a): ?>
<tr><td><input type="checkbox" name="ids[]" value="<?= $a['id'] ?>"></td><td><?= $a['id'] ?></td><td><?= e($a['name']) ?></td><td><?= e($a['species_name']) ?></td><td><?= e($a['enclosure_name']) ?></td><td><a class="btn btn-sm btn-outline-light" href="?edit=<?= $a['id'] ?>">Редактировать</a> <a class="btn btn-sm btn-outline-danger js-confirm" href="?delete=<?= $a['id'] ?>">Удалить</a></td></tr>
<?php endforeach; ?>
</table></div></form>
</section></div>
<?php require __DIR__ . '/../templates/footer.php'; ?>

