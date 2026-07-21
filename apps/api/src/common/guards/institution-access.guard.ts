import { Injectable, CanActivate, ExecutionContext, ForbiddenException } from '@nestjs/common';

@Injectable()
export class InstitutionAccessGuard implements CanActivate {
  canActivate(context: ExecutionContext): boolean {
    const request = context.switchToHttp().getRequest();
    const user = request.user;
    const institutionId = request.params?.institutionId || request.body?.institutionId;

    if (!user) {
      throw new ForbiddenException('Usuario nao autenticado');
    }

    // ADMIN_GERAL tem acesso a todas as instituicoes
    if (user.role === 'ADMIN_GERAL') {
      return true;
    }

    // Usuario comum so acessa sua propria instituicao
    if (user.institutionId && institutionId && user.institutionId !== institutionId) {
      throw new ForbiddenException('Acesso negado a esta instituicao');
    }

    return true;
  }
}
