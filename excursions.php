<?php require __DIR__ . '/templates/header.php'; ?>
<section class="container py-5">
    <h1 class="section-title">Экскурсии</h1>
    <div class="row g-3">
        <?php foreach (db()->query('SELECT * FROM view_excursions_full WHERE is_active=1 ORDER BY start_time') as $ex): ?>
            <div class="col-md-6"><div class="app-card h-100">
                <h2><?= e($ex['title']) ?></h2>
                <p class="text-secondary"><?= e($ex['description']) ?></p>
                <div class="row g-2 small">
                    <div class="col-6">Начало: <strong><?= date('d.m.Y H:i', strtotime($ex['start_time'])) ?></strong></div>
                    <div class="col-6">Длительность: <strong><?= e((string)$ex['duration_minutes']) ?> мин.</strong></div>
                    <div class="col-6">Группа: <strong><?= e((string)$ex['max_people']) ?> чел.</strong></div>
                    <div class="col-6">Цена: <strong><?= e((string)$ex['price']) ?> ₽</strong></div>
                </div>
            </div></div>
        <?php endforeach; ?>
    </div>
</section>
<?php require __DIR__ . '/templates/footer.php'; ?>

