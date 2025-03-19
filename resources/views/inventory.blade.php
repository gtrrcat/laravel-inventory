<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <div class="container">
        <h1>Инвентарь пользователя {{ $id }}</h1>
        <h3>Общая стоимость: ${{ number_format($totalPrice, 2) }}</h3>

        <ul id="items-list">
            @foreach($allItems as $item)
                <li>{{ $item['description']['market_name'] }} </li>
            @endforeach
        </ul>
    </div>
</html>
