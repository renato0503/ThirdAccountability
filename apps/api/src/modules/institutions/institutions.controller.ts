import {
  Controller, Get, Post, Put, Delete, Param, Query, Body, UseGuards,
} from '@nestjs/common';
import { InstitutionsService } from './institutions.service';
import { DirectorsService } from './directors/directors.service';
import { ProjectHistoryService } from './project-history/project-history.service';
import { FirebaseAuthGuard } from '../../common/guards/firebase-auth.guard';
import { RolesGuard } from '../../common/guards/roles.guard';
import { Roles } from '../../common/decorators/roles.decorator';
import { CurrentUser } from '../../common/decorators/current-user.decorator';

@Controller('institutions')
@UseGuards(FirebaseAuthGuard, RolesGuard)
export class InstitutionsController {
  constructor(
    private readonly institutionsService: InstitutionsService,
    private readonly directorsService: DirectorsService,
    private readonly projectHistoryService: ProjectHistoryService,
  ) {}

  @Get()
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'GESTOR_PROJETO', 'FISCAL_PROJETO')
  async findAll(
    @Query('page') page = 1,
    @Query('limit') limit = 15,
    @Query('search') search?: string,
  ) {
    return this.institutionsService.findAll(Number(page), Number(limit), search);
  }

  @Get(':id')
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'GESTOR_PROJETO', 'FISCAL_PROJETO')
  async findById(@Param('id') id: string) {
    return this.institutionsService.findById(id);
  }

  @Post()
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO')
  async create(@Body() body: any, @CurrentUser('uid') uid: string) {
    return this.institutionsService.create(body, uid);
  }

  @Put(':id')
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO')
  async update(@Param('id') id: string, @Body() body: any) {
    return this.institutionsService.update(id, body);
  }

  @Delete(':id')
  @Roles('ADMIN_GERAL')
  async delete(@Param('id') id: string) {
    await this.institutionsService.delete(id);
    return { message: 'Instituicao excluida' };
  }

  @Post(':id/toggle-active')
  @Roles('ADMIN_GERAL')
  async toggleActive(@Param('id') id: string) {
    return this.institutionsService.toggleActive(id);
  }

  // ─── Directors ───
  @Get(':id/directors')
  async findDirectors(@Param('id') id: string) {
    return this.directorsService.findAll(id);
  }

  @Post(':id/directors')
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO')
  async createDirector(@Param('id') id: string, @Body() body: any) {
    return this.directorsService.create(id, body);
  }

  @Put(':id/directors/:directorId')
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO')
  async updateDirector(
    @Param('id') id: string,
    @Param('directorId') directorId: string,
    @Body() body: any,
  ) {
    return this.directorsService.update(id, directorId, body);
  }

  @Delete(':id/directors/:directorId')
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO')
  async deleteDirector(
    @Param('id') id: string,
    @Param('directorId') directorId: string,
  ) {
    await this.directorsService.delete(id, directorId);
    return { message: 'Diretor excluido' };
  }

  // ─── Project History ───
  @Get(':id/project-history')
  async findProjectHistory(@Param('id') id: string) {
    return this.projectHistoryService.findAll(id);
  }

  @Post(':id/project-history')
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO')
  async createProjectHistory(@Param('id') id: string, @Body() body: any) {
    return this.projectHistoryService.create(id, body);
  }

  @Delete(':id/project-history/:historyId')
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO')
  async deleteProjectHistory(
    @Param('id') id: string,
    @Param('historyId') historyId: string,
  ) {
    await this.projectHistoryService.delete(id, historyId);
    return { message: 'Historico excluido' };
  }
}
