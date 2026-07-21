import { Controller, Get, Post, Put, Delete, Param, Body, UseGuards } from '@nestjs/common';
import { GoalsService } from './goals.service';
import { FirebaseAuthGuard } from '../../common/guards/firebase-auth.guard';
import { RolesGuard } from '../../common/guards/roles.guard';
import { Roles } from '../../common/decorators/roles.decorator';
import { CurrentUser } from '../../common/decorators/current-user.decorator';

@Controller('projects/:projectId/goals')
@UseGuards(FirebaseAuthGuard, RolesGuard)
export class GoalsController {
  constructor(private readonly service: GoalsService) {}

  @Get()
  async findAll(@Param('projectId') projectId: string) {
    return this.service.findAll(projectId);
  }

  @Get(':goalId')
  async findById(@Param('projectId') projectId: string, @Param('goalId') goalId: string) {
    return this.service.findById(projectId, goalId);
  }

  @Post()
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'GESTOR_PROJETO')
  async create(@Param('projectId') projectId: string, @Body() body: any) {
    return this.service.create(projectId, body);
  }

  @Put(':goalId')
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'GESTOR_PROJETO')
  async update(@Param('projectId') projectId: string, @Param('goalId') goalId: string, @Body() body: any) {
    return this.service.update(projectId, goalId, body);
  }

  @Delete(':goalId')
  @Roles('ADMIN_GERAL')
  async delete(@Param('projectId') projectId: string, @Param('goalId') goalId: string) {
    await this.service.delete(projectId, goalId);
    return { message: 'Meta excluida' };
  }

  @Post(':goalId/send-analysis')
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'GESTOR_PROJETO')
  async sendToAnalysis(@Param('projectId') projectId: string, @Param('goalId') goalId: string) {
    return this.service.sendToAnalysis(projectId, goalId);
  }

  @Post(':goalId/approve')
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'FISCAL_PROJETO')
  async approve(
    @Param('projectId') projectId: string,
    @Param('goalId') goalId: string,
    @Body() body: any,
    @CurrentUser('uid') uid: string,
  ) {
    return this.service.approve(projectId, goalId, uid, body.observacao);
  }

  @Post(':goalId/disapprove')
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'FISCAL_PROJETO')
  async disapprove(
    @Param('projectId') projectId: string,
    @Param('goalId') goalId: string,
    @Body() body: any,
    @CurrentUser('uid') uid: string,
  ) {
    return this.service.disapprove(projectId, goalId, uid, body.observacao);
  }

  @Post(':goalId/send-accounting')
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'GESTOR_PROJETO')
  async sendToAccounting(@Param('projectId') projectId: string) {
    return this.service.sendToAccounting(projectId);
  }
}
