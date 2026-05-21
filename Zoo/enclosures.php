<?php require __DIR__ . '/templates/header.php'; ?>
<section class="container py-5">
    <h1 class="section-title">Вольеры</h1>
    <div class="row g-3">
        <?php foreach (db()->query('SELECT e.*, count_animals_in_enclosure(e.id) AS animals_count FROM enclosures e ORDER BY e.name') as $e): ?>
            <div class="col-md-6 col-xl-4"><div class="app-card h-100">
                <h2><?= e($e['name']) ?></h2>
                <p class="text-secondary"><?= e($e['location']) ?></p>
                <div class="d-flex justify-content-between"><span>Площадь</span><strong><?= e((string)$e['area']) ?> м²</strong></div>
                <div class="d-flex justify-content-between"><span>Климат</span><strong><?= e($e['climate_zone']) ?></strong></div>
                <div class="d-flex justify-content-between"><span>Животных</span><strong><?= e((string)$e['animals_count']) ?>/<?= e((string)$e['capacity']) ?></strong></div>
            </div></div>
        <?php endforeach; ?>
    </div>
</section>
<?php require __DIR__ . '/templates/footer.php'; ?>

