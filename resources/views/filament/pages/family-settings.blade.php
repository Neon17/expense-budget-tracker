<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}
        
        <div class="mt-6">
            <x-filament::button type="submit">
                Save Settings
            </x-filament::button>
        </div>
    </form>

    <x-filament::section class="mt-8">
        <x-slot name="heading">
            Family Summary
        </x-slot>
        <x-slot name="description">
            Overview of your family account
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @php
                $user = auth()->user();
                $childrenCount = $user->children()->count();
                $activeChildren = $user->children()->where('is_active', true)->count();
            @endphp
            
            <div class="p-4 rounded-lg bg-primary-50 dark:bg-primary-950">
                <div class="text-sm text-gray-600 dark:text-gray-400">Total Members</div>
                <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                    {{ $childrenCount + 1 }}
                </div>
                <div class="text-xs text-gray-500">Including yourself</div>
            </div>

            <div class="p-4 rounded-lg bg-success-50 dark:bg-success-950">
                <div class="text-sm text-gray-600 dark:text-gray-400">Active Members</div>
                <div class="text-2xl font-bold text-success-600 dark:text-success-400">
                    {{ $activeChildren + 1 }}
                </div>
                <div class="text-xs text-gray-500">Can log in</div>
            </div>

            <div class="p-4 rounded-lg bg-warning-50 dark:bg-warning-950">
                <div class="text-sm text-gray-600 dark:text-gray-400">Inactive Members</div>
                <div class="text-2xl font-bold text-warning-600 dark:text-warning-400">
                    {{ $childrenCount - $activeChildren }}
                </div>
                <div class="text-xs text-gray-500">Access disabled</div>
            </div>
        </div>
    </x-filament::section>

    @if($childrenCount > 0)
    <x-filament::section class="mt-6">
        <x-slot name="heading">
            Family Members
        </x-slot>

        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($user->children as $child)
            <div class="py-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary-500 flex items-center justify-center text-white font-semibold">
                        {{ strtoupper(substr($child->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-medium text-gray-900 dark:text-white">{{ $child->name }}</div>
                        <div class="text-sm text-gray-500">{{ $child->email }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($child->is_active)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200">
                            Active
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-200">
                            Inactive
                        </span>
                    @endif
                    <span class="text-sm text-gray-500">
                        {{ count($child->permissions ?? []) }} permissions
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </x-filament::section>
    @endif
</x-filament-panels::page>
