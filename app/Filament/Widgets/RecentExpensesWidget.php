<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Expense;
use Illuminate\Support\Facades\Auth;

class RecentExpensesWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Expenses';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Expense::query()
                    ->where('user_id', Auth::id())
                    ->with('category')
                    ->orderBy('date', 'desc')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\ColorColumn::make('category.color')
                    ->label(''),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category'),
                Tables\Columns\TextColumn::make('amount')
                    ->money('NPR')
                    ->color('danger'),
                Tables\Columns\TextColumn::make('date')
                    ->date(),
                Tables\Columns\TextColumn::make('note')
                    ->limit(30),
            ])
            ->paginated(false);
    }
}
