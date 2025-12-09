<?php

namespace App\Filament\Resources\FamilyUserResource\Pages;

use App\Filament\Resources\FamilyUserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFamilyUser extends EditRecord
{
    protected static string $resource = FamilyUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return "Edit {$this->record->name}";
    }
}
