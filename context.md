# Contexto do Sistema — Gestão Terceiro Setor

## 1. Visão Geral

Gestão Terceiro Setor é uma plataforma web de gestão documental, prestação de contas e conformidade para organizações do terceiro setor (ONGs, OSCIPs, associações, fundações). O sistema centraliza todo o ciclo de vida de projetos sociais financiados por editais, emendas parlamentares, convênios e recursos privados, garantindo rastreabilidade, governança e conformidade com exigências legais.

O produto é oferecido como SaaS multi-tenant, onde cada organização (instituição) opera em seu próprio ambiente isolado, com controle de acesso baseado em papéis (RBAC).

## 2. Problema Resolvido

### 2.1 Dores do Terceiro Setor

Organizações do terceiro setor operam sob regimes de accountability rigorosos, com:

- **Múltiplas fontes de financiamento simultâneas**: editais públicos, emendas parlamentares, convênios federais/estaduais, recursos privados — cada um com regras documentais distintas.
- **Dispersão de arquivos**: documentos comprobatórios espalhados em drives compartilhados, e-mails e pastas locais, sem rastreabilidade.
- **Risco de reprovação na prestação de contas**: falta de comprovação documental de execução física e financeira, resultando em devolução de recursos ou suspensão de repasses.
- **Falta de visão gerencial**: dificuldade em acompanhar status de projetos, metas, despesas e prazos em tempo real.
- **Processos manuais de diligência**: notificações de conformidade (documental, técnica, financeira) geradas em PDF ou e-mail avulso, sem controle de prazo ou status.
- **Pesquisa de preços não estruturada**: cotações feitas em planilhas dispersas, sem integração com fontes oficiais (PNCP) e sem geração automática de relatórios comparativos.

### 2.2 Objetivo do Produto

Substituir processos fragmentados por um ambiente único que:

- Centraliza projetos, documentos, evidências e responsáveis.
- Garante rastreabilidade ponta a ponta com logs de auditoria.
- Automatiza fluxos de diligência e aprovação com notificações.
- Estrutura pesquisa de preços com integração PNCP, mercado e IA generativa.
- Gera relatórios e PDFs prontos para entrega a financiadores.

**Contagem atual (sistema legado — Laravel):** 46 migrations, 29 models, 32 controllers, 63 views, 7 services

**Contagem atual (nova arquitetura — NestJS + React):** 3 workspaces, 11 módulos NestJS, 15+ páginas React, 12 componentes Shadcn UI, 6 serviços de integração, 50+ endpoints API

## 3. Stack Tecnológica

### 3.1 Nova Arquitetura (em construção)

| Camada | Tecnologia | Versão |
|---|---|---|
| Frontend | React + TypeScript + Vite | 19 / 5.7 / 6 |
| Estilização | Tailwind CSS + Shadcn UI | 4 / latest |
| API Backend | NestJS + Firebase Admin SDK | 11 |
| Database | Firestore (NoSQL) | — |
| Autenticação | Firebase Auth (email, Google, anônimo) | 11 |
| Storage | Firebase Storage | — |
| PDF | Puppeteer | 24 |
| Orquestração | Turborepo + pnpm | 2.10 / 11 |
| Cache | React Query (frontend) + Firestore | 5 |
| Integrações | Groq IA, PNCP, BrasilAPI, ViaCEP, Mercado Livre | — |
| Hospedagem | Firebase + Cloud SQL (planejado) | — |

### 3.2 Sistema Legado (Laravel — manter como referência)

| Camada | Tecnologia | Versão |
|---|---|---|
| Backend | Laravel | 13.x |
| Linguagem | PHP | 8.3+ |
| Frontend | Blade + Vite + JavaScript vanilla | — |
| Banco de dados | MySQL | 8.x (Dbaas remoto) |
| Autenticação | Laravel Breeze | 2.x |
| PDF | DomPDF (barryvdh/laravel-dompdf) | 3.x |
| Hospedagem | Produção: https://project.byrees.com/sistemaphpgestao | — |

## 4. Arquitetura do Sistema

### 4.1 Nova Arquitetura (NestJS + React + Firebase)

```
┌────────────────────────────────────────────────────────────┐
│                    React SPA (Vite)                         │
│  React Router + React Query + Tailwind 4 + Shadcn UI       │
│  TypeScript estrito                                         │
└─────────┬──────────────────────────────────────────────────┘
           │ Firebase Client SDK (leituras)
           │ Axios (operações complexas)
           │
┌──────────▼──────────────────────────────────────────────────┐
│                 Firebase (Google Cloud)                      │
│  ┌──────────────┐  ┌──────────────┐  ┌───────────────────┐ │
│  │ Firebase Auth │  │  Firestore   │  │ Firebase Storage  │ │
│  │ (JWT + roles) │  │  (NoSQL)     │  │ (arquivos/fotos)  │ │
│  └──────────────┘  └──────┬───────┘  └───────────────────┘ │
└───────────────────────────┼──────────────────────────────────┘
                            │ Firebase Admin SDK
┌───────────────────────────▼──────────────────────────────────┐
│                 NestJS API (Node/TypeScript)                  │
│  Modules: Auth, Users, Institutions, Integrations, Seed      │
│  Common: Guards (3), Decorators (3), Interceptors, Filters   │
│  Integrations: BrasilAPI, ViaCEP                             │
└──────────────────────────────────────────────────────────────┘
```

### 4.2 Arquitetura Legado (Laravel — manter referência)

O sistema original segue o padrão MVC (Model-View-Controller) do Laravel:

```
┌─────────────────────────────────────────────────────────┐
│                    Browser (Cliente)                     │
│         Blade Templates + Vite + JavaScript              │
└──────────────────────┬──────────────────────────────────┘
                       │ HTTPS
┌──────────────────────▼──────────────────────────────────┐
│                   Laravel Application                    │
│  └─────────────┘  └──────┬──────┘  │  ViaCEP)       │ │
│                          │         └─────────────────┘ │
│                    Eloquent ORM                         │
│                    Query Builder                         │
│                    Validation                            │
│                    Middleware (auth, throttle)           │
└──────────────────────┬──────────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────────┐
│                    MySQL Database                        │
│         42 migrations | 18+ tabelas principais           │
└─────────────────────────────────────────────────────────┘
                       │
                       │ HTTP/REST
┌──────────────────────▼──────────────────────────────────┐
│                Serviços Externos                         │
│  BrasilAPI (CNPJ) │ ViaCEP (CEP) │ PNCP                  │
│  Groq IA (Llama 3.3) │ Mercado Livre/Zoom/Buscapé │ Asaas (futuro) │ Z-API (futuro) │
└─────────────────────────────────────────────────────────┘
```

### 4.2 Fluxo de Dados

1. Request → Middleware (auth, throttle) → Route → Controller
2. Controller → Validação → Service (se necessário) → Model (Eloquent)
3. Model → Query Builder → MySQL
4. Controller → View (Blade) com dados renderizados
5. Response → HTML + assets (Vite) → Browser

Para PDFs:
1. Controller coleta dados via Models
2. View Blade é renderizada como HTML
3. DomPDF converte HTML → PDF
4. Response com download

Para integrações externas:
1. Controller ou Service faz HTTP request
2. Cache com TTL (CNPJ/CEP: 24h)
3. Resposta normalizada retorna ao Controller

### 4.3 Isolamento por Instituição

Todos os Models que pertencem a uma instituição possuem `institution_id`. O escopo de dados é aplicado via:

- **Middleware de autenticação**: apenas usuários autenticados acessam o sistema.
- **Checks inline nos Controllers**: `ensureAccess()`, `scope()`, `isAdmin()` — usuário vê apenas dados da sua instituição, exceto ADMIN_GERAL.
- **AuditLog**: toda ação crítica registra `user_id`, `ip`, dados (antes/depois em JSON).

## 5. Modelo de Dados

### 5.1 Diagrama Relacional Simplificado

```
users
  ├─→ institutions (via institution_id, exceto ADMIN_GERAL)
  ├─→ audit_logs (user_id)
  ├─→ projects (user_id = criador)
  │    ├─→ goals
  │    │    ├─→ activities
  │    │    ├─→ goal_proofs
  │    │    ├─→ goal_approvals
  │    │    └─→ diligence_meta_parecer (via diligences)
  │    ├─→ expenses
  │    ├─→ documents
  │    ├─→ accounting_reports
  │    ├─→ diligences
  │    ├─→ budget_items
  │    ├─→ execution_locations
  │    ├─→ specific_objectives
  │    ├─→ team_members
  │    ├─→ contracted_services
  │    ├─→ capability_photos
  │    ├─→ price_researches
  │    │    └─→ price_research_results
  │    ├─→ project_report_selection
  │    ├─→ project_compliance_selection
  │    └─→ project_notifications
  ├─→ institutions (como admin, via role ADMIN_INSTITUICAO)
  └─→ institutions_project_history

institutions
  ├─→ directors
  ├─→ projects
  ├─→ price_researches
  └─→ users (via institution_id)

funding_sources
  └─→ projects

settings (tabela singleton, config global do sistema)

jobs (fila de tarefas assíncronas)
```

### 5.2 Tabelas Principais

#### `users` (extends Laravel default)

| Campo | Tipo | Descrição |
|---|---|---|
| name | string | Nome completo |
| email | string | Login único |
| password | string | Hash bcrypt |
| institution_id | FK nullable | Instituição (nulo para ADMIN_GERAL) |
| role | enum | Papel (ver seção 6) |
| ativo | boolean | Status ativo/inativo |

#### `institutions`

| Campo | Tipo | Descrição |
|---|---|---|
| razao_social | string | Nome legal |
| cnpj | string | CNPJ único |
| email | string | E-mail institucional |
| telefone | string | Contato |
| endereco | json | Logradouro, número, bairro, cidade, UF, CEP |
| banco_dados | json | Dados bancários para repasse |
| utilidade_publica | boolean | Declaração de utilidade pública |
| active | boolean | Status ativa/inativa |

#### `projects`

| Campo | Tipo | Descrição |
|---|---|---|
| institution_id | FK | Instituição proponente |
| funding_source_id | FK | Fonte de recurso |
| nome | string | Nome do projeto |
| codigo | string | Código interno/externo |
| numero_proposta | string | Número da proposta no financiador |
| fonte | string | Origem do recurso |
| parlamentar | string | Emenda parlamentar (se aplicável) |
| secretaria | string | Secretaria responsável |
| descricao | text | Descrição detalhada |
| objetivo_geral | text | Objetivo principal |
| publico_alvo | string | Público beneficiado |
| quantidade_publico | int | Número de beneficiários |
| valor_total | decimal | Valor aprovado |
| valor_recebido | decimal | Valor já recebido |
| valor_executado | decimal | Valor executado |
| data_inicio | date | Data de início |
| data_fim | date | Data de término |
| status | enum | RASCUNHO, EM_ANALISE, APROVADO, EM_EXECUCAO, SUSPENSO, FINALIZADO, PRESTACAO_CONTAS, PRESTACAO_APROVADA, PRESTACAO_REPROVADA |
| responsavel | string | Responsável técnico |
| local_execucao | string | Local de execução |

#### `goals`

| Campo | Tipo | Descrição |
|---|---|---|
| project_id | FK | Projeto pai |
| numero | int | Número da meta |
| titulo | string | Título da meta |
| descricao | text | Descrição |
| orcamento | decimal | Orçamento da meta |
| percentual_execucao | float | % de execução física |
| status | enum | Pendente, Em andamento, Concluída |
| afericao | text | Descrição da aferição |

#### `expenses`

| Campo | Tipo | Descrição |
|---|---|---|
| project_id | FK | Projeto |
| institution_id | FK | Instituição |
| categoria | string | Categoria da despesa |
| descricao | text | Descrição |
| valor | decimal | Valor lançado |
| data_gasto | date | Data da despesa |
| comprovante_id | FK nullable | Documento comprobatório |
| fornecedor | string | Fornecedor |
| status | enum | PENDENTE, APROVADO, PAGO |

#### `price_researches`

| Campo | Tipo | Descrição |
|---|---|---|
| institution_id | FK | Instituição |
| project_id | FK nullable | Projeto vinculado |
| user_id | FK | Usuário que criou |
| search_term | string | Termo de busca |
| category | string | Categoria CATMAT/CATSER |
| quantity | float | Quantidade |
| unit | string | Unidade de medida |
| sources | json | Fontes: PNCP, RADAR_TCE_MT |
| state | string | UF |
| city | string | Município |
| date_start | date | Data inicial da pesquisa |
| date_end | date | Data final |
| min_price | decimal | Menor preço encontrado |
| max_price | decimal | Maior preço |
| average_price | decimal | Média |
| median_price | decimal | Mediana |
| reference_type | enum | MENOR, MAIOR, MEDIA, MEDIANA, MANUAL, ITEM |
| selected_reference_price | decimal | Preço de referência escolhido |
| justification | text | Justificativa da escolha |
| status | enum | RASCUNHO, BUSCADA, COM_RESULTADOS, SEM_RESULTADOS, SELECIONADA, FINALIZADA, CANCELADA |

#### `audit_logs`

| Campo | Tipo | Descrição |
|---|---|---|
| user_id | FK | Usuário executor |
| acao | string | CREATE, UPDATE, DELETE, SEARCH, SELECT_PRICE, etc. |
| entidade | string | Nome do modelo (ex: PriceResearch) |
| entidade_id | int | ID do registro |
| dados | json | Before/after ou payload |
| ip | string | IP do usuário |

## 6. Papéis e Permissões (RBAC)

| Papel | Descrição | Acesso |
|---|---|---|
| ADMIN_GERAL | Administrador global | Acesso total a todas as instituições, configurações do sistema, diagnóstico |
| ADMIN_INSTITUICAO | Administrador da instituição | CRUD completo da sua instituição, projetos, usuários da sua org |
| GESTOR_PROJETO | Gestor de projeto | Gerencia projetos, metas, despesas, documentos da sua instituição |
| FISCAL_PROJETO | Fiscal de projeto | Visualiza projetos, diligências, pode aprovar/desaprovar metas |
| CONSELHO_FISCAL_1 | Membro do conselho fiscal | Visualiza relatórios, diligências, prestação de contas |
| CONSELHO_FISCAL_2 | Membro do conselho fiscal | Idem |
| CONSELHO_FISCAL_3 | Membro do conselho fiscal | Idem |
| FISCAL_EXTERNO | Fiscal externo | Apenas visualização de projetos e documentos |

### Controle de Acesso Implementado

- **Middleware auth**: todas as rotas protegidas.
- **Throttle**: APIs de integração limitadas a 30 req/min.
- **Inline nos Controllers**: `isAdmin()`, `isInstAdmin()`, `ensureAccess()` — verificam se o usuário pertence à instituição do registro.
- **Escopo padrão**: queries filtram por `institution_id` do usuário logado.

## 7. Módulos do Sistema

### 7.1 Dashboard

- **Controller**: `DashboardController`
- **View**: `dashboard.blade.php`

Visão central com estatísticas da instituição:
- Total de projetos por status
- Projetos em execução
- Despesas pendentes
- Diligências abertas
- Prestações de contas pendentes
- Atividades recentes

### 7.2 Instituições

- **Controller**: `InstitutionController`
- **Model**: `Institution`, `Director`, `InstitutionProjectHistory`

CRUD completo de organizações:
- Dados cadastrais (CNPJ, endereço, contato, dados bancários)
- Diretores com mandato (cargo, data início/fim, ementa)
- Histórico de projetos anteriores
- Exportação PDF da ficha da instituição
- Ativação/desativação
- Force-delete (hard delete) com usuário específico de exclusão

### 7.3 Projetos

- **Controller**: `ProjectController`
- **Model**: `Project`, `FundingSource`, `ProjectExecutionLocation`, `ProjectSpecificObjective`, `ProjectTeamMember`, `ProjectContractedService`, `ProjectCapabilityPhoto`, `ProjectReportSelection`, `ProjectComplianceSelection`, `ProjectNotification`

O módulo mais rico do sistema. Um projeto representa uma iniciativa financiada com:

**Dados principais:**
- Identificação (nome, código, número da proposta)
- Financiamento (fonte, parlamentar, secretaria, valor total/recebido/executado)
- Ciclo de vida (datas, status em 10 estados)
- Detalhamento (descrição, objetivo geral, público alvo, metodologia, riscos)

**Relacionamentos aninhados:**
- Fontes de recurso (`FundingSource`): instrumento/convênio, valor, período
- Objetivos específicos (`ProjectSpecificObjective`): metas secundárias com ordenação
- Locais de execução (`ProjectExecutionLocation`): múltiplas cidades/UF
- Equipe (`ProjectTeamMember`): membros com função e ordenação
- Serviços contratados (`ProjectContractedService`): tipo PF/PJ, período, valor
- Fotos de capacidade técnica (`ProjectCapabilityPhoto`): evidências técnicas
- Relatório selecionável (`ProjectReportSelection`): escolha de itens para relatório
- Compliance selecionável (`ProjectComplianceSelection`): itens de conformidade
- Notificações (`ProjectNotification`): avisos do projeto

**Funcionalidades:**
- CRUD completo
- Exportação PDF do inventário do projeto
- Filtros por instituição, status, busca textual
- Vinculação com metas, despesas, documentos, diligências, prestação de contas

### 7.4 Metas e Atividades

- **Controller**: `GoalController`
- **Models**: `Goal`, `Activity`, `GoalProof`, `GoalApproval`

Estrutura hierárquica dentro de projetos:

**Metas (`Goal`):**
- Número, título, descrição, orçamento
- Percentual de execução física
- Status (Pendente, Em andamento, Concluída)
- Aferição (descrição da verificação)

**Atividades (`Activity`):**
- Subtarefas dentro de metas

**Comprovação (`GoalProof`):**
- Fotos e anexos como evidência de execução
- Upload de arquivos

**Aprovação (`GoalApproval`):**
- Sistema de aprovação/desaprovação de metas
- Usuário aprovador, data, observação

**Fluxo:**
1. Meta criada → status PENDENTE
2. Usuário envia para análise → evidências anexadas
3. Fiscal aprova/desaprova → status atualizado
4. Meta aprovada pode ser enviada para Prestação de Contas

### 7.5 Despesas

- **Controller**: `ExpenseController`
- **Model**: `Expense`, `BudgetItem`

CRUD de gastos vinculados a projetos:
- Categoria, descrição, valor, data do gasto
- Fornecedor
- Comprovante (FK para `documents`)
- Status: PENDENTE → APROVADO → PAGO
- Filtros por projeto, categoria, período
- Visão financeira agregada

### 7.6 Documentos

- **Controller**: `DocumentController`
- **Model**: `Document`

Repositório de arquivos:
- Upload para `storage/app/public`
- Categorias: JURIDICO, PROJETO, RELATORIO
- Status de análise
- Vinculação a projetos
- Download e visualização

### 7.7 Diligências

- **Controller**: `DiligenceController`
- **Model**: `Diligence`

Notificações de conformidade com:
- Tipo: DOCUMENTAL, TECNICA, FINANCEIRA
- Projeto e meta vinculados
- Prazo de resposta
- Status (aberta, respondida, vencida)
- Resposta e parecer por meta
- Observações

**Fluxo:**
1. Sistema/admin cria diligência para projeto/meta
2. Notificação enviada ao responsável
3. Responsável responde dentro do prazo
4. Fiscal analisa resposta → aprova ou reabre

### 7.8 Prestação de Contas

- **Controller**: `AccountingReportController`
- **Model**: `AccountingReport`

Relatórios financeiros enviados ao financiador:
- Vinculação a projeto
- Período de competência
- Fotos comprobatórias
- Status: PENDENTE, APROVADA, REPROVADA
- Remoção de fotos
- Exportação PDF

### 7.9 Pesquisa de Preços (Cotação)

- **Controller**: `PriceResearchController`
- **Models**: `PriceResearch`, `PriceResearchResult`
- **Services**: `PriceResearchAggregator`, `PncpPriceService`, `RadarTceMtPriceService`

Módulo de cotações com múltiplas fontes:

**Funcionalidades:**
- CRUD de pesquisas de preço
- Busca em PNCP (fonte oficial de contratações públicas)
- Busca em Radar TCE-MT (portal de transparência)
- Agregador de mercado (Zoom + Buscapé)
- Entrada manual de resultados
- Seleção de preço de referência (menor, maior, média, mediana, manual, item)
- Justificativa obrigatória para escolha do preço
- Estatísticas automáticas (min, max, avg, median)
- Exportação PDF do relatório comparativo
- Status do fluxo: RASCUNHO → BUSCADA → COM_RESULTADOS / SEM_RESULTADOS → SELECIONADA → FINALIZADA / CANCELADA

### 7.10 Auditoria

- **Controller**: `AuditController`
- **Model**: `AuditLog`

Log completo de ações:
- Usuário executor, IP, timestamp
- Ação: CREATE, UPDATE, DELETE, SEARCH, SELECT_PRICE, EXPORT_PDF, etc.
- Entidade e entidade_id
- Dados (JSON com before/after ou payload)

### 7.11 Relatórios

- **Controller**: `ReportController`
- **Model**: Report (implícito via views)

Geração de relatórios consolidados:
- Seleção de itens por projeto
- Exportação PDF via DomPDF
- Filtros por período, instituição, projeto

### 7.12 Configurações

- **Controller**: `SettingController`
- **Model**: `Setting`

Painel admin global:
- Configuração SMTP para envio de e-mails
- Chaves de API (Asaas, Z-API, D4Sign, Pluggy)
- Configurações de integração

### 7.13 Diagnóstico

- **Controller**: `DiagnosticController`

Painel técnico para ADMIN_GERAL:
- Status das migrations
- Criação do link simbólico storage
- Limpeza de caches
- Verificação de integridade

## 8. Integrações Externas

### 8.1 BrasilAPI (CNPJ)

- **Controller**: `IntegrationController::cnpj()`
- **Service**: `CnpjService`
- Busca dados de empresas por CNPJ
- Cache de 24h
- Rate limit: 30 req/min
- Preenche automaticamente campos de instituições

### 8.2 ViaCEP (CEP)

- **Controller**: `IntegrationController::cep()`
- **Service**: `CepService`
- Busca endereço por CEP
- Cache de 24h
- Rate limit: 30 req/min

### 8.3 PNCP (Portal Nacional de Contratações Públicas)

- **Service**: `PncpPriceService`
- Consulta contratos e atas de registro de preço
- Fonte oficial para pesquisa de preços em contratações públicas
- Baseada em scraping/API pública do PNCP

### 8.4 Radar TCE-MT

- **Service**: `RadarTceMtPriceService`
- Portal de transparência do Tribunal de Contas de Mato Grosso
- Fonte complementar de dados de contratação

### 8.5 Agregador de Mercado (Zoom + Buscapé)

- **Service**: `MercadoLivre` (no ConsultaPublica)
- HTML scraping de sites de comparação de preços
- Sem autenticação necessária
- Retorna título, preço, URL

### 8.6 Groq IA (Llama 3.3 70B)

- **Service**: `GroqClient` (`app/Services/GroqClient.php`)
- Autocomplete de descrições de produtos (`suggestProductDetails`)
- Padronização de material e categoria CATMAT/CATSER
- Interpretação de texto livre em lote (`interpretBatch` — extrai múltiplos itens de um parágrafo)
- Integrado ao Chat IA na rota `POST /api/chat-ia/processar`
- Usa SDK HTTP puro (Laravel Http facade) contra a API REST da Groq

### 8.7 Agregador de Mercado (Mercado Livre + Zoom + Buscapé)

- **Service**: `MercadoLivrePriceService` (`app/Services/PriceResearch/MercadoLivrePriceService.php`)
- Busca em 3 fontes simultâneas: Mercado Livre API, Zoom (HTML), Buscapé (HTML)
- Retorna título, preço, URL do vendedor
- Integrado ao Chat IA para enriquecer cotações com preços de mercado

### 8.8 Integrações Planejadas (não implementadas)

| Serviço | Função | Status |
|---|---|---|
| Asaas | Pagamentos e cobranças | Placeholder no `.env.example` |
| Z-API | WhatsApp Business API | Placeholder no `.env.example` |
| D4Sign | Assinatura digital qualificada | Placeholder no `.env.example` |
| Pluggy | Open Finance (conciliação bancária) | Placeholder no `.env.example` |
| Google Maps | Geolocalização de execução | Planejado |

## 9. Módulo Chat IA para Pesquisa de Preços ✅

> Implementado no commit `168e87d` — Sprint 3 concluída.

### 9.1 Visão Geral

Este módulo incorpora a tecnologia desenvolvida no projeto ConsultaPublica (`D:\Dev\ConsultaPublica`) ao sistema Gestão Terceiro Setor. Trata-se de um assistente conversacional por chat que utiliza IA generativa (Groq / Llama 3.3 70B) para automatizar a criação de pesquisas de preços a partir de texto livre em linguagem natural.

### 9.2 Problema Específico Resolvido

O módulo anterior de Pesquisa de Preços (`PriceResearchController`) funcionava de forma assíncrona e discreta: o usuário preenchia um formulário, clicava em "Buscar" e aguardava resultados. Isso funcionava para casos simples, mas falhava em cenários onde:

- O usuário precisava cotar múltiplos itens de uma só vez (ex.: "Cota 5 cotações de cada item: Bola MAX 200, Bola de Vôlei Penalty VP500, Rede de Vôlei 4 faixas...")
- O usuário não sabia o termo exato de busca no PNCP
- O usuário precisava de padronização de descrição e material (CATMAT/CATSER)
- O usuário queria um fluxo mais fluido, sem preencher formulários extensos

O Chat IA resolve isso permitindo que o usuário escreva em linguagem natural e o sistema:

1. Interpreta o texto via IA e extrai a lista de itens com descrição, quantidade e material.
2. Busca automaticamente em PNCP + Mercado Livre + Zoom + Buscapé para cada item.
3. Apresenta os resultados em painel visual com cotações ranqueadas.
4. Permite seleção de cotações válidas (checkboxes) e inclusão de orçamentos manuais.

### 9.3 Arquitetura do Chat IA

O módulo foi implementado como uma API REST dentro do Laravel, com frontend em AJAX e Bootstrap 5.

```
┌─────────────────────────────────────────────────────────┐
│                  Frontend (Blade + JS)                   │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────┐ │
│  │ Chat Input  │  │  Painel de  │  │  Modal de       │ │
│  │ (texto livre)│  │  Resultados │  │  Seleção        │ │
│  └──────┬──────┘  └──────┬──────┘  └────────┬────────┘ │
│         │                │                  │           │
│         └────────────────┼──────────────────┘           │
│                          │ POST /api/chat-ia/processar  │
└──────────────────────────┼──────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────┐
│              Laravel (ChatIaController)                   │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────┐ │
│  │ Interpretar │  │   Buscar    │  │   Responder     │ │
│  │   Lote IA   │→ │  PNCP+Mercado│→ │   JSON ao FE    │ │
│  │ (Groq SDK)  │  │ (Services)  │  │                 │ │
│  └─────────────┘  └─────────────┘  └─────────────────┘ │
└──────────────────────────┬──────────────────────────────┘
                           │
              ┌────────────┼────────────┐
              │            │            │
    ┌─────────▼─────┐ ┌────▼─────┐ ┌───▼──────────┐
    │ Groq API      │ │ PNCP     │ │ Mercado      │
    │ (Llama 3.3)   │ │ Service  │ │ Livre/Zoom   │
    └───────────────┘ └──────────┘ └──────────────┘
```

### 9.4 Fluxo do Usuário

**Passo 1 — Entrada por Chat**

O usuário acessa o módulo de Pesquisa de Preços e vê uma interface de chat. Ele digita:

> "Cota 5 cotações de cada item: Bola MAX 200, Bola de Vôlei Penalty VP500, Rede de Volei 4 faixas, Coletes salva-vidas, Cinta de natação"

**Passo 2 — Interpretação por IA (Groq)**

O sistema envia o texto para o Groq com o prompt:

```
Você é um assistente que interpreta pedidos de cotação de preços.
Extraia a lista de itens a cotar, normalizando as descrições.

Texto: "Cota 5 cotações de cada item: Bola MAX 200, Bola de Vôlei Penalty VP500..."

Responda JSON:
{"itens": [
  {"descricao": "BOLA DE FUTEBOL CAMPO MAX 200", "quantidade": 5, "material": "PU"},
  {"descricao": "BOLA DE VOLEI PENALTY VP500", "quantidade": 5, "material": "PU"},
  ...
]}
```

O Groq retorna a lista estruturada.

**Passo 3 — Busca Automatizada**

Para cada item, o sistema executa em paralelo:
- Busca PNCP: `PncpPriceService::search($descricao, $filtros)`
- Busca Mercado: `MercadoLivre::search($descricao, $limit=6)`

Os resultados são normalizados em `PriceResearchResult` (model existente).

**Passo 4 — Apresentação no Painel**

Os resultados aparecem em cards/tabela:

| Item | Descrição | PNCP (até 3) | Mercado (até 6) | Ações |
|---|---|---|---|---|
| 1 | Bola MAX 200 | R$ 45,00 (contrato X) | R$ 42,90 - R$ 55,00 | Selecionar / Adicionar manual |

Cada card mostra:
- Estatísticas (min, max, média, mediana)
- Ordenação por preço (maior → menor)
- Checkbox para selecionar cotações válidas

**Passo 5 — Seleção e Orçamento Manual**

O usuário pode:
- **Selecionar cotações**: marcar checkboxes nas cotações que considera válidas.
- **Adicionar orçamento manual**: clicar em "Adicionar manual" e preencher:
  - CNPJ da empresa
  - Nome do item
  - Valor
  - Upload de arquivo (PDF/imagem do orçamento externo)

O orçamento manual é salvo como `PriceResearchResult` com `source = MANUAL`.

**Passo 6 — Finalização**

Após selecionar todas as cotações desejadas:
1. O sistema calcula estatísticas atualizadas
2. O usuário define preço de referência (MENOR, MAIOR, MEDIA, MEDIANA, MANUAL, ITEM)
3. Justificativa obrigatória
4. Status muda para FINALIZADA
5. PDF é gerado automaticamente com o relatório comparativo

### 9.5 Modelo de Dados (Integração com Sistema Existente)

O módulo reutiliza as tabelas existentes:

- **`price_researches`** — Cabeçalho da pesquisa
  - Criado automaticamente pelo chat a partir da interpretação do texto
  - `search_term` = descrição principal do lote
  - `status` inicia como RASCUNHO → BUSCADA → COM_RESULTADOS

- **`price_research_results`** — Resultados individuais
  - Cada cotação do PNCP ou mercado vira um registro
  - `source` = PNCP, RADAR_TCE_MT, MANUAL
  - `selected` = boolean (checkbox do usuário)
  - `selection_justification` = texto livre

**Novos campos em `price_research_results` (migration `2026_07_20_150000`):**

```php
Schema::table('price_research_results', function (Blueprint $table) {
    $table->string('cnpj_fornecedor')->nullable()->after('buyer_cnpj');
    $table->string('item_descricao')->nullable()->after('original_description');
    $table->string('anexo_path')->nullable()->after('source_url');
    $table->text('observacoes')->nullable()->after('selection_justification');
});
```

### 9.6 API Endpoints (Implementados)

| Método | Rota | Função |
|---|---|---|
| POST | `/api/chat-ia/processar` | Recebe texto livre, retorna lista de itens interpretados + cotações |
| POST | `/api/chat-ia/selecionar` | Marca/desmarca cotações como selecionadas |
| POST | `/api/chat-ia/orcamento-manual` | Adiciona orçamento manual com upload de arquivo |
| GET | `/api/chat-ia/status/{pesquisa_id}` | Retorna status atual da pesquisa com resultados |

### 9.7 Regras de Negócio

- **Interpretação em lote**: IA processa até 20 itens por requisição.
- **Busca paralela**: PNCP + mercado executam em paralelo via `Http::pool()` ou jobs de fila.
- **Seleção múltipla**: usuário pode selecionar até 3 cotas por item (conforme regra original).
- **Orçamento manual**: requer CNPJ válido (formatação automática) e anexo opcional.
- **Validação de CNPJ**: deve passar por validação de dígitos verificadores antes de salvar.
- **Anexos**: salvos em `storage/app/public/orcamentos-manuais/` com nome único (UUID).
- **Finalização**: bloqueada se nenhuma cotação estiver selecionada e nenhum orçamento manual adicionado.
- **Auditoria**: todas as ações no chat são logadas (interpretação, seleção, adição manual, finalização).

### 9.8 Componentes do Frontend

**Interface de Chat:**
- Campo de texto com placeholder: "Descreva os itens para cotação..."
- Botão "Processar com IA"
- Indicador de loading com animação enquanto IA processa

**Painel de Resultados:**
- Cards por item com:
  - Descrição normalizada
  - Quantidade
  - Tabela de cotações PNCP (até 3)
  - Tabela de referências de mercado (até 6)
  - Estatísticas calculadas
  - Checkbox de seleção por cotação

**Modal de Orçamento Manual:**
- Campos: CNPJ (com máscara), Razão social (auto-preenchido via BrasilAPI), Item, Valor, Data, Observações
- Upload de arquivo (PDF, JPG, PNG — max 10MB)
- Preview do arquivo antes de salvar

**Fluxo de Finalização:**
- Botão "Finalizar Pesquisa"
- Modal de confirmação com resumo dos itens e cotações selecionadas
- Campo de justificativa
- Geração automática de PDF

## 10. Fluxos Principais do Sistema

### 10.1 Fluxo de Projeto (Lifecycle)

```
RASCUNHO → EM_ANÁLISE → APROVADO → EM_EXECUÇÃO → SUSPENSO → FINALIZADO
                                                              ↓
                                                    PRESTAÇÃO_CONTAS
                                                              ↓
                                                  PRESTAÇÃO_APROVADA
                                                  PRESTAÇÃO_REPROVADA
```

### 10.2 Fluxo de Meta/Atividade

```
Criação → Pendente → Enviado para Análise
                              ↓
                        Aprovado / Desaprovado
                              ↓
                        Comprovação (fotos + anexos)
                              ↓
                        Aprovação final
                              ↓
                        Enviado para Prestação de Contas
```

### 10.3 Fluxo de Despesa

```
Lançamento → PENDENTE → APROVADO → PAGO
                    ↓
               REPROVADO (volta para PENDENTE)
```

### 10.4 Fluxo de Diligência

```
Criação (DOCUMENTAL/TÉCNICA/FINANCEIRA) → ABERTA
                                              ↓
                                    Resposta do responsável
                                              ↓
                                    Parecer do fiscal
                                              ↓
                                    FECHADA / REABERTA
```

### 10.5 Fluxo de Pesquisa de Preços

```
RASCUNHO → [Busca] → BUSCADA → COM_RESULTADOS / SEM_RESULTADOS
                                                    ↓
                                              Seleção de referência
                                                    ↓
                                              SELECIONADA → FINALIZADA
                                                                  ↓
                                                          Exportação PDF
```

### 10.6 Fluxo do Chat IA (Novo)

```
Texto Livre → [IA Interpreta] → Lista de Itens
                                       ↓
                            Busca Paralela (PNCP + Mercado)
                                       ↓
                            Painel com Cotações + Estatísticas
                                       ↓
                            Seleção de Cotações + Orçamento Manual
                                       ↓
                            Definição de Preço de Referência + Justificativa
                                       ↓
                            FINALIZADA → Exportação PDF
```

## 11. Infraestrutura e Deploy

### 11.1 Ambiente de Produção

| Item | Configuração |
|---|---|
| URL | https://project.byrees.com/sistemaphpgestao |
| Banco | MySQL remoto em Dbaas (gestao3setor.mysql.dbaas.com.br) |
| Armazenamento | `storage/app/public` com link simbólico para `public/storage` |
| Cache | File cache (configurável para Redis) |
| Filas | Database driver (tabela `jobs`) |
| Logs | `storage/logs/laravel.log` + Pail (logs em tempo real) |

### 11.2 Variáveis de Ambiente

```ini
APP_NAME=Gestão Terceiro
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://project.byrees.com/sistemaphpgestao

DB_CONNECTION=mysql
DB_HOST=gestao3setor.mysql.dbaas.com.br
DB_PORT=3306
DB_DATABASE=gestao3setor
DB_USERNAME=...
DB_PASSWORD=...

MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=...

# Integrações planejadas (placeholders)
ASAAS_API_KEY=...
ZAPI_TOKEN=...
D4SIGN_API_KEY=...
PLUGGY_CLIENT_ID=...
PLUGGY_CLIENT_SECRET=...
GOOGLE_MAPS_KEY=...

# IA (Chat IA — implementado)
GROQ_API_KEY=gsk_...
GROQ_MODEL=llama-3.3-70b-versatile
```

### 11.3 Deploy

- **Produção**: deploy manual via FTP/SSH ou painel do hosting
- **Migrations**: executadas via `php artisan migrate --force`
- **Storage link**: `php artisan storage:link`
- **Cache**: `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`
- **Manutenção**: `php artisan down` / `php artisan up`

### 11.4 Monitoramento

- **Logs**: Pail (`php artisan pail`) para tail em tempo real
- **Erros**: `storage/logs/laravel.log` (diário)
- **Banco**: monitoramento via painel Dbaas
- **Uptime**: dependente do hosting

## 12. Observações Técnicas e Dívidas Técnicas

### 12.1 Pendências Conhecidas

- **Testes automatizados**: PHPUnit está configurado no `composer.json` e `phpunit.xml`, mas não há suites de teste implementadas. Nenhum teste unitário ou de feature foi escrito.
- **Filas**: O driver de fila está configurado como `database` (tabela `jobs`), porém nenhum job foi implementado. Todas as operações são síncronas.
- **Notificações**: O sistema não envia e-mails nem notificações em tempo real. O Mailgun está configurado no `config/mail.php` como fallback, mas sem implementação de `Notification` classes ou `Mail` classes.
- **Cache Redis**: Configurado como opcional, mas não implementado. Atualmente usando file cache.
- **Integrações**: As chaves de API para Asaas, Z-API, D4Sign, Pluggy existem como placeholders no `.env.example`, mas nenhuma integração está implementada além de BrasilAPI, ViaCEP, PNCP e Groq IA.

### 12.2 Padrões de Código

- **Controllers**: Todos estendem `Controller` base do Laravel. Usam `ensureAccess()` e `scope()` para controle de acesso inline.
- **Models**: Usam `HasFactory`, `SoftDeletes` (exceto tabelas de junção). Relacionamentos definidos com `belongsTo`, `hasMany`, `morphMany`.
- **Migrations**: Convenção `YYYY_MM_DD_HHMMSS_create_X_table.php`. Usam `up()` e `down()` completos.
- **Views**: Blade templates com sections (`@extends`, `@section`, `@push`). Usam Bootstrap 5 para estilização.
- **Rotas**: Agrupadas por prefixo `/admin`, protegidas por middleware `auth`. Rotas de API sem prefixo adicional.

### 12.3 Segurança

- Senhas com hash bcrypt (default Laravel)
- CSRF protection ativo em todas as rotas web
- Validação de CNPJ com cálculo de dígitos verificadores
- Rate limiting em rotas de integração (30 req/min)
- Soft deletes em todas as tabelas principais (exceto logs de auditoria)

### 12.4 Próximos Passos Prioritários (Sistema Legado)

1. **Deploy em produção** — contatar cliente (FTP/SSH), enviar ao servidor, rodar migrations
2. **Escrever testes** — começar com feature tests para os controllers principais (ProjectController, PriceResearchController)
3. **Implementar filas** — migrar buscas PNCP e mercado para jobs assíncronos
4. **Notificações** — implementar Notifications do Laravel para diligências e aprovações
5. **Integrar APIs planejadas** — Asaas (pagamentos), Z-API (WhatsApp), D4Sign (assinatura digital)

---

## 13. Nova Arquitetura — Status das Sprints

### 13.1 Sprints Concluídas (9 de 9) ✅

| Sprint | Módulos | Commits | Status |
|---|---|---|---|
| **Sprint 1** | Fundação e Setup | `5513db9`, `c13210e` | ✅ **100%** |
| **Sprint 2** | Autenticação e Usuários | `0b2fb2c` | ✅ **100%** |
| **Sprint 3** | Instituições | `e624d7a`, `008a81d` | ✅ **100%** |
| **Sprint 4** | Projetos | `9bb5621` | ✅ **100%** |
| **Sprint 5** | Metas, Atividades e Despesas | `551771a` | ✅ **100%** |
| **Sprint 6** | Documentos, Diligências e Prestação de Contas | `c326380` | ✅ **100%** |
| **Sprint 7** | Pesquisa de Preços + Chat IA | `cd7eb70` | ✅ **100%** |
| **Sprint 8** | Auditoria, Relatórios, Configurações | `51d1097` | ✅ **100%** |
| **Sprint 9** | Diagnóstico, Deploy e Polimento | `018e989` | ✅ **100%** |

### 13.2 Estrutura do Monorepo

```
/gestao-terceiro-setor
├── apps/
│   ├── api/          # NestJS + Firebase Admin SDK
│   └── web/          # React + Vite + Tailwind 4 + Shadcn UI
├── packages/
│   └── shared/       # Enums, interfaces, DTOs compartilhados
├── firebase/         # Firestore + Storage rules (deployed)
├── scripts/          # Seed, utilitarios
├── docker-compose.yml
├── turbo.json
└── package.json      # pnpm workspace
```

### 13.3 Módulos Implementados (NestJS)

| Módulo | Rotas | Status |
|---|---|---|
| **AuthModule** | `POST /api/auth/sync`, `POST /api/auth/me` | ✅ |
| **UsersModule** | `GET /api/users`, `GET /api/users/:uid`, `PUT /api/users/:uid`, `PATCH /api/users/:uid/role` | ✅ |
| **InstitutionsModule** | CRUD `/api/institutions` + directors + project-history (8 rotas) | ✅ |
| **IntegrationsModule** | `GET /api/integrations/cnpj/:cnpj`, `GET /api/integrations/cep/:cep` | ✅ |
| **SeedModule** | `POST /api/seed/admin` | ✅ |
| **ProjectsModule** | CRUD `/api/projects` + generate-code + sub-entities (12 rotas) | ✅ |
| **FundingSourcesModule** | CRUD `/api/funding-sources` | ✅ |
| **GoalsModule** | CRUD `/api/projects/:id/goals` + workflow (9 rotas) | ✅ |
| **ExpensesModule** | CRUD `/api/projects/:id/expenses` + status workflow | ✅ |
| **BudgetItemsModule** | CRUD `/api/projects/:id/budget-items` | ✅ |
| **DocumentsModule** | CRUD `/api/projects/:id/documents` + Firebase Storage | ✅ |
| **DiligencesModule** | CRUD `/api/projects/:id/diligences` + respond/close/reopen | ✅ |
| **AccountingModule** | CRUD `/api/projects/:id/accounting` + photos | ✅ |
| **PriceResearchModule** | CRUD `/api/price-research` + results + select + reference | ✅ |
| **ChatIaModule** | `POST /api/chat-ia/processar`, `selecionar`, `orcamento-manual`, `status` | ✅ |
| **IntegrationsServicesModule** | GroqClient, PncpService, MercadoLivreService | ✅ |
| **AuditModule** | `GET /api/audit` com filtros (userId, acao, entidade, data) | ✅ |
| **SettingsModule** | `GET/PUT /api/settings` (config global) | ✅ |
| **DashboardModule** | `GET /api/dashboard/stats` (agregacao) | ✅ |
| **ReportsModule** | `GET /api/reports/project/:id` (consolidado) | ✅ |

### 13.4 Páginas Implementadas (React)

| Rota | Página | Status |
|---|---|---|
| `/login` | LoginPage (email/senha + Google) | ✅ |
| `/register` | RegisterPage | ✅ |
| `/forgot-password` | ForgotPasswordPage | ✅ |
| `/dashboard` | DashboardPage (stats + atividades recentes) | ✅ |
| `/perfil` | ProfilePage (editar nome, senha, tema) | ✅ |
| `/usuarios` | UsersPage (admin: listar, buscar, alterar papel) | ✅ |
| `/instituicoes` | InstitutionsListPage (busca + paginacao) | ✅ |
| `/instituicoes/nova` | InstitutionFormPage (4 etapas, autocomplete) | ✅ |
| `/instituicoes/:id` | InstitutionDetailPage (6 abas) | ✅ |
| `/instituicoes/:id/editar` | InstitutionFormPage (edicao) | ✅ |
| `/projetos` | ProjectsListPage (filtros + busca) | ✅ |
| `/projetos/novo` | ProjectFormPage (wizard 6 etapas) | ✅ |
| `/projetos/:id` | ProjectDetailPage (5 abas + metas + financeiro) | ✅ |
| `/projetos/:id/editar` | ProjectFormPage (edicao) | ✅ |
| `/financeiro` | ExpensesListPage (filtro status + acao inline) | ✅ |
| `/despesas/nova` | ExpenseFormPage | ✅ |
| `/documentos` | DocumentsPage (grid + upload Firebase Storage) | ✅ |
| `/diligencias` | DiligencesPage (timeline + resposta + parecer) | ✅ |
| `/prestacao-contas` | AccountingPage (relatorios + fotos) | ✅ |
| `/pesquisa-precos` | PriceResearchListPage (filtros + busca) | ✅ |
| `/pesquisa-precos/chat-ia` | ChatIaPage (conversacional + resultados) | ✅ |
| `/auditoria` | AuditPage (tabela + filtros) | ✅ |
| `/relatorios` | ReportsPage (consolidado por projeto) | ✅ |
| `/configuracoes` | SettingsPage (SMTP + API Keys) | ✅ |

### 13.5 Common Layer (NestJS)

- **Guards**: FirebaseAuthGuard, RolesGuard, InstitutionAccessGuard
- **Decorators**: @CurrentUser, @Roles, @Public
- **Interceptors**: AuditLogInterceptor (global)
- **Filters**: AllExceptionsFilter
- **Pipes**: AppValidationPipe

### 13.6 Firebase Deployed

| Recurso | Status |
|---|---|
| Firebase Auth (email + Google + anonimo) | ✅ Ativo |
| Firestore Rules (RBAC completo, 8 papeis) | ✅ Deployed |
| Storage Rules (pastas com limites de tamanho) | ✅ Deployed |
| Firestore Indexes (12 composite indexes) | ✅ Deployed |
| Admin user seed (gestor.renatorosa@gmail.com) | ✅ ADMIN_GERAL |
| Admin user seed (cleitonxadrez@gmail.com) | ✅ ADMIN_GERAL |
| Firebase Hosting | ✅ https://gestaosetor3.web.app |

---

*Documento gerado em 21/07/2026 — Versão 2.3 (9 sprints concluídas — refatoração completa)*
