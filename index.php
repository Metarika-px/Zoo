<?php require __DIR__ . '/templates/header.php'; ?>
<section class="hero">
    <div class="container">
        <?php show_flash(); ?>
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <p class="text-accent fw-semibold">Информационная система зоопарка</p>
                <h1>Современный учет животных, билетов, сотрудников и экскурсий</h1>
                <p class="lead text-secondary">Учебный PHP + MySQL проект с ролями, кабинетами, CRUD, отчетами и темной адаптивной темой.</p>
                <div class="d-flex gap-2 flex-wrap">
                    <a class="btn btn-accent btn-lg" href="tickets.php">Купить билет</a>
                    <a class="btn btn-outline-light btn-lg" href="animals.php">Смотреть животных</a>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="metric-grid">
                    <?php
                    $stats = [
                        ['Животных', db()->query('SELECT COUNT(*) FROM animals WHERE is_active=1')->fetchColumn()],
                        ['Вольеров', db()->query('SELECT COUNT(*) FROM enclosures')->fetchColumn()],
                        ['Экскурсий', db()->query('SELECT COUNT(*) FROM excursions WHERE is_active=1')->fetchColumn()],
                        ['Типов билетов', db()->query('SELECT COUNT(*) FROM ticket_types')->fetchColumn()],
                    ];
                    foreach ($stats as $stat): ?>
                        <div class="metric-card"><span><?= e((string)$stat[1]) ?></span><small><?= e($stat[0]) ?></small></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="container py-5">
    <h2 class="section-title">Ближайшие экскурсии</h2>
    <div class="row g-3">
        <?php foreach (db()->query('SELECT * FROM view_excursions_full WHERE is_active=1 ORDER BY start_time LIMIT 3') as $ex): ?>
            <div class="col-md-4"><div class="app-card h-100">
                <h3><?= e($ex['title']) ?></h3>
                <p class="text-secondary"><?= e($ex['description']) ?></p>
                <div><?= date('d.m.Y H:i', strtotime($ex['start_time'])) ?> · <?= e((string)$ex['price']) ?> ₽</div>
            </div></div>
        <?php endforeach; ?>
    </div>
</section>
<?php require __DIR__ . '/templates/footer.php'; ?>

