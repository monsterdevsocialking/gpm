GPM — PAINEL DO CLIENTE

ESTRUTURA ATUAL
- cliente/painel-cliente/index.php: resumo da conta e compras recentes.
- cliente/painel-cliente/pedidos.php: lista de encomendas.
- cliente/painel-cliente/pedido.php: detalhe de uma encomenda.
- cliente/painel-cliente/chat.php: conversa associada a uma encomenda.
- cliente/painel-cliente/dados.php: dados pessoais e morada habitual.
- cliente/painel-cliente/alterar-senha.php: alteração da palavra-passe.
- cliente/includes/header.php e footer.php: estrutura visual comum desta área.

ACESSO
1. Inicia sessão pelo marketplace em /gpm2/cliente/login.php.
2. A área do cliente abre em /gpm2/cliente/painel-cliente/index.php.
3. Os links de compras, dados, chat e segurança ficam dentro da pasta painel-cliente.
4. O link para voltar ao marketplace aponta para /gpm2/cliente/index.php.

REGRAS
- As consultas de encomendas e conversas são sempre filtradas pelo ID do cliente autenticado.
- O chat só permite enviar mensagens para uma conversa aberta que pertence a uma encomenda do cliente.
- As alterações de dados e palavra-passe usam proteção CSRF.
- As palavras-passe são guardadas com password_hash() e verificadas com password_verify().
- Não copiar este painel para a raiz cliente/ nem substituir os ficheiros do marketplace.
