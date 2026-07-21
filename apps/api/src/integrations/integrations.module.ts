import { Module } from '@nestjs/common';
import { GroqClientService } from './groq-client.service';
import { PncpService } from './pncp.service';
import { MercadoLivreService } from './mercado-livre.service';

@Module({
  providers: [GroqClientService, PncpService, MercadoLivreService],
  exports: [GroqClientService, PncpService, MercadoLivreService],
})
export class IntegrationsServicesModule {}
