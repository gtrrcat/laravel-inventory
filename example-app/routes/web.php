<?php
use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SteamController;
Route::get('/fetch-inventory', [ItemController::class, 'fetchInventory']);
Route::get('/items', [ItemController::class, 'showItems']);


Route::get('/inventory/{steamId}', [SteamController::class, 'getInventory']);


Route::get('/', function () {
    return view('welcome');
});
