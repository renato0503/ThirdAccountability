import { Module } from '@nestjs/common';
import { InstitutionsController } from './institutions.controller';
import { InstitutionsService } from './institutions.service';
import { DirectorsModule } from './directors/directors.module';
import { ProjectHistoryModule } from './project-history/project-history.module';

@Module({
  imports: [DirectorsModule, ProjectHistoryModule],
  controllers: [InstitutionsController],
  providers: [InstitutionsService],
  exports: [InstitutionsService],
})
export class InstitutionsModule {}
