<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;

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
