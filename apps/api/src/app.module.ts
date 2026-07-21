import { Module } from '@nestjs/common';
import { FirebaseModule } from './firebase.module';
import { AuthModule } from './modules/auth/auth.module';
import { UsersModule } from './modules/users/users.module';
import { InstitutionsModule } from './modules/institutions/institutions.module';
import { IntegrationsModule } from './modules/integrations/integrations.module';
import { ProjectsModule } from './modules/projects/projects.module';
import { FundingSourcesModule } from './modules/funding-sources/funding-sources.module';
import { GoalsModule } from './modules/goals/goals.module';
import { ExpensesModule } from './modules/expenses/expenses.module';
import { BudgetItemsModule } from './modules/budget-items/budget-items.module';
import { SeedModule } from './modules/seed/seed.module';

@Module({
  imports: [
    FirebaseModule, AuthModule, UsersModule,
    InstitutionsModule, IntegrationsModule,
    ProjectsModule, FundingSourcesModule,
    GoalsModule, ExpensesModule, BudgetItemsModule,
    SeedModule,
  ],
})
export class AppModule {}
