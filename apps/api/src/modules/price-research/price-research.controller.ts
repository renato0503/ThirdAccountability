import { Controller, Get, Post, Put, Patch, Delete, Param, Query, Body, UseGuards } from '@nestjs/common';
import { PriceResearchService } from './price-research.service';
import { FirebaseAuthGuard } from '../../common/guards/firebase-auth.guard';
import { RolesGuard } from '../../common/guards/roles.guard';
import { Roles } from '../../common/decorators/roles.decorator';
import { CurrentUser } from '../../common/decorators/current-user.decorator';

@Controller('price-research')
@UseGuards(FirebaseAuthGuard, RolesGuard)
export class PriceResearchController {
  constructor(private readonly service: PriceResearchService) {}

  @Get()
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'GESTOR_PROJETO')
  async findAll(
    @Query('page') page = 1, @Query('limit') limit = 15,
    @Query('status') status?: string, @Query('institutionId') institutionId?: string,
    @Query('search') search?: string,
  ) {
    return this.service.findAll(Number(page), Number(limit), { status, institutionId, search });
  }

  @Get(':id')
  async findById(@Param('id') id: string) { return this.service.findById(id); }

  @Post()
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'GESTOR_PROJETO')
  async create(@Body() body: any, @CurrentUser('uid') uid: string) {
    return this.service.create(body, uid);
  }

  @Put(':id')
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'GESTOR_PROJETO')
  async update(@Param('id') id: string, @Body() body: any) { return this.service.update(id, body); }

  @Delete(':id')
  @Roles('ADMIN_GERAL')
  async delete(@Param('id') id: string) { await this.service.delete(id); return { message: 'Excluido' }; }

  // Results
  @Post(':id/results')
  async addResult(@Param('id') id: string, @Body() body: any) {
    return this.service.addResult(id, body);
  }

  @Delete(':id/results/:resultId')
  async deleteResult(@Param('id') id: string, @Param('resultId') resultId: string) {
    await this.service.deleteResult(id, resultId); return { message: 'Resultado removido' };
  }

  @Patch(':id/results/:resultId/select')
  async toggleSelect(
    @Param('id') id: string, @Param('resultId') resultId: string,
    @Body() body: { selected: boolean; justification?: string },
  ) {
    return this.service.toggleSelect(id, resultId, body.selected, body.justification);
  }

  @Post(':id/set-reference')
  async setReference(@Param('id') id: string, @Body() body: any) {
    return this.service.setReference(id, body.type, body.justification, body.manualPrice, body.resultId);
  }

  @Post(':id/search')
  async search(@Param('id') id: string) {
    return this.service.updateStats(id);
  }
}
