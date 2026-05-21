<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . BASE_URL . $path);
    exit;
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $stmt = db()->prepare('SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function has_role(array|string $roles): bool
{
    $user = current_user();
    if (!$user) {
        return false;
    }
    $roles = (array)$roles;
    return in_array($user['role_name'], $roles, true);
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect('/login.php');
    }
}

function require_role(array|string $roles): void
{
    require_login();
    if (!has_role($roles)) {
        http_response_code(403);
        require __DIR__ . '/../templates/header.php';
        echo '<main class="container py-5"><div class="alert alert-danger">Нет доступа к разделу.</div></main>';
        require __DIR__ . '/../templates/footer.php';
        exit;
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function show_flash(): void
{
    foreach ($_SESSION['flash'] ?? [] as $item) {
        echo '<div class="alert alert-' . e($item['type']) . ' alert-dismissible fade show" role="alert">';
        echo e($item['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
    unset($_SESSION['flash']);
}

function upload_image(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        flash('danger', 'Ошибка загрузки изображения.');
        return null;
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        flash('danger', 'Разрешены только JPG, PNG и WEBP.');
        return null;
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0775, true);
    }
    $name = uniqid('animal_', true) . '.' . $allowed[$mime];
    move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $name);
    return $name;
}

function active_salary(int $employeeId): float
{
    $stmt = db()->prepare('SELECT COALESCE(SUM(salary_rate), 0) FROM employee_positions WHERE employee_id = ? AND is_active = 1');
    $stmt->execute([$employeeId]);
    return (float)$stmt->fetchColumn();
}

function paginate(string $sql, array $params, int $page, int $perPage = 8): array
{
    $offset = max(0, ($page - 1) * $perPage);
    $stmt = db()->prepare($sql . ' LIMIT ' . $perPage . ' OFFSET ' . $offset);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

