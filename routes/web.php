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

Route::get('/sites/', 'SiteController@index');
Route::get('/test/', 'VisitorController@test');
Route::get('/bannerScriptTest/', 'VisitorController@bannerScriptTest');

Route::post('/testreceiver/', 'VisitorController@receive');
Route::post('/testvisittime/', 'VisitorController@receiveTime');

Route::get('/visitors/','VisitorController@allVisitorStats');
Route::get('/','VisitorController@allVisitorStats');
Route::get('/visitors/{id}','VisitorController@visitorInfo');
Route::get('/keywords/{name}','KeywordController@keywordInfo');
Route::get('/visitors/{id}/m/{monthAgo}','VisitorController@visitorInfoByMonthAgo');
Route::get('/links/{id}','SiteLinkController@linkInfo');
