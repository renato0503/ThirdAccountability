import {
  Controller, Get, Post, Put, Delete,
  Param, Query, Body, UseGuards,
} from '@nestjs/common';
import { ProjectsService } from './projects.service';
import { FirebaseAuthGuard } from '../../common/guards/firebase-auth.guard';
import { RolesGuard } from '../../common/guards/roles.guard';
import { Roles } from '../../common/decorators/roles.decorator';
import { CurrentUser } from '../../common/decorators/current-user.decorator';

@Controller('projects')
@UseGuards(FirebaseAuthGuard, RolesGuard)
export class ProjectsController {
  constructor(private readonly service: ProjectsService) {}

  @Get()
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'GESTOR_PROJETO', 'FISCAL_PROJETO')
  async findAll(
    @Query('page') page = 1,
    @Query('limit') limit = 15,
    @Query('status') status?: string,
    @Query('institutionId') institutionId?: string,
    @Query('search') search?: string,
  ) {
    return this.service.findAll(Number(page), Number(limit), { status, institutionId, search });
  }

  @Get('generate-code')
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'GESTOR_PROJETO')
  async generateCode(@Query('year') year?: string) {
    const y = year ? parseInt(year) : new Date().getFullYear();
    return { codigo: await this.service.generateCode(y) };
  }

  @Get(':id')
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'GESTOR_PROJETO', 'FISCAL_PROJETO')
  async findById(@Param('id') id: string) {
    return this.service.findById(id);
  }

  @Post()
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'GESTOR_PROJETO')
  async create(@Body() body: any, @CurrentUser('uid') uid: string) {
    if (body.gerarCodigo) {
      const year = body.anoCodigo || new Date().getFullYear();
      body.codigo = await this.service.generateCode(year);
    }
    delete body.gerarCodigo;
    delete body.anoCodigo;

    const { locations, objectives, teamMembers, contractedServices, capabilityPhotos, ...projectData } = body;
    const project = await this.service.create(projectData, uid);

    // Create sub-entities
    if (locations?.length) {
      for (let i = 0; i < locations.length; i++) {
        if (locations[i].cidade) await this.service.createSub(project.id, 'executionLocations', { ...locations[i], ordem: i });
      }
    }
    if (objectives?.length) {
      for (let i = 0; i < objectives.length; i++) {
        if (objectives[i]) await this.service.createSub(project.id, 'specificObjectives', { objetivo: objectives[i], ordem: i });
      }
    }
    if (teamMembers?.length) {
      for (let i = 0; i < teamMembers.length; i++) {
        if (teamMembers[i]?.funcao) await this.service.createSub(project.id, 'teamMembers', { ...teamMembers[i], ordem: i });
      }
    }
    if (contractedServices?.length) {
      for (let i = 0; i < contractedServices.length; i++) {
        if (contractedServices[i]?.descricao) await this.service.createSub(project.id, 'contractedServices', { ...contractedServices[i], ordem: i });
      }
    }

    return this.service.findById(project.id);
  }

  @Put(':id')
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'GESTOR_PROJETO')
  async update(@Param('id') id: string, @Body() body: any) {
    const { locations, objectives, teamMembers, contractedServices, ...projectData } = body;
    const project = await this.service.update(id, projectData);

    // Replace sub-entities
    const subs = ['executionLocations', 'specificObjectives', 'teamMembers', 'contractedServices'];
    for (const sub of subs) {
      const existing = await this.service.findSub(id, sub);
      for (const e of existing) await this.service.deleteSub(id, sub, e.id);
    }

    if (locations?.length) {
      for (let i = 0; i < locations.length; i++) {
        if (locations[i].cidade) await this.service.createSub(id, 'executionLocations', { ...locations[i], ordem: i });
      }
    }
    if (objectives?.length) {
      for (let i = 0; i < objectives.length; i++) {
        if (objectives[i]) await this.service.createSub(id, 'specificObjectives', { objetivo: objectives[i], ordem: i });
      }
    }
    if (teamMembers?.length) {
      for (let i = 0; i < teamMembers.length; i++) {
        if (teamMembers[i]?.funcao) await this.service.createSub(id, 'teamMembers', { ...teamMembers[i], ordem: i });
      }
    }
    if (contractedServices?.length) {
      for (let i = 0; i < contractedServices.length; i++) {
        if (contractedServices[i]?.descricao) await this.service.createSub(id, 'contractedServices', { ...contractedServices[i], ordem: i });
      }
    }

    return this.service.findById(id);
  }

  @Delete(':id')
  @Roles('ADMIN_GERAL')
  async delete(@Param('id') id: string) {
    await this.service.delete(id);
    return { message: 'Projeto excluido' };
  }

  // ─── Sub-entity endpoints ───

  @Get(':id/execution-locations')
  async getLocations(@Param('id') id: string) { return this.service.findSub(id, 'executionLocations'); }

  @Get(':id/specific-objectives')
  async getObjectives(@Param('id') id: string) { return this.service.findSub(id, 'specificObjectives'); }

  @Get(':id/team-members')
  async getTeam(@Param('id') id: string) { return this.service.findSub(id, 'teamMembers'); }

  @Get(':id/contracted-services')
  async getServices(@Param('id') id: string) { return this.service.findSub(id, 'contractedServices'); }

  @Get(':id/capability-photos')
  async getPhotos(@Param('id') id: string) { return this.service.findSub(id, 'capabilityPhotos'); }
}
