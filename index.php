<?php require __DIR__ . '/templates/header.php'; ?>

<section class="hero hero-zoo">
    <div class="container">
        <?php show_flash(); ?>

        <div class="align-items-center d-flex">
            <div class="">
                <p class="text-accent fw-semibold hero-label">
                    Добро пожаловать в зоопарк <span>🌿</span>
                </p>

                <h1 class="hero-title">
                    Удобный сервис для посещения современного <span>зоопарка</span>
                </h1>

                <p class="lead text-secondary hero-text">
                    Легко узнать время, цену и информацию о животных ближайших экскурсий.
                </p>

                <div class="d-flex gap-3 flex-wrap">
                    <a class="btn btn-accent btn-lg hero-btn" href="tickets.php">🎟 Купить билет</a>
                    <a class="btn btn-outline-light btn-lg hero-btn" href="animals.php">👁 Смотреть животных</a>
                </div>
            </div>

            <div class="">
                <div class="hero-visual">
                    <img class="hero-animal" src="<?= BASE_URL ?>/assets/images/red-panda.png" alt="Красная панда">

                    <div class="metric-grid hero-metrics">
                        <?php
                        $stats = [
                            ['Животных', db()->query('SELECT COUNT(*) FROM animals WHERE is_active=1')->fetchColumn(), '🐾', 'Уникальные виды со всего мира'],
                            ['Вольеров', db()->query('SELECT COUNT(*) FROM enclosures')->fetchColumn(), '🏠', 'Комфортные условия для питомцев'],
                            ['Экскурсий', db()->query('SELECT COUNT(*) FROM excursions WHERE is_active=1')->fetchColumn(), '🧍', 'Увлекательные маршруты для всей семьи'],
                            ['Типов билетов', db()->query('SELECT COUNT(*) FROM ticket_types')->fetchColumn(), '🎫', 'Выберите подходящий вариант'],
                        ];

                        foreach ($stats as $stat): ?>
                            <div class="metric-card">
                                <div class="metric-icon"><?= $stat[2] ?></div>
                                <span><?= e((string)$stat[1]) ?></span>
                                <small><?= e($stat[0]) ?></small>
                                <p><?= e($stat[3]) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="hero-benefits">
            <div>🛡 <strong>Безопасно</strong><span>Надежная онлайн-оплата</span></div>
            <div>🕒 <strong>Удобно</strong><span>Покупка билетов в пару кликов</span></div>
            <div>📅 <strong>Актуально</strong><span>Расписание экскурсий и мероприятий</span></div>
            <div>💚 <strong>С заботой</strong><span>Мы заботимся о животных и природе</span></div>
        </div>
    </div>
</section>

<section class="container py-5">
    <h2 class="section-title">Ближайшие экскурсии</h2>
    <div class="row g-3">
        <?php foreach (db()->query('SELECT * FROM view_excursions_full WHERE is_active=1 ORDER BY start_time LIMIT 3') as $ex): ?>
            <div class="col-md-4">
                <div class="app-card h-100">
                    <h3><?= e($ex['title']) ?></h3>
                    <p class="text-secondary"><?= e($ex['description']) ?></p>
                    <div><?= date('d.m.Y H:i', strtotime($ex['start_time'])) ?> · <?= e((string)$ex['price']) ?> ₽</div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php require __DIR__ . '/templates/footer.php'; ?>