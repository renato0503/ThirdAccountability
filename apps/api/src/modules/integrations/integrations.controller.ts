import { Controller, Get, Param, UseGuards } from '@nestjs/common';
import { FirebaseAuthGuard } from '../../common/guards/firebase-auth.guard';

@Controller('integrations')
@UseGuards(FirebaseAuthGuard)
export class IntegrationsController {
  @Get('cnpj/:cnpj')
  async cnpj(@Param('cnpj') cnpj: string) {
    const cleaned = cnpj.replace(/\D/g, '');
    const response = await fetch(`https://brasilapi.com.br/api/cnpj/v1/${cleaned}`);
    if (!response.ok) {
      return { error: 'CNPJ nao encontrado' };
    }
    return response.json();
  }

  @Get('cep/:cep')
  async cep(@Param('cep') cep: string) {
    const cleaned = cep.replace(/\D/g, '');
    const response = await fetch(`https://viacep.com.br/ws/${cleaned}/json/`);
    if (!response.ok) {
      return { error: 'CEP nao encontrado' };
    }
    return response.json();
  }
}
