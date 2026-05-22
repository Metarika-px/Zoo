<?php require __DIR__ . '/templates/header.php'; ?>

<div class="site-container">
    <section class="hero hero-zoo section-padding">
    <div class="container">
        <?php show_flash(); ?>
                <div class="hero-badge">
                    🌿 Добро пожаловать в современный зоопарк
                </div>
        <div class="hero-wrapper">
               
            <div class="hero-left">

               

                <h1 class="hero-title">
                    Погрузитесь в удивительный мир

                    <span>животных и природы</span>
                </h1>

                <p class="hero-description">
                    Покупайте билеты онлайн, узнавайте о животных,
                    посещайте экскурсии и открывайте природу по-новому.
                </p>

                <div class="hero-actions">
                    <a class="btn btn-accent btn-lg hero-btn" href="tickets.php">
                        Купить билет
                    </a>

                    <a class="btn btn-outline-light btn-lg hero-btn" href="animals.php">
                        Смотреть животных
                    </a>
                </div>

              

            </div>

            <div class="hero-right">

                <div class="hero-image-wrapper">
  <img
                        class="hero-animal"
                        src="<?= BASE_URL ?>/assets/images/red-panda.png"
                        alt="Красная панда"
                    >
                    <div class="hero-glow"></div>

                  

                    <div class="metric-grid hero-metrics">

                        <?php
                        $stats = [
                            [
                                'Животных',
                                db()->query('SELECT COUNT(*) FROM animals WHERE is_active=1')->fetchColumn(),
                                '<img src="assets/images/lapa.png" alt="paw" width="60" height="60">'
                            ],

                            [
                                'Вольеров',
                                db()->query('SELECT COUNT(*) FROM enclosures')->fetchColumn(),
                                '<img src="assets/images/home.png" alt="home" width="60" height="60">'
                            ],

                            [
                                'Экскурсий',
                                db()->query('SELECT COUNT(*) FROM excursions WHERE is_active=1')->fetchColumn(),
                                '<img src="assets/images/men.png" alt="excursion" width="60" height="60">'
                            ],

                            [
                                'Билетов',
                                db()->query('SELECT COUNT(*) FROM ticket_types')->fetchColumn(),
                                '<img src="assets/images/ticket.png" alt="ticket" width="60" height="60">'
                            ],
                        ];

                        foreach ($stats as $stat): ?>

                            <div class="metric-card">
                                <div class="metric-icon"><?= $stat[2] ?></div>

                                <span><?= e((string)$stat[1]) ?></span>

                                <small><?= e($stat[0]) ?></small>
                            </div>

                        <?php endforeach; ?>

                    </div>

                </div>

            </div>

        </div>
          <div class="hero-benefits">
                    <div>
                        <img src="<?= BASE_URL ?>/assets/images/shield-icon.png" alt="">
                        <strong> Безопасно</strong>
                        <span>Надежная онлайн-оплата</span>
                    </div>

                    <div>
                          <img src="<?= BASE_URL ?>/assets/images/time-icon.png" alt="">
                        <strong>🕒 Удобно</strong>
                        <span>Покупка билетов за минуту</span>
                    </div>

                    <div>
                          <img src="<?= BASE_URL ?>/assets/images/live-icon.png" alt="">
                        <strong>💚 С заботой</strong>
                        <span>Комфорт животных — наш приоритет</span>
                    </div>
                </div>
             </div>
</section>

<section class="container py-5 section-padding">
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
</div>


<?php require __DIR__ . '/templates/footer.php'; ?>