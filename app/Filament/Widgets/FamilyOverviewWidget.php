<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class FamilyOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Family Members Overview';

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && ($user->role === 'parent' || $user->children()->exists());
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->where('parent_id', Auth::id())
                    ->where('role', 'child')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Member')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Status')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('current_month_expenses')
                    ->label('Expenses')
                    ->money('NPR')
                    ->color('danger'),
                Tables\Columns\TextColumn::make('current_month_income')
                    ->label('Income')
                    ->money('NPR')
                    ->color('success'),
                Tables\Columns\TextColumn::make('permissions')
                    ->label('Permissions')
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) : 0)
                    ->badge()
                    ->color('primary'),
            ])
            ->paginated(false)
            ->emptyStateHeading('No family members yet')
            ->emptyStateDescription('Add family members to track their expenses together')
            ->emptyStateIcon('heroicon-o-users');
    }
}
