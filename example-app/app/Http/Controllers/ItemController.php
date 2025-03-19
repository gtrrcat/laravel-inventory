<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;

class ItemController extends Controller
{
    public function fetchInventory()
    {
        $steamID = '76561198180480114'; // Ваш Steam ID
        $api_url = "https://steamcommunity.com/inventory/$steamID/730/2/";

        $response = @file_get_contents($api_url);
        if ($response === false) {
            return "Ошибка: Не удалось получить данные с сервера.";
        }

        $json = json_decode($response, true);
        if ($json === null) {
            return "Ошибка: Неверный формат JSON.";
        }

        // Очищаем таблицу перед добавлением новых данных
        Item::truncate();

        // Сохраняем предметы в базу данных
        if (isset($json['descriptions']) && is_array($json['descriptions'])) {
            foreach ($json['descriptions'] as $item) {
                if (isset($item['market_hash_name'])) {
                    Item::create([
                        'market_hash_name' => $item['market_hash_name']
                    ]);
                }
            }
        }

        return "Данные успешно загружены.";
    }

    public function showItems()
    {
        $items = Item::all();
        return view('items', compact('items'));
    }
}
