import { Injectable } from '@nestjs/common';

@Injectable()
export class MercadoLivreService {
  async search(term: string, limit = 6): Promise<any[]> {
    try {
      const url = `https://api.mercadolibre.com/sites/MLB/search?q=${encodeURIComponent(term)}&limit=${limit}`;
      const res = await fetch(url, { headers: { accept: 'application/json' } });
      if (!res.ok) return [];
      const data = await res.json() as any;
      return (data.results || []).map((item: any) => ({
        source: 'MERCADO_LIVRE',
        originalDescription: item.title,
        unitPrice: item.price,
        quantity: 1,
        unit: 'un',
        buyerName: item.seller?.nickname || '',
        sourceUrl: item.permalink,
        city: item.address?.city_name || '',
        state: item.address?.state_id || '',
      }));
    } catch {
      return [];
    }
  }
}
