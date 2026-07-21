import { Controller, Get, Post, Put, Delete, Param, Body, UseGuards } from '@nestjs/common';
import { DiligencesService } from './diligences.service';
import { FirebaseAuthGuard } from '../../common/guards/firebase-auth.guard';
import { RolesGuard } from '../../common/guards/roles.guard';
import { Roles } from '../../common/decorators/roles.decorator';

@Controller('projects/:projectId/diligences')
@UseGuards(FirebaseAuthGuard, RolesGuard)
export class DiligencesController {
  constructor(private readonly service: DiligencesService) {}

  @Get()
  async findAll(@Param('projectId') projectId: string) {
    return this.service.findAll(projectId);
  }

  @Get(':dilId')
  async findById(@Param('projectId') projectId: string, @Param('dilId') dilId: string) {
    return this.service.findById(projectId, dilId);
  }

  @Post()
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'FISCAL_PROJETO')
  async create(@Param('projectId') projectId: string, @Body() body: any) {
    return this.service.create(projectId, body);
  }

  @Put(':dilId')
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'FISCAL_PROJETO')
  async update(@Param('projectId') projectId: string, @Param('dilId') dilId: string, @Body() body: any) {
    return this.service.update(projectId, dilId, body);
  }

  @Post(':dilId/respond')
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'GESTOR_PROJETO')
  async respond(
    @Param('projectId') projectId: string,
    @Param('dilId') dilId: string,
    @Body() body: { resposta: string; anexoPath?: string },
  ) {
    return this.service.respond(projectId, dilId, body.resposta, body.anexoPath);
  }

  @Post(':dilId/close')
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'FISCAL_PROJETO')
  async close(
    @Param('projectId') projectId: string,
    @Param('dilId') dilId: string,
    @Body() body: { parecer: string },
  ) {
    return this.service.close(projectId, dilId, body.parecer);
  }

  @Post(':dilId/reopen')
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'FISCAL_PROJETO')
  async reopen(@Param('projectId') projectId: string, @Param('dilId') dilId: string) {
    return this.service.reopen(projectId, dilId);
  }

  @Delete(':dilId')
  @Roles('ADMIN_GERAL')
  async delete(@Param('projectId') projectId: string, @Param('dilId') dilId: string) {
    await this.service.delete(projectId, dilId);
    return { message: 'Diligencia excluida' };
  }
}
