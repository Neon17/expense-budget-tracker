<?php

namespace App\Filament\Resources\FamilyGroups\Pages;

use App\Filament\Resources\FamilyGroups\FamilyGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFamilyGroups extends ListRecords
{
    protected static string $resource = FamilyGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
