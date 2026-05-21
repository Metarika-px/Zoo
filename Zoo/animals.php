<?php
require __DIR__ . '/templates/header.php';
$q = trim($_GET['q'] ?? '');
$type = (int)($_GET['type'] ?? 0);
$params = [];
$sql = 'SELECT * FROM view_animals_full WHERE is_active=1';
if ($q !== '') {
    $sql .= ' AND (name LIKE ? OR species_name LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($type > 0) {
    $sql .= ' AND animal_type_name = (SELECT name FROM animal_types WHERE id = ?)';
    $params[] = $type;
}
$sql .= ' ORDER BY name';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$animals = $stmt->fetchAll();
?>
<section class="container py-5">
    <h1 class="section-title">Животные</h1>
    <form class="row g-2 mb-4">
        <div class="col-md-6"><input class="form-control" name="q" placeholder="Поиск по имени или виду" value="<?= e($q) ?>"></div>
        <div class="col-md-4"><select class="form-select" name="type"><option value="0">Все типы</option><?php foreach (db()->query('SELECT * FROM animal_types') as $t): ?><option value="<?= $t['id'] ?>" <?= $type===$t['id']?'selected':'' ?>><?= e($t['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><button class="btn btn-accent w-100">Найти</button></div>
    </form>
    <div class="row g-3">
        <?php foreach ($animals as $a): ?>
            <div class="col-md-6 col-xl-4"><article class="animal-card">
                <div class="animal-photo" style="background-image:url('<?= $a['photo'] ? UPLOAD_WEB . e($a['photo']) : 'assets/images/animal-placeholder.svg' ?>')"></div>
                <div class="p-3">
                    <h2><?= e($a['name']) ?></h2>
                    <p class="text-accent mb-1"><?= e($a['species_name']) ?> · <?= e($a['animal_type_name']) ?></p>
                    <p class="text-secondary"><?= e($a['description']) ?></p>
                    <span class="badge text-bg-success"><?= e($a['enclosure_name']) ?></span>
                </div>
            </article></div>
        <?php endforeach; ?>
    </div>
</section>
<?php require __DIR__ . '/templates/footer.php'; ?>

