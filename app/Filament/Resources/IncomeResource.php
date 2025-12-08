<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncomeResource\Pages;
use App\Models\Category;
use App\Models\Income;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IncomeResource extends Resource
{
    protected static ?string $model = Income::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-arrow-trending-up';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'Finance';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\Section::make('Income Details')
                    ->schema([
                        Components\TextInput::make('source')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Salary, Freelance Project'),
                        Components\Select::make('category_id')
                            ->label('Category')
                            ->options(function () {
                                return Category::where('user_id', auth()->id())
                                    ->incomeType()
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Components\ColorPicker::make('color')
                                    ->required()
                                    ->default('#10B981'),
                            ])
                            ->createOptionUsing(function (array $data) {
                                $category = Category::create([
                                    'user_id' => auth()->id(),
                                    'name' => $data['name'],
                                    'color' => $data['color'],
                                    'type' => 'income',
                                ]);
                                return $category->id;
                            }),
                        Components\TextInput::make('amount')
                            ->numeric()
                            ->required()
                            ->prefix('NPR')
                            ->minValue(0.01),
                        Components\DatePicker::make('date')
                            ->required()
                            ->default(now())
                            ->maxDate(now()),
                        Components\Textarea::make('note')
                            ->maxLength(500)
                            ->rows(3)
                            ->columnSpanFull(),
                        Components\Hidden::make('user_id')
                            ->default(fn () => auth()->id()),
                        Components\Hidden::make('currency')
                            ->default(fn () => auth()->user()->currency ?? 'NPR'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ColorColumn::make('category.color')
                    ->label(''),
                Tables\Columns\TextColumn::make('source')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->placeholder('Uncategorized'),
                Tables\Columns\TextColumn::make('amount')
                    ->money('NPR')
                    ->sortable()
                    ->color('success'),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('note')
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->options(function () {
                        return Category::where('user_id', auth()->id())
                            ->incomeType()
                            ->pluck('name', 'id');
                    }),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Components\DatePicker::make('from'),
                        Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
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
            'index' => Pages\ListIncomes::route('/'),
            'create' => Pages\CreateIncome::route('/create'),
            'edit' => Pages\EditIncome::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id());
    }
}
