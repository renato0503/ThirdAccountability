import { Controller, Get, Post, Put, Delete, Param, Body, UseGuards } from '@nestjs/common';
import { FundingSourcesService } from './funding-sources.service';
import { FirebaseAuthGuard } from '../../common/guards/firebase-auth.guard';
import { RolesGuard } from '../../common/guards/roles.guard';
import { Roles } from '../../common/decorators/roles.decorator';

@Controller('funding-sources')
@UseGuards(FirebaseAuthGuard, RolesGuard)
export class FundingSourcesController {
  constructor(private readonly service: FundingSourcesService) {}

  @Get()
  async findAll() { return this.service.findAll(); }

  @Get(':id')
  async findById(@Param('id') id: string) { return this.service.findById(id); }

  @Post()
  @Roles('ADMIN_GERAL')
  async create(@Body() body: any) { return this.service.create(body); }

  @Put(':id')
  @Roles('ADMIN_GERAL')
  async update(@Param('id') id: string, @Body() body: any) { return this.service.update(id, body); }

  @Delete(':id')
  @Roles('ADMIN_GERAL')
  async delete(@Param('id') id: string) { await this.service.delete(id); return { message: 'Excluido' }; }
}
