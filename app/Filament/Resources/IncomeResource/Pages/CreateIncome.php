<?php

namespace App\Filament\Resources\IncomeResource\Pages;

use App\Filament\Resources\IncomeResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateIncome extends CreateRecord
{
    protected static string $resource = IncomeResource::class;

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
