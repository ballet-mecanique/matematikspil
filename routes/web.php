<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Gangetabel');
})->name('home');

//require __DIR__.'/settings.php';
