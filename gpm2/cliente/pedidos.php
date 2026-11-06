<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/iniciar.php';
exigir_cliente();

$pdo = db();
$clienteId = (int) cliente_atual()['id'];

$stmt = $pdo->prepare(
    "SELECT id, order_number, status, payment_status, total, ordered_at
     FROM orders
     WHERE customer_id = ?
     ORDER BY ordered_at DESC, id DESC"
);
$stmt->execute([$clienteId]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

function estado_pedido_cliente(string $estado): string
{
    $estados = [
        'pending' => 'Pendente',
        'confirmed' => 'Confirmado',
        'preparing' => 'Em preparação',
        'shipped' => 'Enviado',
        'delivered' => 'Entregue',
        'completed' => 'Concluído',
        'cancelled' => 'Cancelado',
    ];

    return $estados[$estado] ?? ucfirst(str_replace('_', ' ', $estado));
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Os meus pedidos | GPM</title>
    <link rel="stylesheet" href="../assets/css/cliente.css">
    <style>
        body { background:#f7f7f7; }
        .pagina { max-width:1000px; margin:30px auto; padding:20px; }
        .pedido { background:#fff; padding:18px; border-radius:12px; margin-bottom:14px; display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; }
        .btn { background:#FEA619; color:#222; padding:10px 14px; border-radius:8px; text-decoration:none; font-weight:bold; }
    </style>
</head>
<body>
<main class="pagina">
    <p><a href="produtos.php">← Voltar à loja</a></p>
    <h1>Os meus pedidos</h1>

    <?php if (!$pedidos): ?>
        <section class="pedido">
            <div>
                <p>Ainda não fizeste nenhuma compra.</p>
                <a class="btn" href="produtos.php">Ver produtos</a>
            </div>
        </section>
    <?php endif; ?>

    <?php foreach ($pedidos as $pedido): ?>
        <article class="pedido">
            <div>
                <h2><?= escapar($pedido['order_number']) ?></h2>
                <p>Data: <?= escapar($pedido['ordered_at']) ?></p>
                <p>Estado: <strong><?= escapar(estado_pedido_cliente($pedido['status'])) ?></strong></p>
                <p>Pagamento: <?= escapar($pedido['payment_status']) ?></p>
                <p>Total: <?= escapar(money($pedido['total'])) ?></p>
            </div>
            <a class="btn" href="pedido.php?id=<?= (int) $pedido['id'] ?>">Ver pedido</a>
        </article>
    <?php endforeach; ?>
</main>
</body>
</html>
