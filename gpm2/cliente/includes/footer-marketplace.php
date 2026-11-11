<!-- Footer Marketplace -->
<footer class="bg-[#0d233f] text-white pt-16 pb-8 border-t-4 border-[#f96b21] mt-auto">
    <div class="max-w-site mx-auto px-4 sm:px-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-10 mb-12">
            <div class="md:col-span-2">
                <a href="<?= $baseMarketplace ?>/index.php" class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 bg-[#f96b21] text-white flex items-center justify-center font-bold text-lg">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <span class="text-2xl font-black tracking-tighter">GPM.</span>
                </a>
                <p class="text-gray-400 text-sm max-w-sm leading-relaxed mb-6">
                    Materiais, equipamentos e soluções de fornecimento para empresas e profissionais.
                </p>
                <div class="flex space-x-4 text-lg text-gray-400">
                    <a href="#" aria-label="Facebook" class="hover:text-white transition"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" aria-label="Instagram" class="hover:text-white transition"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" aria-label="LinkedIn" class="hover:text-white transition"><i class="fa-brands fa-linkedin-in"></i></a>
                    <a href="#" aria-label="WhatsApp" class="hover:text-white transition"><i class="fa-brands fa-whatsapp"></i></a>
                </div>
            </div>

            <div>
                <h3 class="text-xs font-bold uppercase tracking-widest text-[#f96b21] mb-6">Links Úteis</h3>
                <ul class="space-y-3 text-sm text-gray-300">
                    <li><a href="<?= $baseMarketplace ?>/index.php" class="hover:text-white transition">Início</a></li>
                    <li><a href="<?= $baseMarketplace ?>/index.php#produtos" class="hover:text-white transition">Produtos</a></li>
                    <li><a href="<?= $baseMarketplace ?>/carrinho.php" class="hover:text-white transition">Meu Carrinho</a></li>
                    <li><a href="<?= $baseMarketplace ?>/login.php" class="hover:text-white transition">Minha Conta</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-xs font-bold uppercase tracking-widest text-[#f96b21] mb-6">Contactos</h3>
                <p class="text-sm text-gray-400 mb-4 leading-relaxed">
                    Entre em contacto connosco para obter informações sobre produtos, preços e entregas.
                </p>
                <a href="<?= $baseMarketplace ?>/index.php#contactos" class="inline-block bg-[#f96b21] hover:bg-[#e05812] text-[#0d233f] font-bold text-xs uppercase tracking-wider px-6 py-3 transition shadow-md">
                    Fale Connosco
                </a>
            </div>
        </div>

        <div class="border-t border-white/10 pt-6 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-gray-500">
            <p>&copy; <?= date('Y') ?> GPM. Todos os direitos reservados.</p>
            <p>Fornecimento, equipamentos e soluções para empresas.</p>
        </div>
    </div>
</footer>

<script>
(function () {
    const botao = document.getElementById('gpm-market-menu-btn');
    const menu = document.getElementById('gpm-market-nav');

    if (!botao || !menu) return;

    function fecharMenu() {
        menu.classList.add('hidden');
        botao.setAttribute('aria-expanded', 'false');
        botao.setAttribute('aria-label', 'Abrir menu');
        botao.textContent = '☰';
    }

    botao.addEventListener('click', function () {
        const vaiAbrir = menu.classList.contains('hidden');

        menu.classList.toggle('hidden', !vaiAbrir);
        botao.setAttribute('aria-expanded', vaiAbrir ? 'true' : 'false');
        botao.setAttribute('aria-label', vaiAbrir ? 'Fechar menu' : 'Abrir menu');
        botao.textContent = vaiAbrir ? '✕' : '☰';
    });

    menu.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', fecharMenu);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') fecharMenu();
    });
})();
</script>

</body>
</html>
