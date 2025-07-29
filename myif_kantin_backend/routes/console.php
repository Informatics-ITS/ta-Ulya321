<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;
use App\Console\Commands\MakeCustomController;
use App\Console\Commands\MakeCustomModel;
use App\Console\Commands\MakeCustomIndexView;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('my:custom-controller {name} {model}', function ($name, $model) {
    $this->call('make:custom-controller', [
        'name'  => $name,
        'model' => $model,
    ]);
})->describe('Membungkus make:custom-controller');

Artisan::command('my:custom-model {name}', function ($name) {
    $this->call('make:custom-model', [
        'name' => $name,
    ]);
})->describe('Membungkus make:custom-model');

Artisan::command('my:custom-index-view {model}', function ($model) {
    $this->call('make:custom-index-view', [
        'model' => $model,
    ]);
})->describe('Membungkus make:custom-index-view');
