<?php

namespace App\Filament\Resources\BudgetResource\Pages;

use App\Filament\Resources\BudgetResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateBudget extends CreateRecord
{
    protected static string $resource = BudgetResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        $data['currency'] = Auth::user()->currency ?? 'NPR';
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
