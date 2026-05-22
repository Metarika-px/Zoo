<?php
require __DIR__ . '/../includes/functions.php';
require_role(['Администратор', 'Директор']);

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->beginTransaction();
    try {
        $roleStmt = $pdo->prepare('SELECT id FROM roles WHERE name = ? LIMIT 1');
        $roleStmt->execute(['Сотрудник']);
        $employeeRoleId = (int)($roleStmt->fetchColumn() ?: 3);

        $userStmt = $pdo->prepare('INSERT INTO users (role_id, login, password, full_name, email, phone) VALUES (?, ?, ?, ?, ?, ?)');
        $userStmt->execute([
            $employeeRoleId,
            trim($_POST['login'] ?? ''),
            trim($_POST['password'] ?? ''),
            trim($_POST['full_name'] ?? ''),
            trim($_POST['email'] ?? ''),
            trim($_POST['phone'] ?? ''),
        ]);
        $userId = (int)$pdo->lastInsertId();

        $employeeStmt = $pdo->prepare('INSERT INTO employees (user_id, birth_date, gender, passport_series, passport_number, address, education, work_experience, medical_book_number, medical_book_expire_date, hire_date, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)');
        $employeeStmt->execute([
            $userId,
            $_POST['birth_date'] ?: null,
            $_POST['gender'] ?? 'unknown',
            trim($_POST['passport_series'] ?? ''),
            trim($_POST['passport_number'] ?? ''),
            trim($_POST['address'] ?? ''),
            trim($_POST['education'] ?? ''),
            trim($_POST['work_experience'] ?? ''),
            trim($_POST['medical_book_number'] ?? ''),
            $_POST['medical_book_expire_date'] ?: null,
            $_POST['hire_date'] ?: date('Y-m-d'),
        ]);
        $employeeId = (int)$pdo->lastInsertId();

        $positionStmt = $pdo->prepare('INSERT INTO employee_positions (employee_id, position_id, salary_rate, date_from, is_active) VALUES (?, ?, ?, ?, 1)');
        $positionStmt->execute([
            $employeeId,
            (int)$_POST['position_id'],
            (float)$_POST['salary_rate'],
            $_POST['date_from'] ?: date('Y-m-d'),
        ]);

        $pdo->commit();
        flash('success', 'Сотрудник создан: пользователь, карточка сотрудника и должность сохранены.');
        redirect('/admin/employees.php');
    } catch (Throwable $e) {
        $pdo->rollBack();
        flash('danger', 'Не удалось создать сотрудника: ' . $e->getMessage());
    }
}

if (isset($_GET['delete'])) {
    $employeeId = (int)$_GET['delete'];
    $stmt = $pdo->prepare('SELECT user_id FROM employees WHERE id = ?');
    $stmt->execute([$employeeId]);
    $userId = (int)$stmt->fetchColumn();

    if ($userId > 0) {
        try {
            $deleteStmt = $pdo->prepare('CALL delete_user_with_relations(?)');
            $deleteStmt->execute([$userId]);
            $deleteStmt->closeCursor();
            flash('success', 'Сотрудник и связанные данные удалены.');
        } catch (Throwable $e) {
            flash('danger', 'Не удалось удалить сотрудника. Выполните актуальный SQL-файл с процедурами.');
        }
    }
    redirect('/admin/employees.php');
}

$positionsStmt = $pdo->prepare('SELECT * FROM positions ORDER BY name');
$positionsStmt->execute();
$positions = $positionsStmt->fetchAll();

$employeesStmt = $pdo->prepare('SELECT * FROM view_employees_full ORDER BY id DESC');
$employeesStmt->execute();
$employees = $employeesStmt->fetchAll();

require __DIR__ . '/../templates/header.php';
?>
<div class="admin-layout">
    <?php require __DIR__ . '/../templates/admin_sidebar.php'; ?>
    <section class="admin-content">
        <?php show_flash(); ?>
        <h1 class="section-title">Сотрудники</h1>

        <form method="post" class="app-panel mb-4">
            <h2 class="mb-3">Добавить сотрудника</h2>
            <div class="row g-3">
                <div class="col-md-4"><input class="form-control" name="full_name" placeholder="ФИО" required></div>
                <div class="col-md-2"><input class="form-control" name="login" placeholder="Логин" required></div>
                <div class="col-md-2"><input class="form-control" name="password" placeholder="Пароль" required></div>
                <div class="col-md-2"><input class="form-control" type="email" name="email" placeholder="Email" required></div>
                <div class="col-md-2"><input class="form-control" name="phone" placeholder="Телефон"></div>

                <div class="col-md-3"><input class="form-control" type="date" name="birth_date" title="Дата рождения"></div>
                <div class="col-md-3">
                    <label class="form-label d-block">Пол</label>
                    <div class="d-flex gap-3 flex-wrap">
                        <label class="form-check-label"><input class="form-check-input me-1" type="radio" name="gender" value="male"> Мужской</label>
                        <label class="form-check-label"><input class="form-check-input me-1" type="radio" name="gender" value="female"> Женский</label>
                        <label class="form-check-label"><input class="form-check-input me-1" type="radio" name="gender" value="unknown" checked> Не указан</label>
                    </div>
                </div>
                <div class="col-md-3"><input class="form-control" name="passport_series" placeholder="Серия паспорта"></div>
                <div class="col-md-3"><input class="form-control" name="passport_number" placeholder="Номер паспорта"></div>

                <div class="col-md-4"><input class="form-control" name="address" placeholder="Адрес"></div>
                <div class="col-md-4"><input class="form-control" name="education" placeholder="Образование"></div>
                <div class="col-md-4"><input class="form-control" name="medical_book_number" placeholder="Номер медкнижки"></div>

                <div class="col-md-3"><input class="form-control" type="date" name="medical_book_expire_date" title="Срок медкнижки"></div>
                <div class="col-md-3"><input class="form-control" type="date" name="hire_date" value="<?= date('Y-m-d') ?>" required title="Дата приема"></div>
                <div class="col-md-3">
                    <select class="form-select" name="position_id" required>
                        <?php foreach ($positions as $position): ?>
                            <option value="<?= $position['id'] ?>"><?= e($position['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3"><input class="form-control" type="number" step="0.01" min="0" name="salary_rate" placeholder="Ставка" required></div>
                <div class="col-md-3"><input class="form-control" type="date" name="date_from" value="<?= date('Y-m-d') ?>" required title="Дата начала должности"></div>

                <div class="col-12"><textarea class="form-control" name="work_experience" rows="3" placeholder="Опыт работы"></textarea></div>
            </div>
            <button class="btn btn-accent mt-3">Создать сотрудника</button>
        </form>

        <div class="table-responsive"><table class="table table-dark table-hover align-middle">
            <tr><th>ID</th><th>ФИО</th><th>Должности</th><th>Зарплата</th><th>Медкнижка</th><th>Активен</th><th></th></tr>
            <?php foreach ($employees as $emp): ?>
                <tr>
                    <td><?= $emp['id'] ?></td>
                    <td><?= e($emp['full_name']) ?></td>
                    <td><?= e($emp['positions']) ?></td>
                    <td><?= e((string)$emp['salary_total']) ?> ₽</td>
                    <td><?= e($emp['medical_book_expire_date']) ?></td>
                    <td><?= $emp['is_active'] ? 'Да' : 'Нет' ?></td>
                    <td><a class="btn btn-sm btn-outline-danger js-confirm" href="?delete=<?= $emp['id'] ?>">Удалить</a></td>
                </tr>
            <?php endforeach; ?>
        </table></div>
    </section>
</div>
<?php require __DIR__ . '/../templates/footer.php'; ?>