<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Plugin\EnablePluginRequest;
use App\Http\Requests\Plugin\StoreConfigPluginRequest;
use App\Http\Requests\Plugin\StorePluginRequest;
use App\Models\Plugin;
use App\Services\PluginService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PluginController extends Controller
{
    public function __construct(private readonly PluginService $service)
    {
    }

    public function index(): JsonResponse
    {
        $plugins = Plugin::orderBy('title')->get()->map(fn(Plugin $plugin): array => $this->serialize($plugin));
        return response()->json($plugins);
    }

    public function catalog(Request $request): JsonResponse
    {
        return response()->json($this->service->catalog($request->boolean('refresh')));
    }

    public function store(StorePluginRequest $request): JsonResponse
    {
        $plugin = $this->service->install($request->validated('remote'));
        return response()->json($this->serialize($plugin), 201);
    }

    public function show(Plugin $plugin): JsonResponse
    {
        return response()->json($this->serialize($plugin));
    }

    public function upgrade(Plugin $plugin): JsonResponse
    {
        return response()->json($this->serialize($this->service->upgrade($plugin)));
    }

    public function enable(EnablePluginRequest $request, Plugin $plugin): JsonResponse
    {
        return response()->json($this->serialize($this->service->setEnabled($plugin, $request->boolean('is_enabled'))));
    }

    public function storeConfig(StoreConfigPluginRequest $request, Plugin $plugin): JsonResponse
    {
        return response()->json($this->serialize($this->service->saveConfig($plugin, $request->validated('config', []))));
    }

    public function destroy(Plugin $plugin): JsonResponse
    {
        $this->service->uninstall($plugin);
        return response()->json(['message' => $plugin->package.' uninstalled']);
    }

    private function serialize(Plugin $plugin): array
    {
        $loaded = $plugin->getPlugin() !== null;
        $stars = $this->service->stars($plugin->remote);
        return array_merge($plugin->toArray(), [
            'is_loaded' => $loaded,
            'load_error' => $loaded ? null : $plugin->getLoadError(),
            'is_approved' => $this->service->isApproved($plugin->remote),
            'stars' => $stars,
            'is_popular' => $this->service->isPopular($stars),
        ]);
    }
}
