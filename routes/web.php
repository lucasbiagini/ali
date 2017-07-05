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

Route::get('', function () {
    return view('ali.index');
});

Route::get('/instagram', function () {
	return view('ali.index');
});

Route::get('/spotify', function(){
	return view('ali.index');
});

Route::post('/player', 'SpotifyController@player');

Route::post('/seek', 'SpotifyController@seek');

Route::post('/getMedia', 'InstagramController@getMedia');

Route::post('/recommendations', 'SpotifyController@recommendations');

Route::post('/start', 'SpotifyController@playTracks');