<?php
require __DIR__ . '/../includes/functions.php';
require_role(['Администратор', 'Директор']);
require __DIR__ . '/../templates/header.php';
$animalsByType = db()->query('SELECT * FROM view_stats_animals_by_type')->fetchAll();
$ticketSales = db()->query('SELECT * FROM view_stats_ticket_sales')->fetchAll();
$excursions = db()->query('SELECT * FROM view_stats_excursion_popularity LIMIT 5')->fetchAll();
$dashboardCounts = array_column(db()->query('SELECT metric, total FROM view_dashboard_counts')->fetchAll(), 'total', 'metric');
?>
<div class="admin-layout">
    <?php require __DIR__ . '/../templates/admin_sidebar.php'; ?>
    <section class="admin-content">
        <?php show_flash(); ?>
        <h1 class="section-title">Панель управления</h1>
        <div class="row g-3 mb-4">
            <div class="col-md-3"><div class="metric-card"><span><?= e((string)$dashboardCounts['users']) ?></span><small>Пользователей</small></div></div>
            <div class="col-md-3"><div class="metric-card"><span><?= e((string)$dashboardCounts['animals']) ?></span><small>Животных</small></div></div>
            <div class="col-md-3"><div class="metric-card"><span><?= e((string)$dashboardCounts['employees']) ?></span><small>Сотрудников</small></div></div>
            <div class="col-md-3"><div class="metric-card"><span><?= e((string)$dashboardCounts['ticket_sales']) ?></span><small>Продажи, ₽</small></div></div>
        </div>
        <div class="row g-3">
            <div class="col-lg-4"><div class="app-card"><h2>Животные по типам</h2><canvas data-chart='<?= e(json_encode($animalsByType, JSON_UNESCAPED_UNICODE)) ?>' data-type="doughnut"></canvas></div></div>
            <div class="col-lg-4"><div class="app-card"><h2>Продажи билетов</h2><canvas data-chart='<?= e(json_encode($ticketSales, JSON_UNESCAPED_UNICODE)) ?>' data-type="bar"></canvas></div></div>
            <div class="col-lg-4"><div class="app-card"><h2>Популярность экскурсий</h2><canvas data-chart='<?= e(json_encode($excursions, JSON_UNESCAPED_UNICODE)) ?>' data-type="polarArea"></canvas></div></div>
        </div>
    </section>
</div>
<?php require __DIR__ . '/../templates/footer.php'; ?>

