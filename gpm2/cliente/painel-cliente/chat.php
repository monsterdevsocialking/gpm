<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/iniciar.php';

$pedidoId = filter_input(INPUT_GET, 'pedido', FILTER_VALIDATE_INT);
if (!$pedidoId || $pedidoId < 1) {
    http_response_code(400);
    exit('Compra inválida.');
}

$cliente = cliente_atual();
if (!$cliente) {
    $_SESSION['gpm_redirect_after_login'] = 'painel-cliente/index.php';
    header('Location: ../login.php');
    exit;
}

$pdo = db();
$stmt = $pdo->prepare(
    'SELECT id, order_number
     FROM orders
     WHERE id = ? AND customer_id = ?
     LIMIT 1'
);
$stmt->execute([$pedidoId, (int)$cliente['id']]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    http_response_code(404);
    exit('Compra não encontrada.');
}

$stmt = $pdo->prepare(
    'SELECT id, status
     FROM conversations
     WHERE order_id = ? AND customer_id = ?
     LIMIT 1'
);
$stmt->execute([$pedidoId, (int)$cliente['id']]);
$conversa = $stmt->fetch(PDO::FETCH_ASSOC);

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $body = trim((string)($_POST['body'] ?? ''));

    if (!$conversa) {
        $erro = 'Ainda não existe uma conversa para esta compra. Contacta a equipa GPM.';
    } elseif ($conversa['status'] !== 'open') {
        $erro = 'Esta conversa foi encerrada e já não aceita novas mensagens.';
    } elseif ($body === '' || mb_strlen($body) > 5000) {
        $erro = 'Escreve uma mensagem com até 5000 caracteres.';
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO messages
                (conversation_id, sender_type, customer_sender_id, user_sender_id, body, created_at)
             VALUES (?, 'customer', ?, NULL, ?, NOW())"
        );
        $stmt->execute([(int)$conversa['id'], (int)$cliente['id'], $body]);

        $stmt = $pdo->prepare(
            'UPDATE conversations
             SET last_message_at = NOW(), updated_at = NOW()
             WHERE id = ? AND customer_id = ?'
        );
        $stmt->execute([(int)$conversa['id'], (int)$cliente['id']]);

        header('Location: chat.php?pedido=' . (int)$pedidoId);
        exit;
    }
}

$mensagens = [];
if ($conversa) {
    $stmt = $pdo->prepare(
        'SELECT sender_type, body, created_at
         FROM messages
         WHERE conversation_id = ?
         ORDER BY created_at ASC, id ASC'
    );
    $stmt->execute([(int)$conversa['id']]);
    $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function painel_chat_e(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$tituloPagina = 'Chat da compra';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
.painel-chat { display:grid; gap:16px; }
.painel-chat-messages { display:flex; flex-direction:column; gap:12px; max-height:55vh; min-height:240px; overflow-y:auto; padding:18px; background:#f7f8fa; border:1px solid var(--gpm-border); border-radius:12px; }
.painel-chat-message { align-self:flex-start; max-width:min(82%,620px); padding:12px 15px; border-radius:12px; background:#e9edf3; white-space:pre-wrap; overflow-wrap:anywhere; }
.painel-chat-message.mine { align-self:flex-end; background:#fff0d4; }
.painel-chat-message small { display:block; margin-top:7px; color:var(--gpm-muted); font-size:11px; }
.painel-chat-form { border-top:1px solid var(--gpm-border); padding-top:16px; }
.painel-chat-form textarea { width:100%; min-height:95px; resize:vertical; }
.painel-chat-alert { padding:12px 14px; border-radius:9px; background:#fff0f0; color:#9b1c1c; }
.painel-chat-empty { padding:28px 15px; text-align:center; color:var(--gpm-muted); }
@media(max-width:600px){.painel-chat-message{max-width:95%}}
</style>

<section class="gpm-card painel-chat">
    <div>
        <h1 class="gpm-title">Chat da compra <?= painel_chat_e($pedido['order_number']) ?></h1>
        <p class="gpm-muted">Esta conversa é privada e está associada apenas a esta encomenda.</p>
    </div>

    <div class="painel-chat-messages" aria-live="polite">
        <?php if (!$conversa): ?>
            <div class="painel-chat-empty">A conversa desta compra ainda não foi criada.</div>
        <?php elseif (!$mensagens): ?>
            <div class="painel-chat-empty">Ainda não existem mensagens. Envia a primeira mensagem à equipa GPM.</div>
        <?php else: ?>
            <?php foreach ($mensagens as $mensagem): ?>
                <div class="painel-chat-message <?= $mensagem['sender_type'] === 'customer' ? 'mine' : '' ?>">
                    <?= painel_chat_e($mensagem['body']) ?>
                    <small>
                        <?= $mensagem['sender_type'] === 'customer' ? 'Tu' : 'Equipa GPM' ?>
                        · <?= painel_chat_e(date('d/m/Y H:i', strtotime((string)$mensagem['created_at']))) ?>
                    </small>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($erro !== ''): ?>
        <div class="painel-chat-alert" role="alert"><?= painel_chat_e($erro) ?></div>
    <?php endif; ?>

    <?php if ($conversa && $conversa['status'] === 'open'): ?>
        <form class="painel-chat-form" method="post">
            <?= csrf_field() ?>
            <div class="gpm-field">
                <label for="body">A tua mensagem</label>
                <textarea id="body" name="body" maxlength="5000" required placeholder="Escreve aqui a tua mensagem..."></textarea>
            </div>
            <button class="gpm-btn" type="submit">Enviar mensagem</button>
        </form>
    <?php elseif ($conversa): ?>
        <p class="gpm-muted">Esta conversa está encerrada.</p>
    <?php endif; ?>

    <div>
        <a class="gpm-btn gpm-btn-secundario" href="pedido.php?id=<?= (int)$pedidoId ?>">Voltar aos detalhes da compra</a>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
