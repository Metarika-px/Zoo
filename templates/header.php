<?php
require_once __DIR__ . '/../includes/functions.php';
$user = current_user();
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<header class="navbar navbar-expand-lg navbar-dark app-header sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/index.php"><img src="<?= BASE_URL ?>/assets/images/logo2.png" alt="" class="logo"></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <nav class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/animals.php">Животные</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/enclosures.php">Вольеры</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/excursions.php">Экскурсии</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/tickets.php">Купить билет</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/about.php">О зоопарке</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/contact.php">Контакты</a></li>
            </ul>
            <ul class="navbar-nav">
                <?php if ($user): ?>
                    <?php if (has_role(['Администратор', 'Директор'])): ?><li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/index.php">Админка</a></li><?php endif; ?>
                    <?php if (has_role('Клиент')): ?><li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/client/index.php">Кабинет</a></li><?php endif; ?>
                    <?php if (has_role('Сотрудник')): ?><li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/employee/index.php">Сотрудник</a></li><?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/logout.php">Выход</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/login.php">Вход</a></li>
                    <li class="nav-item"><a class="btn btn-accent ms-lg-2" href="<?= BASE_URL ?>/register.php">Регистрация</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
<main>

