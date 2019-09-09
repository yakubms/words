<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('words', 'WordsQuizController@generate');
Route::post('words', 'WordsQuizController@score');
Route::middleware('auth:api')->get('words/{lemma}', 'WordsQuizController@show');
Route::middleware('auth:api')->get('projects', 'ProjectsController@index');
Route::middleware('auth:api')->post('projects', 'ProjectsController@store');
// Route::middleware('auth:api')->get('words', 'WordsQuizController@generate');
Route::middleware('auth:api')->post('tasks', 'TasksController@store');
