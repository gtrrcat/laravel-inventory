<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class InventoryController extends Controller
{
    public function show($id, Request $request)
    {
        $allItems = [];
        $lastItemId = null;

        do {
            // Формируем URL запроса
            $apiUrl = "https://steamcommunity.com/inventory/{$id}/730/2/" . ($lastItemId ? "&last_item_id={$lastItemId}" : "");

            // Делаем API-запрос
            $response = Http::get($apiUrl);

            // Проверяем, успешен ли запрос
            if ($response->failed()) {
                return abort(500, 'Ошибка при получении данных');
            }

            $data = $response->json();
            // Достаем предметы из ответа
            $items = $data['assets'] ?? [];

            // Добавляем их в общий массив
            $allItems = array_merge($allItems, $items);

            // Проверяем, есть ли ещё предметы
            $lastItemId = end($items)['assetid'] ?? null;

            // Добавляем описания
            foreach ($data['descriptions'] ?? [] as $desc) {
                $descriptions[$desc['classid']] = $desc;
            }

        } while (count($items) == 500); // Если пришло ровно 500, значит, есть ещё

        // Объединяем предметы с их описаниями
        foreach ($allItems as &$item) {
            $item['description'] = $descriptions[$item['classid']] ?? null;
        }

        // Считаем общую стоимость всех предметов
        $totalPrice = collect($allItems)->sum('price');

        // Отправляем данные в шаблон
        return view('inventory', compact('allItems', 'totalPrice', 'id'));
    }
}

