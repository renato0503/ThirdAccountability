# Plano de Refatoração Completo: Laravel → NestJS + React + Firebase

> Baseado no código atual (46 migrations, 29 models, 32 controllers, 63 views, 7 services)

---

## Stack Final

| Camada | Tecnologia |
|---|---|
| Frontend | React 19 + TypeScript + Vite |
| Estilização | Tailwind CSS 4 + Shadcn UI |
| API Backend | NestJS (TypeScript) + Firebase Admin SDK |
| Database | Firestore (NoSQL) |
| Autenticação | Firebase Auth |
| Storage | Firebase Storage |
| PDF | Puppeteer |
| Cache | Firestore (leituras otimizadas) + React Query |
| External APIs | Groq IA, PNCP, BrasilAPI, ViaCEP, Mercado Livre, Zoom, Buscapé |

---

## 1. Arquitetura

```
┌────────────────────────────────────────────────────────────┐
│                    React SPA (Vite)                         │
│  React Router + React Query + Tailwind + Shadcn UI         │
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
│                                                              │
│  ┌──────────────────────────────────────────────────────┐    │
│  │  Common Layer                                        │    │
│  │  ├── Guards: FirebaseAuthGuard, RolesGuard            │    │
│  │  ├── Decorators: @CurrentUser, @Roles                │    │
│  │  ├── Interceptors: AuditLogInterceptor               │    │
│  │  └── Filters: GlobalExceptionFilter                  │    │
│  └──────────────────────────────────────────────────────┘    │
│                                                              │
│  ┌──────────────────────────────────────────────────────┐    │
│  │  Modules (14)                                        │    │
│  │  ├── AuthModule          ├── ExpensesModule          │    │
│  │  ├── UsersModule         ├── DocumentsModule         │    │
│  │  ├── InstitutionsModule  ├── DiligencesModule        │    │
│  │  ├── ProjectsModule      ├── AccountingModule        │    │
│  │  ├── GoalsModule         ├── PriceResearchModule     │    │
│  │  │   ├─ Activities       │   └─ ChatIaModule         │    │
│  │  │   ├─ Proofs           ├── AuditModule             │    │
│  │  │   └─ Approvals        ├── ReportsModule           │    │
│  │  └── SettingsModule      └── DiagnosticsModule       │    │
│  └──────────────────────────────────────────────────────┘    │
│                                                              │
│  ┌──────────────────────────────────────────────────────┐    │
│  │  Integrations (6)                                    │    │
│  │  ├── BrasilAPIService    ├── ViaCEPService           │    │
│  │  ├── PncpService         ├── RadarTceMtService       │    │
│  │  ├── MercadoLivreService └── GroqClientService       │    │
│  └──────────────────────────────────────────────────────┘    │
│                                                              │
│  ┌──────────────────────────────────────────────────────┐    │
│  │  PDF Generator (Puppeteer)                           │    │
│  └──────────────────────────────────────────────────────┘    │
└──────────────────────────────────────────────────────────────┘
```

---

## 2. Modelagem dos Dados no Firestore

```
/institutions/{institutionId}
  ├── directors/{directorId}
  ├── projectHistories/{historyId}
  └── priceResearches/{researchId}
        └── results/{resultId}

/projects/{projectId}
  ├── goals/{goalId}
  │     ├── activities/{activityId}
  │     ├── proofs/{proofId}
  │     └── approvals/{approvalId}
  ├── expenses/{expenseId}
  ├── documents/{documentId}
  ├── diligences/{diligenceId}
  ├── accountingReports/{reportId}
  ├── executionLocations/{locId}
  ├── specificObjectives/{objId}
  ├── teamMembers/{memberId}
  ├── contractedServices/{serviceId}
  ├── capabilityPhotos/{photoId}
  ├── reportSelections/{selectionId}
  ├── complianceSelections/{selectionId}
  └── notifications/{notifId}

/users/{userId}

/fundingSources/{fundingSourceId}

/auditLogs/{autoId}          (TTL 90 dias)

/settings/global             (documento único)
```

### Exemplo de Documento — Projeto

```typescript
// projects/{projectId}
{
  id: string;
  institutionId: string;
  institutionName: string;       // denormalizado
  fundingSourceId?: string;
  nome: string;
  codigo: string;
  numeroProposta?: string;
  fonte?: string;
  parlamentar?: string;
  secretaria?: string;
  descricao?: string;
  objetivoGeral?: string;
  publicoAlvo?: string;
  quantidadePublico: number;
  valorTotal: number;
  valorRecebido: number;
  valorExecutado?: number;
  dataInicio: Timestamp;
  dataFim: Timestamp;
  status: ProjectStatus;
  responsavel?: string;
  localExecucao?: string;
  // metadados
  createdAt: Timestamp;
  updatedAt: Timestamp;
  createdBy: string;
  deletedAt?: Timestamp;       // soft delete
}
```

---

## 3. Estrutura do Projeto (Monorepo)

```
/gestao-terceiro-setor
├── apps/
│   ├── api/                        # NestJS + Firebase Admin
│   │   ├── src/
│   │   │   ├── main.ts
│   │   │   ├── app.module.ts
│   │   │   ├── common/
│   │   │   │   ├── guards/         # FirebaseAuthGuard, RolesGuard
│   │   │   │   ├── decorators/     # @CurrentUser, @Roles
│   │   │   │   ├── interceptors/   # AuditLogInterceptor
│   │   │   │   ├── filters/        # AllExceptionsFilter
│   │   │   │   └── pipes/          # ValidationPipe
│   │   │   ├── modules/
│   │   │   │   ├── auth/           # Firebase token verification
│   │   │   │   ├── institutions/   # CRUD + directors + history
│   │   │   │   ├── projects/       # CRUD + subcollections
│   │   │   │   ├── goals/          # + activities, proofs, approvals
│   │   │   │   ├── expenses/       # CRUD + status workflow
│   │   │   │   ├── documents/      # Upload/download metadata
│   │   │   │   ├── diligences/     # Workflow
│   │   │   │   ├── accounting/     # Financial reports
│   │   │   │   ├── price-research/ # CRUD + chat-ia
│   │   │   │   ├── users/          # User management
│   │   │   │   ├── audit/          # Logs
│   │   │   │   ├── reports/        # PDF generation
│   │   │   │   ├── settings/       # System config
│   │   │   │   ├── dashboard/      # Aggregated stats
│   │   │   │   └── diagnostics/    # System health
│   │   │   └── integrations/
│   │   │       ├── brasil-api.service.ts
│   │   │       ├── via-cep.service.ts
│   │   │       ├── pncp.service.ts
│   │   │       ├── radar-tce-mt.service.ts
│   │   │       ├── mercado-livre.service.ts
│   │   │       └── groq-client.service.ts
│   │   ├── test/
│   │   └── package.json
│   │
│   └── web/                        # React + Vite
│       ├── src/
│       │   ├── main.tsx
│       │   ├── App.tsx
│       │   ├── routes.tsx
│       │   ├── lib/
│       │   │   ├── api/            # Axios + React Query hooks
│       │   │   ├── firebase/       # Firebase init, auth hooks
│       │   │   └── utils/          # formatCurrency, masks, validators
│       │   ├── components/
│       │   │   ├── ui/             # Shadcn (button, card, table, dialog)
│       │   │   ├── layout/         # Sidebar, Header, Breadcrumbs
│       │   │   └── shared/         # DataTable, SearchInput, FileUpload
│       │   ├── features/
│       │   │   ├── auth/           # LoginPage, RegisterPage
│       │   │   ├── dashboard/      # DashboardPage, StatCards
│       │   │   ├── institutions/   # List, Form, Detail
│       │   │   ├── projects/       # List, Wizard, Detail
│       │   │   ├── goals/          # GoalList, ProofModal, ApprovalPanel
│       │   │   ├── expenses/       # List, Form, StatusManager
│       │   │   ├── documents/      # Grid, UploadModal, Preview
│       │   │   ├── diligences/     # Timeline, ResponseForm
│       │   │   ├── accounting/     # List, Form, PhotoGallery
│       │   │   ├── price-research/ # List, Detail, ChatIa
│       │   │   ├── audit/          # Table + Filters
│       │   │   ├── reports/        # Builder + Print
│       │   │   ├── users/          # List, Form, RoleSelector
│       │   │   ├── settings/       # System settings
│       │   │   └── diagnostics/    # Admin panel
│       │   ├── hooks/              # useAuth, useFirestore, useUpload
│       │   ├── stores/             # Zustand (auth, sidebar, theme)
│       │   ├── types/              # TypeScript interfaces
│       │   └── styles/             # globals.css
│       └── package.json
│
├── packages/
│   └── shared/                     # Shared types, DTOs, enums
│       ├── src/
│       │   ├── enums/              # ProjectStatus, Role, etc.
│       │   ├── dto/                # CreateProjectDto, etc.
│       │   ├── interfaces/         # IUser, IProject, IInstitution
│       │   └── validators/         # Zod schemas
│       └── package.json
│
├── firebase/
│   ├── firestore.rules
│   ├── firestore.indexes.json
│   └── storage.rules
│
├── docker-compose.yml              # Local dev (Firebase Emulator)
├── turbo.json
└── package.json                    # Root (pnpm workspace)
```

---

## 4. Mapeamento Laravel → NestJS + React

| Módulo Laravel | Módulo NestJS | Rotas API | Páginas React |
|---|---|---|---|
| Auth (Breeze) | AuthModule | `POST /auth/register`, `POST /auth/login` | `/login`, `/register`, `/forgot-password` |
| Dashboard | DashboardModule | `GET /dashboard/stats` | `/dashboard` |
| Institutions | InstitutionsModule | CRUD `/institutions` + sub-rotas directors/history | `/instituicoes`, `/instituicoes/nova`, `/instituicoes/:id` |
| Projects | ProjectsModule | CRUD `/projects` + 7 sub-recursos | `/projetos`, `/projetos/novo`, `/projetos/:id` |
| Goals | GoalsModule | `POST /projects/:id/goals`, fluxo de aprovação | Dentro de `/projetos/:id` (abas) |
| Expenses | ExpensesModule | CRUD + `PATCH /expenses/:id/status` | `/financeiro`, `/despesas/nova` |
| Documents | DocumentsModule | Upload/download + CRUD metadata | `/documentos` |
| Diligences | DiligencesModule | CRUD + responder + parecer | `/diligencias` |
| Accounting | AccountingModule | CRUD + fotos | `/prestacao-contas` |
| Price Research | PriceResearchModule | CRUD + busca + resultados | `/pesquisa-precos`, `/pesquisa-precos/:id` |
| Chat IA | ChatIaModule | `POST /chat-ia/processar`, selecionar, orçamento manual, status | `/pesquisa-precos/chat-ia` |
| Audit | AuditModule | `GET /audit-logs` | `/auditoria` |
| Reports | ReportsModule | `POST /reports/generate`, `GET /reports/download/:id` | `/relatorios` |
| Users | UsersModule | CRUD + `PATCH /users/:id/role` | `/usuarios` |
| Settings | SettingsModule | `GET/PUT /settings` | `/configuracoes` |
| Diagnostics | DiagnosticsModule | `GET /diagnostics/*` | `/sistema/diagnostico` |

---

## 5. Plano de Sprints Detalhado

### Sprint 1 — Fundação e Setup do Monorepo (3 semanas)

**Objetivo:** Infraestrutura base, tooling, Firebase configurado, boilerplate funcional

| # | Tarefa | Responsável | Estimativa | Dependências |
|---|---|---|---|---|
| 1.1 | Criar monorepo pnpm + Turborepo | Backend | 1 dia | — |
| 1.2 | Configurar ESLint, Prettier, Husky, lint-staged | Backend | 1 dia | 1.1 |
| 1.3 | Configurar Docker Compose com Firebase Emulator | Backend | 2 dias | 1.1 |
| 1.4 | Inicializar projeto NestJS com estrutura de pastas | Backend | 1 dia | 1.1 |
| 1.5 | Configurar Firebase Admin SDK no NestJS | Backend | 1 dia | 1.4 |
| 1.6 | Criar FirebaseAuthGuard + RolesGuard | Backend | 2 dias | 1.5 |
| 1.7 | Criar decorators @CurrentUser, @Roles | Backend | 1 dia | 1.6 |
| 1.8 | Criar AuditLogInterceptor global | Backend | 1 dia | 1.7 |
| 1.9 | Criar GlobalExceptionFilter + ValidationPipe | Backend | 1 dia | 1.8 |
| 1.10 | Inicializar projeto React + Vite + Tailwind | Frontend | 1 dia | 1.1 |
| 1.11 | Configurar Shadcn UI + tema claro/escuro | Frontend | 1 dia | 1.10 |
| 1.12 | Criar layout base: Sidebar + Header + Breadcrumbs | Frontend | 2 dias | 1.11 |
| 1.13 | Configurar Firebase Client SDK (Auth + Firestore + Storage) | Frontend | 1 dia | 1.12 |
| 1.14 | Criar hook useAuth + ProtectedRoute | Frontend | 2 dias | 1.13 |
| 1.15 | Criar React Query provider + Axios instance | Frontend | 1 dia | 1.14 |
| 1.16 | Configurar projeto Firebase (Web + Admin) | DevOps | 1 dia | — |
| 1.17 | Escrever Firestore security rules base | DevOps | 1 dia | 1.16 |
| 1.18 | Escrever Storage security rules | DevOps | 1 dia | 1.17 |
| 1.19 | Criar package shared com enums + interfaces + Zod schemas | Backend | 2 dias | 1.1 |
| 1.20 | Configurar CI (GitHub Actions): lint + typecheck | DevOps | 1 dia | 1.1 |

**Entregáveis:**
- Monorepo funcional com turborepo
- NestJS rodando com Firebase Admin SDK
- React rodando com Shadcn UI
- Firebase Emulator local com dados de seed
- CI pipeline verde

---

### Sprint 2 — Autenticação e Usuários (2 semanas)

**Objetivo:** Login funcional com Firebase Auth, CRUD de usuários, RBAC

| # | Tarefa | Responsável | Estimativa | Dependências |
|---|---|---|---|---|
| 2.1 | AuthModule: verificação de token Firebase no NestJS | Backend | 2 dias | 1.6, 1.19 |
| 2.2 | Sync user: cria documento users/{uid} no primeiro login | Backend | 1 dia | 2.1 |
| 2.3 | UsersModule: CRUD de usuários no Firestore | Backend | 2 dias | 2.2 |
| 2.4 | Custom claims: role + institutionId no Firebase Auth | Backend | 2 dias | 2.3 |
| 2.5 | Página de login (email/senha + Google) | Frontend | 2 dias | 1.14, 1.15 |
| 2.6 | Página de registro | Frontend | 1 dia | 2.5 |
| 2.7 | Página de recuperação de senha | Frontend | 1 dia | 2.5 |
| 2.8 | Página de perfil (editar nome, email, foto) | Frontend | 2 dias | 2.5 |
| 2.9 | Página de gerenciamento de usuários (admin) | Frontend | 3 dias | 2.3, 2.4 |
| 2.10 | Testar fluxo completo: registro → login → role sync | QA | 1 dia | 2.1-2.9 |

**Entregáveis:**
- Login com email/senha e Google
- Registro de novos usuários
- Admin pode gerenciar usuários e papéis
- RBAC funcional (guards no NestJS, regras no Firestore)

---

### Sprint 3 — Instituições (3 semanas)

**Objetivo:** CRUD completo de instituições com diretores, histórico, PDF

| # | Tarefa | Responsável | Estimativa | Dependências |
|---|---|---|---|---|
| 3.1 | InstitutionsModule: CRUD no Firestore | Backend | 3 dias | 2.1, 1.19 |
| 3.2 | DirectorsModule (subcollection): CRUD | Backend | 2 dias | 3.1 |
| 3.3 | InstitutionProjectHistoryModule: CRUD | Backend | 1 dia | 3.1 |
| 3.4 | Integração BrasilAPI: endpoint GET /integrations/cnpj/:cnpj | Backend | 2 dias | 3.1 |
| 3.5 | Validação de CNPJ (dígitos verificadores) | Backend | 1 dia | 3.4 |
| 3.6 | PDF da ficha da instituição (Puppeteer) | Backend | 2 dias | 3.1 |
| 3.7 | Lista de instituições com busca + paginação cursor | Frontend | 2 dias | 3.1 |
| 3.8 | Formulário criação/edição multi-step: dados, endereço, banco, presidente, utilidade pública | Frontend | 4 dias | 3.1, 3.4 |
| 3.9 | Página de detalhes com abas (diretores, projetos, histórico) | Frontend | 3 dias | 3.2, 3.3 |
| 3.10 | Modal de diretores (CRUD inline) | Frontend | 2 dias | 3.9 |
| 3.11 | Upload foto presidente → Firebase Storage | Frontend | 1 dia | 3.8 |
| 3.12 | Botão "Exportar PDF" + download | Frontend | 1 dia | 3.6 |
| 3.13 | Testar CRUD + integrações + PDF | QA | 2 dias | 3.1-3.12 |

**Entregáveis:**
- CRUD completo de instituições
- Autocomplete CNPJ (BrasilAPI)
- Autocomplete CEP (ViaCEP)
- Diretores com mandato
- Histórico de projetos
- PDF exportável
- Upload de fotos e documentos

---

### Sprint 4 — Projetos (5 semanas)

**Objetivo:** Módulo mais complexo — CRUD completo com 7 sub-entities, wizard de criação

| # | Tarefa | Responsável | Estimativa | Dependências |
|---|---|---|---|---|
| 4.1 | ProjectsModule: CRUD no Firestore | Backend | 3 dias | 2.1, 1.19 |
| 4.2 | FundingSourcesModule: CRUD | Backend | 1 dia | 4.1 |
| 4.3 | ExecutionLocationsModule (subcollection): CRUD | Backend | 1 dia | 4.1 |
| 4.4 | SpecificObjectivesModule: CRUD | Backend | 1 dia | 4.1 |
| 4.5 | TeamMembersModule: CRUD | Backend | 1 dia | 4.1 |
| 4.6 | ContractedServicesModule: CRUD | Backend | 1 dia | 4.1 |
| 4.7 | CapabilityPhotosModule: upload + CRUD | Backend | 1 dia | 4.1 |
| 4.8 | Geração de código sequencial (001/2026) | Backend | 1 dia | 4.1 |
| 4.9 | Validações de negócio do projeto | Backend | 2 dias | 4.1-4.8 |
| 4.10 | PDF inventário do projeto (Puppeteer) | Backend | 2 dias | 4.1 |
| 4.11 | Lista de projetos com filtros (status, instituição, busca) | Frontend | 2 dias | 4.1 |
| 4.12 | Wizard de criação (6 etapas): dados básicos, detalhamento, locais, objetivos, equipe, serviços + fotos | Frontend | 5 dias | 4.1-4.7 |
| 4.13 | Página de detalhes com abas: visão geral, metas, despesas, documentos, diligências, financeiro | Frontend | 4 dias | 4.1 |
| 4.14 | Timeline de status do projeto | Frontend | 2 dias | 4.1 |
| 4.15 | Botão "Exportar Inventário PDF" | Frontend | 1 dia | 4.10 |
| 4.16 | Testar CRUD + validações + wizard + PDF | QA | 3 dias | 4.1-4.15 |

**Entregáveis:**
- CRUD completo de projetos
- Wizard de criação com 6 etapas
- 7 sub-entities (locais, objetivos, equipe, serviços, fotos, funding sources)
- Geração automática de código
- PDF de inventário
- Timeline de ciclo de vida

---

### Sprint 5 — Metas, Atividades e Despesas (4 semanas)

**Objetivo:** Workflow completo de metas com comprovação e aprovação, controle de despesas

| # | Tarefa | Responsável | Estimativa | Dependências |
|---|---|---|---|---|
| 5.1 | GoalsModule: CRUD no Firestore | Backend | 2 dias | 4.1 |
| 5.2 | Workflow de meta: send-to-analysis, approve, disapprove, send-to-accounting | Backend | 3 dias | 5.1 |
| 5.3 | ActivitiesModule (subcollection de goals): CRUD | Backend | 1 dia | 5.1 |
| 5.4 | GoalProofsModule: upload + CRUD | Backend | 1 dia | 5.1 |
| 5.5 | GoalApprovalsModule: registro de aprovações | Backend | 1 dia | 5.1 |
| 5.6 | ExpensesModule: CRUD | Backend | 2 dias | 4.1 |
| 5.7 | Workflow de despesa: PENDENTE → APROVADO/REPROVADO → PAGO | Backend | 2 dias | 5.6 |
| 5.8 | BudgetItemsModule: CRUD | Backend | 1 dia | 5.6 |
| 5.9 | Seção de metas no projeto: lista/kanban com status | Frontend | 3 dias | 5.1 |
| 5.10 | Card de meta expandível: atividades, comprovações, aprovações | Frontend | 3 dias | 5.2-5.5 |
| 5.11 | Modal de criação/edição de meta | Frontend | 1 dia | 5.9 |
| 5.12 | Modal de comprovação (upload múltiplo fotos + anexo) | Frontend | 2 dias | 5.4 |
| 5.13 | Painel de aprovação para fiscal | Frontend | 2 dias | 5.5 |
| 5.14 | Seção de despesas: lista com filtros | Frontend | 2 dias | 5.6 |
| 5.15 | Formulário de despesa com seleção de comprovante | Frontend | 2 dias | 5.7 |
| 5.16 | Badge de status + ação inline (aprovar/reprovar) | Frontend | 1 dia | 5.15 |
| 5.17 | Testar workflow completo | QA | 2 dias | 5.1-5.16 |

**Entregáveis:**
- Metas com atividades, comprovações (fotos/anexos) e aprovações
- Workflow: Pendente → Análise → Aprovado/Desaprovado → Prestação de Contas
- Despesas com status workflow
- Dashboard financeiro do projeto

---

### Sprint 6 — Documentos, Diligências e Prestação de Contas (4 semanas)

**Objetivo:** Repositório de documentos, sistema de diligências com resposta, relatórios financeiros

| # | Tarefa | Responsável | Estimativa | Dependências |
|---|---|---|---|---|
| 6.1 | DocumentsModule: upload Firebase Storage + CRUD metadata | Backend | 2 dias | 4.1 |
| 6.2 | Download com URL assinada (validade 1h) | Backend | 1 dia | 6.1 |
| 6.3 | DiligencesModule: CRUD com workflow | Backend | 2 dias | 4.1 |
| 6.4 | Fluxo: ABERTA → RESPONDIDA → FECHADA/REABERTA | Backend | 2 dias | 6.3 |
| 6.5 | Resposta com parecer por meta + anexos | Backend | 1 dia | 6.3 |
| 6.6 | AccountingModule: CRUD de relatórios financeiros | Backend | 2 dias | 4.1 |
| 6.7 | Upload de fotos comprobatórias | Backend | 1 dia | 6.6 |
| 6.8 | Grid de documentos com preview (imagem/PDF inline) | Frontend | 3 dias | 6.1 |
| 6.9 | Upload drag-and-drop com barra de progresso | Frontend | 2 dias | 6.1 |
| 6.10 | Timeline de diligências com cards de status + formulário resposta | Frontend | 3 dias | 6.3-6.5 |
| 6.11 | Badge de prazo (vencidas em vermelho) | Frontend | 1 dia | 6.10 |
| 6.12 | Lista de relatórios financeiros por projeto | Frontend | 2 dias | 6.6 |
| 6.13 | Formulário de relatório com upload de fotos (gallery view) | Frontend | 2 dias | 6.7 |
| 6.14 | PDF exportável do relatório | Frontend | 1 dia | 6.6 |
| 6.15 | Testar fluxos completos | QA | 2 dias | 6.1-6.14 |

**Entregáveis:**
- Grid de documentos com preview e download
- Timeline de diligências com resposta e parecer
- Relatórios financeiros com fotos
- PDFs exportáveis

---

### Sprint 7 — Pesquisa de Preços + Chat IA (5 semanas)

**Objetivo:** Módulo mais inovador — cotação automatizada com IA, PNCP e mercado

| # | Tarefa | Responsável | Estimativa | Dependências |
|---|---|---|---|---|
| 7.1 | PriceResearchModule: CRUD no Firestore | Backend | 2 dias | 4.1 |
| 7.2 | PriceResearchResultsModule (subcollection): CRUD | Backend | 1 dia | 7.1 |
| 7.3 | PriceResearchAggregatorService: estatísticas (min, max, avg, median) | Backend | 2 dias | 7.1 |
| 7.4 | GroqClientService: interpretBatch + suggestProductDetails | Backend | 3 dias | — |
| 7.5 | PncpService: busca em contratações públicas | Backend | 3 dias | — |
| 7.6 | RadarTceMtService: portal de transparência MT | Backend | 2 dias | — |
| 7.7 | MercadoLivreService: ML API + Zoom + Buscapé | Backend | 3 dias | — |
| 7.8 | ChatIaModule: POST /chat-ia/processar | Backend | 3 dias | 7.1-7.7 |
| 7.9 | ChatIaModule: POST /chat-ia/selecionar | Backend | 1 dia | 7.8 |
| 7.10 | ChatIaModule: POST /chat-ia/orcamento-manual | Backend | 2 dias | 7.8 |
| 7.11 | ChatIaModule: GET /chat-ia/status | Backend | 1 dia | 7.8 |
| 7.12 | PDF relatório comparativo de preços (Puppeteer) | Backend | 2 dias | 7.1 |
| 7.13 | Lista de pesquisas de preço | Frontend | 2 dias | 7.1 |
| 7.14 | Interface de chat conversacional: campo texto + botão + loading | Frontend | 3 dias | 7.8 |
| 7.15 | Painel de resultados por item: cards + tabelas + estatísticas | Frontend | 4 dias | 7.8 |
| 7.16 | Checkbox de seleção por cotação com toggle AJAX | Frontend | 2 dias | 7.9 |
| 7.17 | Modal "Adicionar Orçamento Manual": CNPJ + upload + preview | Frontend | 3 dias | 7.10 |
| 7.18 | Modal "Finalizar Pesquisa": resumo + justificativa + referência | Frontend | 2 dias | 7.11 |
| 7.19 | Botão "Exportar PDF" do relatório comparativo | Frontend | 1 dia | 7.12 |
| 7.20 | Testar fluxo completo do Chat IA + integrações | QA | 3 dias | 7.1-7.19 |

**Entregáveis:**
- Chat IA funcional (texto livre → itens estruturados)
- Busca automática em PNCP + Mercado Livre + Zoom + Buscapé
- Painel de resultados com estatísticas e seleção
- Orçamento manual com upload
- PDF do relatório comparativo

---

### Sprint 8 — Auditoria, Relatórios, Configurações (2 semanas)

**Objetivo:** Logs de auditoria, gerador de relatórios, painel de configurações

| # | Tarefa | Responsável | Estimativa | Dependências |
|---|---|---|---|---|
| 8.1 | AuditModule: interceptor global registra no Firestore | Backend | 2 dias | 1.8 |
| 8.2 | GET /audit-logs com filtros (usuário, ação, entidade, data) | Backend | 1 dia | 8.1 |
| 8.3 | Auto-cleanup com TTL 90 dias (Cloud Function) | Backend | 1 dia | 8.1 |
| 8.4 | ReportsModule: geração de relatórios consolidados | Backend | 2 dias | 4.1 |
| 8.5 | PDF de relatório via Puppeteer | Backend | 1 dia | 8.4 |
| 8.6 | SettingsModule: documento único /settings/global | Backend | 1 dia | 2.1 |
| 8.7 | DashboardModule: agregação de estatísticas | Backend | 2 dias | 4.1, 5.6 |
| 8.8 | Tabela de auditoria com filtros avançados | Frontend | 2 dias | 8.2 |
| 8.9 | Gerador de relatórios: selecionar projeto + itens + período | Frontend | 3 dias | 8.4 |
| 8.10 | Preview de PDF antes do download | Frontend | 1 dia | 8.5 |
| 8.11 | Página de configurações do sistema (admin global) | Frontend | 2 dias | 8.6 |
| 8.12 | Dashboard com cards de estatísticas + atividades recentes | Frontend | 3 dias | 8.7 |
| 8.13 | Testar auditoria + relatórios + configurações | QA | 1 dia | 8.1-8.12 |

**Entregáveis:**
- Log de auditoria de todas as operações
- Gerador de relatórios consolidados
- Painel de configurações do sistema
- Dashboard com métricas

---

### Sprint 9 — Diagnóstico, Deploy e Polimento (2 semanas)

**Objetivo:** Sistema em produção, health checks, CI/CD, refinamentos finais

| # | Tarefa | Responsável | Estimativa | Dependências |
|---|---|---|---|---|
| 9.1 | DiagnosticsModule: health checks (Firestore, Auth, Storage) | Backend | 1 dia | 1.5 |
| 9.2 | ThrottlerModule: rate limiting | Backend | 1 dia | 1.4 |
| 9.3 | Helmet + CORS + segurança de headers | Backend | 1 dia | 9.2 |
| 9.4 | Dockerfile NestJS (multi-stage build) | DevOps | 1 dia | 1.4 |
| 9.5 | Dockerfile React (nginx) | DevOps | 1 dia | 1.10 |
| 9.6 | docker-compose.prod.yml | DevOps | 1 dia | 9.4, 9.5 |
| 9.7 | CI/CD: GitHub Actions (lint + typecheck + test + build + deploy) | DevOps | 2 dias | 9.4-9.6 |
| 9.8 | Firestore indexes configurados | DevOps | 1 dia | 1.17 |
| 9.9 | Storage rules finais | DevOps | 1 dia | 1.18 |
| 9.10 | Templates de email Firebase Auth pt-BR | DevOps | 1 dia | 1.16 |
| 9.11 | Script de migração de dados MySQL → Firestore | Backend | 3 dias | 4.1 |
| 9.12 | Script de migração de arquivos storage → Firebase Storage | Backend | 2 dias | 6.1 |
| 9.13 | Página de diagnóstico no frontend | Frontend | 1 dia | 9.1 |
| 9.14 | Responsividade + testes cross-browser | Frontend | 2 dias | — |
| 9.15 | Testes end-to-end (Cypress) nos fluxos principais | QA | 3 dias | 2.5, 3.7, 4.11, 5.9, 7.14 |
| 9.16 | Deploy produção + verificação final | DevOps | 1 dia | 9.4-9.12 |

**Entregáveis:**
- Sistema em produção
- CI/CD automatizado
- Migração de dados concluída
- Testes E2E nos fluxos principais
- Health checks e monitoramento

---

## 6. Resumo de Esforço

| Sprint | Módulos | Semanas | Dias Úteis |
|---|---|---|---|
| **Sprint 1** | Fundação e Setup | 3 | 15 |
| **Sprint 2** | Autenticação e Usuários | 2 | 10 |
| **Sprint 3** | Instituições | 3 | 15 |
| **Sprint 4** | Projetos | 5 | 25 |
| **Sprint 5** | Metas, Atividades e Despesas | 4 | 20 |
| **Sprint 6** | Documentos, Diligências e Prestação de Contas | 4 | 20 |
| **Sprint 7** | Pesquisa de Preços + Chat IA | 5 | 25 |
| **Sprint 8** | Auditoria, Relatórios e Configurações | 2 | 10 |
| **Sprint 9** | Diagnóstico, Deploy e Polimento | 2 | 10 |
| **Total** | | **30 semanas** | **~150 dias úteis** |

---

## 7. Regras de Segurança (Firestore Rules)

```javascript
rules_version = '2';

service cloud.firestore {
  match /databases/{database}/documents {
    function isAuthenticated() {
      return request.auth != null;
    }
    function isAdmin() {
      return request.auth.token.role == 'ADMIN_GERAL';
    }
    function belongsToInstitution(institutionId) {
      return request.auth.token.institution_id == institutionId;
    }
    function hasRole(...roles) {
      return request.auth.token.role in roles;
    }

    match /users/{userId} {
      allow read: if isAuthenticated();
      allow write: if isAuthenticated() &&
        (request.auth.uid == userId || isAdmin());
      allow delete: if isAdmin();
    }

    match /institutions/{institutionId} {
      allow read: if isAuthenticated();
      allow create: if isAdmin();
      allow update: if isAdmin() ||
        (hasRole('ADMIN_INSTITUICAO') && belongsToInstitution(institutionId));
      allow delete: if isAdmin();

      match /directors/{directorId} {
        allow read, write: if isAuthenticated() &&
          (isAdmin() || belongsToInstitution(institutionId));
      }
    }

    match /projects/{projectId} {
      allow read: if isAuthenticated();
      allow write: if isAuthenticated() &&
        (isAdmin() || hasRole('ADMIN_INSTITUICAO', 'GESTOR_PROJETO'));

      match /goals/{goalId} {
        allow read: if isAuthenticated();
        allow write: if isAuthenticated() &&
          (isAdmin() || hasRole('ADMIN_INSTITUICAO', 'GESTOR_PROJETO', 'FISCAL_PROJETO'));
      }
    }

    match /auditLogs/{logId} {
      allow read: if isAdmin();
      allow write: if isAdmin();
    }

    match /settings/global {
      allow read: if isAdmin();
      allow write: if isAdmin();
    }
  }
}
```

---

## 8. Pontos de Atenção

1. **Auditoria**: Interceptor global no NestJS registra em Firestore. TTL de 90 dias via Cloud Function.

2. **RBAC no Firestore**: Claims personalizados do Firebase Auth armazenam `role` e `institutionId`. Atualizados via Admin SDK quando admin altera papel do usuário.

3. **Migração de dados**: Script Node.js que lê MySQL atual, desnormaliza e escreve no Firestore. Estimar 3 dias.

4. **Arquivos existentes**: Migrar de `storage/app/public` para Firebase Storage. Script de upload batch.

5. **PDFs**: Substituir DomPDF por Puppeteer (renderiza HTML/CSS → PDF). Templates React transformados em HTML string.

6. **Chat IA**: O `GroqClient` precisa ser reescrito em TypeScript (axios). A lógica de interpretação de lote e busca paralela permanece a mesma.

7. **Pesquisa de preços**: A lógica de agregação (estatísticas) será feita em memória no NestJS, já que Firestore não tem `AVG()`, `MEDIAN()`.

8. **Performance Firestore**: Evitar reads desnecessários com cache em memória (NestJS) e React Query (frontend).

---

*Gerado em 21/07/2026 — Versão 2.0 (Refatoração completa: Laravel → NestJS + React + Firebase)*
