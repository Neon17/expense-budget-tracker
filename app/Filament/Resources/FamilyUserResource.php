<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FamilyUserResource\Pages;
use App\Filament\Resources\FamilyUserResource\RelationManagers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use UnitEnum;

class FamilyUserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Family Members';

    protected static string|UnitEnum|null $navigationGroup = 'Family';

    protected static ?int $navigationSort = 21;

    protected static ?string $modelLabel = 'Family Member';

    protected static ?string $pluralModelLabel = 'Family Members';

    protected static ?string $slug = 'family-members';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Member Information')
                    ->description('Basic information about the family member')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Full Name')
                            ->placeholder('Enter member name'),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('member@example.com'),
                        TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->minLength(8)
                            ->label(fn (string $context): string => $context === 'create' ? 'Password' : 'New Password (leave empty to keep current)')
                            ->placeholder('Enter password'),
                        Select::make('currency')
                            ->options([
                                'NPR' => 'NPR - Nepalese Rupee',
                                'USD' => 'USD - US Dollar',
                                'EUR' => 'EUR - Euro',
                                'GBP' => 'GBP - British Pound',
                                'INR' => 'INR - Indian Rupee',
                            ])
                            ->default('NPR')
                            ->required(),
                    ])->columns(2),

                Section::make('Permissions')
                    ->description('Control what this family member can do')
                    ->schema([
                        CheckboxList::make('permissions')
                            ->options([
                                'view_expenses' => 'View Expenses',
                                'create_expenses' => 'Create Expenses',
                                'edit_expenses' => 'Edit Own Expenses',
                                'delete_expenses' => 'Delete Own Expenses',
                                'view_incomes' => 'View Incomes',
                                'create_incomes' => 'Create Incomes',
                                'edit_incomes' => 'Edit Own Incomes',
                                'delete_incomes' => 'Delete Own Incomes',
                                'view_budgets' => 'View Budgets',
                                'view_reports' => 'View Reports',
                                'view_categories' => 'View Categories',
                                'manage_categories' => 'Manage Categories',
                            ])
                            ->columns(3)
                            ->bulkToggleable()
                            ->default(['view_expenses', 'create_expenses', 'view_incomes', 'create_incomes', 'view_budgets']),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive members cannot log in'),
                    ]),

                Hidden::make('parent_id')
                    ->default(fn() => Auth::id()),
                Hidden::make('role')
                    ->default('child'),
                Hidden::make('family_name')
                    ->default(fn() => Auth::user()->family_name),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('permissions')
                    ->badge()
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) . ' permissions' : '0 permissions')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('current_month_expenses')
                    ->label('Expenses (Month)')
                    ->money('NPR')
                    ->sortable(false),
                Tables\Columns\TextColumn::make('current_month_income')
                    ->label('Income (Month)')
                    ->money('NPR')
                    ->sortable(false),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->boolean()
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only')
                    ->native(false),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->before(function (User $record) {
                        // Before deleting, transfer expenses to parent or handle appropriately
                    }),
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
            'index' => Pages\ListFamilyUsers::route('/'),
            'create' => Pages\CreateFamilyUser::route('/create'),
            'edit' => Pages\EditFamilyUser::route('/{record}/edit'),
        ];
    }

    /**
     * Only show child users belonging to the current user.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('parent_id', Auth::id())
            ->where('role', 'child');
    }

    /**
     * Only show this resource to parent users or users with family_name.
     * Child users should not see this (they can't create children).
     */
    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // Child users cannot create other children
        if ($user->role === 'child') {
            return false;
        }

        return $user->role === 'parent' || $user->family_name !== null;
    }

    /**
     * Only parent users can create child accounts.
     */
    public static function canCreate(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // Child users cannot create other children
        if ($user->role === 'child') {
            return false;
        }

        return true;
    }
}
