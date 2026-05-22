<?php
require __DIR__ . '/../includes/functions.php';
require_role(['Администратор', 'Директор']);

$pdo = db();
$currentUser = current_user();
$isDirector = ($currentUser['role_name'] ?? '') === 'Директор';

function role_id_by_name(PDO $pdo, string $name): ?int
{
    $stmt = $pdo->prepare('SELECT id FROM roles WHERE name = ? LIMIT 1');
    $stmt->execute([$name]);
    $id = $stmt->fetchColumn();
    return $id === false ? null : (int)$id;
}

function role_name_by_id(PDO $pdo, int $id): ?string
{
    $stmt = $pdo->prepare('SELECT name FROM roles WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $name = $stmt->fetchColumn();
    return $name === false ? null : (string)$name;
}

function director_count(PDO $pdo, ?int $excludeUserId = null): int
{
    $directorRoleId = role_id_by_name($pdo, 'Директор');
    if (!$directorRoleId) {
        return 0;
    }

    if ($excludeUserId) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE role_id = ? AND id <> ?');
        $stmt->execute([$directorRoleId, $excludeUserId]);
    } else {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE role_id = ?');
        $stmt->execute([$directorRoleId]);
    }

    return (int)$stmt->fetchColumn();
}

function validate_user_role(PDO $pdo, int $roleId, int $userId, bool $isDirector): void
{
    $roleName = role_name_by_id($pdo, $roleId);
    if (!$roleName) {
        throw new RuntimeException('Выбрана несуществующая роль.');
    }

    if ($roleName === 'Сотрудник') {
        throw new RuntimeException('Сотрудников нужно создавать через раздел "Сотрудники".');
    }

    if ($roleName === 'Гость') {
        throw new RuntimeException('Гость не является учетной записью для входа.');
    }

    if ($roleName === 'Директор') {
        if (!$isDirector) {
            throw new RuntimeException('Администратор не может создавать или назначать директора.');
        }

        if (director_count($pdo, $userId > 0 ? $userId : null) > 0) {
            throw new RuntimeException('В системе может быть только один аккаунт директора.');
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = (int)($_POST['id'] ?? 0);
        $roleId = (int)($_POST['role_id'] ?? 0);
        validate_user_role($pdo, $roleId, $id, $isDirector);

        if ($id) {
            $stmt = $pdo->prepare('UPDATE users SET role_id=?, login=?, password=?, full_name=?, email=?, phone=? WHERE id=?');
            $stmt->execute([$roleId, $_POST['login'], $_POST['password'], $_POST['full_name'], $_POST['email'], $_POST['phone'], $id]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO users (role_id, login, password, full_name, email, phone) VALUES (?,?,?,?,?,?)');
            $stmt->execute([$roleId, $_POST['login'], $_POST['password'], $_POST['full_name'], $_POST['email'], $_POST['phone']]);
        }

        flash('success', 'Пользователь сохранен.');
        redirect('/admin/users.php');
    } catch (Throwable $e) {
        flash('danger', $e->getMessage());
    }
}

if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    if ($deleteId === (int)($_SESSION['user_id'] ?? 0)) {
        flash('danger', 'Нельзя удалить текущую учетную запись.');
        redirect('/admin/users.php');
    }

    $stmt = $pdo->prepare('SELECT r.name FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?');
    $stmt->execute([$deleteId]);
    $deleteRole = $stmt->fetchColumn();
    if ($deleteRole === 'Директор' && director_count($pdo, $deleteId) === 0) {
        flash('danger', 'Нельзя удалить единственный аккаунт директора.');
        redirect('/admin/users.php');
    }

    $stmt = $pdo->prepare('CALL delete_user_with_relations(?)');
    $stmt->execute([$deleteId]);
    $stmt->closeCursor();
    flash('success', 'Пользователь и связанные данные удалены вручную через процедуру.');
    redirect('/admin/users.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id=?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

$rolesStmt = $pdo->prepare("SELECT * FROM roles WHERE name NOT IN ('Гость', 'Сотрудник') ORDER BY id");
$rolesStmt->execute();
$roles = $rolesStmt->fetchAll();
if (!$isDirector) {
    $roles = array_values(array_filter($roles, static fn(array $role): bool => $role['name'] !== 'Директор'));
} elseif (!$edit || role_name_by_id($pdo, (int)$edit['role_id']) !== 'Директор') {
    $roles = array_values(array_filter($roles, static fn(array $role): bool => $role['name'] !== 'Директор' || director_count($pdo) === 0));
}

$usersStmt = $pdo->prepare('SELECT * FROM view_users_full ORDER BY id DESC');
$usersStmt->execute();
$users = $usersStmt->fetchAll();

require __DIR__ . '/../templates/header.php';
?>
<div class="admin-layout">
    <?php require __DIR__ . '/../templates/admin_sidebar.php'; ?>
    <section class="admin-content">
        <?php show_flash(); ?>
        <h1 class="section-title">Пользователи</h1>

        <form method="post" class="app-panel mb-4">
            <input type="hidden" name="id" value="<?= e((string)($edit['id'] ?? 0)) ?>">
            <div class="row g-2">
                <div class="col-md-2">
                    <select name="role_id" class="form-select" required>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= (($edit['role_id'] ?? 0) == $r['id']) ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><input class="form-control" name="login" placeholder="Логин" value="<?= e($edit['login'] ?? '') ?>" required></div>
                <div class="col-md-2"><input class="form-control" name="password" placeholder="Пароль" value="<?= e($edit['password'] ?? '') ?>" required></div>
                <div class="col-md-3"><input class="form-control" name="full_name" placeholder="ФИО" value="<?= e($edit['full_name'] ?? '') ?>" required></div>
                <div class="col-md-2"><input class="form-control" type="email" name="email" placeholder="Email" value="<?= e($edit['email'] ?? '') ?>" required></div>
                <div class="col-md-1"><input class="form-control" name="phone" placeholder="Телефон" value="<?= e($edit['phone'] ?? '') ?>"></div>
            </div>
            <button class="btn btn-accent mt-3">Сохранить</button>
        </form>

        <div class="alert alert-info">Сотрудники создаются через отдельный раздел "Сотрудники". Аккаунт директора в системе может быть только один.</div>

        <div class="table-responsive"><table class="table table-dark table-hover">
            <tr><th>ID</th><th>Роль</th><th>Логин</th><th>ФИО</th><th>Email</th><th></th></tr>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= e($u['role_name']) ?></td>
                    <td><?= e($u['login']) ?></td>
                    <td><?= e($u['full_name']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td>
                        <a class="btn btn-sm btn-outline-light" href="?edit=<?= $u['id'] ?>">Редактировать</a>
                        <a class="btn btn-sm btn-outline-danger js-confirm" href="?delete=<?= $u['id'] ?>">Удалить</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table></div>
    </section>
</div>
<?php require __DIR__ . '/../templates/footer.php'; ?>