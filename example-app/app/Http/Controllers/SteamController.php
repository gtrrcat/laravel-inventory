<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SteamController extends Controller
{
    public function getInventory($steamId)
    {
        $apiKey = env('STEAM_API_KEY');
        $gameId = '730'; // CS:GO
        $url = "https://steamcommunity.com/inventory/$steamId/$gameId/2?l=english&count=5000";

        $response = Http::get($url);

        if ($response->failed()) {
            return response()->json(['error' => 'Не удалось получить инвентарь'], 500);
        }

        $data = $response->json();

        if (!isset($data['descriptions'])) {
            return response()->json(['error' => 'Инвентарь пуст или ошибка API'], 404);
        }

        // Извлекаем только названия предметов
        $items = collect($data['descriptions'])->map(function ($item) {
            return $item['market_hash_name'] ?? 'Unknown Item';
        });

        return view('inventory', ['items' => $items]);
    }
}
