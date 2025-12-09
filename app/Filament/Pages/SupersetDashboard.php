<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Http;
use UnitEnum;

class SupersetDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected string $view = 'filament.pages.superset-dashboard';

    protected static ?string $navigationLabel = 'Analytics Dashboard';

    protected static string|UnitEnum|null $navigationGroup = 'Analytics';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'Superset Analytics';

    public ?string $supersetToken = null;
    public ?string $supersetUrl = null;
    public array $dashboards = [];
    public ?int $selectedDashboard = null;
    public bool $isConnected = false;
    public ?string $errorMessage = null;

    public function mount(): void
    {
        $this->supersetUrl = config('services.superset.url', env('SUPERSET_URL', 'http://localhost:8088'));
        $this->authenticateWithSuperset();
        
        if ($this->isConnected) {
            $this->fetchDashboards();
        }
    }

    protected function authenticateWithSuperset(): void
    {
        try {
            $response = Http::timeout(10)->post("{$this->supersetUrl}/api/v1/security/login", [
                'username' => config('services.superset.username', env('SUPERSET_USERNAME', 'admin')),
                'password' => config('services.superset.password', env('SUPERSET_PASSWORD', 'admin')),
                'provider' => 'db',
                'refresh' => true,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->supersetToken = $data['access_token'] ?? null;
                $this->isConnected = true;
            } else {
                $this->errorMessage = 'Failed to authenticate with Superset: ' . $response->status();
                $this->isConnected = false;
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'Could not connect to Superset: ' . $e->getMessage();
            $this->isConnected = false;
        }
    }

    protected function fetchDashboards(): void
    {
        try {
            $response = Http::withToken($this->supersetToken)
                ->timeout(10)
                ->get("{$this->supersetUrl}/api/v1/dashboard/");

            if ($response->successful()) {
                $data = $response->json();
                $this->dashboards = $data['result'] ?? [];
                
                // Select first dashboard by default
                if (!empty($this->dashboards)) {
                    $this->selectedDashboard = $this->dashboards[0]['id'] ?? null;
                }
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'Could not fetch dashboards: ' . $e->getMessage();
        }
    }

    public function selectDashboard(int $dashboardId): void
    {
        $this->selectedDashboard = $dashboardId;
    }

    public function getEmbedUrl(): ?string
    {
        if (!$this->selectedDashboard || !$this->isConnected) {
            return null;
        }

        // Superset embedded dashboard URL with standalone mode
        return "{$this->supersetUrl}/superset/dashboard/{$this->selectedDashboard}/?standalone=true";
    }

    public function refreshConnection(): void
    {
        $this->errorMessage = null;
        $this->authenticateWithSuperset();
        
        if ($this->isConnected) {
            $this->fetchDashboards();
        }
    }

    public function getTitle(): string
    {
        return 'Superset Analytics Dashboard';
    }

    public function getSubheading(): ?string
    {
        return 'Advanced analytics powered by Apache Superset';
    }
}
