<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/iniciar.php';

if (!cliente_atual()) {
    $_SESSION['gpm_redirect_after_login'] = 'painel-cliente/pedidos.php';
    flash('Inicia sessão para consultar os teus pedidos.', 'error');
    redirect('login.php');
}

redirect('painel-cliente/pedidos.php');
