import { Module } from '@nestjs/common';
import { BudgetItemsService } from './budget-items.service';

@Module({
  providers: [BudgetItemsService],
  exports: [BudgetItemsService],
})
export class BudgetItemsModule {}
