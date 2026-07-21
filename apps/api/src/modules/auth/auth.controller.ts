import { Controller, Post, Body, UseGuards } from '@nestjs/common';
import { AuthService } from './auth.service';
import { FirebaseAuthGuard } from '../../common/guards/firebase-auth.guard';
import { CurrentUser } from '../../common/decorators/current-user.decorator';
import { Public } from '../../common/decorators/public.decorator';

@Controller('auth')
export class AuthController {
  constructor(private readonly authService: AuthService) {}

  @Public()
  @Post('sync')
  async syncUser(@Body() body: { token: string }) {
    const decoded = await this.authService.verifyToken(body.token);
    return this.authService.syncUser(decoded.uid);
  }

  @UseGuards(FirebaseAuthGuard)
  @Post('me')
  async me(@CurrentUser() user: any) {
    return user;
  }
}
