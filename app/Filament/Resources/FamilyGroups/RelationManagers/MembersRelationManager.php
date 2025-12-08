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
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pivot.role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'success',
                        'member' => 'primary',
                        'viewer' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('pivot.joined_at')
                    ->label('Joined')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Add Member')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'email'])
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
