import { Controller, Get, Put, Patch, Param, Query, Body, UseGuards } from '@nestjs/common';
import { UsersService } from './users.service';
import { AuthService } from '../auth/auth.service';
import { FirebaseAuthGuard } from '../../common/guards/firebase-auth.guard';
import { RolesGuard } from '../../common/guards/roles.guard';
import { Roles } from '../../common/decorators/roles.decorator';

@Controller('users')
@UseGuards(FirebaseAuthGuard, RolesGuard)
export class UsersController {
  constructor(
    private readonly usersService: UsersService,
    private readonly authService: AuthService,
  ) {}

  @Get()
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO')
  async findAll(@Query('page') page = 1, @Query('limit') limit = 15) {
    return this.usersService.findAll(Number(page), Number(limit));
  }

  @Get(':uid')
  @Roles('ADMIN_GERAL', 'ADMIN_INSTITUICAO')
  async findById(@Param('uid') uid: string) {
    return this.usersService.findById(uid);
  }

  @Put(':uid')
  @Roles('ADMIN_GERAL')
  async update(@Param('uid') uid: string, @Body() body: any) {
    return this.usersService.update(uid, body);
  }

  @Patch(':uid/role')
  @Roles('ADMIN_GERAL')
  async updateRole(
    @Param('uid') uid: string,
    @Body() body: { role: string; institutionId?: string },
  ) {
    await this.authService.updateUserRole(uid, body.role, body.institutionId);
    return this.usersService.findById(uid);
  }
}
