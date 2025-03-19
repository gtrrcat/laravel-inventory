<?php
// Имя пользователя Steam
$query = "gtrr12";
$apiKey = '738A14F11003309D87BC67FC74CA7413';
$steamID = '76561198180480114';

// Функция для получения инвентаря с учетом пагинации
function getInventory($steamID, $appID = 730, $contextID = 2, $startAssetID = null) {
    $api_url = "https://steamcommunity.com/inventory/$steamID/$appID/$contextID";
    if ($startAssetID !== null) {
        $api_url .= "?start_assetid=$startAssetID";
    }

    // Настройка контекста для таймаута
    $context = stream_context_create([
        'http' => [
            'timeout' => 10 // Таймаут 10 секунд
        ]
    ]);

    $response = @file_get_contents($api_url, false, $context);
    if ($response === false) {
        echo "Ошибка: Не удалось получить данные с сервера.\n";
        return null;
    }

    $json = json_decode($response, true);
    if ($json === null) {
        echo "Ошибка: Неверный формат JSON.\n";
        return null;
    }

    return $json;
}

// Функция для получения цены предмета из Steam Market с кэшированием
function getMarketPrice($marketHashName) {
    $cacheFile = 'market_prices_cache.json';
    if (file_exists($cacheFile)) {
        $cache = json_decode(file_get_contents($cacheFile), true);
    } else {
        $cache = [];
    }

    if (isset($cache[$marketHashName])) {
        echo "Используем кэшированную цену для: $marketHashName\n";
        return $cache[$marketHashName];
    }

    $url = "https://steamcommunity.com/market/priceoverview/?currency=5&country=ru&appid=730&market_hash_name=" . urlencode($marketHashName) . "&format=json";
    echo "Запрос цены по ссылке: $url\n";

    $attempts = 3; // Количество попыток
    $delay = 30; // Задержка между попытками в секундах

    for ($i = 0; $i < $attempts; $i++) {
        // Настройка контекста для таймаута
        $context = stream_context_create([
            'http' => [
                'timeout' => 10 // Таймаут 10 секунд
            ]
        ]);

        $response = @file_get_contents($url, false, $context); // Используем @ для подавления ошибок
        if ($response === false) {
            echo "Ошибка: Не удалось получить цену для $marketHashName. Попытка " . ($i + 1) . " из $attempts.\n";
            sleep($delay); // Ждем перед повторной попыткой
            continue;
        }

        $json = json_decode($response, true);
        if ($json === null || !isset($json['lowest_price'])) {
            echo "Ошибка: Неверный формат JSON для $marketHashName.\n";
            return null;
        }

        // Сохраняем цену в кэш
        $cache[$marketHashName] = $json['lowest_price'];
        file_put_contents($cacheFile, json_encode($cache));

        return $json['lowest_price'];
    }

    echo "Ошибка: Не удалось получить цену для $marketHashName после $attempts попыток.\n";
    return null; // Если все попытки неудачны
}

// Создаем массивы для хранения данных
$assetsWithMarketNamesAndPrices = [];
$lastAssetID = null;

do {
    echo "Получение инвентаря...\n";
    $json = getInventory($steamID, 730, 2, $lastAssetID);

    if ($json === null) {
        break; // Прерываем цикл, если данные не получены
    }

    // Создаем временный массив для хранения описаний (descriptions)
    $descriptions = [];
    if (isset($json['descriptions']) && is_array($json['descriptions'])) {
        foreach ($json['descriptions'] as $item) {
            $key = $item['classid'] . '_' . $item['instanceid'];
            $descriptions[$key] = $item['market_hash_name'];
        }
    }

    // Собираем assets с market_hash_name и ценой
    if (isset($json['assets']) && is_array($json['assets'])) {
        echo "Обработка " . count($json['assets']) . " предметов...\n";
        foreach ($json['assets'] as $asset) {
            $key = $asset['classid'] . '_' . $asset['instanceid'];
            if (isset($descriptions[$key])) {
                $marketHashName = $descriptions[$key];
                echo "Получение цены для: $marketHashName\n";
                $marketPrice = getMarketPrice($marketHashName);
                echo "Цена для $marketHashName: " . ($marketPrice ?? 'Цена недоступна') . "\n";
                $assetsWithMarketNamesAndPrices[] = [
                    'market_hash_name' => $marketHashName,
                    'market_price' => $marketPrice
                ];

                // Увеличиваем задержку между запросами
                sleep(30); // 30 секунд задержки между запросами
            }
        }
    }

    // Обновляем last_assetid для пагинации
    $lastAssetID = $json['last_assetid'] ?? null;
} while ($lastAssetID !== null); // Продолжаем, пока есть данные для пагинации

// Записываем данные в файл
$file = fopen('market_prices.txt', 'w');
if ($file) {
    foreach ($assetsWithMarketNamesAndPrices as $item) {
        $line = $item['market_hash_name'] . " - " . ($item['market_price'] ?? 'Цена недоступна') . "\n";
        fwrite($file, $line);
    }
    fclose($file);
    echo "Данные успешно записаны в файл market_prices.txt\n";
} else {
    echo "Ошибка: Не удалось открыть файл для записи.\n";
}

// Выводим результат
echo "Список предметов с ценами:\n";
print_r($assetsWithMarketNamesAndPrices); // Выводим список предметов с ценами
echo "Количество предметов: " . count($assetsWithMarketNamesAndPrices) . "\n"; // Выводим количество предметов
?>