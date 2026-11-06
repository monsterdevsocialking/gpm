<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| SIDEBAR ADMINISTRATIVO GPM
|--------------------------------------------------------------------------
| Este ficheiro contém PHP, HTML, CSS e JavaScript.
| Para utilizar: require __DIR__ . '/sidebar.php';
*/

$paginaAtual = basename($_SERVER['PHP_SELF']);

$menuAdmin = [
    [
        'titulo' => 'Painel',
        'icone' => '🏠',
        'link' => 'index.php'
    ],
    [
        'titulo' => 'Categorias',
        'icone' => '📁',
        'link' => 'categorias.php'
    ],
    [
        'titulo' => 'Produtos',
        'icone' => '📦',
        'link' => 'produtos.php'
    ],
    [
        'titulo' => 'Stock',
        'icone' => '📊',
        'link' => 'stock.php'
    ],
    [
        'titulo' => 'Pedidos',
        'icone' => '🛒',
        'link' => 'pedidos.php'
    ],
    [
        'titulo' => 'Gestão de Conversas',
        'icone' => '💬',
        'link' => 'chat.php'
    ],
    [
        'titulo' => 'Clientes',
        'icone' => '👥',
        'link' => 'clientes.php'
    ],
];

?>

<style>
/* SIDEBAR */
.gpm-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 250px;
    height: 100vh;
    background: #172536;
    color: #fff;
    display: flex;
    flex-direction: column;
    z-index: 1000;
    transition: transform .3s ease;
}

/* LOGÓTIPO */
.gpm-sidebar-brand {
    padding: 25px 20px;
    border-bottom: 1px solid rgba(255,255,255,.1);
}

.gpm-sidebar-brand h2 {
    margin: 0;
    color: #fea619;
    font-size: 25px;
}

.gpm-sidebar-brand p {
    margin: 5px 0 0;
    color: #aab5c2;
    font-size: 12px;
}

/* MENU */
.gpm-sidebar-menu {
    padding: 20px 12px;
    flex: 1;
    overflow-y: auto;
}

.gpm-sidebar-menu a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 13px 14px;
    margin-bottom: 5px;
    border-radius: 8px;
    color: #cbd5e1;
    text-decoration: none;
    font-size: 14px;
    transition: .2s;
}

.gpm-sidebar-menu a:hover {
    background: rgba(255,255,255,.08);
    color: #fff;
}

.gpm-sidebar-menu a.active {
    background: #fea619;
    color: #172536;
    font-weight: 700;
}

.gpm-sidebar-icon {
    width: 24px;
    text-align: center;
    font-size: 18px;
}

/* RODAPÉ */
.gpm-sidebar-footer {
    padding: 18px;
    border-top: 1px solid rgba(255,255,255,.1);
}

.gpm-sidebar-footer a {
    color: #fca5a5;
    text-decoration: none;
    font-size: 14px;
}

/* BOTÃO MOBILE */
.gpm-sidebar-toggle {
    display: none;
    border: none;
    background: #fea619;
    color: #172536;
    border-radius: 7px;
    padding: 9px 13px;
    font-size: 20px;
    cursor: pointer;
    margin-bottom: 15px;
}

/* SOBREPOSIÇÃO MOBILE */
.gpm-sidebar-overlay {
    display: none;
}

/* RESPONSIVIDADE */
@media (max-width: 850px) {
    .gpm-sidebar {
        transform: translateX(-100%);
    }

    .gpm-sidebar.gpm-open {
        transform: translateX(0);
    }

    .gpm-sidebar-toggle {
        display: inline-block;
    }

    .gpm-sidebar-overlay.gpm-show {
        display: block;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.5);
        z-index: 999;
    }
}
</style>

<!-- BOTÃO PARA ABRIR MENU NO TELEMÓVEL -->
<button
    type="button"
    class="gpm-sidebar-toggle"
    onclick="gpmToggleSidebar()"
    aria-label="Abrir menu"
>
    ☰
</button>

<!-- MENU LATERAL -->
<aside class="gpm-sidebar" id="gpmSidebar">

    <div class="gpm-sidebar-brand">
        <h2>GPM</h2>
        <p>Gestão de Materiais</p>
    </div>

    <nav class="gpm-sidebar-menu">

        <?php foreach ($menuAdmin as $item): ?>

            <?php
            $ativo = $paginaAtual === basename($item['link']);
            ?>

            <a
                href="<?= e($item['link']) ?>"
                class="<?= $ativo ? 'active' : '' ?>"
                <?= $ativo ? 'aria-current="page"' : '' ?>
            >

                <span class="gpm-sidebar-icon">
                    <?= $item['icone'] ?>
                </span>

                <span>
                    <?= e($item['titulo']) ?>
                </span>

            </a>

        <?php endforeach; ?>

    </nav>

    <div class="gpm-sidebar-footer">
        <a href="../logout.php">
            🚪 Terminar sessão
        </a>
    </div>

</aside>

<div
    class="gpm-sidebar-overlay"
    id="gpmSidebarOverlay"
    onclick="gpmToggleSidebar()"
></div>

<script>
function gpmToggleSidebar() {
    const sidebar = document.getElementById('gpmSidebar');
    const overlay = document.getElementById('gpmSidebarOverlay');

    sidebar.classList.toggle('gpm-open');
    overlay.classList.toggle('gpm-show');
}
</script>
