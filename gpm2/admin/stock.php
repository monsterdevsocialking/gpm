<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';
require_admin();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = (float)($_POST['quantity'] ?? 0);
        $type = (string)($_POST['movement_type'] ?? '');
        $notes = trim((string)($_POST['notes'] ?? ''));
        if ($productId <= 0 || $quantity <= 0 || !in_array($type, ['entrada', 'saida', 'ajuste'], true)) {
            throw new RuntimeException('Confirma o produto, o tipo e a quantidade.');
        }
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('SELECT stock_quantity FROM products WHERE id = ? FOR UPDATE');
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        if (!$product) throw new RuntimeException('Produto não encontrado.');
        $before = (float)$product['stock_quantity'];
        if ($type === 'ajuste') {
            $after = $quantity;
            $delta = abs($after - $before);
        } else {
            $delta = $quantity;
            $after = $type === 'entrada' ? $before + $quantity : $before - $quantity;
        }
        if ($after < 0) throw new RuntimeException('Stock insuficiente para esta saída.');
        $pdo->prepare('UPDATE products SET stock_quantity = ? WHERE id = ?')->execute([$after, $productId]);
        if ($delta > 0) {
            $pdo->prepare('INSERT INTO stock_movements (product_id, user_id, type, quantity, notes) VALUES (?, ?, ?, ?, ?)')
                ->execute([$productId, (int)current_user()['id'], $type, $delta, $notes]);
        }
        $pdo->commit();
        flash('Movimento de stock registado.');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('[GPM stock] ' . $e->getMessage());
        flash($e instanceof RuntimeException ? $e->getMessage() : 'Não foi possível registar o movimento.', 'error');
    }
    redirect('stock.php');
}
$products = $pdo->query('SELECT id, name, stock_quantity, unit FROM products WHERE is_active = 1 ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$rows = $pdo->query('SELECT sm.*, p.name AS product_name, u.name AS user_name FROM stock_movements sm JOIN products p ON p.id = sm.product_id LEFT JOIN users u ON u.id = sm.user_id ORDER BY sm.id DESC LIMIT 100')->fetchAll(PDO::FETCH_ASSOC);

layout_start('Movimentos de stock');
?>
<section class="panel">
    <h2>Registar movimento</h2>
    <form method="post"><?= csrf_field() ?>
        <div class="form-grid">
            <div class="field"><label for="product_id">Produto</label><select id="product_id" name="product_id" required><option value="">Selecionar produto</option><?php foreach ($products as $product): ?><option value="<?= (int)$product['id'] ?>"><?= e($product['name']) ?> — stock: <?= e($product['stock_quantity']) ?> <?= e($product['unit']) ?></option><?php endforeach; ?></select></div>
            <div class="field"><label for="movement_type">Tipo</label><select id="movement_type" name="movement_type" required><option value="entrada">Entrada</option><option value="saida">Saída</option><option value="ajuste">Ajustar stock para a quantidade indicada</option></select></div>
            <div class="field"><label for="quantity">Quantidade</label><input id="quantity" name="quantity" type="number" min="0.001" step="0.001" required></div>
            <div class="field"><label for="notes">Observações</label><input id="notes" name="notes" maxlength="500" placeholder="Ex.: compra ao fornecedor"></div>
        </div>
        <button type="submit">Registar movimento</button>
    </form>
</section>
<section class="panel">
    <h2>Últimos 100 movimentos</h2>
    <div class="table-wrap"><table><thead><tr><th>Data</th><th>Produto</th><th>Tipo</th><th>Quantidade</th><th>Responsável</th><th>Observações</th></tr></thead><tbody>
    <?php if (!$rows): ?><tr><td colspan="6" class="empty">Sem movimentos registados.</td></tr><?php endif; ?>
    <?php foreach ($rows as $row): ?><tr><td><?= e($row['created_at']) ?></td><td><?= e($row['product_name']) ?></td><td><span class="badge"><?= e($row['type']) ?></span></td><td><?= e($row['quantity']) ?></td><td><?= e($row['user_name'] ?: '—') ?></td><td><?= e($row['notes'] ?: '—') ?></td></tr><?php endforeach; ?>
    </tbody></table></div>
</section>
<?php layout_end(); ?>
