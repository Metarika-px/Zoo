<?php require_once __DIR__ . '/../includes/functions.php'; ?>
</main>

<footer class="app-footer mt-5">
    <div class="container py-4">
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
            
            <div class="d-flex align-items-center gap-3">
                <img src="<?= BASE_URL ?>/assets/images/logo2.png" alt="Логотип зоопарка" class="logo">

                <div>
                    <div class="fw-semibold">Зоопарк</div>
                    <small class="text-secondary">
                        Современный сервис для посещения зоопарка
                    </small>
                </div>
            </div>

            <div class="text-secondary small text-center text-md-end">
                © <?= date('Y') ?> Все права защищены
            </div>

        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>