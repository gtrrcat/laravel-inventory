<?php
use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;

Route::get('/fetch-inventory', [ItemController::class, 'fetchInventory']);
Route::get('/items', [ItemController::class, 'showItems']);

Route::get('/', function () {
    return view('welcome');
});
