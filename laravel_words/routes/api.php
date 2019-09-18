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
Route::middleware('auth:api')->get('users', 'UsersController@show');

Route::get('words', 'WordsQuizController@generate');
Route::middleware('auth:api')->get('words/quiz', 'WordsQuizController@quiz');
Route::post('words', 'WordsQuizController@score');
Route::middleware('auth:api')->post('words/quiz', 'WordsQuizController@check');
Route::middleware('auth:api')->get('words/{lemma}', 'WordsQuizController@show');
Route::middleware('auth:api')->get('projects', 'ProjectsController@index');
Route::middleware('auth:api')->get('projects/name/{id}', 'ProjectsController@getName');
Route::middleware('auth:api')->get('words/edit/{id}', 'ProjectsController@show');
Route::middleware('auth:api')->post('projects', 'ProjectsController@store');
Route::middleware('auth:api')->patch('projects', 'ProjectsController@update');
Route::middleware('auth:api')->delete('projects', 'ProjectsController@destroy');
// Route::middleware('auth:api')->get('words', 'WordsQuizController@generate');
Route::middleware('auth:api')->post('tasks/create', 'TasksController@create');
Route::middleware('auth:api')->delete('tasks/create', 'TasksController@revert');
// Route::post('tasks/create', 'TasksController@create');
Route::middleware('auth:api')->post('tasks', 'TasksController@store');
Route::middleware('auth:api')->get('tasks/{lemma}', 'TasksController@show');
Route::middleware('auth:api')->patch('tasks', 'TasksController@update');
Route::middleware('auth:api')->delete('tasks', 'TasksController@destroy');
