<?php

namespace App\Filament\Resources\FamilyGroups\RelationManagers;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (User $record): ?string => $record->isChild() ? 'Child Account' : null),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Account Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst($state ?? 'member'))
                    ->color(fn (?string $state): string => match ($state) {
                        'parent' => 'success',
                        'child' => 'warning',
                        default => 'primary',
                    }),
                Tables\Columns\TextColumn::make('pivot.role')
                    ->label('Group Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'success',
                        'member' => 'primary',
                        'viewer' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                Tables\Columns\TextColumn::make('pivot.joined_at')
                    ->label('Joined')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'parent' => 'Parent Accounts',
                        'child' => 'Child Accounts',
                    ])
                    ->label('Account Type'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Add Member')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'email'])
                    ->recordSelectOptionsQuery(function () {
                        $user = Auth::user();
                        // Allow adding self, children, or other users
                        return User::query()
                            ->where(function ($query) use ($user) {
                                $query->where('id', $user->id)
                                    ->orWhere('parent_id', $user->id);
                            })
                            ->whereNotIn('id', $this->ownerRecord->members->pluck('id'));
                    })
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Select::make('role')
                            ->options([
                                'admin' => 'Admin',
                                'member' => 'Member',
                                'viewer' => 'Viewer',
                            ])
                            ->default('member')
                            ->required(),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['joined_at'] = now();
                        return $data;
                    }),
            ])
            ->actions([
                Action::make('updateRole')
                    ->label('Change Role')
                    ->icon('heroicon-o-user-circle')
                    ->form([
                        Select::make('role')
                            ->options([
                                'admin' => 'Admin',
                                'member' => 'Member',
                                'viewer' => 'Viewer',
                            ])
                            ->required(),
                    ])
                    ->action(function (User $record, array $data): void {
                        $this->ownerRecord->members()->updateExistingPivot($record->id, [
                            'role' => $data['role'],
                        ]);
                    }),
                Action::make('viewExpenses')
                    ->label('View Expenses')
                    ->icon('heroicon-o-banknotes')
                    ->url(fn (User $record): string => route('filament.admin.resources.expenses.index', ['tableFilters[user_id][value]' => $record->id]))
                    ->openUrlInNewTab(),
                DetachAction::make()
                    ->label('Remove'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->label('Remove Selected'),
                ]),
            ]);
    }
}
