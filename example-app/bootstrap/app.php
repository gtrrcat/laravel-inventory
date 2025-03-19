<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

$app = require_once __DIR__.'/../vendor/autoload.php';

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

$app->make(Kernel::class)->handle(Request::capture());
