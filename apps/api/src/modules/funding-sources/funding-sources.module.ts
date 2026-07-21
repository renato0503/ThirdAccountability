import { Module } from '@nestjs/common';
import { FundingSourcesController } from './funding-sources.controller';
import { FundingSourcesService } from './funding-sources.service';

@Module({
  controllers: [FundingSourcesController],
  providers: [FundingSourcesService],
  exports: [FundingSourcesService],
})
export class FundingSourcesModule {}
