<?php

use Illuminate\Support\Facades\Route;

// Catch-all route to serve the Vue 3 Sneat Vuetify SPA
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '^(?!api|storage).*$')->name('spa');
