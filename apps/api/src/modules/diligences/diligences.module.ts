import { Module } from '@nestjs/common';
import { DiligencesController } from './diligences.controller';
import { DiligencesService } from './diligences.service';

@Module({
  controllers: [DiligencesController],
  providers: [DiligencesService],
  exports: [DiligencesService],
})
export class DiligencesModule {}
