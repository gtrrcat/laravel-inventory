<?php

$apiKey = '738A14F11003309D87BC67FC74CA7413';

// SteamID пользователя, инвентарь которого вы хотите получить
$steamID = '76561198180480114';
$appID = 570;
// URL для запроса к Steam API
$url = "http://api.steampowered.com/IEconItems_$appID/GetPlayerItems/v1/?key=$apiKey&SteamID=$steamID";
echo $url;
// Используем file_get_contents для получения данных
$response = file_get_contents($url);

if ($response === FALSE) {
    echo "Ошибка при получении данных.";
} else {
    $inventory = json_decode($response, true);

    // Отладочный вывод
    echo "<pre>";
    print_r($inventory);
    echo "</pre>";

    // Проверяем, есть ли данные о предметах
    if (isset($inventory['result']['items'])) {
        $items = $inventory['result']['items'];

        echo "Инвентарь пользователя:<br>";
        foreach ($items as $item) {
            $itemName = $item['name'];
            $itemID = $item['id'];
            $itemDescription = $item['description'] ?? 'Нет описания';

            echo "ID предмета: $itemID<br>";
            echo "Название: $itemName<br>";
            echo "Описание: $itemDescription<br>";
            echo "----------------------------------<br>";
        }
    } else {
        echo "Инвентарь пуст или произошла ошибка при получении данных.";
    }
}
echo $url;
?>
