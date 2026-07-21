# Plano de Refatoração Completo: Laravel → NestJS + React + Firebase

> Baseado no código atual (46 migrations, 29 models, 32 controllers, 63 views, 7 services)

---

## Progresso Atual

> **Última atualização:** 21/07/2026 — Sprints 1, 2 e 3 concluídas

| Sprint | Status | Commits | % |
|---|---|---|---|
| **Sprint 1** — Fundação e Setup | ✅ **Concluída** | `5513db9`, `c13210e` | 100% |
| **Sprint 2** — Autenticação e Usuários | ✅ **Concluída** | `0b2fb2c` | 100% |
| **Sprint 3** — Instituições | ✅ **Concluída** | `e624d7a`, `008a81d` | 100% |
| **Sprint 4** — Projetos | ⏳ Pendente | — | 0% |
| **Sprint 5** — Metas, Atividades e Despesas | ⏳ Pendente | — | 0% |
| **Sprint 6** — Documentos, Diligências e Prestação de Contas | ⏳ Pendente | — | 0% |
| **Sprint 7** — Pesquisa de Preços + Chat IA | ⏳ Pendente | — | 0% |
| **Sprint 8** — Auditoria, Relatórios e Configurações | ⏳ Pendente | — | 0% |
| **Sprint 9** — Diagnóstico, Deploy e Polimento | ⏳ Pendente | — | 0% |

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

### Sprint 1 — Fundação e Setup do Monorepo (3 semanas) ✅

**Objetivo:** Infraestrutura base, tooling, Firebase configurado, boilerplate funcional

**Commit:** `5513db9` | **Fix:** `c13210e` (remove API key de arquivos públicos)

| # | Tarefa | Responsável | Status | Arquivos |
|---|---|---|---|---|
| 1.1 | Criar monorepo pnpm + Turborepo | Backend | ✅ | `package.json`, `pnpm-workspace.yaml`, `turbo.json` |
| 1.2 | Configurar ESLint, Prettier, Husky, lint-staged | Backend | ✅ | `.prettierrc`, `.eslintrc.js` |
| 1.3 | Configurar Docker Compose com Firebase Emulator | Backend | ✅ | `docker-compose.yml` |
| 1.4 | Inicializar projeto NestJS com estrutura de pastas | Backend | ✅ | `apps/api/` — `nest-cli.json`, `tsconfig.json`, `main.ts`, `app.module.ts` |
| 1.5 | Configurar Firebase Admin SDK no NestJS | Backend | ✅ | `apps/api/src/firebase.module.ts` |
| 1.6 | Criar FirebaseAuthGuard + RolesGuard | Backend | ✅ | `apps/api/src/common/guards/` (3 guards) |
| 1.7 | Criar decorators @CurrentUser, @Roles, @Public | Backend | ✅ | `apps/api/src/common/decorators/` (3 decorators) |
| 1.8 | Criar AuditLogInterceptor global | Backend | ✅ | `apps/api/src/common/interceptors/` |
| 1.9 | Criar GlobalExceptionFilter + ValidationPipe | Backend | ✅ | `apps/api/src/common/filters/`, `apps/api/src/common/pipes/` |
| 1.10 | Inicializar projeto React + Vite + Tailwind | Frontend | ✅ | `apps/web/` — Vite + Tailwind 4 configurados |
| 1.11 | Configurar Shadcn UI + tema claro/escuro | Frontend | ✅ | `apps/web/src/components/ui/` (11 componentes) |
| 1.12 | Criar layout base: Sidebar + Header + AppLayout | Frontend | ✅ | `apps/web/src/components/layout/` (3 arquivos) |
| 1.13 | Configurar Firebase Client SDK (Auth + Firestore + Storage) | Frontend | ✅ | `apps/web/src/lib/firebase.ts` |
| 1.14 | Criar hook useAuth + ProtectedRoute + auth pages | Frontend | ✅ | `use-auth.ts`, `login-page.tsx`, `register-page.tsx`, `forgot-password-page.tsx` |
| 1.15 | Criar React Query provider + Axios instance | Frontend | ✅ | `App.tsx` (QueryClientProvider), `lib/api.ts` |
| 1.16 | Configurar projeto Firebase (Web + Admin) | DevOps | ✅ | Firebase Console configurado (gestaosetor3) |
| 1.17 | Escrever Firestore security rules base | DevOps | ✅ | `firebase/firestore.rules` (RBAC completo) |
| 1.18 | Escrever Storage security rules | DevOps | ✅ | `firebase/storage.rules` (por pasta + limites) |
| 1.19 | Criar package shared com enums + interfaces + DTOs | Backend | ✅ | `packages/shared/` (11 enums, 12 interfaces, 10 DTOs) |
| 1.20 | Configurar CI (GitHub Actions): lint + typecheck | DevOps | 🔶 Pendente | — |

**Entregáveis entregues:**
- Monorepo funcional com Turborepo (3 workspaces: api, web, shared)
- NestJS com Common Layer completa (guards, decorators, interceptors, filters, pipes)
- AuthModule + UsersModule implementados
- React + Vite + Tailwind 4 + Shadcn UI funcionando
- 3 telas de auth (Login, Register, Forgot Password)
- Dashboard com layout responsivo e sidebar
- Firebase Client + Admin SDK integrados
- 11 componentes Shadcn UI customizados
- Firestore + Storage security rules completas (RBAC com 8 papéis)
- 12 composite indexes configurados
- Package shared com tipos compartilhados entre API e Web
- Build de todos os pacotes compila sem erros

---

### Sprint 2 — Autenticação e Usuários (2 semanas) ✅

**Objetivo:** Login funcional com Firebase Auth, CRUD de usuários, RBAC

| # | Tarefa | Responsável | Status | Arquivos |
|---|---|---|---|---|
| 2.1 | Conectar AuthModule + UsersModule no app.module.ts | Backend | ✅ | `app.module.ts` (imports) |
| 2.2 | Sync user no primeiro login | Backend | ✅ | `auth.controller.ts` + `auth.service.ts` |
| 2.3 | UsersModule: CRUD + PATCH /users/:uid/role | Backend | ✅ | `users.controller.ts`, `users.service.ts` |
| 2.4 | Custom claims via Admin SDK | Backend | ✅ | `auth.service.ts` (updateUserRole) |
| 2.5 | Página de login (email/senha + Google) | Frontend | ✅ | `features/auth/login-page.tsx` |
| 2.6 | Página de registro | Frontend | ✅ | `features/auth/register-page.tsx` |
| 2.7 | Página de recuperação de senha | Frontend | ✅ | `features/auth/forgot-password-page.tsx` |
| 2.8 | Página de perfil (nome, senha, tema) | Frontend | ✅ | `features/profile/profile-page.tsx` |
| 2.9 | Página de gerenciamento de usuários (admin) | Frontend | ✅ | `features/users/users-page.tsx` |
| 2.10 | Header com dropdown → perfil | Frontend | ✅ | `components/layout/header.tsx` |

**Entregáveis:**
- AuthModule + UsersModule conectados no NestJS
- Sync automático de usuário no Firestore no primeiro login
- Endpoint `PATCH /users/:uid/role` com custom claims
- Página de perfil: editar nome, alterar senha (com reautenticação), toggle tema
- Página de gerenciamento de usuários: listar, buscar, alterar papel (admin)
- Header com dropdown: perfil + logout + toggle tema

---

### Sprint 3 — Instituições (3 semanas) ✅

**Objetivo:** CRUD completo de instituições com diretores, histórico
**Commits:** `e624d7a`, `008a81d`

| # | Tarefa | Responsável | Status | Arquivos |
|---|---|---|---|---|
| 3.1 | InstitutionsModule: CRUD Firestore (paginacao + busca) | Backend | ✅ | `institutions.service.ts`, `institutions.controller.ts` |
| 3.2 | DirectorsModule (subcollection): CRUD | Backend | ✅ | `directors/directors.service.ts` |
| 3.3 | InstitutionProjectHistoryModule: CRUD | Backend | ✅ | `project-history/project-history.service.ts` |
| 3.4 | Integração BrasilAPI: GET /api/integrations/cnpj/:cnpj | Backend | ✅ | `integrations.controller.ts` |
| 3.5 | Integração ViaCEP: GET /api/integrations/cep/:cep | Backend | ✅ | `integrations.controller.ts` |
| 3.6 | Validação de CNPJ (dígitos verificadores) | Frontend | ✅ | `lib/utils.ts` (validateCnpj) |
| 3.7 | Formulário multi-step (4 etapas) com autocomplete | Frontend | ✅ | `institution-form-page.tsx` |
| 3.8 | Lista de instituições com busca + paginação | Frontend | ✅ | `institutions-list-page.tsx` |
| 3.9 | Detalhes com 6 abas (dados, endereco, banco, presidente, diretores, historico) | Frontend | ✅ | `institution-detail-page.tsx` |
| 3.10 | Modal de diretores (CRUD inline) | Frontend | ✅ | Dentro de `institution-detail-page.tsx` |
| 3.11 | Seed admin: usuario gestor.renatorosa@gmail.com como ADMIN_GERAL | DevOps | ✅ | `scripts/seed-admin.ts`, `seed.controller.ts` |
| 3.12 | PDF da ficha da instituicao (Puppeteer) | Backend | ⏳ Pendente | — |

**Entregáveis:**
- InstitutionsModule com CRUD completo + subcolecoes
- Integracao BrasilAPI + ViaCEP com autocomplete no formulario
- Formulario multi-step (4 etapas): dados, endereco, banco, presidente
- Detalhes com 6 abas e CRUD inline de diretores e historico
- Seed de admin principal no Firestore + Custom Claims

---

### Sprint 4 — Projetos (5 semanas) ⏳

**Objetivo:** Módulo mais complexo — CRUD completo com 7 sub-entities, wizard de criação

| # | Tarefa | Responsável | Status | Arquivos |
|---|---|---|---|---|
| 4.1 | ProjectsModule: CRUD no Firestore | Backend | ⏳ Pendente | — |
| 4.2 | FundingSourcesModule: CRUD | Backend | ⏳ Pendente | — |
| 4.3 | ExecutionLocationsModule (subcollection): CRUD | Backend | ⏳ Pendente | — |
| 4.4 | SpecificObjectivesModule: CRUD | Backend | ⏳ Pendente | — |
| 4.5 | TeamMembersModule: CRUD | Backend | ⏳ Pendente | — |
| 4.6 | ContractedServicesModule: CRUD | Backend | ⏳ Pendente | — |
| 4.7 | CapabilityPhotosModule: upload + CRUD | Backend | ⏳ Pendente | — |
| 4.8 | Geração de código sequencial (001/2026) | Backend | ⏳ Pendente | — |
| 4.9 | Validações de negócio do projeto | Backend | ⏳ Pendente | — |
| 4.10 | PDF inventário do projeto (Puppeteer) | Backend | ⏳ Pendente | — |
| 4.11 | Lista de projetos com filtros | Frontend | ⏳ Pendente | — |
| 4.12 | Wizard de criação (6 etapas) | Frontend | ⏳ Pendente | — |
| 4.13 | Página de detalhes com abas | Frontend | ⏳ Pendente | — |
| 4.14 | Timeline de status do projeto | Frontend | ⏳ Pendente | — |
| 4.15 | Botão "Exportar Inventário PDF" | Frontend | ⏳ Pendente | — |
| 4.16 | Testar CRUD + validações + wizard + PDF | QA | ⏳ Pendente | — |

**Entregáveis:**
- CRUD completo de projetos
- Wizard de criação com 6 etapas
- 7 sub-entities (locais, objetivos, equipe, serviços, fotos, funding sources)
- Geração automática de código
- PDF de inventário
- Timeline de ciclo de vida

---

### Sprint 5 — Metas, Atividades e Despesas (4 semanas) ⏳

**Objetivo:** Workflow completo de metas com comprovação e aprovação, controle de despesas

| # | Tarefa | Responsável | Status | Arquivos |
|---|---|---|---|---|
| 5.1 | GoalsModule: CRUD no Firestore | Backend | ⏳ Pendente | — |
| 5.2 | Workflow de meta (send-to-analysis, approve, etc.) | Backend | ⏳ Pendente | — |
| 5.3 | ActivitiesModule (subcollection de goals): CRUD | Backend | ⏳ Pendente | — |
| 5.4 | GoalProofsModule: upload + CRUD | Backend | ⏳ Pendente | — |
| 5.5 | GoalApprovalsModule: registro de aprovações | Backend | ⏳ Pendente | — |
| 5.6 | ExpensesModule: CRUD | Backend | ⏳ Pendente | — |
| 5.7 | Workflow de despesa | Backend | ⏳ Pendente | — |
| 5.8 | BudgetItemsModule: CRUD | Backend | ⏳ Pendente | — |
| 5.9 | Seção de metas no projeto (lista/kanban) | Frontend | ⏳ Pendente | — |
| 5.10 | Card de meta expandível | Frontend | ⏳ Pendente | — |
| 5.11 | Modal de comprovação (upload fotos + anexo) | Frontend | ⏳ Pendente | — |
| 5.12 | Painel de aprovação para fiscal | Frontend | ⏳ Pendente | — |
| 5.13 | Seção de despesas: lista com filtros | Frontend | ⏳ Pendente | — |
| 5.14 | Testar workflow completo | QA | ⏳ Pendente | — |

---

### Sprint 6 — Documentos, Diligências e Prestação de Contas (4 semanas) ⏳

**Objetivo:** Repositório de documentos, sistema de diligências com resposta, relatórios financeiros

| # | Tarefa | Responsável | Status | Arquivos |
|---|---|---|---|---|
| 6.1 | DocumentsModule: upload Firebase Storage + CRUD metadata | Backend | ⏳ Pendente | — |
| 6.2 | DiligencesModule: CRUD com workflow | Backend | ⏳ Pendente | — |
| 6.3 | Fluxo: ABERTA → RESPONDIDA → FECHADA/REABERTA | Backend | ⏳ Pendente | — |
| 6.4 | AccountingModule: CRUD de relatórios financeiros | Backend | ⏳ Pendente | — |
| 6.5 | Grid de documentos com preview (imagem/PDF inline) | Frontend | ⏳ Pendente | — |
| 6.6 | Upload drag-and-drop com barra de progresso | Frontend | ⏳ Pendente | — |
| 6.7 | Timeline de diligências com formulário resposta | Frontend | ⏳ Pendente | — |
| 6.8 | Lista de relatórios financeiros por projeto | Frontend | ⏳ Pendente | — |
| 6.9 | Testar fluxos completos | QA | ⏳ Pendente | — |

---

### Sprint 7 — Pesquisa de Preços + Chat IA (5 semanas) ⏳

**Objetivo:** Módulo mais inovador — cotação automatizada com IA, PNCP e mercado

| # | Tarefa | Responsável | Status | Arquivos |
|---|---|---|---|---|
| 7.1 | PriceResearchModule: CRUD no Firestore | Backend | ⏳ Pendente | — |
| 7.2 | PriceResearchAggregatorService: estatísticas | Backend | ⏳ Pendente | — |
| 7.3 | GroqClientService: interpretBatch + suggestProductDetails | Backend | ⏳ Pendente | — |
| 7.4 | PncpService: busca em contratações públicas | Backend | ⏳ Pendente | — |
| 7.5 | RadarTceMtService: portal de transparência MT | Backend | ⏳ Pendente | — |
| 7.6 | MercadoLivreService: ML API + Zoom + Buscapé | Backend | ⏳ Pendente | — |
| 7.7 | ChatIaModule: 4 endpoints | Backend | ⏳ Pendente | — |
| 7.8 | PDF relatório comparativo de preços (Puppeteer) | Backend | ⏳ Pendente | — |
| 7.9 | Interface de chat conversacional | Frontend | ⏳ Pendente | — |
| 7.10 | Painel de resultados por item | Frontend | ⏳ Pendente | — |
| 7.11 | Modal "Orçamento Manual" + "Finalizar Pesquisa" | Frontend | ⏳ Pendente | — |
| 7.12 | Testar fluxo completo do Chat IA | QA | ⏳ Pendente | — |

---

### Sprint 8 — Auditoria, Relatórios, Configurações (2 semanas) ⏳

**Objetivo:** Logs de auditoria, gerador de relatórios, painel de configurações

| # | Tarefa | Responsável | Status | Arquivos |
|---|---|---|---|---|
| 8.1 | AuditModule: interceptor global registra no Firestore | Backend | 🔶 Parcial | `AuditLogInterceptor` (console log) |
| 8.2 | GET /audit-logs com filtros | Backend | ⏳ Pendente | — |
| 8.3 | ReportsModule: geração de relatórios consolidados | Backend | ⏳ Pendente | — |
| 8.4 | SettingsModule: documento único /settings/global | Backend | ⏳ Pendente | — |
| 8.5 | DashboardModule: agregação de estatísticas | Backend | ⏳ Pendente | — |
| 8.6 | Tabela de auditoria com filtros avançados | Frontend | ⏳ Pendente | — |
| 8.7 | Gerador de relatórios | Frontend | ⏳ Pendente | — |
| 8.8 | Página de configurações do sistema | Frontend | ⏳ Pendente | — |
| 8.9 | Dashboard com cards de estatísticas | Frontend | 🔶 Parcial | `dashboard-page.tsx` (placeholder) |
| 8.10 | Testar | QA | ⏳ Pendente | — |

---

### Sprint 9 — Diagnóstico, Deploy e Polimento (2 semanas) ⏳

**Objetivo:** Sistema em produção, health checks, CI/CD, refinamentos finais

| # | Tarefa | Responsável | Status | Arquivos |
|---|---|---|---|---|
| 9.1 | DiagnosticsModule: health checks | Backend | ⏳ Pendente | — |
| 9.2 | Dockerfile NestJS (multi-stage build) | DevOps | ⏳ Pendente | — |
| 9.3 | Dockerfile React (nginx) | DevOps | ⏳ Pendente | — |
| 9.4 | CI/CD: GitHub Actions | DevOps | ⏳ Pendente | — |
| 9.5 | Firestore indexes | DevOps | ✅ Já feito | `firestore.indexes.json` (12 indexes) |
| 9.6 | Storage rules | DevOps | ✅ Já feito | `storage.rules` |
| 9.7 | Script de migração de dados MySQL → Firestore | Backend | ⏳ Pendente | — |
| 9.8 | Script de migração de arquivos → Firebase Storage | Backend | ⏳ Pendente | — |
| 9.9 | Testes end-to-end (Cypress) | QA | ⏳ Pendente | — |
| 9.10 | Deploy produção + verificação final | DevOps | ⏳ Pendente | — |

---

## 6. Resumo de Esforço

| Sprint | Módulos | Status | Semanas | Dias Úteis |
|---|---|---|---|---|---|
| **Sprint 1** | Fundação e Setup | ✅ **Concluída** | 3 | 15 |
| **Sprint 2** | Autenticação e Usuários | ✅ **Concluída** | 2 | 10 |
| **Sprint 3** | Instituições | ✅ **Concluída** | 3 | 15 |
| **Sprint 4** | Projetos | ⏳ Pendente | 5 | 25 |
| **Sprint 5** | Metas, Atividades e Despesas | ⏳ Pendente | 4 | 20 |
| **Sprint 6** | Documentos, Diligências e Prestação de Contas | ⏳ Pendente | 4 | 20 |
| **Sprint 7** | Pesquisa de Preços + Chat IA | ⏳ Pendente | 5 | 25 |
| **Sprint 8** | Auditoria, Relatórios e Configurações | ⏳ Pendente | 2 | 10 |
| **Sprint 9** | Diagnóstico, Deploy e Polimento | ⏳ Pendente | 2 | 10 |
| **Total** | | | **30 semanas** | **~150 dias úteis** |

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

*Gerado em 21/07/2026 — Versão 2.3 (Sprints 1, 2 e 3 concluídas: monorepo + auth + instituições + seed admin)*
