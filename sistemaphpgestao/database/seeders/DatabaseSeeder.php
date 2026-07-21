<?php

namespace Database\Seeders;

use App\Models\{User, Institution, Director, FundingSource, Project, Goal, Activity, Expense, Diligence, AccountingReport, Document};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SettingSeeder::class);

        // ── Usuários ──────────────────────────────────────────────────────
        $admin = User::create([
            'name'     => 'Administrador Geral',
            'email'    => 'admin@gestao.org',
            'password' => Hash::make('Admin@Gestao#2026'),
            'role'     => 'ADMIN_GERAL',
            'active'   => true,
        ]);

        // Usuário exclusivo para exclusão permanente de registros
        User::create([
            'name'     => 'Usuário Exclusão',
            'email'    => 'exclusao@gestao.org',
            'password' => Hash::make('Excl@Delete#2026!'),
            'role'     => 'ADMIN_GERAL',
            'active'   => true,
        ]);

        User::create([
            'name'     => 'Coordenador de Projetos',
            'email'    => 'coordenador@gestao.org',
            'password' => Hash::make('Coord$Proj#2026!'),
            'role'     => 'GESTOR_PROJETO',
            'active'   => true,
        ]);

        // ── Instituições ──────────────────────────────────────────────────
        $inst1 = Institution::create([
            'razao_social'       => 'Associação Beneficente Esperança',
            'nome_fantasia'      => 'ABE - Esperança',
            'cnpj'               => '12.345.678/0001-99',
            'email'              => 'contato@esperanca.org.br',
            'telefone'           => '(11) 3456-7890',
            'municipio'          => 'São Paulo',
            'estado'             => 'SP',
            'area_atuacao'       => 'Assistência Social',
            'representante_legal'=> 'Maria da Silva',
            'active'             => true,
        ]);

        $inst2 = Institution::create([
            'razao_social'       => 'Fundação Construindo Futuros',
            'nome_fantasia'      => 'Construindo Futuros',
            'cnpj'               => '98.765.432/0001-11',
            'email'              => 'info@construindofuturos.org',
            'telefone'           => '(21) 2345-6789',
            'municipio'          => 'Rio de Janeiro',
            'estado'             => 'RJ',
            'area_atuacao'       => 'Educação',
            'representante_legal'=> 'João Oliveira',
            'active'             => true,
        ]);

        // Usuários por instituição
        User::create([
            'name'           => 'Gestora ABE',
            'email'          => 'gestora@esperanca.org.br',
            'password'       => Hash::make('password'),
            'role'           => 'ADMIN_INSTITUICAO',
            'institution_id' => $inst1->id,
            'active'         => true,
        ]);

        User::create([
            'name'           => 'Financeiro ABE',
            'email'          => 'financeiro@esperanca.org.br',
            'password'       => Hash::make('password'),
            'role'           => 'FINANCEIRO',
            'institution_id' => $inst1->id,
            'active'         => true,
        ]);

        // ── Diretores ─────────────────────────────────────────────────────
        Director::create([
            'institution_id'  => $inst1->id,
            'nome'            => 'Maria da Silva',
            'cargo'           => 'Presidente',
            'cpf'             => '123.456.789-00',
            'email'           => 'maria@esperanca.org.br',
            'mandato_inicio'  => '2022-01-01',
            'mandato_fim'     => '2025-12-31',
        ]);

        Director::create([
            'institution_id'  => $inst2->id,
            'nome'            => 'João Oliveira',
            'cargo'           => 'Diretor Executivo',
            'cpf'             => '987.654.321-00',
            'email'           => 'joao@construindofuturos.org',
            'mandato_inicio'  => '2023-01-01',
            'mandato_fim'     => '2026-12-31',
        ]);

        // ── Fontes de Recurso ─────────────────────────────────────────────
        $fonte1 = FundingSource::create([
            'institution_id'  => $inst1->id,
            'nome'            => 'Fundo Municipal de Assistência Social',
            'tipo'            => 'PUBLICO',
            'orgao_concedente'=> 'Prefeitura de São Paulo',
            'valor_aprovado'  => 500000.00,
            'instrumento'     => 'Convênio',
            'numero'          => 'CONV-2023-001',
            'data_inicio'     => '2023-01-01',
            'data_fim'        => '2025-12-31',
            'status'          => 'ATIVO',
        ]);

        $fonte2 = FundingSource::create([
            'institution_id'  => $inst2->id,
            'nome'            => 'Programa Nacional de Educação',
            'tipo'            => 'FEDERAL',
            'orgao_concedente'=> 'Ministério da Educação',
            'valor_aprovado'  => 300000.00,
            'instrumento'     => 'Termo de Fomento',
            'numero'          => 'TF-2024-042',
            'data_inicio'     => '2024-03-01',
            'data_fim'        => '2026-02-28',
            'status'          => 'ATIVO',
        ]);

        // ── Projetos ──────────────────────────────────────────────────────
        $proj1 = Project::create([
            'institution_id'      => $inst1->id,
            'funding_source_id'   => $fonte1->id,
            'nome'                => 'Programa de Apoio Familiar Integrado',
            'codigo'              => 'ABE-2023-001',
            'descricao'           => 'Programa de atendimento e apoio a famílias em vulnerabilidade social no município de São Paulo.',
            'objetivo_geral'      => 'Promover o fortalecimento de vínculos familiares e a inclusão social de 200 famílias.',
            'publico_alvo'        => 'Famílias em situação de vulnerabilidade social',
            'valor_total'         => 250000.00,
            'valor_recebido'      => 200000.00,
            'valor_executado'     => 145000.00,
            'data_inicio'         => '2023-03-01',
            'data_fim'            => '2025-02-28',
            'status'              => 'EM_EXECUCAO',
            'responsavel'         => 'Maria da Silva',
            'local_execucao'      => 'São Paulo - SP',
        ]);

        $proj2 = Project::create([
            'institution_id'      => $inst1->id,
            'nome'                => 'Oficinas de Capacitação Profissional',
            'codigo'              => 'ABE-2024-002',
            'descricao'           => 'Capacitação profissional para jovens de 16 a 24 anos em situação de vulnerabilidade.',
            'objetivo_geral'      => 'Capacitar 150 jovens em habilidades profissionais.',
            'publico_alvo'        => 'Jovens de 16 a 24 anos',
            'valor_total'         => 80000.00,
            'valor_recebido'      => 80000.00,
            'valor_executado'     => 80000.00,
            'data_inicio'         => '2024-01-01',
            'data_fim'            => '2024-12-31',
            'status'              => 'CONCLUIDO',
            'responsavel'         => 'Ana Costa',
            'local_execucao'      => 'São Paulo - SP',
        ]);

        $proj3 = Project::create([
            'institution_id'      => $inst2->id,
            'funding_source_id'   => $fonte2->id,
            'nome'                => 'Escola do Futuro Digital',
            'codigo'              => 'CF-2024-001',
            'descricao'           => 'Programa de inclusão digital e reforço escolar para crianças de 8 a 14 anos.',
            'objetivo_geral'      => 'Atender 300 crianças com acesso a tecnologia e educação de qualidade.',
            'publico_alvo'        => 'Crianças de 8 a 14 anos em escolas públicas',
            'valor_total'         => 180000.00,
            'valor_recebido'      => 90000.00,
            'valor_executado'     => 42000.00,
            'data_inicio'         => '2024-06-01',
            'data_fim'            => '2026-05-31',
            'status'              => 'EM_EXECUCAO',
            'responsavel'         => 'João Oliveira',
            'local_execucao'      => 'Rio de Janeiro - RJ',
        ]);

        // ── Metas ─────────────────────────────────────────────────────────
        $meta1 = Goal::create([
            'project_id'           => $proj1->id,
            'titulo'               => 'Atendimento de Famílias',
            'descricao'            => 'Realizar atendimentos individuais e em grupo para 200 famílias.',
            'indicador'            => 'Número de famílias atendidas',
            'quantidade_prevista'  => 200,
            'quantidade_realizada' => 156,
            'unidade_medida'       => 'famílias',
            'valor_previsto'       => 120000.00,
            'prazo'                => '2025-02-28',
            'responsavel'          => 'Equipe Social',
            'status'               => 'EM_ANDAMENTO',
        ]);

        $meta2 = Goal::create([
            'project_id'           => $proj1->id,
            'titulo'               => 'Oficinas de Fortalecimento de Vínculos',
            'descricao'            => 'Realizar 24 oficinas mensais de fortalecimento familiar.',
            'indicador'            => 'Número de oficinas realizadas',
            'quantidade_prevista'  => 24,
            'quantidade_realizada' => 18,
            'unidade_medida'       => 'oficinas',
            'valor_previsto'       => 60000.00,
            'prazo'                => '2025-02-28',
            'responsavel'          => 'Psicóloga Ana Lima',
            'status'               => 'EM_ANDAMENTO',
        ]);

        $meta3 = Goal::create([
            'project_id'           => $proj3->id,
            'titulo'               => 'Aulas de Informática Básica',
            'descricao'            => 'Oferecer 120 turmas de informática básica para crianças.',
            'indicador'            => 'Número de turmas realizadas',
            'quantidade_prevista'  => 120,
            'quantidade_realizada' => 48,
            'unidade_medida'       => 'turmas',
            'valor_previsto'       => 90000.00,
            'prazo'                => '2025-12-31',
            'responsavel'          => 'Equipe TI',
            'status'               => 'EM_ANDAMENTO',
        ]);

        // ── Atividades ────────────────────────────────────────────────────
        Activity::create([
            'goal_id'              => $meta1->id,
            'nome'                 => 'Visitas domiciliares',
            'descricao'            => 'Realização de visitas às famílias cadastradas',
            'data_inicio'          => '2023-03-15',
            'data_fim'             => '2025-02-28',
            'responsavel'          => 'Assistente Social',
            'percentual_execucao'  => 78,
            'status'               => 'EM_ANDAMENTO',
        ]);

        Activity::create([
            'goal_id'              => $meta1->id,
            'nome'                 => 'Encaminhamentos a serviços',
            'descricao'            => 'Encaminhamento de famílias para serviços da rede de proteção social',
            'data_inicio'          => '2023-04-01',
            'data_fim'             => '2025-02-28',
            'responsavel'          => 'Equipe Social',
            'percentual_execucao'  => 65,
            'status'               => 'EM_ANDAMENTO',
        ]);

        // ── Despesas ──────────────────────────────────────────────────────
        Expense::create([
            'project_id'   => $proj1->id,
            'goal_id'      => $meta1->id,
            'categoria'    => 'Recursos Humanos',
            'fornecedor'   => 'Assistente Social Contratada',
            'descricao'    => 'Honorários Assistente Social - Março/2024',
            'data_despesa' => '2024-03-31',
            'data_pagamento'=> '2024-04-05',
            'valor'        => 4500.00,
            'forma_pagamento'=> 'Transferência',
            'status'       => 'PAGO',
        ]);

        Expense::create([
            'project_id'   => $proj1->id,
            'goal_id'      => $meta2->id,
            'categoria'    => 'Material de Consumo',
            'fornecedor'   => 'Papelaria Ideal Ltda',
            'cnpj_fornecedor'=> '11.222.333/0001-44',
            'descricao'    => 'Material para oficinas - papel, canetas, cadernos',
            'data_despesa' => '2024-04-10',
            'valor'        => 1200.00,
            'forma_pagamento'=> 'Boleto',
            'numero_nf'    => 'NF-001234',
            'status'       => 'APROVADO',
        ]);

        Expense::create([
            'project_id'   => $proj1->id,
            'categoria'    => 'Serviços de Terceiros',
            'fornecedor'   => 'Consultoria Técnica ABC',
            'descricao'    => 'Consultoria em gestão de projetos sociais',
            'data_despesa' => '2024-05-15',
            'valor'        => 8000.00,
            'forma_pagamento'=> 'Transferência',
            'status'       => 'PENDENTE',
        ]);

        Expense::create([
            'project_id'   => $proj3->id,
            'goal_id'      => $meta3->id,
            'categoria'    => 'Equipamentos',
            'fornecedor'   => 'TechShop Informática',
            'cnpj_fornecedor'=> '55.666.777/0001-88',
            'descricao'    => 'Tablets para aulas de informática (20 unidades)',
            'data_despesa' => '2024-07-20',
            'data_pagamento'=> '2024-07-25',
            'valor'        => 18000.00,
            'forma_pagamento'=> 'Boleto',
            'numero_nf'    => 'NF-005678',
            'status'       => 'PAGO',
        ]);

        Expense::create([
            'project_id'   => $proj3->id,
            'categoria'    => 'Recursos Humanos',
            'fornecedor'   => 'Professor Instrutor TI',
            'descricao'    => 'Honorários instrutor de informática - Jul-Set/2024',
            'data_despesa' => '2024-09-30',
            'valor'        => 9000.00,
            'forma_pagamento'=> 'Transferência',
            'status'       => 'APROVADO',
        ]);

        // ── Diligências ───────────────────────────────────────────────────
        Diligence::create([
            'project_id'  => $proj1->id,
            'tipo'        => 'DOCUMENTAL',
            'descricao'   => 'Enviar comprovantes de despesas do 1º semestre de 2024',
            'responsavel' => 'Maria da Silva',
            'prazo'       => '2024-08-31',
            'status'      => 'RESPONDIDA',
            'resposta'    => 'Documentos enviados via e-mail em 15/08/2024.',
        ]);

        Diligence::create([
            'project_id'  => $proj1->id,
            'tipo'        => 'TECNICA',
            'descricao'   => 'Apresentar relatório de atendimentos do 3º trimestre com assinatura das famílias.',
            'responsavel' => 'Coordenação Social',
            'prazo'       => '2024-10-31',
            'status'      => 'PENDENTE',
        ]);

        Diligence::create([
            'project_id'  => $proj3->id,
            'tipo'        => 'FINANCEIRA',
            'descricao'   => 'Justificar aquisição de tablets acima do valor previsto em orçamento.',
            'responsavel' => 'João Oliveira',
            'prazo'       => '2024-09-15',
            'status'      => 'EM_ANALISE',
            'resposta'    => 'Os preços aumentaram devido à variação cambial. Cotações anexadas.',
        ]);

        // ── Prestação de Contas ───────────────────────────────────────────
        AccountingReport::create([
            'project_id'     => $proj1->id,
            'status'         => 'APROVADA',
            'observacoes'    => 'Prestação de contas do 1º semestre aprovada sem ressalvas.',
            'data_envio'     => '2023-07-31',
            'data_aprovacao' => '2023-09-15',
        ]);

        AccountingReport::create([
            'project_id'  => $proj1->id,
            'status'      => 'PENDENTE',
            'observacoes' => 'Aguardando documentação complementar para fechamento do 2º semestre.',
            'data_envio'  => '2024-01-31',
        ]);

        AccountingReport::create([
            'project_id'  => $proj3->id,
            'status'      => 'PENDENTE',
            'observacoes' => 'Primeira prestação de contas do projeto.',
            'data_envio'  => '2024-12-31',
        ]);

        // ── Documentos ────────────────────────────────────────────────────
        Document::create([
            'institution_id' => $inst1->id,
            'nome'           => 'Estatuto Social - ABE',
            'categoria'      => 'JURIDICO',
            'tipo'           => 'PDF',
            'url'            => '/documentos/estatuto-abe.pdf',
            'tamanho'        => '1.2MB',
            'status_analise' => 'APROVADO',
        ]);

        Document::create([
            'project_id'     => $proj1->id,
            'institution_id' => $inst1->id,
            'nome'           => 'Plano de Trabalho - PAFI 2023',
            'categoria'      => 'PROJETO',
            'tipo'           => 'PDF',
            'url'            => '/documentos/plano-pafi-2023.pdf',
            'tamanho'        => '3.5MB',
            'status_analise' => 'APROVADO',
        ]);

        Document::create([
            'project_id'     => $proj3->id,
            'institution_id' => $inst2->id,
            'nome'           => 'Relatório de Atividades - Jul/2024',
            'categoria'      => 'RELATORIO',
            'tipo'           => 'PDF',
            'url'            => '/documentos/relatorio-jul-2024.pdf',
            'tamanho'        => '2.1MB',
            'status_analise' => 'PENDENTE',
        ]);
    }
}
