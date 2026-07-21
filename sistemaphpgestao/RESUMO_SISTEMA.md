# Gestão Terceiro — Resumo do Sistema

## O que é

Sistema web para **gestão de projetos e prestação de contas de ONGs/instituições sociais brasileiras**. Controla projetos com fontes de financiamento, orçamentos, despesas, metas, diligências de conformidade e relatórios de accountability.

## Stack

- **Backend**: Laravel 13 (PHP 8.3+)
- **Frontend**: Blade + Vite
- **Banco**: MySQL
- **Auth**: Laravel Breeze com controle de papéis

## Módulos principais

| Módulo | O que faz |
|---|---|
| **Instituições** | Cadastro de ONGs com CNPJ, representantes, dados bancários |
| **Projetos** | Projetos com fonte de financiamento, valor aprovado, prazo, status |
| **Metas / Atividades** | Objetivos do projeto com métricas, orçamento e % de execução |
| **Despesas** | Lançamento de gastos com status: PENDENTE → APROVADO → PAGO |
| **Prestação de Contas** | Relatórios financeiros enviados ao financiador |
| **Diligências** | Notificações de conformidade (DOCUMENTAL, TÉCNICA, FINANCEIRA) com prazo de resposta |
| **Documentos** | Repositório de arquivos com status de validação |
| **Auditoria** | Log completo de todas as ações (usuário, IP, antes/depois) |
| **Configurações** | SMTP, chaves de API (Asaas, Z-API, D4Sign) via painel admin |

## Papéis de usuário

- `ADMIN_GERAL` — acesso total a todas as instituições
- `ADMIN_INSTITUICAO` — administra sua própria instituição
- `FINANCEIRO` — gerencia despesas e relatórios
- `GESTOR_PROJETO` — gerencia projetos específicos

## Arquitetura

```
Blade Views → Routes → Controllers (24) → Models (14) → MySQL
                                        ↓
                               APIs externas: BrasilAPI (CNPJ/CEP)
                               Futuro: Asaas, Z-API, D4Sign, Pluggy
```

- Dados **isolados por instituição** (usuários só veem o que é da sua instituição, exceto ADMIN_GERAL)
- Cache de dashboard (1h), CNPJ/CEP (24h)
- Filas via banco de dados (emails, relatórios assíncronos)
- Geração de PDF via DomPDF

## Estrutura de pastas relevante

```
app/
  Http/Controllers/   — 24 controllers (um por módulo)
  Models/             — 14 modelos Eloquent
  Services/           — CnpjService, CepService
  Mail/               — templates de email
resources/views/      — 45+ templates Blade
database/
  migrations/         — 16 migrations
  seeders/            — dados de teste
routes/
  web.php             — rotas principais
  auth.php            — rotas de autenticação
```

## Modelos principais e relacionamentos

```
Institution
  └── Director (diretores com mandato)
  └── Project
        └── FundingSource (instrumento/convênio)
        └── Goal (meta)
              └── Activity (atividade)
        └── Expense (despesa)
        └── AccountingReport (prestação de contas)
        └── Diligence (diligência de conformidade)
        └── Document (documentos)
User → pertence a uma Institution (exceto ADMIN_GERAL)
Setting → configurações do sistema (SMTP, APIs)
AuditLog → histórico de todas as ações
```

## Variáveis de ambiente essenciais (.env)

```
APP_NAME=Gestão Terceiro
APP_LOCALE=pt_BR
DB_CONNECTION=mysql
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
MAIL_MAILER=smtp  (ou log para dev)
```

## Como rodar localmente

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
composer run dev   # inicia servidor + queue + vite juntos
```

Login padrão: `admin@gestao.org` / `password`

## Integrações

- **Ativas**: BrasilAPI (CNPJ), ViaCEP (endereço)
- **Planejadas**: Asaas (pagamentos), Z-API (WhatsApp), D4Sign (assinatura digital), Pluggy (open finance)
