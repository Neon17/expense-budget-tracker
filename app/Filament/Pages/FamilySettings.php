<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class FamilySettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected string $view = 'filament.pages.family-settings';

    protected static ?string $navigationLabel = 'Family Settings';

    protected static string|UnitEnum|null $navigationGroup = 'Family';

    protected static ?int $navigationSort = 22;

    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();
        $this->form->fill([
            'family_name' => $user->family_name,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Family Configuration')
                    ->description('Configure your family account settings')
                    ->schema([
                        TextInput::make('family_name')
                            ->label('Family Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Smith Family')
                            ->helperText('This name will be displayed for all family members'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        
        $user = Auth::user();
        $user->update([
            'family_name' => $data['family_name'],
            'role' => 'parent',
        ]);

        // Update all children with the same family name
        $user->children()->update([
            'family_name' => $data['family_name'],
        ]);

        Notification::make()
            ->title('Family settings saved')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->submit('save'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user && ($user->role === 'parent' || $user->family_name !== null || $user->children()->exists());
    }

    public function getTitle(): string
    {
        return 'Family Settings';
    }

    public function getSubheading(): ?string
    {
        return 'Manage your family account configuration';
    }
}
