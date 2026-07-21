import { Controller, Get, Post, Put, Delete, Param, Body, UseGuards } from '@nestjs/common';
import { AccountingService } from './accounting.service';
import { FirebaseAuthGuard } from '../../common/guards/firebase-auth.guard';
import { RolesGuard } from '../../common/guards/roles.guard';
import { Roles } from '../../common/decorators/roles.decorator';

@Controller('projects/:projectId/accounting')
@UseGuards(FirebaseAuthGuard, RolesGuard)
export class AccountingController {
  constructor(private readonly service: AccountingService) {}

  @Get()
  async findAll(@Param('projectId') projectId: string) {
    return this.service.findAll(projectId);
  }

  @Get(':reportId')
  async findById(@Param('projectId') projectId: string, @Param('reportId') reportId: string) {
    return this.service.findById(projectId, reportId);
  }

  @Post()
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'GESTOR_PROJETO')
  async create(@Param('projectId') projectId: string, @Body() body: any) {
    return this.service.create(projectId, body);
  }

  @Put(':reportId')
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'GESTOR_PROJETO')
  async update(@Param('projectId') projectId: string, @Param('reportId') reportId: string, @Body() body: any) {
    return this.service.update(projectId, reportId, body);
  }

  @Post(':reportId/photos')
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'GESTOR_PROJETO')
  async addPhoto(@Param('projectId') projectId: string, @Param('reportId') reportId: string, @Body() body: { photoPath: string }) {
    return this.service.addPhoto(projectId, reportId, body.photoPath);
  }

  @Delete(':reportId/photos/:photoIndex')
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'GESTOR_PROJETO')
  async removePhoto(@Param('projectId') projectId: string, @Param('reportId') reportId: string, @Param('photoIndex') photoIndex: string) {
    return this.service.removePhoto(projectId, reportId, parseInt(photoIndex));
  }

  @Delete(':reportId')
  @Roles('ADMIN_GERAL')
  async delete(@Param('projectId') projectId: string, @Param('reportId') reportId: string) {
    await this.service.delete(projectId, reportId);
    return { message: 'Relatorio excluido' };
  }
}
