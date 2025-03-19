<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Инвентарь Steam</title>
</head>
<body>
<h1>Инвентарь пользователя Steam</h1>
<ul>
    @foreach ($items as $item)
        <li>{{ $item }}</li>
    @endforeach
</ul>
</body>
</html>
