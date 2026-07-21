import { Injectable } from '@nestjs/common';

@Injectable()
export class GroqClientService {
  private apiKey = process.env.GROQ_API_KEY || '';
  private model = process.env.GROQ_MODEL || 'llama-3.3-70b-versatile';

  private async request(messages: any[]) {
    if (!this.apiKey) return { itens: [] };
    try {
      const res = await fetch('https://api.groq.com/openai/v1/chat/completions', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${this.apiKey}`,
        },
        body: JSON.stringify({
          model: this.model,
          messages,
          temperature: 0.1,
          response_format: { type: 'json_object' },
        }),
      });
      const data = await res.json() as any;
      const content = data.choices?.[0]?.message?.content || '{}';
      return JSON.parse(content);
    } catch {
      return { itens: [] };
    }
  }

  async interpretBatch(texto: string): Promise<{ itens: Array<{ descricao: string; quantidade: number; material?: string }> }> {
    return this.request([
      {
        role: 'system',
        content: `Voce e um assistente que interpreta pedidos de cotacao de precos.
Extraia a lista de itens a cotar, normalizando as descricoes para portugues.
Responda JSON: {"itens": [{"descricao": "...", "quantidade": 5, "material": "..."}]}`,
      },
      { role: 'user', content: `Texto: "${texto}"` },
    ]);
  }

  async suggestProductDetails(descricao: string): Promise<{ descricaoNormalizada: string; material?: string; categoria?: string }> {
    const result = await this.request([
      {
        role: 'system',
        content: `Voce padroniza descricoes de produtos para cotacao.
Responda JSON: {"descricaoNormalizada": "...", "material": "...", "categoria": "..."}`,
      },
      { role: 'user', content: `Produto: "${descricao}"` },
    ]);
    return result;
  }
}
