<?php

use Illuminate\Support\Facades\Route;
use Pqd\AiContentBuilder\Http\Controllers\AiContentController;

Route::post('ai-content/generate', [AiContentController::class, 'generate'])
    ->name('ai-content-builder.generate');
