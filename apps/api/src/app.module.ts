import { Module } from '@nestjs/common';
import { FirebaseModule } from './firebase.module';
import { AuthModule } from './modules/auth/auth.module';
import { UsersModule } from './modules/users/users.module';

@Module({
  imports: [FirebaseModule, AuthModule, UsersModule],
  controllers: [],
  providers: [],
})
export class AppModule {}
