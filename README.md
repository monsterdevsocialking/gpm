# GPM Conecta

Aplicação Laravel 12 para o portal institucional, marketplace de fornecimento e operação comercial da GPM Conecta.

O projeto mantém o PHP original em `gpm2/` como referência durante a migração. Não elimine essa pasta nem importe o SQL sobre uma base de produção sem backup e plano de migração.

## Requisitos

- PHP 8.2 ou superior e extensões exigidas pelo Laravel 12
- Composer 2
- MySQL 8+ ou MariaDB compatível
- Node.js LTS e npm para compilar os assets
- Git

## Instalação local

1. Clone o repositório e entre na pasta:

```bash
git clone https://github.com/monsterdevsocialking/gpm.git
cd gpm
```

2. Instale as dependências PHP e crie o ficheiro de ambiente:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

No Windows PowerShell, se `cp` não estiver disponível, use:

```powershell
Copy-Item .env.example .env
```

3. Crie uma base de dados MySQL vazia e configure `.env`:

- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

O projeto usa MySQL por padrão. Não altere para SQLite na instalação normal.

4. Configure a primeira conta de administrador no `.env`:

```dotenv
GPM_ADMIN_NAME="Administrador GPM"
GPM_ADMIN_EMAIL="admin@seudominio.ao"
GPM_ADMIN_PASSWORD="uma-palavra-passe-forte-com-12-ou-mais"
```

Não use estes exemplos como credenciais reais e nunca envie o seu `.env` para o GitHub.

5. Execute as migrations e crie o administrador:

```bash
php artisan migrate
php artisan db:seed
```

Se as variáveis `GPM_ADMIN_*` não estiverem preenchidas, o seeder não cria uma conta. Se o email já existir, não altera a palavra-passe nem o perfil.

6. Instale e compile os assets:

```bash
npm install
npm run build
```

7. Inicie a aplicação:

```bash
php artisan serve
```

Abra `http://127.0.0.1:8000`.

## Base de dados existente

Para uma instalação que já tenha dados no sistema PHP:

1. Faça backup completo da base de dados e dos ficheiros de imagens.
2. Importe o SQL legado para uma base de dados de migração ou confirme que as tabelas existentes correspondem ao dump `gpm_mvp_nova.sql`.
3. Configure as credenciais dessa base no `.env`.
4. Execute `php artisan migrate` para criar apenas as tabelas novas em falta.

A migration de base verifica a existência das tabelas antigas e não as recria. O rollback dessa migration é intencionalmente não destrutivo para proteger dados importados. Faça primeiro um ensaio numa cópia da base de dados; não execute `migrate:fresh` numa base com dados reais.

## Fluxo comercial implementado

- Catálogo público, carrinho de fornecimento e registo de pedidos.
- Gestão interna de produtos, categorias, imagens e stock.
- Diretório de fornecedores, consultas e registo manual de respostas.
- Propostas comerciais com versões emitidas imutáveis.
- Decisão do cliente associada à versão exata da proposta.
- Confirmação explícita da encomenda por funcionário autorizado.
- Emissão de proformas comerciais ligadas às encomendas.
- Acompanhamento da encomenda: preparação, expedição e conclusão.
- Conversas ligadas às encomendas, com isolamento por cliente.
- Gestão de clientes, utilizadores internos, contactos e dados da empresa.

A aceitação do cliente não cria automaticamente uma encomenda. O pagamento não é iniciado automaticamente e a confirmação não desconta stock. Na conversão inicial, o total da encomenda corresponde ao subtotal aprovado da proposta; taxas de entrega, impostos e descontos ainda não são calculados separadamente.

## APIs e pagamentos

A aplicação tem a estrutura inicial para API versionada, contratos de gateway e registo idempotente de eventos externos. Nenhum gateway ou fornecedor externo está ativo. As credenciais devem ficar apenas no ambiente do servidor.

## Verificações

Execute:

```bash
php artisan route:list
php artisan test
php artisan view:cache
```

O workflow de CI também verifica a sintaxe PHP, executa as migrations numa base SQLite temporária e corre os testes automatizados.

## Documentação técnica

- `docs/ARCHITECTURE.md` — arquitetura e regras comerciais.
- `docs/LEGACY_SCHEMA_MAP.md` — mapa da base antiga e estratégia de migração.
