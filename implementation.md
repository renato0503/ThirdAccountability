# Plano de Refatoração Completo: Laravel → NestJS + React + Firebase

> Baseado no código atual (46 migrations, 29 models, 32 controllers, 63 views, 7 services)

---

## Progresso Atual

> **Última atualização:** 21/07/2026 — **Fase 1 concluída (9 sprints). Fase 2: Sprint 10 concluída.**

| Sprint | Status | Commits | % |
|---|---|---|---|
| **Sprint 1** — Fundação e Setup | ✅ **Concluída** | `5513db9`, `c13210e` | 100% |
| **Sprint 2** — Autenticação e Usuários | ✅ **Concluída** | `0b2fb2c` | 100% |
| **Sprint 3** — Instituições | ✅ **Concluída** | `e624d7a`, `008a81d` | 100% |
| **Sprint 4** — Projetos | ✅ **Concluída** | `9bb5621` | 100% |
| **Sprint 5** — Metas, Atividades e Despesas | ✅ **Concluída** | `551771a` | 100% |
| **Sprint 6** — Documentos, Diligências e Prestação de Contas | ✅ **Concluída** | `c326380` | 100% |
| **Sprint 7** — Pesquisa de Preços + Chat IA | ✅ **Concluída** | `cd7eb70` | 100% |
| **Sprint 8** — Auditoria, Relatórios e Configurações | ✅ **Concluída** | `51d1097` | 100% |
| **Sprint 9** — Diagnóstico, Deploy e Polimento | ✅ **Concluída** | `018e989` | 100% |
| **Sprint 10** — Deploy da API + Infra | ✅ **Concluída** | `78197dd` | 100% |
| **Sprint 11** — Migração de Dados | ⏳ Pendente | — | 0% |
| **Sprint 12** — Funcionalidades Faltantes | ⏳ Pendente | — | 0% |
| **Sprint 13** — Testes + Polimento | ⏳ Pendente | — | 0% |

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

### Sprint 4 — Projetos (5 semanas) ✅

**Objetivo:** Módulo mais complexo — CRUD completo com 7 sub-entities, wizard de criação
**Commits:** `9bb5621`

| # | Tarefa | Responsável | Status | Arquivos |
|---|---|---|---|---|
| 4.1 | ProjectsModule: CRUD Firestore (paginacao + filtros + busca) | Backend | ✅ | `projects.service.ts`, `projects.controller.ts` |
| 4.2 | FundingSourcesModule: CRUD | Backend | ✅ | `funding-sources/` (3 arquivos) |
| 4.3 | ExecutionLocationsModule (subcollection): CRUD | Backend | ✅ | Dentro de `projects.service.ts` |
| 4.4 | SpecificObjectivesModule (subcollection): CRUD | Backend | ✅ | Dentro de `projects.service.ts` |
| 4.5 | TeamMembersModule (subcollection): CRUD | Backend | ✅ | Dentro de `projects.service.ts` |
| 4.6 | ContractedServicesModule (subcollection): CRUD | Backend | ✅ | Dentro de `projects.service.ts` |
| 4.7 | CapabilityPhotosModule: upload + CRUD | Backend | ✅ | Endpoints em `projects.controller.ts` |
| 4.8 | Geração de código sequencial (001/2026) | Backend | ✅ | `projects.service.ts` (generateCode) |
| 4.9 | Validações de negócio do projeto | Backend | ✅ | `projects.controller.ts` |
| 4.10 | Lista de projetos com filtros (status, busca) | Frontend | ✅ | `projects-list-page.tsx` |
| 4.11 | Wizard de criação (6 etapas) | Frontend | ✅ | `project-form-page.tsx` |
| 4.12 | Página de detalhes com 5 abas + cards financeiros | Frontend | ✅ | `project-detail-page.tsx` |
| 4.13 | Edição de projeto | Frontend | ✅ | `project-form-page.tsx` (isEditing) |

**Entregáveis:**
- CRUD completo de projetos com 5 sub-entities
- Wizard de criação com 6 etapas (dados, detalhamento, locais, objetivos, equipe, serviços)
- Geração automática de código (001/2026)
- Filtros por status + busca textual
- Cards financeiros (valor total, recebido, executado, %)

---

### Sprint 5 — Metas, Atividades e Despesas (4 semanas) ✅

**Objetivo:** Workflow completo de metas com comprovação e aprovação, controle de despesas
**Commits:** `551771a`

| # | Tarefa | Responsável | Status | Arquivos |
|---|---|---|---|---|
| 5.1 | GoalsModule: CRUD no Firestore | Backend | ✅ | `goals.service.ts`, `goals.controller.ts` |
| 5.2 | Workflow de meta (send-analysis, approve, disapprove, send-accounting) | Backend | ✅ | `goals.service.ts` (6 metodos de workflow) |
| 5.3 | ActivitiesModule (subcollection de goals): CRUD | Backend | ✅ | `goals.controller.ts` (nested) |
| 5.4 | GoalProofsModule: upload + CRUD | Backend | ✅ | `goals.controller.ts` (nested) |
| 5.5 | GoalApprovalsModule: registro de aprovações | Backend | ✅ | Dentro de approve/disapprove |
| 5.6 | ExpensesModule: CRUD | Backend | ✅ | `expenses.service.ts`, `expenses.controller.ts` |
| 5.7 | Workflow de despesa (PENDENTE→APROVADO→PAGO) | Backend | ✅ | `expenses.service.ts` (updateStatus) |
| 5.8 | BudgetItemsModule: CRUD | Backend | ✅ | `budget-items/` (2 arquivos) |
| 5.9 | Seção de metas no projeto (lista + card expandível) | Frontend | ✅ | `goals-section.tsx` |
| 5.10 | Card de meta: atividades inline, comprovação (upload), aprovação | Frontend | ✅ | `goals-section.tsx` |
| 5.11 | Painel de aprovação para fiscal (Aprovar/Reprovar) | Frontend | ✅ | `goals-section.tsx` |
| 5.12 | Seção de despesas: lista com filtro por status + ação inline | Frontend | ✅ | `expenses-list-page.tsx` |
| 5.13 | Formulário de despesa | Frontend | ✅ | `expense-form-page.tsx` |

**Entregáveis:**
- Workflow de meta: Pendente → Em análise → Concluída / Desaprovada
- Atividades inline, upload de comprovações, registro de aprovações
- Despesas com workflow PENDENTE → APROVADO/REPROVADO → PAGO
- Budget items
- Aba "Metas" no detalhe do projeto

---

### Sprint 6 — Documentos, Diligências e Prestação de Contas (4 semanas) ✅

**Objetivo:** Repositório de documentos, sistema de diligências com resposta, relatórios financeiros
**Commits:** `c326380`

| # | Tarefa | Responsável | Status | Arquivos |
|---|---|---|---|---|
| 6.1 | DocumentsModule: upload Firebase Storage + CRUD metadata | Backend | ✅ | `documents/` (3 arquivos) |
| 6.2 | DiligencesModule: CRUD com workflow | Backend | ✅ | `diligences/` (3 arquivos) |
| 6.3 | Fluxo: ABERTA → RESPONDIDA → FECHADA/REABERTA | Backend | ✅ | `diligences.service.ts` (respond, close, reopen) |
| 6.4 | AccountingModule: CRUD relatórios financeiros + fotos | Backend | ✅ | `accounting/` (3 arquivos) |
| 6.5 | Grid de documentos com upload + preview + download | Frontend | ✅ | `documents-page.tsx` |
| 6.6 | Upload via Firebase Storage com progresso | Frontend | ✅ | `documents-page.tsx` |
| 6.7 | Timeline de diligências: resposta inline + parecer + reabrir | Frontend | ✅ | `diligences-page.tsx` |
| 6.8 | Lista de relatórios financeiros com galeria de fotos | Frontend | ✅ | `accounting-page.tsx` |
| 6.9 | Upload de fotos comprobatórias via Firebase Storage | Frontend | ✅ | `accounting-page.tsx` |

**Entregáveis:**
- Documentos com upload para Firebase Storage, grid de cards, download
- Diligências com timeline completa (criar → responder → parecer → fechar/reabrir)
- Prestação de contas com relatórios financeiros e galeria de fotos

---

### Sprint 7 — Pesquisa de Preços + Chat IA (5 semanas) ✅

**Objetivo:** Módulo mais inovador — cotação automatizada com IA, PNCP e mercado
**Commits:** `cd7eb70`

| # | Tarefa | Responsável | Status | Arquivos |
|---|---|---|---|---|
| 7.1 | PriceResearchModule: CRUD Firestore + stats + resultados | Backend | ✅ | `price-research.service.ts` |
| 7.2 | PriceResearchAggregator (min, max, avg, median) | Backend | ✅ | Dentro de `price-research.service.ts` |
| 7.3 | GroqClientService: interpretBatch + suggestProductDetails | Backend | ✅ | `integrations/groq-client.service.ts` |
| 7.4 | PncpService: busca em contratações públicas | Backend | ✅ | `integrations/pncp.service.ts` |
| 7.5 | MercadoLivreService: ML API | Backend | ✅ | `integrations/mercado-livre.service.ts` |
| 7.6 | ChatIaController: 4 endpoints (processar, selecionar, orcamento-manual, status) | Backend | ✅ | `chat-ia.controller.ts` |
| 7.7 | Interface de chat conversacional + painel resultados | Frontend | ✅ | `chat-ia-page.tsx` |
| 7.8 | Lista de pesquisas de preço com filtros | Frontend | ✅ | `price-research-list-page.tsx` |
| 7.9 | Checkbox seleção + orçamento manual + finalização | Frontend | ✅ | `chat-ia-page.tsx` |

**Entregáveis:**
- PriceResearch CRUD com estatísticas automáticas (min, max, média, mediana)
- Chat IA: texto livre → Groq interpreta → busca PNCP + Mercado Livre
- Seleção de cotações com checkboxes
- Orçamento manual com CNPJ e valor
- Finalização com definição de preço de referência + justificativa
- Lista de pesquisas com filtro por status

---

### Sprint 8 — Auditoria, Relatórios, Configurações (2 semanas) ✅

**Objetivo:** Logs de auditoria, gerador de relatórios, painel de configurações
**Commits:** `51d1097`

| # | Tarefa | Responsável | Status | Arquivos |
|---|---|---|---|---|
| 8.1 | AuditModule: GET /api/audit com filtros | Backend | ✅ | `audit.service.ts`, `audit.controller.ts` |
| 8.2 | LogService para registro de auditoria | Backend | ✅ | `audit.service.ts` |
| 8.3 | ReportsModule: relatório consolidado por projeto | Backend | ✅ | `reports.service.ts`, `reports.controller.ts` |
| 8.4 | SettingsModule: GET/PUT /api/settings | Backend | ✅ | `settings/` (3 arquivos) |
| 8.5 | DashboardModule: GET /api/dashboard/stats | Backend | ✅ | `dashboard/` (3 arquivos) |
| 8.6 | Tabela de auditoria com filtros avançados | Frontend | ✅ | `audit-page.tsx` |
| 8.7 | Gerador de relatórios consolidados (metas, despesas, diligencias) | Frontend | ✅ | `reports-page.tsx` |
| 8.8 | Página de configurações (SMTP + API Keys) | Frontend | ✅ | `settings-page.tsx` |
| 8.9 | Dashboard real com dados da API (6 cards) | Frontend | ✅ | `dashboard-page.tsx` |

**Entregáveis:**
- Auditoria com filtros por userId, ação, entidade, data
- Relatório consolidado por projeto (dados, metas, despesas, diligências)
- Configurações SMTP + API Keys (Groq, Asaas, Z-API)
- Dashboard com cards reais (projetos, instituições, orçamento, usuários)

---

### Sprint 9 — Diagnóstico, Deploy e Polimento (2 semanas) ✅

**Objetivo:** Sistema em produção, health checks, CI/CD, refinamentos finais
**Commits:** `018e989`

| # | Tarefa | Responsável | Status | Arquivos |
|---|---|---|---|---|
| 9.1 | DiagnosticsModule: health checks (Firebase Auth, Firestore, Storage) | Backend | ✅ | `diagnostics/` (3 arquivos) |
| 9.2 | ThrottlerModule: rate limiting (60 req/min) | Backend | ✅ | `app.module.ts` (ThrottlerGuard global) |
| 9.3 | Helmet: seguranca de headers HTTP | Backend | ✅ | `main.ts` (helmet) |
| 9.4 | Dockerfile NestJS (multi-stage build, node:22-alpine) | DevOps | ✅ | `apps/api/Dockerfile` |
| 9.5 | Dockerfile React (nginx, alpine) + nginx.conf | DevOps | ✅ | `apps/web/Dockerfile`, `nginx.conf` |
| 9.6 | Firestore indexes | DevOps | ✅ | `firestore.indexes.json` (12 indexes) |
| 9.7 | Storage rules | DevOps | ✅ | `storage.rules` |
| 9.8 | Firebase Hosting: frontend deployado | DevOps | ✅ | https://gestaosetor3.web.app |
| 9.9 | Firestore + Storage rules deployed | DevOps | ✅ | Firestore + Storage no ar |
| 9.10 | Pagina de diagnostico no frontend | Frontend | ✅ | `diagnostics-page.tsx` |

---

### Sprint 10 — Deploy da API + Infraestrutura (2 semanas) ✅

**Objetivo:** API NestJS em produção, CI/CD, domínio próprio
**Commits:** `78197dd`

| # | Tarefa | Responsável | Status | Arquivos |
|---|---|---|---|---|
| 10.1 | GitHub Actions CI/CD: lint → typecheck → test → deploy | DevOps | ✅ | `.github/workflows/deploy.yml` |
| 10.2 | Cloud Build config (deploy Cloud Run + Firebase) | DevOps | ✅ | `cloudbuild.yaml` |
| 10.3 | Script de deploy manual passo a passo | DevOps | ✅ | `scripts/deploy.ps1` |
| 10.4 | .env.production.example com configs de producao | DevOps | ✅ | `apps/web/.env.production.example` |
| 10.5 | build:prod script para build em modo production | DevOps | ✅ | `apps/web/package.json` |
| 10.6 | Secrets via Secret Manager (GROQ_API_KEY) | DevOps | ✅ | `cloudbuild.yaml` |
| 10.7 | Service account + permissoes Cloud Run (documentado) | DevOps | ⏳ Executar manual | `scripts/deploy.ps1` |
| 10.8 | Cloud Run deploy (pendente de execucao) | DevOps | ⏳ Executar manual | Comandos em `deploy.ps1` |
| 10.9 | Dominio proprio + SSL | DevOps | ⏳ Futuro | — |

**Entregáveis:**
- API NestJS rodando em producao (Cloud Run ou similar)
- Frontend React apontando para API real
- CI/CD automatizado (GitHub Actions)
- Dominio propio com SSL
- Monitoramento de uptime

---

### Sprint 11 — Migração de Dados (2 semanas) ⏳

**Objetivo:** Transportar dados do Laravel/MySQL para o Firestore

| # | Tarefa | Responsável | Status |
|---|---|---|---|
| 11.1 | Analisar schema MySQL e mapear para Firestore collections | Backend | ⏳ |
| 11.2 | Script: exportar usuarios do MySQL → Firestore | Backend | ⏳ |
| 11.3 | Script: exportar instituicoes + diretores + historico | Backend | ⏳ |
| 11.4 | Script: exportar projetos + sub-entities | Backend | ⏳ |
| 11.5 | Script: exportar metas + atividades + comprovacoes + aprovacoes | Backend | ⏳ |
| 11.6 | Script: exportar despesas + budget items | Backend | ⏳ |
| 11.7 | Script: exportar documentos → Firebase Storage + metadata | Backend | ⏳ |
| 11.8 | Script: exportar diligencias + respostas + pareceres | Backend | ⏳ |
| 11.9 | Script: exportar prestacao de contas + fotos → Firebase Storage | Backend | ⏳ |
| 11.10 | Script: exportar pesquisa de precos + resultados | Backend | ⏳ |
| 11.11 | Script: exportar audit logs | Backend | ⏳ |
| 11.12 | Script: exportar configuracoes (settings) | Backend | ⏳ |
| 11.13 | Migrar arquivos de storage local → Firebase Storage | Backend | ⏳ |
| 11.14 | Verificar integridade: contar registros origem vs destino | QA | ⏳ |

**Entregáveis:**
- Scripts de migracao para cada entidade
- Dados historicos disponiveis no novo sistema
- Arquivos migrados para Firebase Storage
- Relatorio de integridade (counts origem vs destino)

---

### Sprint 12 — Funcionalidades Faltantes (3 semanas) ⏳

**Objetivo:** Completar funcionalidades que ficaram de fora da Fase 1

| # | Tarefa | Responsável | Prioridade |
|---|---|---|---|
| 12.1 | PDF da ficha da instituicao (Puppeteer) | Backend | Alta |
| 12.2 | PDF do inventario do projeto (Puppeteer) | Backend | Alta |
| 12.3 | PDF do relatorio comparativo de precos (Puppeteer) | Backend | Alta |
| 12.4 | Upload de foto do presidente → Firebase Storage | Frontend | Alta |
| 12.5 | Upload de fotos de capacidade tecnica → Firebase Storage | Frontend | Alta |
| 12.6 | Upload de comprovacoes de metas → Firebase Storage | Frontend | Alta |
| 12.7 | Timeline visual de status do projeto | Frontend | Media |
| 12.8 | Notificacoes em tempo real (Firebase Realtime DB ou Cloud Messaging) | Backend | Media |
| 12.9 | Exportar CSV/Excel de despesas e relatorios | Frontend | Media |
| 12.10 | Dark mode persistente (localStorage) | Frontend | Baixa |
| 12.11 | Responsividade mobile refinada | Frontend | Baixa |
| 12.12 | Paginacao em todas as listas (verificar) | Frontend | Media |
| 12.13 | Filtros avancados em todas as listas | Frontend | Media |
| 12.14 | Loading states / spinners em operacoes demoradas | Frontend | Media |
| 12.15 | Confirmacao em todas as exclusoes | Frontend | Media |
| 12.16 | Autocomplete CNPJ/BrasilAPI no formulario de instituicao | Frontend | ✅ Ja feito |
| 12.17 | Autocomplete CEP/ViaCEP no formulario de instituicao | Frontend | ✅ Ja feito |
| 12.18 | Autocomplete CNPJ no orcamento manual (Chat IA) | Frontend | Media |
| 12.19 | Integracao Radar TCE-MT (servico existente no Laravel) | Backend | Baixa |
| 12.20 | Auditoria real: interceptor escrevendo no Firestore | Backend | Media |

**Entregáveis:**
- PDFs de instituicao, projeto e pesquisa de precos
- Upload de fotos e comprovacoes via Firebase Storage
- Timeline de status, notificacoes, exportacao CSV
- Polimento de UX: loading, confirmacao, responsividade

---

### Sprint 13 — Testes e Qualidade (3 semanas) ⏳

**Objetivo:** Garantir qualidade com testes automatizados

| # | Tarefa | Responsável | Tipo |
|---|---|---|---|
| 13.1 | Configurar Jest + Supertest para testes NestJS | Backend | Infra |
| 13.2 | Teste unitario: AuthService (verifyToken, syncUser) | Backend | Unitario |
| 13.3 | Teste unitario: Cnpj validation | Backend | Unitario |
| 13.4 | Teste unitario: PriceResearchAggregator (stats) | Backend | Unitario |
| 13.5 | Teste unitario: Project code generation | Backend | Unitario |
| 13.6 | Teste de integracao: Institutions CRUD | Backend | Integracao |
| 13.7 | Teste de integracao: Projects CRUD + sub-entities | Backend | Integracao |
| 13.8 | Teste de integracao: Goals workflow (send-analysis, approve, disapprove) | Backend | Integracao |
| 13.9 | Teste de integracao: Expenses status workflow | Backend | Integracao |
| 13.10 | Teste de integracao: Diligences workflow | Backend | Integracao |
| 13.11 | Teste de integracao: Chat IA (processar, selecionar, orcamento-manual) | Backend | Integracao |
| 13.12 | Configurar Cypress para testes E2E | Frontend | Infra |
| 13.13 | Teste E2E: Fluxo de login + dashboard | Frontend | E2E |
| 13.14 | Teste E2E: CRUD instituicao | Frontend | E2E |
| 13.15 | Teste E2E: Criacao de projeto wizard | Frontend | E2E |
| 13.16 | Teste E2E: Fluxo de meta (criar → analisar → aprovar) | Frontend | E2E |
| 13.17 | Teste E2E: Fluxo de despesa (criar → aprovar → pagar) | Frontend | E2E |
| 13.18 | Teste E2E: Chat IA (texto → processar → selecionar → finalizar) | Frontend | E2E |
| 13.19 | Teste de seguranca: RBAC (tentar acessar rota sem permissao) | QA | Seguranca |
| 13.20 | Teste de performance: Firestore reads optimization | QA | Performance |

**Entregáveis:**
- Suite de testes unitarios (NestJS)
- Suite de testes de integracao (NestJS)
- Suite de testes E2E (Cypress)
- Testes de seguranca RBAC
- Relatorio de cobertura

---

## 6. Resumo de Esforço

| Sprint | Módulos | Status | Semanas | Dias Úteis |
|---|---|---|---|---|
| **Sprint 1** | Fundação e Setup | ✅ **Concluída** | 3 | 15 |
| **Sprint 2** | Autenticação e Usuários | ✅ **Concluída** | 2 | 10 |
| **Sprint 3** | Instituições | ✅ **Concluída** | 3 | 15 |
| **Sprint 4** | Projetos | ✅ **Concluída** | 5 | 25 |
| **Sprint 5** | Metas, Atividades e Despesas | ✅ **Concluída** | 4 | 20 |
| **Sprint 6** | Documentos, Diligências e Prestação de Contas | ✅ **Concluída** | 4 | 20 |
| **Sprint 7** | Pesquisa de Preços + Chat IA | ✅ **Concluída** | 5 | 25 |
| **Sprint 8** | Auditoria, Relatórios e Configurações | ✅ **Concluída** | 2 | 10 |
| **Sprint 9** | Diagnóstico, Deploy e Polimento | ✅ **Concluída** | 2 | 10 |
| **Sprint 10** | Deploy da API + Infraestrutura | ✅ **Concluída** | 2 | 10 |
| **Sprint 11** | Migração de Dados | ⏳ Pendente | 2 | 10 |
| **Sprint 12** | Funcionalidades Faltantes | ⏳ Pendente | 3 | 15 |
| **Sprint 13** | Testes e Qualidade | ⏳ Pendente | 3 | 15 |
| **Total Fase 1** | | ✅ **100%** | **30 semanas** | **~150 dias** |
| **Total Fase 2** | | ⏳ **Pendente** | **10 semanas** | **~50 dias** |

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

*Gerado em 21/07/2026 — Versão 3.2 (Fase 1: 9/9 ✅ | Fase 2: 1/4 concluída — Sprint 10 deploy/infra)*
