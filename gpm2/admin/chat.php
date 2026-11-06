<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_admin();

$pdo = db();
$user = current_user();
$userId = (int) ($user['id'] ?? 0);
$mensagemErro = '';
$conversationId = (int) ($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $conversationId = (int) ($_POST['conversation_id'] ?? 0);
    $message = trim((string) ($_POST['message'] ?? ''));

    if ($conversationId <= 0 || $message === '') {
        $mensagemErro = 'Escreve uma mensagem antes de enviar.';
    } elseif (mb_strlen($message) > 5000) {
        $mensagemErro = 'A mensagem não pode ultrapassar 5000 caracteres.';
    } else {
        $check = $pdo->prepare('SELECT id FROM conversations WHERE id = ? LIMIT 1');
        $check->execute([$conversationId]);
        if (!$check->fetchColumn()) {
            $mensagemErro = 'Esta conversa não existe.';
        } else {
            $insert = $pdo->prepare('INSERT INTO messages (conversation_id, sender_user_id, message, created_at) VALUES (?, ?, ?, NOW())');
            $insert->execute([$conversationId, $userId, $message]);
            $pdo->prepare('UPDATE conversations SET updated_at = NOW() WHERE id = ?')->execute([$conversationId]);
            redirect('chat.php?id=' . $conversationId);
        }
    }
}

$conversas = $pdo->query(
    'SELECT c.id, c.order_id, c.updated_at,
            o.order_number, o.customer_name,
            (SELECT COUNT(*) FROM messages m
             JOIN users mu ON mu.id = m.sender_user_id
             WHERE m.conversation_id = c.id AND mu.role = "customer"
               AND m.read_at IS NULL) AS mensagens_nao_lidas
     FROM conversations c
     LEFT JOIN orders o ON o.id = c.order_id
     ORDER BY c.updated_at DESC, c.id DESC'
)->fetchAll(PDO::FETCH_ASSOC);

$conversa = null;
$mensagens = [];
if ($conversationId > 0) {
    $stmt = $pdo->prepare('SELECT c.id, c.order_id, o.order_number FROM conversations c LEFT JOIN orders o ON o.id = c.order_id WHERE c.id = ? LIMIT 1');
    $stmt->execute([$conversationId]);
    $conversa = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    if ($conversa) {
        $read = $pdo->prepare(
            'UPDATE messages SET read_at = NOW()
             WHERE conversation_id = ? AND sender_user_id <> ? AND read_at IS NULL'
        );
        $read->execute([$conversationId, $userId]);

        $stmt = $pdo->prepare(
            'SELECT m.id, m.sender_user_id, m.message, m.read_at, m.created_at, u.name AS sender_name, u.role AS sender_role
             FROM messages m LEFT JOIN users u ON u.id = m.sender_user_id
             WHERE m.conversation_id = ? ORDER BY m.created_at ASC, m.id ASC'
        );
        $stmt->execute([$conversationId]);
        $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

layout_start('Gestão de Conversas');
?>
<section class="panel">
    <h2>Atendimento aos clientes</h2>
    <p class="muted">Consulta as conversas associadas aos pedidos e responde aos clientes.</p>
    <?php if ($mensagemErro !== ''): ?><div class="alert error" role="alert"><?= e($mensagemErro) ?></div><?php endif; ?>
    <div class="chat-layout">
        <aside class="chat-list">
            <h2>Conversas</h2>
            <?php if (!$conversas): ?><p class="empty">Ainda não existem conversas.</p><?php endif; ?>
            <?php foreach ($conversas as $item): ?>
                <a class="conversation-link <?= $conversationId === (int)$item['id'] ? 'active' : '' ?>" href="chat.php?id=<?= (int)$item['id'] ?>">
                    <strong><?= e($item['customer_name'] ?: 'Cliente') ?></strong>
                    <br><small>Pedido <?= e($item['order_number'] ?: ('#' . $item['order_id'])) ?></small>
                    <div class="conversation-meta">
                        <span class="status">Conversa</span>
                        <?php if ((int)$item['mensagens_nao_lidas'] > 0): ?><span class="unread"><?= (int)$item['mensagens_nao_lidas'] ?> novas</span><?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </aside>
        <section class="chat-panel">
            <?php if (!$conversa): ?>
                <h2>Seleciona uma conversa</h2>
                <p class="muted">Escolhe uma conversa na coluna ao lado para ver as mensagens.</p>
            <?php else: ?>
                <h2>Conversa · <?= e($conversa['order_number'] ?: ('Pedido #' . $conversa['order_id'])) ?></h2>
                <div class="messages" aria-live="polite">
                    <?php if (!$mensagens): ?><p class="empty">Ainda não existem mensagens.</p><?php endif; ?>
                    <?php foreach ($mensagens as $msg): $minha = (int)$msg['sender_user_id'] === $userId; ?>
                        <article class="message <?= $minha ? 'mine' : '' ?>">
                            <strong><?= e($msg['sender_name'] ?: ($minha ? 'Equipa GPM' : 'Cliente')) ?></strong>
                            <div><?= nl2br(e($msg['message'])) ?></div>
                            <small><?= e($msg['created_at']) ?></small>
                        </article>
                    <?php endforeach; ?>
                </div>
                <form method="post" class="chat-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="conversation_id" value="<?= (int)$conversa['id'] ?>">
                    <label for="message">A tua resposta</label>
                    <textarea id="message" name="message" placeholder="Escreve uma resposta..." maxlength="5000" required></textarea>
                    <button type="submit">Enviar mensagem</button>
                </form>
            <?php endif; ?>
        </section>
    </div>
</section>
<?php layout_end(); ?>
