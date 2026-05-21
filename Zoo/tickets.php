<?php
require __DIR__ . '/includes/mail.php';
require_login();
$user = current_user();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $visitDate = $_POST['visit_date'] ?? date('Y-m-d');
    $quantities = $_POST['quantity'] ?? [];
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $order = $pdo->prepare('CALL buy_ticket(?, ?)');
        $order->execute([$user['id'], $visitDate]);
        $orderResult = $order->fetch();
        $order->closeCursor();
        $orderId = (int)$orderResult['order_id'];
        $total = 0;
        $priceStmt = $pdo->prepare('SELECT id, price FROM ticket_types WHERE id = ?');
        $itemStmt = $pdo->prepare('INSERT INTO ticket_order_items (order_id, ticket_type_id, quantity, price_at_purchase, subtotal) VALUES (?, ?, ?, ?, ?)');
        foreach ($quantities as $typeId => $quantity) {
            $quantity = max(0, (int)$quantity);
            if ($quantity === 0) {
                continue;
            }
            $priceStmt->execute([(int)$typeId]);
            $ticket = $priceStmt->fetch();
            if (!$ticket) {
                continue;
            }
            $subtotal = $quantity * (float)$ticket['price'];
            $total += $subtotal;
            $itemStmt->execute([$orderId, $ticket['id'], $quantity, $ticket['price'], $subtotal]);
        }
        if ($total <= 0) {
            throw new RuntimeException('Выберите хотя бы один билет.');
        }
        $totalStmt = $pdo->prepare('SELECT calculate_ticket_total(?)');
        $totalStmt->execute([$orderId]);
        $total = (float)$totalStmt->fetchColumn();
        $pdo->prepare('UPDATE ticket_orders SET total_price = ? WHERE id = ?')->execute([$total, $orderId]);
        $pdo->commit();
        send_app_mail($user['email'], 'Покупка билета в зоопарк', "Ваш заказ #$orderId на сумму $total руб. оформлен.");
        flash('success', 'Билеты успешно куплены. Итоговая стоимость рассчитана на сервере.');
        redirect('/client/orders.php');
    } catch (Throwable $e) {
        $pdo->rollBack();
        flash('danger', $e->getMessage());
    }
}
require __DIR__ . '/templates/header.php';
?>
<section class="container py-5">
    <?php show_flash(); ?>
    <h1 class="section-title">Купить билет</h1>
    <form method="post" class="app-panel">
        <label class="form-label">Дата посещения</label>
        <input class="form-control mb-4" type="date" name="visit_date" min="<?= date('Y-m-d') ?>" required>
        <div class="row g-3">
            <?php foreach (db()->query('SELECT * FROM ticket_types ORDER BY price DESC') as $t): ?>
                <div class="col-md-4"><div class="app-card">
                    <h3><?= e($t['name']) ?></h3>
                    <p class="text-secondary"><?= e($t['description']) ?></p>
                    <strong><?= e((string)$t['price']) ?> ₽</strong>
                    <input class="form-control mt-3" type="number" name="quantity[<?= $t['id'] ?>]" value="0" min="0">
                </div></div>
            <?php endforeach; ?>
        </div>
        <button class="btn btn-accent mt-4">Оформить заказ</button>
    </form>
</section>
<?php require __DIR__ . '/templates/footer.php'; ?>

