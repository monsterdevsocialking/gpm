<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';
require_admin();
$pdo = db();
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT o.*, c.nif FROM orders o LEFT JOIN customers c ON c.id = o.customer_id WHERE o.id = ?');
$stmt->execute([$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) { http_response_code(404); exit('Pedido não encontrado.'); }

$labels = ['pendente'=>'Pendente','confirmada'=>'Confirmada','em_preparacao'=>'Em preparação','pronta_para_entrega'=>'Pronta para entrega','concluida'=>'Concluída','cancelada'=>'Cancelada'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $newStatus = (string)($_POST['status'] ?? '');
    $note = trim((string)($_POST['notes'] ?? ''));
    if (!array_key_exists($newStatus, $labels)) {
        flash('Estado inválido.', 'error');
        redirect('order.php?id=' . $id);
    }
    try {
        $pdo->beginTransaction();
        $lock = $pdo->prepare('SELECT status FROM orders WHERE id = ? FOR UPDATE');
        $lock->execute([$id]);
        $oldStatus = (string)$lock->fetchColumn();
        if ($oldStatus !== $newStatus) {
            $pdo->prepare('UPDATE orders SET status = ?, confirmed_at = IF(? = "confirmada", NOW(), confirmed_at), completed_at = IF(? = "concluida", NOW(), completed_at) WHERE id = ?')
                ->execute([$newStatus, $newStatus, $newStatus, $id]);
            $pdo->prepare('INSERT INTO order_status_history (order_id, changed_by, old_status, new_status, notes) VALUES (?, ?, ?, ?, ?)')
                ->execute([$id, (int)current_user()['id'], $oldStatus, $newStatus, $note !== '' ? $note : null]);
        }
        $pdo->commit();
        flash('Estado do pedido atualizado.');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('[GPM order] ' . $e->getMessage());
        flash('Não foi possível atualizar o pedido.', 'error');
    }
    redirect('order.php?id=' . $id);
}
$stmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id');
$stmt->execute([$id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $pdo->prepare('SELECT h.*, u.name AS user_name FROM order_status_history h LEFT JOIN users u ON u.id = h.changed_by WHERE h.order_id = ? ORDER BY h.id DESC');
$stmt->execute([$id]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);

layout_start('Detalhes do pedido');
?>
<section class="panel">
    <div class="toolbar"><h2><?= e($order['order_number']) ?></h2><a class="btn secondary" href="orders.php">Voltar aos pedidos</a></div>
    <div class="form-grid">
        <p><strong>Cliente:</strong> <?= e($order['customer_name']) ?></p><p><strong>NIF:</strong> <?= e($order['nif'] ?: '—') ?></p>
        <p><strong>Telefone:</strong> <?= e($order['customer_phone']) ?></p><p><strong>Email:</strong> <?= e($order['customer_email']) ?></p>
        <p><strong>Morada:</strong> <?= e($order['delivery_address'] ?: '—') ?></p><p><strong>Data:</strong> <?= e($order['created_at']) ?></p>
        <p><strong>Observações:</strong> <?= e($order['customer_notes'] ?: '—') ?></p>
    </div>
    <div class="table-wrap"><table><thead><tr><th>Produto</th><th>Quantidade</th><th>Preço unitário</th><th>Total</th></tr></thead><tbody>
    <?php foreach ($items as $item): ?><tr><td><?= e($item['product_name']) ?></td><td><?= e($item['quantity']) ?> <?= e($item['unit']) ?></td><td><?= e(money($item['unit_price'])) ?></td><td><?= e(money($item['line_total'])) ?></td></tr><?php endforeach; ?>
    <?php if (!$items): ?><tr><td colspan="4" class="empty">Este pedido não tem linhas.</td></tr><?php endif; ?>
    </tbody></table></div>
    <div style="text-align:right;margin-top:18px"><p>Subtotal: <strong><?= e(money($order['subtotal'])) ?></strong></p><p>Entrega: <strong><?= e(money($order['delivery_fee'])) ?></strong></p><h2>Total: <?= e(money($order['total'])) ?></h2></div>
</section>
<section class="panel">
    <h2>Atualizar estado</h2>
    <form method="post"><?= csrf_field() ?><div class="form-grid"><div class="field"><label for="status">Estado</label><select id="status" name="status"><?php foreach ($labels as $key=>$label): ?><option value="<?= e($key) ?>"<?= $order['status'] === $key ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div><div class="field"><label for="notes">Observação</label><input id="notes" name="notes" maxlength="500"></div></div><button type="submit">Guardar estado</button></form>
</section>
<section class="panel">
    <h2>Histórico de estados</h2>
    <div class="table-wrap"><table><thead><tr><th>Data</th><th>Anterior</th><th>Novo</th><th>Responsável</th><th>Nota</th></tr></thead><tbody>
    <?php foreach ($history as $entry): ?><tr><td><?= e($entry['created_at']) ?></td><td><?= e($entry['old_status'] ?: '—') ?></td><td><?= e($labels[$entry['new_status']] ?? $entry['new_status']) ?></td><td><?= e($entry['user_name'] ?: '—') ?></td><td><?= e($entry['notes'] ?: '—') ?></td></tr><?php endforeach; ?>
    <?php if (!$history): ?><tr><td colspan="5" class="empty">Sem histórico registado.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
<?php layout_end(); ?>
