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
import { DocumentsModule } from './modules/documents/documents.module';
import { DiligencesModule } from './modules/diligences/diligences.module';
import { AccountingModule } from './modules/accounting/accounting.module';
import { PriceResearchModule } from './modules/price-research/price-research.module';
import { IntegrationsServicesModule } from './integrations/integrations.module';
import { AuditModule } from './modules/audit/audit.module';
import { SettingsModule } from './modules/settings/settings.module';
import { DashboardModule } from './modules/dashboard/dashboard.module';
import { ReportsModule } from './modules/reports/reports.module';
import { SeedModule } from './modules/seed/seed.module';

@Module({
  imports: [
    FirebaseModule, AuthModule, UsersModule,
    InstitutionsModule, IntegrationsModule,
    ProjectsModule, FundingSourcesModule,
    GoalsModule, ExpensesModule, BudgetItemsModule,
    DocumentsModule, DiligencesModule, AccountingModule,
    PriceResearchModule, IntegrationsServicesModule,
    AuditModule, SettingsModule, DashboardModule, ReportsModule,
    SeedModule,
  ],
})
export class AppModule {}
