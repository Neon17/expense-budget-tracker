<?php

namespace App\Filament\Resources\FamilyGroups;

use App\Filament\Resources\FamilyGroups\Pages\CreateFamilyGroup;
use App\Filament\Resources\FamilyGroups\Pages\EditFamilyGroup;
use App\Filament\Resources\FamilyGroups\Pages\ListFamilyGroups;
use App\Filament\Resources\FamilyGroups\RelationManagers\MembersRelationManager;
use App\Models\FamilyGroup;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class FamilyGroupResource extends Resource
{
    protected static ?string $model = FamilyGroup::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Family Groups';

    protected static string|UnitEnum|null $navigationGroup = 'Family';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Family Group Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Group Name')
                            ->placeholder('e.g., Smith Family'),
                        Textarea::make('description')
                            ->maxLength(500)
                            ->rows(3)
                            ->placeholder('Describe your family group...'),
                        TextInput::make('shared_budget')
                            ->numeric()
                            ->prefix('NPR')
                            ->label('Shared Monthly Budget')
                            ->placeholder('Enter shared budget amount'),
                        TextInput::make('invite_code')
                            ->label('Invite Code')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Share this code with family members to join'),
                        Hidden::make('owner_id')
                            ->default(fn() => Auth::id()),
                        Hidden::make('currency')
                            ->default(fn() => Auth::user()->currency ?? 'NPR'),
                    ])->columns(2),
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
                Tables\Columns\TextColumn::make('members_count')
                    ->counts('members')
                    ->label('Members')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('shared_budget')
                    ->money('NPR')
                    ->label('Budget')
                    ->sortable(),
                Tables\Columns\TextColumn::make('invite_code')
                    ->badge()
                    ->color('success')
                    ->copyable()
                    ->copyMessage('Invite code copied!'),
                Tables\Columns\TextColumn::make('owner.name')
                    ->label('Owner'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                Action::make('regenerate_code')
                    ->label('New Code')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn(FamilyGroup $record) => $record->regenerateInviteCode()),
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
            MembersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFamilyGroups::route('/'),
            'create' => CreateFamilyGroup::route('/create'),
            'edit' => EditFamilyGroup::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function ($query) {
                $query->where('owner_id', Auth::id())
                    ->orWhereHas('members', function ($q) {
                        $q->where('user_id', Auth::id());
                    });
            });
    }
}
