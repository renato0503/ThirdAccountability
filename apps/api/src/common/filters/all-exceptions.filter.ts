import {
  ExceptionFilter,
  Catch,
  ArgumentsHost,
  HttpException,
  HttpStatus,
  Logger,
} from '@nestjs/common';
import { Request, Response } from 'express';

@Catch()
export class AllExceptionsFilter implements ExceptionFilter {
  private readonly logger = new Logger(AllExceptionsFilter.name);

  catch(exception: unknown, host: ArgumentsHost) {
    const ctx = host.switchToHttp();
    const response = ctx.getResponse<Response>();
    const request = ctx.getRequest<Request>();

    let status = HttpStatus.INTERNAL_SERVER_ERROR;
    let message = 'Erro interno do servidor';
    let errors: any = undefined;

    if (exception instanceof HttpException) {
      status = exception.getStatus();
      const responseBody = exception.getResponse();
      if (typeof responseBody === 'object') {
        message = (responseBody as any).message || exception.message;
        errors = (responseBody as any).errors;
      } else {
        message = String(responseBody);
      }
    } else if (exception instanceof Error) {
      message = exception.message;
      this.logger.error(
        `Erro nao tratado: ${exception.message}`,
        exception.stack,
      );
    }

    response.status(status).json({
      statusCode: status,
      message: Array.isArray(message) ? message : [message],
      errors,
      timestamp: new Date().toISOString(),
      path: request.url,
    });
  }
}
