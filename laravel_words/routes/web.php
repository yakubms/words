<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('app');
});

Auth::routes();

Route::get('/.well-known/acme-challenge/{filename}', function ($filename) {
    return file_get_contents(
        public_path('.well-known/acme-challenge/' . $filename)
    );
});

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
