import { Module } from '@nestjs/common';
import { PriceResearchController } from './price-research.controller';
import { PriceResearchService } from './price-research.service';
import { ChatIaController } from './chat-ia.controller';
import { IntegrationsServicesModule } from '../../integrations/integrations.module';

@Module({
  imports: [IntegrationsServicesModule],
  controllers: [PriceResearchController, ChatIaController],
  providers: [PriceResearchService],
  exports: [PriceResearchService],
})
export class PriceResearchModule {}
