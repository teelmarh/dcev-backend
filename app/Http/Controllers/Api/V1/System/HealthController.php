<?php

namespace App\Http\Controllers\Api\V1\System;

use App\Http\Controllers\Controller;
use App\Http\Resources\System\EndpointResource;
use App\Traits\Api\ApiResponder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class HealthController extends Controller
{
    use ApiResponder;

    public function index(): JsonResponse
    {
        $endpoints = EndpointResource::collection($this->controllerRoutes())->resolve();

        return $this->dataResponse([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'environment' => app()->environment(),
            'endpoints' => $endpoints,
        ], 'Server is healthy.', true, 200);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function controllerRoutes(): Collection
    {
        return collect(Route::getRoutes())
            ->filter(function ($route) {
                $action = (string) $route->getActionName();

                return str_starts_with($action, 'App\\Http\\Controllers\\');
            })
            ->map(function ($route) {
                $methods = array_values(array_diff($route->methods(), ['HEAD']));

                return [
                    'method' => implode('|', $methods),
                    'uri' => $route->uri(),
                    'name' => $route->getName(),
                    'action' => $route->getActionName(),
                    'middleware' => $route->gatherMiddleware(),
                ];
            })
            ->sortBy('uri')
            ->values();
    }
}
