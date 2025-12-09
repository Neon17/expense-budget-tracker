<?php

namespace App\Filament\Resources\FamilyUserResource\Pages;

use App\Filament\Resources\FamilyUserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFamilyUsers extends ListRecords
{
    protected static string $resource = FamilyUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add Family Member'),
        ];
    }

    public function getTitle(): string
    {
        return 'Family Members';
    }

    public function getSubheading(): ?string
    {
        $user = auth()->user();
        if ($user->family_name) {
            return "Managing members of {$user->family_name}";
        }
        return 'Manage your family members';
    }
}
