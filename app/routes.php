<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{
	return View::make('home');
});

Route::get('/test', 'TestController@get');

Route::post('/start-test/{id}', 'TestController@start');
Route::post('/completed-test', 'TestController@complete');

// Plupload
Route::any('/upload', 'MainController@upload');


// === AJAX === //
Route::get('/queue-status', function() {
    return array(
        'queue' => TestElement::queue()->count(), 
        'completed' => TestElement::completed()->count(),
        'total' => TestElement::count());
});
Route::post('/delete-queue', function() {
    TestElement::queue()->delete();
});
Route::post('/create-test', 'TestController@create');