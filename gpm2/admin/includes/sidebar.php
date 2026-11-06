<?php
declare(strict_types=1);

$paginaAtual = basename($_SERVER['PHP_SELF'] ?? '');
$menuAdmin = [
    ['label' => 'Visão geral', 'icon' => '▦', 'file' => 'index.php'],
    ['label' => 'Produtos', 'icon' => '◇', 'file' => 'products.php'],
    ['label' => 'Categorias', 'icon' => '▤', 'file' => 'categories.php'],
    ['label' => 'Stock', 'icon' => '↗', 'file' => 'stock.php'],
    ['label' => 'Pedidos', 'icon' => '▣', 'file' => 'orders.php'],
    ['label' => 'Conversas', 'icon' => '◌', 'file' => 'chat.php'],
    ['label' => 'Contactos', 'icon' => '✉', 'file' => 'contacts.php'],
    ['label' => 'Clientes', 'icon' => '♙', 'file' => 'customers.php'],
    ['label' => 'Dados da empresa', 'icon' => '⌂', 'file' => 'company.php'],
    ['label' => 'Utilizadores', 'icon' => '♧', 'file' => 'users.php'],
];
?>
<div class="sidebar-overlay" id="gpmSidebarOverlay"></div>
<aside class="sidebar" id="gpmSidebar" aria-label="Navegação principal">
    <a class="brand" href="<?= e(BASE_URL) ?>/admin/index.php">
        <span class="brand-mark">G</span>
        <span class="brand-copy"><strong>GPM<span>.</span></strong><small>PAINEL DE GESTÃO</small></span>
    </a>
    <div class="sidebar-section-label">MENU PRINCIPAL</div>
    <nav class="sidebar-nav">
        <?php foreach ($menuAdmin as $item): ?>
            <?php $active = $paginaAtual === $item['file'] || ($paginaAtual === 'order.php' && $item['file'] === 'orders.php'); ?>
            <a href="<?= e(BASE_URL) ?>/admin/<?= e($item['file']) ?>" class="sidebar-link<?= $active ? ' active' : '' ?>"<?= $active ? ' aria-current="page"' : '' ?>>
                <span class="nav-icon" aria-hidden="true"><?= e($item['icon']) ?></span>
                <span><?= e($item['label']) ?></span>
                <?php if ($active): ?><span class="active-indicator"></span><?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar-bottom">
        <div class="sidebar-user">
            <span class="sidebar-user-avatar"><?= e(strtoupper(substr((string)(current_user()['name'] ?? 'U'), 0, 1))) ?></span>
            <span><strong><?= e(current_user()['name'] ?? 'Utilizador') ?></strong><small>Conta administrativa</small></span>
        </div>
        <a class="sidebar-logout" href="<?= e(BASE_URL) ?>/admin/logout.php"><span>↪</span> Terminar sessão</a>
    </div>
</aside>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('gpmSidebar');
    const overlay = document.getElementById('gpmSidebarOverlay');
    const toggle = document.getElementById('gpmSidebarToggle');
    if (!sidebar || !overlay || !toggle) return;
    function closeMenu() {
        sidebar.classList.remove('is-open');
        overlay.classList.remove('is-visible');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('sidebar-open');
    }
    toggle.addEventListener('click', function () {
        const open = !sidebar.classList.contains('is-open');
        sidebar.classList.toggle('is-open', open);
        overlay.classList.toggle('is-visible', open);
        toggle.setAttribute('aria-expanded', String(open));
        document.body.classList.toggle('sidebar-open', open);
    });
    overlay.addEventListener('click', closeMenu);
    document.addEventListener('keydown', event => { if (event.key === 'Escape') closeMenu(); });
});
</script>
