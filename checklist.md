# Checklist — Perguntas para o Cliente

## 1. Acesso ao Servidor (Hospedagem)

- [ ] Credenciais de **FTP** ou **SSH** do `project.byrees.com` (host, usuário, senha, porta)
- [ ] Acesso ao **painel de controle** da hospedagem (cPanel, Plesk, Helm, etc.) — URL + login
- [ ] Quem é o provedor de hospedagem? (HostGator, Locaweb, KingHost, UOL Host, etc.)
- [ ] O plano atual suporta **PHP 8.3+**? Se não, precisa upgrade
- [ ] O plano atual tem **SSL** ativo? (HTTPS já funciona?)

## 2. Domínio e DNS

- [ ] Quem gerencia o domínio `byrees.com`? (cliente tem acesso ao painel DNS?)
- [ ] O subdomínio `project.byrees.com` aponta para a hospedagem atual?
- [ ] Se precisar criar novos subdomínios (ex: `api.project.byrees.com`), tem acesso?
- [ ] Conta de **e-mail institucional** disponível? (para cadastrar em serviços: GitHub, Groq, etc.)

## 3. Banco de Dados

- [ ] O banco atual (`gestao3setor.mysql.dbaas.com.br`) ainda está ativo?
- [ ] Quem paga/mantém a conta no **Dbaas**? (credenciais de acesso ao painel Dbaas)
- [ ] Pedir um **dump SQL** de segurança do banco atual (backup manual)
- [ ] Confirmar: **usuário/senha** do banco ainda são os mesmos do `.env`?

## 4. API Keys e Serviços (se já tiver contratado)

- [ ] **Groq IA**: já tem chave de API? (precisa pra IA)
- [ ] **Asaas**: já tem conta/configurado token?
- [ ] **Z-API**: já tem instância do WhatsApp configurada?
- [ ] **D4Sign**: já tem conta para assinatura digital?
- [ ] **Pluggy**: já tem client_id/client_secret?
- [ ] **SendGrid / Mailgun / Brevo**: já tem serviço de e-mail transacional?

## 5. Domínio / Marca

- [ ] O nome "Gestão Terceiro Setor" é o nome definitivo? Ou quer outro?
- [ ] URL definitiva: mantém `project.byrees.com/sistemaphpgestao` ou quer um domínio próprio (ex: `gestaoterceirosetor.com.br`)?

## 6. Versionamento e Suporte

- [ ] Já existe repositório **Git** em algum lugar? (GitHub, GitLab, Bitbucket)
- [ ] Se não: posso criar um repositório particular meu — ok?
- [ ] Quer acesso ao repositório para acompanhar o desenvolvimento?

## 7. Contrato e Alinhamento

- [ ] Quem autoriza as alterações/manutenção no sistema?
- [ ] Tem urgência? Prazo desejado para voltar ao ar?
- [ ] Vai precisar de **nota fiscal**? (PJ ou PF?)
- [ ] Orçamento da hospedagem (servidor atual) é por conta dele ou minha?

---

## Resumo do que preciso para começar (prioridade)

1. **FTP/SSH** da hospedagem → *sem isso não sobe nada*
2. **Backup do banco** → *segurança antes de qualquer alteração*
3. **Confirmação de que o banco ainda está ativo** → *se caiu, precisa reativar*
4. **API Groq** → *necessária pro Chat IA*
