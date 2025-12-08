<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BudgetResource\Pages;
use App\Models\Budget;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class BudgetResource extends Resource
{
    protected static ?string $model = Budget::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-calculator';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return 'Finance';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Budget Details')
                    ->schema([
                        TextInput::make('monthly_limit')
                            ->numeric()
                            ->required()
                            ->prefix('NPR')
                            ->minValue(1)
                            ->label('Monthly Budget Limit'),
                        TextInput::make('month')
                            ->required()
                            ->default(fn () => now()->format('Y-m'))
                            ->placeholder('YYYY-MM')
                            ->regex('/^\d{4}-(0[1-9]|1[0-2])$/')
                            ->helperText('Format: YYYY-MM (e.g., 2024-01)'),
                        Hidden::make('user_id')
                            ->default(fn () => Auth::id()),
                        Hidden::make('currency')
                            ->default(fn () => Auth::user()->currency ?? 'NPR'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('month')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('monthly_limit')
                    ->money('NPR')
                    ->sortable()
                    ->label('Budget'),
                Tables\Columns\TextColumn::make('spent')
                    ->money('NPR')
                    ->label('Spent')
                    ->getStateUsing(fn ($record) => $record->spent),
                Tables\Columns\TextColumn::make('remaining')
                    ->money('NPR')
                    ->label('Remaining')
                    ->getStateUsing(fn ($record) => $record->remaining)
                    ->color(fn ($record) => $record->remaining > 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('usage_percentage')
                    ->label('Usage')
                    ->getStateUsing(fn ($record) => $record->usage_percentage . '%')
                    ->badge()
                    ->color(fn ($record) => match(true) {
                        $record->usage_percentage >= 100 => 'danger',
                        $record->usage_percentage >= 90 => 'danger',
                        $record->usage_percentage >= 70 => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->getStateUsing(fn ($record) => ucfirst($record->status))
                    ->badge()
                    ->color(fn ($record) => match($record->status) {
                        'exceeded' => 'danger',
                        'critical' => 'danger',
                        'warning' => 'warning',
                        default => 'success',
                    }),
            ])
            ->defaultSort('month', 'desc')
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBudgets::route('/'),
            'create' => Pages\CreateBudget::route('/create'),
            'edit' => Pages\EditBudget::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id());
    }
}
