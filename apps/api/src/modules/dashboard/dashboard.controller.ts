import { Controller, Get, Query, UseGuards } from '@nestjs/common';
import { DashboardService } from './dashboard.service';
import { FirebaseAuthGuard } from '../../common/guards/firebase-auth.guard';
import { RolesGuard } from '../../common/guards/roles.guard';

@Controller('dashboard')
@UseGuards(FirebaseAuthGuard, RolesGuard)
export class DashboardController {
  constructor(private readonly service: DashboardService) {}

  @Get('stats')
  async stats(@Query('institutionId') institutionId?: string) {
    return this.service.getStats(institutionId);
  }
}
