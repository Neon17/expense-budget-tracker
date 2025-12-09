<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Connection Status --}}
        @if($this->isConnected)
            <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                <div class="flex items-center gap-3">
                    <x-heroicon-o-check-circle class="w-5 h-5 text-green-500" />
                    <span class="text-green-700 dark:text-green-300 font-medium">
                        Connected to Superset at {{ $this->supersetUrl }}
                    </span>
                </div>
            </div>
        @else
            <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <x-heroicon-o-x-circle class="w-5 h-5 text-red-500" />
                        <div>
                            <span class="text-red-700 dark:text-red-300 font-medium">Not connected to Superset</span>
                            @if($this->errorMessage)
                                <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $this->errorMessage }}</p>
                            @endif
                        </div>
                    </div>
                    <x-filament::button wire:click="refreshConnection" size="sm" color="danger" outlined>
                        <x-heroicon-m-arrow-path class="w-4 h-4 mr-2" />
                        Retry Connection
                    </x-filament::button>
                </div>
            </div>
        @endif

        {{-- Dashboard Selector --}}
        @if($this->isConnected && count($this->dashboards) > 0)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Select Dashboard</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($this->dashboards as $dashboard)
                        <button
                            wire:click="selectDashboard({{ $dashboard['id'] }})"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 
                                {{ $this->selectedDashboard === $dashboard['id'] 
                                    ? 'bg-primary-500 text-white shadow-md' 
                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}"
                        >
                            {{ $dashboard['dashboard_title'] ?? 'Dashboard #' . $dashboard['id'] }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Embedded Superset Dashboard --}}
        @if($this->isConnected && $this->selectedDashboard)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        @foreach($this->dashboards as $dashboard)
                            @if($dashboard['id'] === $this->selectedDashboard)
                                {{ $dashboard['dashboard_title'] ?? 'Dashboard' }}
                            @endif
                        @endforeach
                    </h3>
                    <div class="flex items-center gap-2">
                        <a 
                            href="{{ $this->getEmbedUrl() }}" 
                            target="_blank"
                            class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                        >
                            <x-heroicon-m-arrow-top-right-on-square class="w-4 h-4" />
                            Open in Superset
                        </a>
                    </div>
                </div>
                <div class="relative" style="height: 80vh;">
                    <iframe
                        src="{{ $this->getEmbedUrl() }}"
                        class="w-full h-full border-0"
                        sandbox="allow-same-origin allow-scripts allow-popups allow-forms"
                        loading="lazy"
                    ></iframe>
                </div>
            </div>
        @elseif($this->isConnected && count($this->dashboards) === 0)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-12">
                <div class="text-center">
                    <x-heroicon-o-chart-bar class="w-16 h-16 mx-auto text-gray-400" />
                    <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">No Dashboards Found</h3>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">
                        Create dashboards in Superset to view them here.
                    </p>
                    <a 
                        href="{{ $this->supersetUrl }}" 
                        target="_blank"
                        class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition-colors"
                    >
                        <x-heroicon-m-arrow-top-right-on-square class="w-5 h-5" />
                        Open Superset
                    </a>
                </div>
            </div>
        @elseif(!$this->isConnected)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-12">
                <div class="text-center">
                    <x-heroicon-o-signal-slash class="w-16 h-16 mx-auto text-gray-400" />
                    <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Connection Required</h3>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">
                        Connect to Apache Superset to view analytics dashboards.
                    </p>
                    <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-left max-w-md mx-auto">
                        <h4 class="font-medium text-gray-900 dark:text-white mb-2">Setup Instructions:</h4>
                        <ol class="list-decimal list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400">
                            <li>Ensure Superset is running</li>
                            <li>Update <code class="bg-gray-200 dark:bg-gray-600 px-1 rounded">.env</code> with correct URL</li>
                            <li>Set <code class="bg-gray-200 dark:bg-gray-600 px-1 rounded">SUPERSET_USERNAME</code> and <code class="bg-gray-200 dark:bg-gray-600 px-1 rounded">SUPERSET_PASSWORD</code></li>
                            <li>Click "Retry Connection" above</li>
                        </ol>
                    </div>
                </div>
            </div>
        @endif

        {{-- Quick Links --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a 
                href="{{ $this->supersetUrl }}/superset/sqllab/" 
                target="_blank"
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow"
            >
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <x-heroicon-o-circle-stack class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 dark:text-white">SQL Lab</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Run SQL queries</p>
                    </div>
                </div>
            </a>
            <a 
                href="{{ $this->supersetUrl }}/chart/list/" 
                target="_blank"
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow"
            >
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                        <x-heroicon-o-chart-pie class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 dark:text-white">Charts</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Manage visualizations</p>
                    </div>
                </div>
            </a>
            <a 
                href="{{ $this->supersetUrl }}/tablemodelview/list/" 
                target="_blank"
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow"
            >
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                        <x-heroicon-o-table-cells class="w-6 h-6 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 dark:text-white">Datasets</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Configure data sources</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</x-filament-panels::page>
