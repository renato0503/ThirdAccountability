import { Injectable } from '@nestjs/common';

@Injectable()
export class PncpService {
  async search(term: string, filters?: { state?: string; city?: string }): Promise<any[]> {
    try {
      const url = `https://pncp.gov.br/api/pncp/v1/orgaos/${filters?.state || 'MT'}/contratos?termo=${encodeURIComponent(term)}`;
      const res = await fetch(url, { headers: { accept: 'application/json' } });
      if (!res.ok) return [];
      const data = await res.json() as any[];
      return (data || []).slice(0, 5).map((item: any) => ({
        source: 'PNCP',
        externalId: item.idContrato || '',
        originalDescription: item.objeto || term,
        unitPrice: item.valorInicial || 0,
        quantity: item.quantidade || null,
        unit: item.unidadeMedida || null,
        totalPrice: item.valorGlobal || 0,
        buyerName: item.nomeOrgao || '',
        buyerCnpj: item.cnpjOrgao || '',
        processNumber: item.numeroProcesso || '',
        contractNumber: item.numeroContrato || '',
        sourceUrl: `https://pncp.gov.br/app/contratos/${item.idContrato}`,
      }));
    } catch {
      return [];
    }
  }
}
