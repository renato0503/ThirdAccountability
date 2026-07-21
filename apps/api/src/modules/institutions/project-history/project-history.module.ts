import { Module } from '@nestjs/common';
import { ProjectHistoryService } from './project-history.service';

@Module({
  providers: [ProjectHistoryService],
  exports: [ProjectHistoryService],
})
export class ProjectHistoryModule {}
