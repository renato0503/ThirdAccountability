# Plano de Implementação — Gestão Terceiro Setor

> Baseado no código atual (45 migrations, 28 models, 30 controllers, 62 views)

---

## Legenda

- ✅ **Feito** — implementado e funcional
- 🔶 **Parcial** — existe mas incompleto
- ❌ **Falta** — não iniciado

---

## Sprint 1 — Fundação e Infraestrutura (prioridade máxima)

> Objetivo: sistema estável, versionado e deploys funcionais

| Item | Status | Observação |
|---|---|---|
| **Git + repositório remoto** | ❌ | Criar repo privado GitHub, subir código |
| **Contato com cliente** (FTP, DNS, banco) | ❌ | Ver checklist.md |
| **Testar conexão com banco remoto** | ❌ | Confirmar Dbaas ativo |
| **Deploy manual funcional** | ❌ | FTP/SSH para production.byrees.com |
| **Configurar .env de produção** | 🔶 | Parcial — falta MAIL, API keys |
| **Obter API Key Groq** | ❌ | Necessário para o Chat IA |
| **Configurar SSL / HTTPS** | ✅ | Já ativo no servidor |
| **Storage link funcional** | ✅ | `public/storage` → `storage/app/public` |
| **DiagnosticController** | ✅ | Migrations, caches, storage link |
| **Limpeza de lixo** (pastas `lixo/`) | ❌ | Remover diretórios obsoletos |

---

## Sprint 2 — Correções e Estabilização

> Objetivo: corrigir bugs conhecidos e preencher lacunas do código existente

| Item | Status | Observação |
|---|---|---|
| **Package.json / Vite** | ❌ | Frontend usa Bootstrap 5 via CDN — funcional, mas sem build |
| **Configurar Vite + assets** | 🔶 | Opcional — considerar se precisar de JS moderno |
| **Criar `routes/api.php`** | ❌ | Necessário para Chat IA |
| **Verificar e corrigir views/projects/show.blade.php** | 🔶 | Backup existente (`show.blade.php.bak`) |
| **Verificar views/pdf/ duplicadas** | 🔶 | Backups `.bak` em pdf/ |
| **Testar todas as rotas CRUD manualmente** | ❌ | Validar se quebrou algo |
| **Corrigir Breeze auth views** | ✅ | Funcionando |
| **Configurar fila (queue) com jobs** | ❌ | Driver database já configurado, sem jobs |
| **AppServiceProvider** | 🔶 | Vazio — adicionar binds de serviço |

---

## Sprint 3 — Chat IA (Módulo Novo)

> Objetivo: implementar o assistente conversacional para pesquisa de preços

### Backend

| Item | Status | Estimativa |
|---|---|---|
| **Criar `ChatIaController`** | ❌ | 1 dia |
| **POST `/api/chat-ia/processar`** | ❌ | 1 dia |
| **POST `/api/chat-ia/selecionar`** | ❌ | ½ dia |
| **POST `/api/chat-ia/orcamento-manual`** | ❌ | 1 dia |
| **GET `/api/chat-ia/status/{id}`** | ❌ | ½ dia |
| **Criar `GroqClient` service** | ❌ | 1 dia |
| **Criar migration campos extras (`price_research_results`)** | ❌ | ½ dia |
| **Integrar Groq IA (interpretação de lote)** | ❌ | 2 dias |
| **Busca paralela PNCP + mercado** | ❌ | 1 dia |
| **Upload de anexos (orçamento manual)** | ❌ | 1 dia |
| **AuditLog para ações do chat** | ❌ | ½ dia |
| **Criar rotas em `routes/api.php`** | ❌ | ½ dia |

### Frontend

| Item | Status | Estimativa |
|---|---|---|
| **Interface de chat (campo texto + botão)** | ❌ | 1 dia |
| **Painel de resultados (cards por item)** | ❌ | 2 dias |
| **Tabela cotações PNCP + mercado** | ❌ | 1 dia |
| **Checkbox de seleção por cotação** | ❌ | ½ dia |
| **Modal "Adicionar Orçamento Manual"** | ❌ | 1 dia |
| **Máscara CNPJ + autocomplete BrasilAPI** | 🔶 | BrasilAPI já existe, integrar |
| **Upload de arquivo com preview** | ❌ | ½ dia |
| **Modal de finalização + justificativa** | ❌ | 1 dia |
| **Indicador de loading / feedback visual** | ❌ | ½ dia |
| **Integração com PDF existente** | 🔶 | PDF de price-research já existe |

---

## Sprint 4 — Testes Automatizados

> Objetivo: garantir qualidade mínima com testes

| Item | Status | Observação |
|---|---|---|
| **Configurar PHPUnit + suites** | 🔶 | Já configurado, sem testes |
| **Feature test: autenticação** | ❌ | Login, logout, registro |
| **Feature test: InstitutionController CRUD** | ❌ | Criar, editar, deletar instituição |
| **Feature test: ProjectController CRUD** | ❌ | Projetos com funding sources |
| **Feature test: GoalController + fluxo** | ❌ | Criar meta, enviar análise, aprovar |
| **Feature test: ExpenseController** | ❌ | Criar, status, filtros |
| **Feature test: PriceResearchController** | ❌ | CRUD + busca + seleção |
| **Feature test: DiligenceController** | ❌ | Criar, responder, parecer |
| **Teste unitário: CnpjService** | ❌ | Validação de CNPJ |
| **Teste unitário: CepService** | ❌ | Formatação de CEP |
| **Teste unitário: PncpPriceService** | ❌ | Parse de respostas |
| **Teste unitário: GroqClient** | ❌ | Prompt engineering |
| **Feature test: Chat IA endpoints** | ❌ | API processar, selecionar, orçamento |

---

## Sprint 5 — Filas e Performance

> Objetivo: operações assíncronas para não travar o usuário

| Item | Status | Observação |
|---|---|---|
| **Criar Job: BuscarPrecosPncp** | ❌ | Disparar busca PNCP em background |
| **Criar Job: BuscarPrecosMercado** | ❌ | Disparar busca mercado em background |
| **Criar Job: ProcessarLoteChatIa** | ❌ | Interpretação IA + buscas paralelas |
| **Criar Job: GerarRelatorioPdf** | ❌ | Geração de PDF assíncrona |
| ** Configurar worker de fila** | ❌ | `php artisan queue:work` |
| **Cache Redis (opcional)** | ❌ | Configurar para filas + cache |

---

## Sprint 6 — Notificações e E-mail

> Objetivo: notificações reais para usuários

| Item | Status | Observação |
|---|---|---|
| **Configurar SMTP real** | ❌ | Env vars MAIL_* preencher |
| **Implementar envio de e-mail de boas-vindas** | 🔶 | Mailable `BoasVindasMail.php` existe, não enviado |
| **Implementar envio de diligência por e-mail** | 🔶 | Mailable `DiligenciaNovaEmail.php` existe, não enviado |
| **Notificação de aprovação/desaprovação de meta** | ❌ | E-mail para o gestor |
| **Notificação de nova pesquisa de preços** | ❌ | E-mail para o fiscal |
| **Notificação de prestação de contas pendente** | ❌ | E-mail para o admin da instituição |

---

## Sprint 7 — Integrações Externas

> Objetivo: APIs de parceiros

| Item | Status | Observação |
|---|---|---|
| **BrasilAPI (CNPJ)** | ✅ | Funcionando com cache 24h |
| **ViaCEP (CEP)** | ✅ | Funcionando com cache 24h |
| **PNCP (Portal Nacional de Contratações)** | 🔶 | Service criado, testar integração real |
| **Radar TCE-MT** | 🔶 | Service criado, testar integração real |
| **Agregador Mercado (Zoom + Buscapé)** | 🔶 | Service criado, testar scraping |
| **Groq IA** | ❌ | SDK + Chat IA (Sprint 3) |
| **Asaas (pagamentos)** | ❌ | Placeholder no env |
| **Z-API (WhatsApp)** | ❌ | Placeholder no env |
| **D4Sign (assinatura digital)** | ❌ | Placeholder no env |
| **Pluggy (Open Finance)** | ❌ | Placeholder no env |
| **Google Maps (geolocalização)** | ❌ | Planejado |

---

## Sprint 8 — Polimento e UX

> Objetivo: sistema apresentável e profissional

| Item | Status | Observação |
|---|---|---|
| **Traduções pt_BR completas** | ✅ | auth, pagination, passwords, validation |
| **Responsividade (Bootstrap 5)** | ✅ | Mobile-friendly |
| **Validação de formulários** | 🔶 | Melhorar feedback visual |
| **Loading states / spinners** | ❌ | Adicionar nas operações demoradas |
| **Mensagens de erro amigáveis** | ❌ | Em vez de exceções cruas |
| **Confirmação em exclusões** | 🔶 | Parcial — adicionar onde faltar |
| **Paginação em listas** | ❌ | Instituições, projetos, despesas |
| **Filtros avançados** | 🔶 | Parcial — melhorar busca textual |
| **Exportar CSV/Excel** | ❌ | Além de PDF |
| **Dark mode** | ❌ | Nice to have |

---

## Resumo de Esforço

| Sprint | Estimativa | Descrição |
|---|---|---|
| **Sprint 1 — Fundação** | 2–3 dias | Git, deploy, contato cliente, API keys |
| **Sprint 2 — Correções** | 3–5 dias | Estabilizar código existente |
| **Sprint 3 — Chat IA** | 12–15 dias | Módulo principal novo |
| **Sprint 4 — Testes** | 5–7 dias | Cobertura básica dos controllers |
| **Sprint 5 — Filas** | 3–4 dias | Jobs assíncronos |
| **Sprint 6 — Notificações** | 2–3 dias | E-mails reais |
| **Sprint 7 — Integrações** | 5–7 dias | APIs restantes |
| **Sprint 8 — Polimento** | 3–5 dias | UX e refinamentos |

**Total estimado:** ~35–50 dias úteis (2–3 meses) para **sistema 100% funcional** com todos os módulos operacionais, testados e em produção.

**Mínimo viável para voltar ao ar:** Sprint 1 + Sprint 2 (~1 semana) — sistema já roda, só precisa de deploy estável e contato com cliente.

---

*Gerado em 20/07/2026*
