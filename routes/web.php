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
Route::get('/similar/{id}', 'SiteLinkController@similar');
Route::get('/test/', 'VisitorController@test');
Route::post('/testreceiver/', ['as' => 'visitorReceiverLink', 'uses' => 'VisitorController@receive']);
Route::post('/testvisittime/', ['as' => 'visitorTestReceiverTime', 'uses' => 'VisitorController@receiveTime']);

Route::get('visitors/','VisitorController@allVisitorStats');
Route::get('/','VisitorController@allVisitorStats');
Route::get('visitors/{id}','VisitorController@allVisits');