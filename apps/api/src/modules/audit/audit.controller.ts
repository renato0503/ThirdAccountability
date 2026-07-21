import { Controller, Get, Query, UseGuards } from '@nestjs/common';
import { AuditService } from './audit.service';
import { FirebaseAuthGuard } from '../../common/guards/firebase-auth.guard';
import { RolesGuard } from '../../common/guards/roles.guard';
import { Roles } from '../../common/decorators/roles.decorator';

@Controller('audit')
@UseGuards(FirebaseAuthGuard, RolesGuard)
export class AuditController {
  constructor(private readonly service: AuditService) {}

  @Get()
  @Roles('ADMIN_GERAL')
  async findAll(
    @Query('page') page = 1, @Query('limit') limit = 30,
    @Query('userId') userId?: string, @Query('acao') acao?: string,
    @Query('entidade') entidade?: string,
    @Query('startDate') startDate?: string, @Query('endDate') endDate?: string,
  ) {
    return this.service.findAll(Number(page), Number(limit), { userId, acao, entidade, startDate, endDate });
  }
}
