# GPM — Sistema PHP + MySQL

O GPM está organizado em três áreas principais:

- `index.php`: página institucional pública, com o design original da GPM.
- `cliente/`: marketplace, catálogo, carrinho, checkout, conta do cliente, pedidos, proformas e conversas.
- `admin/`: painel administrativo, produtos, categorias, stock, pedidos, clientes, mensagens e definições da empresa.
- `app/bootstrap.php`: sessão, ligação comum, autenticação, CSRF, helpers e layout administrativo.
- `config/database.php`: configuração PDO da base de dados.

## Versão principal do marketplace

A versão principal em utilização neste projeto é `gpm2/cliente/`, porque é a versão ligada à página institucional `gpm2/index.php` e ao painel administrativo em `gpm2/admin/`.

A pasta `gpm2/gpm/` contém um pacote anterior/alternativo do lado do cliente. Não copiar os ficheiros dessa pasta por cima de `gpm2/` sem comparar primeiro: isso pode substituir páginas e estilos mais recentes.

## Funcionalidades existentes

- Login administrativo com `password_hash` e `password_verify`.
- Perfis internos: administrador, vendedor e gestor de stock.
- Dashboard com métricas.
- CRUD de produtos e categorias.
- Upload e galeria de imagens de produtos.
- Entradas, saídas e ajustes de stock com transações.
- Gestão de pedidos e histórico de estados.
- Reposição de stock ao cancelar pedidos que já tinham baixado stock.
- Consulta de clientes.
- Conversas entre cliente e equipa.
- Dados da empresa e NIF.
- Utilizadores internos.
- Marketplace público, pesquisa, categorias e detalhe do produto.
- Carrinho, checkout, moradas guardadas, pedidos, proformas e chat do cliente.
- Formulário de contacto institucional.

## Instalação e base de dados

1. Importa primeiro o esquema existente `gpm_mvp_nova.sql`.
2. Depois executa uma vez `gpm2/database/patch_contact_messages.sql`. Este patch cria apenas a tabela em falta para o formulário de contacto; não apaga nem recria tabelas existentes.
3. Configura `gpm2/config/database.php` ou as variáveis de ambiente `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` e `DB_PASS`.
4. Cria o administrador com `gpm2/database/create_admin.php`, se ainda não existir.
5. Abre `/gpm2/` para a página institucional, `/gpm2/cliente/` para o marketplace e `/gpm2/admin/login.php` para o painel.

**Importante:** faz backup da base de dados antes de executar qualquer alteração SQL em ambiente com dados reais.

## Segurança e manutenção

- Os formulários de alteração devem usar CSRF.
- Os valores apresentados devem ser escapados com `e()` ou `htmlspecialchars()`.
- Páginas administrativas devem verificar o perfil autorizado antes de ler ou alterar dados.
- O checkout regista o pedido, itens, proforma e conversa sem baixar stock; a confirmação comercial/saída de stock pertence ao painel administrativo.
- A proforma HTML é imprimível e não substitui uma fatura fiscal.
- Testa os fluxos com uma cópia da base de dados antes de publicar em produção.
