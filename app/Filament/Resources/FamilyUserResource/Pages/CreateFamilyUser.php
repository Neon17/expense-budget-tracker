<?php

namespace App\Filament\Resources\FamilyUserResource\Pages;

use App\Filament\Resources\FamilyUserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateFamilyUser extends CreateRecord
{
    protected static string $resource = FamilyUserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        
        // Ensure parent relationship
        $data['parent_id'] = $user->id;
        $data['role'] = 'child';
        
        // Inherit family name from parent
        if (!isset($data['family_name']) || empty($data['family_name'])) {
            $data['family_name'] = $user->family_name;
        }
        
        // If parent doesn't have family name set, update parent's role
        if (!$user->family_name && $user->role !== 'parent') {
            $user->update(['role' => 'parent']);
        }

        return $data;
    }

    public function getTitle(): string
    {
        return 'Add Family Member';
    }
}
