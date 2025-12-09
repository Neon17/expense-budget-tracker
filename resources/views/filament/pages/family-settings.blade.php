<x-filament-panels::page>
    @php
        $familyData = $this->getFamilyData();
        $user = $familyData['user'];
        $isParent = $familyData['isParent'];
        $isChild = $familyData['isChild'];
        $parent = $familyData['parent'];
        $siblings = $familyData['siblings'];
        $children = $familyData['children'];
        $familyGroup = $familyData['familyGroup'];
        $familyMembers = $familyData['familyMembers'];
        $totalMembers = $familyData['totalMembers'];
        $activeMembers = $familyData['activeMembers'];
        $inactiveMembers = $familyData['inactiveMembers'];
        $familyName = $familyData['familyName'];
        $currency = $familyData['currency'];
    @endphp

    {{-- Family Header Banner --}}
    <div class="relative overflow-hidden rounded-xl bg-gradient-to-r from-primary-600 to-primary-800 dark:from-primary-700 dark:to-primary-900 p-6 mb-6">
        <div class="absolute inset-0 bg-grid-white/10"></div>
        <div class="relative flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center">
                        <x-heroicon-o-home class="w-8 h-8 text-white" />
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-white">{{ $familyName }}</h2>
                        <p class="text-primary-100 text-sm">
                            @if($isChild)
                                You are a member of this family
                            @else
                                You are the head of this family
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-white">{{ $totalMembers }}</div>
                <div class="text-primary-100 text-sm">Family Members</div>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                    <x-heroicon-o-users class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalMembers }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Members</div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-success-100 dark:bg-success-900 flex items-center justify-center">
                    <x-heroicon-o-check-circle class="w-5 h-5 text-success-600 dark:text-success-400" />
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $activeMembers }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Active Members</div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-warning-100 dark:bg-warning-900 flex items-center justify-center">
                    <x-heroicon-o-clock class="w-5 h-5 text-warning-600 dark:text-warning-400" />
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $inactiveMembers }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Inactive Members</div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-info-100 dark:bg-info-900 flex items-center justify-center">
                    <x-heroicon-o-currency-dollar class="w-5 h-5 text-info-600 dark:text-info-400" />
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $currency }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Currency</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Parent Settings Form (only for parents) --}}
    @if($isParent)
        <x-filament::section class="mb-6">
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-cog-6-tooth class="w-5 h-5" />
                    Family Configuration
                </div>
            </x-slot>
            <x-slot name="description">
                Configure your family account settings
            </x-slot>

            <form wire:submit="save">
                {{ $this->form }}
                
                <div class="mt-6">
                    <x-filament::button type="submit">
                        <x-heroicon-m-check class="w-4 h-4 mr-2" />
                        Save Settings
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>
    @endif

    {{-- Parent Info (for children) --}}
    @if($isChild && $parent)
        <x-filament::section class="mb-6">
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-user class="w-5 h-5" />
                    Family Head
                </div>
            </x-slot>
            <x-slot name="description">
                The parent/owner of your family account
            </x-slot>

            <div class="flex items-center gap-4 p-4 bg-gradient-to-r from-primary-50 to-primary-100 dark:from-primary-950 dark:to-primary-900 rounded-xl">
                <div class="w-16 h-16 rounded-full bg-primary-500 flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                    {{ strtoupper(substr($parent->name, 0, 1)) }}
                </div>
                <div class="flex-1">
                    <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ $parent->name }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ $parent->email }}</div>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200">
                            <x-heroicon-s-star class="w-3 h-3 mr-1" />
                            Parent
                        </span>
                        @if($parent->is_active !== false)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200">
                                Active
                            </span>
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Currency</div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ $parent->currency ?? 'NPR' }}</div>
                </div>
            </div>
        </x-filament::section>
    @endif

    {{-- Your Profile (for children) --}}
    @if($isChild)
        <x-filament::section class="mb-6">
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-identification class="w-5 h-5" />
                    Your Profile
                </div>
            </x-slot>
            <x-slot name="description">
                Your account information
            </x-slot>

            <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl">
                <div class="w-16 h-16 rounded-full bg-info-500 flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div class="flex-1">
                    <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ $user->name }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ $user->email }}</div>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-info-100 text-info-800 dark:bg-info-900 dark:text-info-200">
                            <x-heroicon-s-user class="w-3 h-3 mr-1" />
                            Child Account
                        </span>
                        @if($user->is_active !== false)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200">
                                Active
                            </span>
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Joined</div>
                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->created_at->format('M d, Y') }}</div>
                </div>
            </div>
        </x-filament::section>
    @endif

    {{-- Siblings (for children) --}}
    @if($isChild && $siblings->count() > 0)
        <x-filament::section class="mb-6">
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-user-group class="w-5 h-5" />
                    Your Siblings
                </div>
            </x-slot>
            <x-slot name="description">
                Other family members like you
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($siblings as $sibling)
                    <div class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold shadow">
                            {{ strtoupper(substr($sibling->name, 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $sibling->name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $sibling->email }}</div>
                        </div>
                        <div>
                            @if($sibling->is_active !== false)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200">
                                    <x-heroicon-s-check-circle class="w-3 h-3 mr-1" />
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-200">
                                    <x-heroicon-s-x-circle class="w-3 h-3 mr-1" />
                                    Inactive
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    @endif

    {{-- Children (for parents) --}}
    @if($isParent && $children->count() > 0)
        <x-filament::section class="mb-6">
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-users class="w-5 h-5" />
                    Family Members
                </div>
            </x-slot>
            <x-slot name="description">
                Members of your family account
            </x-slot>

            <div class="space-y-3">
                @foreach($children as $child)
                    <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center text-white font-bold shadow">
                            {{ strtoupper(substr($child->name, 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $child->name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $child->email }}</div>
                        </div>
                        <div class="flex items-center gap-3">
                            @if($child->is_active !== false)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200">
                                    <x-heroicon-s-check-circle class="w-3 h-3 mr-1" />
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-200">
                                    <x-heroicon-s-x-circle class="w-3 h-3 mr-1" />
                                    Inactive
                                </span>
                            @endif
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ count($child->permissions ?? []) }} permissions
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    @elseif($isParent)
        <x-filament::section class="mb-6">
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-users class="w-5 h-5" />
                    Family Members
                </div>
            </x-slot>

            <div class="text-center py-8">
                <div class="w-16 h-16 mx-auto rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                    <x-heroicon-o-user-plus class="w-8 h-8 text-gray-400" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No family members yet</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-4">Add family members to share your budget and expenses.</p>
                <x-filament::button tag="a" href="{{ route('filament.admin.resources.family-users.create') }}">
                    <x-heroicon-m-plus class="w-4 h-4 mr-2" />
                    Add Family Member
                </x-filament::button>
            </div>
        </x-filament::section>
    @endif

    {{-- Family Group Info --}}
    @if($familyGroup)
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-key class="w-5 h-5" />
                    Family Group
                </div>
            </x-slot>
            <x-slot name="description">
                Family group information and invite code
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Group Name</div>
                    <div class="font-semibold text-gray-900 dark:text-white">{{ $familyGroup->name }}</div>
                </div>
                
                @if($isParent && $familyGroup->invite_code)
                    <div class="p-4 bg-primary-50 dark:bg-primary-950 rounded-xl">
                        <div class="text-sm text-primary-600 dark:text-primary-400 mb-1">Invite Code</div>
                        <div class="flex items-center gap-2">
                            <code class="text-lg font-mono font-bold text-primary-700 dark:text-primary-300 bg-primary-100 dark:bg-primary-900 px-3 py-1 rounded">
                                {{ $familyGroup->invite_code }}
                            </code>
                            <button type="button" 
                                    onclick="navigator.clipboard.writeText('{{ $familyGroup->invite_code }}')"
                                    class="p-2 text-primary-600 hover:bg-primary-100 dark:hover:bg-primary-900 rounded-lg transition-colors">
                                <x-heroicon-o-clipboard class="w-5 h-5" />
                            </button>
                        </div>
                        <div class="text-xs text-primary-500 dark:text-primary-400 mt-2">
                            Share this code to invite others to your family
                        </div>
                    </div>
                @endif

                @if($familyGroup->shared_budget)
                    <div class="p-4 bg-success-50 dark:bg-success-950 rounded-xl">
                        <div class="text-sm text-success-600 dark:text-success-400 mb-1">Shared Budget</div>
                        <div class="font-semibold text-success-700 dark:text-success-300">
                            {{ $currency }} {{ number_format($familyGroup->shared_budget, 2) }}
                        </div>
                    </div>
                @endif

                <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Status</div>
                    <div class="flex items-center gap-2">
                        @if($familyGroup->is_active)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200">
                                <x-heroicon-s-check-circle class="w-3 h-3 mr-1" />
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-200">
                                Inactive
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
