import {
  Injectable,
  NestInterceptor,
  ExecutionContext,
  CallHandler,
} from '@nestjs/common';
import { Observable, tap } from 'rxjs';

@Injectable()
export class AuditLogInterceptor implements NestInterceptor {
  intercept(context: ExecutionContext, next: CallHandler): Observable<any> {
    const request = context.switchToHttp().getRequest();
    const user = request.user;
    const { method, path } = request;

    const startTime = Date.now();

    return next.handle().pipe(
      tap(() => {
        const duration = Date.now() - startTime;

        if (user && method !== 'GET') {
          const logEntry = {
            userId: user.uid,
            userEmail: user.email,
            acao: method,
            entidade: this.extractEntity(path),
            entidadeId: this.extractEntityId(path),
            ip: request.ip,
            timestamp: new Date().toISOString(),
            duration,
            path,
          };

          // Log para console em dev
          if (process.env.NODE_ENV !== 'production') {
            console.log('[AUDIT]', JSON.stringify(logEntry));
          }
        }
      }),
    );
  }

  private extractEntity(path: string): string {
    const parts = path.split('/').filter(Boolean);
    // Pega o primeiro segmento nao numerico apos /api/
    for (const part of parts) {
      if (part !== 'api' && isNaN(Number(part))) {
        return part;
      }
    }
    return 'unknown';
  }

  private extractEntityId(path: string): string | null {
    const parts = path.split('/').filter(Boolean);
    for (const part of parts) {
      if (!isNaN(Number(part))) {
        return part;
      }
    }
    return null;
  }
}
