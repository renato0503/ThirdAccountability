import { Module } from '@nestjs/common';
import { FirebaseModule } from './firebase.module';
import { AuthModule } from './modules/auth/auth.module';
import { UsersModule } from './modules/users/users.module';
import { InstitutionsModule } from './modules/institutions/institutions.module';
import { IntegrationsModule } from './modules/integrations/integrations.module';
import { SeedModule } from './modules/seed/seed.module';

@Module({
  imports: [
    FirebaseModule,
    AuthModule,
    UsersModule,
    InstitutionsModule,
    IntegrationsModule,
    SeedModule,
  ],
  controllers: [],
  providers: [],
})
export class AppModule {}
