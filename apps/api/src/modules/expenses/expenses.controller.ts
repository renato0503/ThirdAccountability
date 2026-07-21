import { Controller, Get, Post, Put, Delete, Patch, Param, Query, Body, UseGuards } from '@nestjs/common';
import { ExpensesService } from './expenses.service';
import { FirebaseAuthGuard } from '../../common/guards/firebase-auth.guard';
import { RolesGuard } from '../../common/guards/roles.guard';
import { Roles } from '../../common/decorators/roles.decorator';

@Controller('projects/:projectId/expenses')
@UseGuards(FirebaseAuthGuard, RolesGuard)
export class ExpensesController {
  constructor(private readonly service: ExpensesService) {}

  @Get()
  async findAll(@Param('projectId') projectId: string, @Query('status') status?: string) {
    return this.service.findAll(projectId, { status });
  }

  @Get(':expenseId')
  async findById(@Param('projectId') projectId: string, @Param('expenseId') expenseId: string) {
    return this.service.findById(projectId, expenseId);
  }

  @Post()
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'GESTOR_PROJETO')
  async create(@Param('projectId') projectId: string, @Body() body: any) {
    return this.service.create(projectId, body);
  }

  @Put(':expenseId')
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'GESTOR_PROJETO')
  async update(@Param('projectId') projectId: string, @Param('expenseId') expenseId: string, @Body() body: any) {
    return this.service.update(projectId, expenseId, body);
  }

  @Patch(':expenseId/status')
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'GESTOR_PROJETO')
  async updateStatus(
    @Param('projectId') projectId: string,
    @Param('expenseId') expenseId: string,
    @Body() body: { status: string },
  ) {
    return this.service.updateStatus(projectId, expenseId, body.status);
  }

  @Delete(':expenseId')
  @Roles('ADMIN_GERAL')
  async delete(@Param('projectId') projectId: string, @Param('expenseId') expenseId: string) {
    await this.service.delete(projectId, expenseId);
    return { message: 'Despesa excluida' };
  }
}
