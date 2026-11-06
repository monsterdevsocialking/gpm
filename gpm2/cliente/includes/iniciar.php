<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

function cliente_atual(): ?array
{
    return $_SESSION['gpm_customer'] ?? null;
}

function exigir_cliente(): void
{
    if (!cliente_atual()) {
        header('Location: login.php');
        exit;
    }
}

function escapar(mixed $valor): string
{
    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}