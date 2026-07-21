import { Controller, Get, Param, UseGuards } from '@nestjs/common';
import { ReportsService } from './reports.service';
import { FirebaseAuthGuard } from '../../common/guards/firebase-auth.guard';
import { RolesGuard } from '../../common/guards/roles.guard';

@Controller('reports')
@UseGuards(FirebaseAuthGuard, RolesGuard)
export class ReportsController {
  constructor(private readonly service: ReportsService) {}

  @Get('project/:projectId')
  async projectReport(@Param('projectId') projectId: string) {
    return this.service.generateProjectReport(projectId);
  }
}
