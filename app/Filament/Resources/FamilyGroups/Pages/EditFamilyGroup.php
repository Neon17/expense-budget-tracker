<?php

namespace App\Filament\Resources\FamilyGroups\Pages;

use App\Filament\Resources\FamilyGroups\FamilyGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFamilyGroup extends EditRecord
{
    protected static string $resource = FamilyGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
