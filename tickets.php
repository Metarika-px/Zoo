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
        $priceStmt = $pdo->prepare('SELECT id, name, price FROM ticket_types WHERE id = ?');
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

        $itemsStmt = $pdo->prepare('SELECT ticket_type, quantity, price_at_purchase, subtotal FROM view_tickets_full WHERE order_id = ? ORDER BY ticket_type');
        $itemsStmt->execute([$orderId]);
        $items = $itemsStmt->fetchAll();

        $pdo->commit();

        $rows = '';
        foreach ($items as $item) {
            $rows .= '<tr>'
                . '<td style="padding:10px;border-bottom:1px solid #263244;">' . e($item['ticket_type']) . '</td>'
                . '<td style="padding:10px;border-bottom:1px solid #263244;text-align:center;">' . e((string)$item['quantity']) . '</td>'
                . '<td style="padding:10px;border-bottom:1px solid #263244;text-align:right;">' . e(number_format((float)$item['price_at_purchase'], 2, ',', ' ')) . ' ₽</td>'
                . '<td style="padding:10px;border-bottom:1px solid #263244;text-align:right;">' . e(number_format((float)$item['subtotal'], 2, ',', ' ')) . ' ₽</td>'
                . '</tr>';
        }

        $ticketEmail = '
            <p style="margin:0 0 14px;">Ваш электронный билет успешно оформлен.</p>
            <table style="width:100%;border-collapse:collapse;margin:16px 0;color:#f8fafc;">
                <tr><td style="padding:6px 0;color:#9aa7b8;">Заказ</td><td style="padding:6px 0;text-align:right;"><strong>#' . e((string)$orderId) . '</strong></td></tr>
                <tr><td style="padding:6px 0;color:#9aa7b8;">Клиент</td><td style="padding:6px 0;text-align:right;">' . e($user['full_name']) . '</td></tr>
                <tr><td style="padding:6px 0;color:#9aa7b8;">Дата посещения</td><td style="padding:6px 0;text-align:right;">' . e(date('d.m.Y', strtotime($visitDate))) . '</td></tr>
                <tr><td style="padding:6px 0;color:#9aa7b8;">Статус</td><td style="padding:6px 0;text-align:right;">Оплачен</td></tr>
                <tr><td style="padding:6px 0;color:#9aa7b8;">Дата оформления</td><td style="padding:6px 0;text-align:right;">' . e(date('d.m.Y H:i')) . '</td></tr>
            </table>
            <h2 style="font-size:18px;margin:18px 0 10px;color:#ff9f1c;">Состав заказа</h2>
            <table style="width:100%;border-collapse:collapse;color:#f8fafc;">
                <thead>
                    <tr>
                        <th style="padding:10px;border-bottom:1px solid #263244;text-align:left;color:#9aa7b8;">Тип билета</th>
                        <th style="padding:10px;border-bottom:1px solid #263244;text-align:center;color:#9aa7b8;">Кол-во</th>
                        <th style="padding:10px;border-bottom:1px solid #263244;text-align:right;color:#9aa7b8;">Цена</th>
                        <th style="padding:10px;border-bottom:1px solid #263244;text-align:right;color:#9aa7b8;">Сумма</th>
                    </tr>
                </thead>
                <tbody>' . $rows . '</tbody>
            </table>
            <p style="font-size:20px;margin:18px 0 8px;text-align:right;"><strong>Итого: ' . e(number_format($total, 2, ',', ' ')) . ' ₽</strong></p>
            <p style="margin:18px 0 0;color:#9aa7b8;">Покажите номер заказа на входе в зоопарк.</p>
        ';

        send_app_mail($user['email'], 'Электронный билет в зоопарк', $ticketEmail, true);
        flash('success', 'Билеты успешно куплены. Электронный билет отправлен на почту.');
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
                    <strong><?= e(number_format((float)$t['price'], 2, ',', ' ')) ?> ₽</strong>
                    <input class="form-control mt-3" type="number" name="quantity[<?= $t['id'] ?>]" value="0" min="0">
                </div></div>
            <?php endforeach; ?>
        </div>
        <button class="btn btn-accent mt-4">Оформить заказ</button>
    </form>
</section>
<?php require __DIR__ . '/templates/footer.php'; ?>