import { Controller, Get, Post, Put, Delete, Param, Body, UseGuards } from '@nestjs/common';
import { DocumentsService } from './documents.service';
import { FirebaseAuthGuard } from '../../common/guards/firebase-auth.guard';
import { RolesGuard } from '../../common/guards/roles.guard';
import { Roles } from '../../common/decorators/roles.decorator';

@Controller('projects/:projectId/documents')
@UseGuards(FirebaseAuthGuard, RolesGuard)
export class DocumentsController {
  constructor(private readonly service: DocumentsService) {}

  @Get()
  async findAll(@Param('projectId') projectId: string) {
    return this.service.findAll(projectId);
  }

  @Get(':docId')
  async findById(@Param('projectId') projectId: string, @Param('docId') docId: string) {
    return this.service.findById(projectId, docId);
  }

  @Post()
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'GESTOR_PROJETO')
  async create(@Param('projectId') projectId: string, @Body() body: any) {
    return this.service.create(projectId, body);
  }

  @Put(':docId')
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO', 'GESTOR_PROJETO')
  async update(@Param('projectId') projectId: string, @Param('docId') docId: string, @Body() body: any) {
    return this.service.update(projectId, docId, body);
  }

  @Delete(':docId')
  @Roles('ADMIN_GERAL')
  async delete(@Param('projectId') projectId: string, @Param('docId') docId: string) {
    await this.service.delete(projectId, docId);
    return { message: 'Documento excluido' };
  }
}
