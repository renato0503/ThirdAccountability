import { ProjectStatus } from './enums';

export interface CreateInstitutionDto {
  razaoSocial: string;
  nomeFantasia?: string;
  cnpj: string;
  email?: string;
  telefone?: string;
  site?: string;
  instagram?: string;
  endereco?: {
    logradouro?: string;
    numero?: string;
    complemento?: string;
    bairro?: string;
    municipio?: string;
    estado?: string;
    cep?: string;
  };
}

export interface UpdateInstitutionDto extends Partial<CreateInstitutionDto> {
  active?: boolean;
}

export interface CreateProjectDto {
  nome: string;
  institutionId: string;
  fundingSourceId?: string;
  codigo?: string;
  numeroProposta?: string;
  fonte?: string;
  parlamentar?: string;
  secretaria?: string;
  descricao?: string;
  objetivoGeral?: string;
  publicoAlvo?: string;
  quantidadePublico?: number;
  valorTotal?: number;
  valorRecebido?: number;
  dataInicio?: string;
  dataFim?: string;
  status?: ProjectStatus;
  responsavel?: string;
  localExecucao?: string;
}

export interface UpdateProjectDto extends Partial<CreateProjectDto> {
  valorExecutado?: number;
}

export interface CreateGoalDto {
  numero: number;
  titulo: string;
  descricao?: string;
  orcamento?: number;
  afericao?: string;
}

export interface CreateExpenseDto {
  projectId: string;
  categoria: string;
  descricao: string;
  valor: number;
  dataGasto: string;
  fornecedor?: string;
}

export interface CreatePriceResearchDto {
  institutionId: string;
  projectId?: string;
  searchTerm: string;
  category?: string;
  quantity?: number;
  unit?: string;
  sources?: string[];
  state?: string;
  city?: string;
  dateStart?: string;
  dateEnd?: string;
}

export interface ChatIaProcessarDto {
  texto: string;
  institutionId: string;
  projectId?: string;
}

export interface ChatIaSelecionarDto {
  pesquisaId: string;
  resultadoId: string;
  selected: boolean;
  selectionJustification?: string;
}

export interface ChatIaOrcamentoManualDto {
  pesquisaId: string;
  cnpjFornecedor: string;
  razaoSocial?: string;
  itemDescricao: string;
  unitPrice: number;
  quantity?: number;
  observacoes?: string;
}

export interface PaginatedResponse<T> {
  data: T[];
  total: number;
  page: number;
  limit: number;
  totalPages: number;
}
