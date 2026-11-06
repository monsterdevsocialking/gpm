<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

require_admin();

$pdo = db();

$user = current_user();
$userId = (int) ($user['id'] ?? 0);

$mensagemErro = '';
$conversationId = (int) ($_GET['id'] ?? 0);

// Enviar mensagem
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $conversationId = (int) ($_POST['conversation_id'] ?? 0);
    $message = trim((string) ($_POST['message'] ?? ''));

    if ($conversationId <= 0 || $message === '') {
        $mensagemErro = 'Escreve uma mensagem antes de enviar.';
    } elseif (mb_strlen($message) > 5000) {
        $mensagemErro = 'A mensagem não pode ultrapassar 5000 caracteres.';
    } else {
        $check = $pdo->prepare(
            "SELECT id
             FROM conversations
             WHERE id = ?
             LIMIT 1"
        );

        $check->execute([$conversationId]);

        if (!$check->fetch(PDO::FETCH_ASSOC)) {
            $mensagemErro = 'Esta conversa não existe.';
        } else {
            $insert = $pdo->prepare(
                "INSERT INTO messages
                    (conversation_id, sender_type, user_sender_id, body, created_at)
                 VALUES (?, 'staff', ?, ?, NOW())"
            );

            $insert->execute([
                $conversationId,
                $userId,
                $message
            ]);

            $update = $pdo->prepare(
                "UPDATE conversations
                 SET last_message_at = NOW(),
                     updated_at = NOW()
                 WHERE id = ?"
            );

            $update->execute([$conversationId]);

            redirect('chat.php?id=' . $conversationId);
        }
    }
}

// Listar conversas
$conversas = $pdo->query(
    "SELECT
        c.id,
        c.order_id,
        c.customer_id,
        c.status,
        c.last_message_at,
        (
            SELECT COUNT(*)
            FROM messages m
            WHERE m.conversation_id = c.id
              AND m.sender_type = 'customer'
              AND m.read_at IS NULL
        ) AS mensagens_nao_lidas
     FROM conversations c
     ORDER BY
        CASE WHEN c.status = 'open' THEN 0 ELSE 1 END,
        c.last_message_at DESC,
        c.id DESC"
)->fetchAll(PDO::FETCH_ASSOC);

// Carregar conversa selecionada
$conversa = null;
$mensagens = [];

if ($conversationId > 0) {
    $stmt = $pdo->prepare(
        "SELECT
            id,
            order_id,
            customer_id,
            status
         FROM conversations
         WHERE id = ?
         LIMIT 1"
    );

    $stmt->execute([$conversationId]);
    $conversa = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($conversa) {
        // Marcar mensagens do cliente como lidas
        $marcarLidas = $pdo->prepare(
            "UPDATE messages
             SET read_at = NOW()
             WHERE conversation_id = ?
               AND sender_type = 'customer'
               AND read_at IS NULL"
        );

        $marcarLidas->execute([$conversationId]);

        // Buscar mensagens
        $stmt = $pdo->prepare(
            "SELECT
                id,
                sender_type,
                customer_sender_id,
                user_sender_id,
                body,
                read_at,
                created_at
             FROM messages
             WHERE conversation_id = ?
             ORDER BY created_at ASC, id ASC"
        );

        $stmt->execute([$conversationId]);
        $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!doctype html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Gestão de Conversas | GPM</title>

    <link rel="stylesheet" href="assets/css/admin.css">

    <style>
        * {
            box-sizing: border-box;
        }

        .chat-layout {
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
            gap: 20px;
            margin-top: 20px;
        }

        .chat-list,
        .chat-panel {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 16px;
            min-width: 0;
        }

        .conversation-link {
            display: block;
            padding: 13px;
            margin-bottom: 9px;
            border-radius: 8px;
            background: #f5f5f5;
            color: #222;
            text-decoration: none;
            border: 1px solid transparent;
        }

        .conversation-link:hover,
        .conversation-link.active {
            background: #fff0d5;
            border-color: #fea619;
        }

        .conversation-link small {
            color: #666;
        }

        .conversation-meta {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            align-items: center;
            margin-top: 8px;
        }

        .status {
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 20px;
            background: #dcfce7;
            color: #166534;
        }

        .status.closed {
            background: #eee;
            color: #555;
        }

        .unread {
            background: #dc2626;
            color: #fff;
            font-size: 11px;
            padding: 3px 7px;
            border-radius: 20px;
        }

        .messages {
            min-height: 250px;
            max-height: 500px;
            overflow-y: auto;
            padding: 12px;
            background: #fafafa;
            border-radius: 8px;
        }

        .message {
            padding: 11px 14px;
            margin: 10px 0;
            max-width: 80%;
            border-radius: 10px;
            background: #eee;
            overflow-wrap: anywhere;
        }

        .message.mine {
            margin-left: auto;
            background: #fff0d5;
        }

        .message small {
            display: block;
            margin-top: 7px;
            color: #666;
            font-size: 11px;
        }

        .chat-form {
            margin-top: 15px;
        }

        .chat-form textarea {
            display: block;
            width: 100%;
            min-height: 90px;
            padding: 12px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font: inherit;
            resize: vertical;
        }

        .chat-form button {
            padding: 11px 20px;
            border: 0;
            border-radius: 6px;
            background: #fea619;
            color: #222;
            cursor: pointer;
            font-weight: bold;
        }

        .chat-form button:hover {
            background: #ed970b;
        }

        .chat-error {
            padding: 12px;
            margin: 15px 0;
            border-radius: 6px;
            background: #ffe5e5;
            color: #a00000;
        }

        .chat-closed {
            padding: 14px;
            background: #f3f4f6;
            border-radius: 8px;
            color: #555;
            margin-top: 15px;
        }

        @media (max-width: 700px) {
            .chat-layout {
                grid-template-columns: 1fr;
            }

            .message {
                max-width: 95%;
            }
        }
    </style>
</head>

<body>

<div class="main" style="padding:24px">

    <h1>Gestão de Conversas</h1>
    <p>Consulta as mensagens dos clientes e responde às suas questões.</p>

    <?php if ($mensagemErro !== ''): ?>
        <div class="chat-error">
            <?= e($mensagemErro) ?>
        </div>
    <?php endif; ?>

    <div class="chat-layout">

        <aside class="chat-list">

            <h2>Conversas</h2>

            <?php if (empty($conversas)): ?>
                <p>Ainda não existem conversas.</p>
            <?php endif; ?>

            <?php foreach ($conversas as $item): ?>

                <a
                    class="conversation-link <?= $conversationId === (int) $item['id'] ? 'active' : '' ?>"
                    href="chat.php?id=<?= (int) $item['id'] ?>"
                >
                    <strong>
                        Conversa #<?= (int) $item['id'] ?>
                    </strong>

                    <br>

                    <small>
                        Pedido #<?= (int) $item['order_id'] ?>
                    </small>

                    <div class="conversation-meta">

                        <span class="status <?= ($item['status'] ?? '') !== 'open' ? 'closed' : '' ?>">
                            <?= ($item['status'] ?? '') === 'open' ? 'Aberta' : 'Fechada' ?>
                        </span>

                        <?php if ((int) $item['mensagens_nao_lidas'] > 0): ?>
                            <span class="unread">
                                <?= (int) $item['mensagens_nao_lidas'] ?> novas
                            </span>
                        <?php endif; ?>

                    </div>
                </a>

            <?php endforeach; ?>

        </aside>

        <section class="chat-panel">

            <?php if (!$conversa): ?>

                <h2>Chat</h2>
                <p>Seleciona uma conversa à esquerda para visualizar as mensagens.</p>

            <?php else: ?>

                <h2>
                    Conversa #<?= (int) $conversa['id'] ?>
                </h2>

                <p>
                    Pedido #<?= (int) $conversa['order_id'] ?>
                </p>

                <div class="messages">

                    <?php if (empty($mensagens)): ?>
                        <p>Ainda não existem mensagens nesta conversa.</p>
                    <?php endif; ?>

                    <?php foreach ($mensagens as $msg): ?>

                        <?php
                        $minhaMensagem = ($msg['sender_type'] ?? '') === 'staff';
                        ?>

                        <div class="message <?= $minhaMensagem ? 'mine' : '' ?>">

                            <strong>
                                <?= $minhaMensagem ? 'Empresa' : 'Cliente' ?>
                            </strong>

                            <div>
                                <?= nl2br(e($msg['body'] ?? '')) ?>
                            </div>

                            <small>
                                <?= e($msg['created_at'] ?? '') ?>
                            </small>

                        </div>

                    <?php endforeach; ?>

                </div>

                <?php if (($conversa['status'] ?? '') === 'open'): ?>

                    <form method="POST" class="chat-form">

                        <?= csrf_field() ?>

                        <input
                            type="hidden"
                            name="conversation_id"
                            value="<?= (int) $conversa['id'] ?>"
                        >

                        <textarea
                            name="message"
                            placeholder="Escreve a tua resposta..."
                            maxlength="5000"
                            required
                        ></textarea>

                        <button type="submit">
                            Enviar mensagem
                        </button>

                    </form>

                <?php else: ?>

                    <div class="chat-closed">
                        Esta conversa está fechada. Não é possível enviar mensagens.
                    </div>

                <?php endif; ?>

            <?php endif; ?>

        </section>

    </div>

    <a class="back-link" href="index.php">
        Voltar ao painel
    </a>

</div>

</body>
</html>
