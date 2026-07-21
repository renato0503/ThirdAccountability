import { Injectable, CanActivate, ExecutionContext, UnauthorizedException } from '@nestjs/common';
import * as admin from 'firebase-admin';

@Injectable()
export class FirebaseAuthGuard implements CanActivate {
  async canActivate(context: ExecutionContext): Promise<boolean> {
    const request = context.switchToHttp().getRequest();
    const token = this.extractToken(request);

    if (!token) {
      throw new UnauthorizedException('Token de autenticacao nao fornecido');
    }

    try {
      const decodedToken = await admin.auth().verifyIdToken(token);
      request.user = {
        uid: decodedToken.uid,
        email: decodedToken.email,
        role: decodedToken.role || 'GESTOR_PROJETO',
        institutionId: decodedToken.institution_id || null,
        name: decodedToken.name || '',
      };
      return true;
    } catch {
      throw new UnauthorizedException('Token invalido ou expirado');
    }
  }

  private extractToken(request: any): string | null {
    const authHeader = request.headers?.authorization;
    if (authHeader?.startsWith('Bearer ')) {
      return authHeader.slice(7);
    }
    return null;
  }
}
