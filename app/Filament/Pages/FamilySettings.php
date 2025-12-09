<?php

namespace App\Filament\Pages;

use App\Models\FamilyGroup;
use App\Models\User;
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

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

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
        $user = Auth::user();
        
        // Only show edit form for parents
        if ($user->isChild()) {
            return $schema->components([]);
        }
        
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
        $user = Auth::user();
        
        // Only parents can save settings
        if ($user->isChild()) {
            Notification::make()
                ->title('Permission denied')
                ->body('Only parents can modify family settings.')
                ->danger()
                ->send();
            return;
        }
        
        $data = $this->form->getState();
        
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
        $user = Auth::user();
        
        // Hide save button for children
        if ($user->isChild()) {
            return [];
        }
        
        return [
            Action::make('save')
                ->label('Save Settings')
                ->submit('save'),
        ];
    }

    /**
     * Get family data for the view.
     */
    public function getFamilyData(): array
    {
        $user = Auth::user();
        $dataOwner = $user->getDataOwner();
        
        // Get the family group if exists
        $familyGroup = $user->getFamilyGroup() ?? $dataOwner->ownedFamilyGroups()->first();
        
        // Determine if user is parent or child
        $isParent = !$user->isChild();
        $isChild = $user->isChild();
        
        // Get parent info
        $parent = $isChild ? $user->parent : $user;
        
        // Get siblings (other children of the same parent, excluding self)
        $siblings = $isChild 
            ? User::where('parent_id', $parent->id)->where('id', '!=', $user->id)->get()
            : collect();
        
        // Get all children (for parent view)
        $children = $isParent ? $user->children()->get() : collect();
        
        // Get all family members
        $familyMembers = $user->getFamilyMembers();
        
        // Calculate stats
        $totalMembers = $familyMembers->count();
        $activeMembers = $familyMembers->filter(fn($m) => $m->is_active !== false)->count();
        $inactiveMembers = $totalMembers - $activeMembers;
        
        return [
            'user' => $user,
            'isParent' => $isParent,
            'isChild' => $isChild,
            'parent' => $parent,
            'siblings' => $siblings,
            'children' => $children,
            'familyGroup' => $familyGroup,
            'familyMembers' => $familyMembers,
            'totalMembers' => $totalMembers,
            'activeMembers' => $activeMembers,
            'inactiveMembers' => $inactiveMembers,
            'familyName' => $parent->family_name ?? $familyGroup?->name ?? 'My Family',
            'currency' => $dataOwner->currency ?? 'NPR',
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Show for parents, children, or users with family groups
        return $user->role === 'parent' 
            || $user->isChild() 
            || $user->family_name !== null 
            || $user->children()->exists()
            || $user->familyGroups()->exists();
    }

    public function getTitle(): string
    {
        return 'Family Settings';
    }

    public function getSubheading(): ?string
    {
        $user = Auth::user();
        if ($user->isChild()) {
            return 'View your family members and settings';
        }
        return 'Manage your family account configuration';
    }
}
