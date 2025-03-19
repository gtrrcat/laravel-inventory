<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SteamInventoryController extends Controller
{
    public function getInventory($steamid)
    {
        $apiKey = env('STEAM_API_KEY'); // Укажите API-ключ в .env
        $appId = 730; // ID игры (730 — CS2, 440 — TF2, 570 — Dota 2)
        $url = "https://steamcommunity.com/inventory/{$steamid}/{$appId}/2?l=english&count=5000";

        $response = Http::get($url);

        if ($response->failed()) {
            return response()->json(['error' => 'Не удалось получить данные'], 500);
        }

        $data = $response->json();

        if (!isset($data['assets']) || !isset($data['descriptions'])) {
            return response()->json(['error' => 'Инвентарь пуст или недоступен'], 404);
        }

        $items = [];
        foreach ($data['descriptions'] as $item) {
            $items[] = [
                'name' => $item['market_hash_name'] ?? 'Unknown',
                'image' => $item['icon_url'] ? 'https://steamcommunity-a.akamaihd.net/economy/image/' . $item['icon_url'] : null
            ];
        }

        return view('inventory', compact('items'));
    }
}
