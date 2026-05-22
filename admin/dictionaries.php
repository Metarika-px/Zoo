<?php
require __DIR__ . '/../includes/functions.php';
require_role(['Администратор', 'Директор']);

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';

        if ($action === 'animal_type') {
            $stmt = $pdo->prepare('INSERT INTO animal_types (name) VALUES (?)');
            $stmt->execute([trim($_POST['name'] ?? '')]);
            flash('success', 'Тип животного добавлен.');
        }

        if ($action === 'species') {
            $stmt = $pdo->prepare('INSERT INTO species (animal_type_id, name, description) VALUES (?, ?, ?)');
            $stmt->execute([(int)$_POST['animal_type_id'], trim($_POST['name'] ?? ''), trim($_POST['description'] ?? '')]);
            flash('success', 'Вид животного добавлен. Теперь его можно выбрать при добавлении животного.');
        }

        if ($action === 'enclosure') {
            $stmt = $pdo->prepare('INSERT INTO enclosures (name, location, area, climate_zone, capacity) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([
                trim($_POST['name'] ?? ''),
                trim($_POST['location'] ?? ''),
                (float)($_POST['area'] ?? 0),
                trim($_POST['climate_zone'] ?? ''),
                (int)($_POST['capacity'] ?? 0),
            ]);
            flash('success', 'Вольер добавлен.');
        }

        if ($action === 'position') {
            $stmt = $pdo->prepare('INSERT INTO positions (name, description) VALUES (?, ?)');
            $stmt->execute([trim($_POST['name'] ?? ''), trim($_POST['description'] ?? '')]);
            flash('success', 'Должность добавлена.');
        }
    } catch (Throwable $e) {
        flash('danger', 'Не удалось сохранить справочник: ' . $e->getMessage());
    }

    redirect('/admin/dictionaries.php');
}

$animalTypes = $pdo->query('SELECT * FROM animal_types ORDER BY name')->fetchAll();
$species = $pdo->query('SELECT s.*, at.name AS animal_type_name FROM species s JOIN animal_types at ON at.id = s.animal_type_id ORDER BY at.name, s.name')->fetchAll();
$enclosures = $pdo->query('SELECT * FROM enclosures ORDER BY name')->fetchAll();
$positions = $pdo->query('SELECT * FROM positions ORDER BY name')->fetchAll();

require __DIR__ . '/../templates/header.php';
?>
<div class="admin-layout">
    <?php require __DIR__ . '/../templates/admin_sidebar.php'; ?>
    <section class="admin-content">
        <?php show_flash(); ?>
        <h1 class="section-title">Справочники</h1>

        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <form method="post" class="app-card h-100">
                    <input type="hidden" name="action" value="animal_type">
                    <h2>Добавить тип животного</h2>
                    <input class="form-control mb-3" name="name" placeholder="Например: Млекопитающие" required>
                    <button class="btn btn-accent">Добавить тип</button>
                </form>
            </div>

            <div class="col-lg-6">
                <form method="post" class="app-card h-100">
                    <input type="hidden" name="action" value="species">
                    <h2>Добавить вид животного</h2>
                    <select class="form-select mb-3" name="animal_type_id" required>
                        <?php foreach ($animalTypes as $type): ?>
                            <option value="<?= $type['id'] ?>"><?= e($type['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input class="form-control mb-3" name="name" placeholder="Например: Лев" required>
                    <textarea class="form-control mb-3" name="description" rows="3" placeholder="Описание вида"></textarea>
                    <button class="btn btn-accent">Добавить вид</button>
                </form>
            </div>

            <div class="col-lg-6">
                <form method="post" class="app-card h-100">
                    <input type="hidden" name="action" value="enclosure">
                    <h2>Добавить вольер</h2>
                    <div class="row g-2">
                        <div class="col-md-6"><input class="form-control" name="name" placeholder="Название" required></div>
                        <div class="col-md-6"><input class="form-control" name="location" placeholder="Локация" required></div>
                        <div class="col-md-4"><input class="form-control" type="number" step="0.01" min="0" name="area" placeholder="Площадь" required></div>
                        <div class="col-md-4"><input class="form-control" name="climate_zone" placeholder="Климат" required></div>
                        <div class="col-md-4"><input class="form-control" type="number" min="0" name="capacity" placeholder="Вместимость" required></div>
                    </div>
                    <button class="btn btn-accent mt-3">Добавить вольер</button>
                </form>
            </div>

            <div class="col-lg-6">
                <form method="post" class="app-card h-100">
                    <input type="hidden" name="action" value="position">
                    <h2>Добавить должность</h2>
                    <input class="form-control mb-3" name="name" placeholder="Например: Зоолог" required>
                    <textarea class="form-control mb-3" name="description" rows="3" placeholder="Описание должности"></textarea>
                    <button class="btn btn-accent">Добавить должность</button>
                </form>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6"><div class="app-card"><h2>Типы животных</h2><div class="table-responsive"><table class="table table-dark table-sm"><tr><th>ID</th><th>Название</th></tr><?php foreach ($animalTypes as $row): ?><tr><td><?= $row['id'] ?></td><td><?= e($row['name']) ?></td></tr><?php endforeach; ?></table></div></div></div>
            <div class="col-lg-6"><div class="app-card"><h2>Виды животных</h2><div class="table-responsive"><table class="table table-dark table-sm"><tr><th>ID</th><th>Тип</th><th>Вид</th></tr><?php foreach ($species as $row): ?><tr><td><?= $row['id'] ?></td><td><?= e($row['animal_type_name']) ?></td><td><?= e($row['name']) ?></td></tr><?php endforeach; ?></table></div></div></div>
            <div class="col-lg-6"><div class="app-card"><h2>Вольеры</h2><div class="table-responsive"><table class="table table-dark table-sm"><tr><th>ID</th><th>Название</th><th>Локация</th><th>Вместимость</th></tr><?php foreach ($enclosures as $row): ?><tr><td><?= $row['id'] ?></td><td><?= e($row['name']) ?></td><td><?= e($row['location']) ?></td><td><?= e((string)$row['capacity']) ?></td></tr><?php endforeach; ?></table></div></div></div>
            <div class="col-lg-6"><div class="app-card"><h2>Должности</h2><div class="table-responsive"><table class="table table-dark table-sm"><tr><th>ID</th><th>Название</th><th>Описание</th></tr><?php foreach ($positions as $row): ?><tr><td><?= $row['id'] ?></td><td><?= e($row['name']) ?></td><td><?= e($row['description']) ?></td></tr><?php endforeach; ?></table></div></div></div>
        </div>
    </section>
</div>
<?php require __DIR__ . '/../templates/footer.php'; ?>