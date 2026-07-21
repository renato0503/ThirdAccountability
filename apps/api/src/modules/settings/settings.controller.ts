import { Controller, Get, Put, Body, UseGuards } from '@nestjs/common';
import { SettingsService } from './settings.service';
import { FirebaseAuthGuard } from '../../common/guards/firebase-auth.guard';
import { RolesGuard } from '../../common/guards/roles.guard';
import { Roles } from '../../common/decorators/roles.decorator';

@Controller('settings')
@UseGuards(FirebaseAuthGuard, RolesGuard)
export class SettingsController {
  constructor(private readonly service: SettingsService) {}

  @Get()
  @Roles('ADMIN_GERAL')
  async get() { return this.service.get(); }

  @Put()
  @Roles('ADMIN_GERAL')
  async update(@Body() body: any) { return this.service.update(body); }
}
