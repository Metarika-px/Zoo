<?php
require __DIR__ . '/../includes/functions.php';
require_role(['Администратор', 'Директор']);
require __DIR__ . '/../templates/header.php';
?>
<div class="admin-layout"><?php require __DIR__ . '/../templates/admin_sidebar.php'; ?><section class="admin-content">
<h1 class="section-title">Справочники</h1>
<div class="row g-3">
<?php foreach (['roles'=>'Роли','animal_types'=>'Типы животных','species'=>'Виды','enclosures'=>'Вольеры','positions'=>'Должности'] as $table=>$title): ?>
<div class="col-lg-6"><div class="app-card"><h2><?= $title ?></h2><div class="table-responsive"><table class="table table-dark table-sm"><?php foreach (db()->query("SELECT * FROM $table LIMIT 20") as $row): ?><tr><?php foreach ($row as $value): ?><td><?= e((string)$value) ?></td><?php endforeach; ?></tr><?php endforeach; ?></table></div></div></div>
<?php endforeach; ?>
</div>
</section></div><?php require __DIR__ . '/../templates/footer.php'; ?>

