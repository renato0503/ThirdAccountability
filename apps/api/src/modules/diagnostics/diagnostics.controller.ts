import { Controller, Get, UseGuards } from '@nestjs/common';
import { DiagnosticsService } from './diagnostics.service';
import { FirebaseAuthGuard } from '../../common/guards/firebase-auth.guard';
import { RolesGuard } from '../../common/guards/roles.guard';
import { Roles } from '../../common/decorators/roles.decorator';

@Controller('diagnostics')
@UseGuards(FirebaseAuthGuard, RolesGuard)
export class DiagnosticsController {
  constructor(private readonly service: DiagnosticsService) {}

  @Get()
  @Roles('ADMIN_GERAL')
  async check() {
    return this.service.checkAll();
  }
}
