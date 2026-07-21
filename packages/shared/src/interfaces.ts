import { UserRole, ProjectStatus } from './enums';

export interface IUser {
  id: string;
  uid: string;
  email: string;
  name: string;
  photoURL?: string;
  role: UserRole;
  institutionId?: string;
  ativo: boolean;
  createdAt: Date;
  updatedAt: Date;
}

export interface IInstitution {
  id: string;
  razaoSocial: string;
  nomeFantasia?: string;
  cnpj: string;
  email?: string;
  telefone?: string;
  site?: string;
  instagram?: string;
  endereco?: IAddress;
  active: boolean;
  createdAt: Date;
  updatedAt: Date;
}

export interface IAddress {
  logradouro?: string;
  numero?: string;
  complemento?: string;
  bairro?: string;
  municipio?: string;
  estado?: string;
  cep?: string;
}

export interface IProject {
  id: string;
  institutionId: string;
  institutionName: string;
  fundingSourceId?: string;
  nome: string;
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
  valorExecutado?: number;
  dataInicio?: Date;
  dataFim?: Date;
  status: ProjectStatus;
  responsavel?: string;
  localExecucao?: string;
  createdAt: Date;
  updatedAt: Date;
  createdBy: string;
  deletedAt?: Date;
}

export interface IGoal {
  id: string;
  projectId: string;
  numero: number;
  titulo: string;
  descricao?: string;
  orcamento?: number;
  percentualExecucao?: number;
  status: string;
  afericao?: string;
  createdAt: Date;
  updatedAt: Date;
}

export interface IAuditLog {
  id: string;
  userId: string;
  userEmail?: string;
  acao: string;
  entidade: string;
  entidadeId?: string;
  dados?: Record<string, any>;
  ip?: string;
  timestamp: Date;
}
