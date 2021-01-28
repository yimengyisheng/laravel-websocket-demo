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

\Illuminate\Support\Facades\Route::get('/test','TestController@test');//websocket服务
\Illuminate\Support\Facades\Route::get('/push','TestController@push');//业务接口
\Illuminate\Support\Facades\Route::get('/dis','TestController@disRole');//业务接口

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
