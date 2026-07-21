import { Controller, Post, Get, Param, Body, UseGuards } from '@nestjs/common';
import { PriceResearchService } from './price-research.service';
import { GroqClientService } from '../../integrations/groq-client.service';
import { PncpService } from '../../integrations/pncp.service';
import { MercadoLivreService } from '../../integrations/mercado-livre.service';
import { FirebaseAuthGuard } from '../../common/guards/firebase-auth.guard';
import { RolesGuard } from '../../common/guards/roles.guard';
import { CurrentUser } from '../../common/decorators/current-user.decorator';

@Controller('chat-ia')
@UseGuards(FirebaseAuthGuard, RolesGuard)
export class ChatIaController {
  constructor(
    private readonly priceResearch: PriceResearchService,
    private readonly groq: GroqClientService,
    private readonly pncp: PncpService,
    private readonly mercadoLivre: MercadoLivreService,
  ) {}

  @Post('processar')
  async processar(
    @Body() body: { texto: string; institutionId: string; projectId?: string },
    @CurrentUser('uid') uid: string,
  ) {
    // 1. Interpretar texto com IA
    const interpreted = await this.groq.interpretBatch(body.texto);
    const itens = interpreted.itens || [];

    if (!itens.length) {
      return { error: 'Nao foi possivel extrair itens do texto', itens: [] };
    }

    // 2. Para cada item, criar pesquisa + buscar resultados
    const results: any[] = [];

    for (const item of itens) {
      const pesquisa = await this.priceResearch.create({
        institutionId: body.institutionId,
        projectId: body.projectId || null,
        searchTerm: item.descricao,
        quantity: item.quantidade || 1,
        unit: 'un',
        sources: ['PNCP'],
      }, uid);

      // Busca paralela: PNCP + Mercado Livre
      const [pncpResults, mlResults] = await Promise.all([
        this.pncp.search(item.descricao),
        this.mercadoLivre.search(item.descricao),
      ]);

      for (const r of [...pncpResults, ...mlResults]) {
        await this.priceResearch.addResult(pesquisa.id, r);
      }

      const full = await this.priceResearch.findById(pesquisa.id);
      results.push(full);
    }

    return { itens: results, total: results.length };
  }

  @Post('selecionar')
  async selecionar(@Body() body: { pesquisaId: string; resultadoId: string; selected: boolean; justification?: string }) {
    return this.priceResearch.toggleSelect(body.pesquisaId, body.resultadoId, body.selected, body.justification);
  }

  @Post('orcamento-manual')
  async orcamentoManual(@Body() body: {
    pesquisaId: string; cnpjFornecedor: string; razaoSocial?: string;
    itemDescricao: string; unitPrice: number; quantity?: number; observacoes?: string;
  }) {
    return this.priceResearch.addResult(body.pesquisaId, {
      source: 'MANUAL',
      originalDescription: body.itemDescricao,
      unitPrice: body.unitPrice,
      quantity: body.quantity || 1,
      unit: 'un',
      buyerName: body.razaoSocial || '',
      buyerCnpj: body.cnpjFornecedor?.replace(/\D/g, ''),
      cnpjFornecedor: body.cnpjFornecedor?.replace(/\D/g, ''),
      observacoes: body.observacoes || '',
    });
  }

  @Get('status/:pesquisaId')
  async status(@Param('pesquisaId') pesquisaId: string) {
    return this.priceResearch.findById(pesquisaId);
  }
}
