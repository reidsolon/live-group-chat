<?php

use Illuminate\Support\Facades\Route;

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

    if(session()->get('participant')) {
        echo 'chat room';
    } else {
        return view('welcome');
    }
    
});

Route::get('/session/getCurrentParticipant', 'ParticipantController@getCurrentUser');

Route::put('/part/isExist', 'ParticipantController@isExist');
Route::post('part/insert', 'ParticipantController@insertParticipant');
Route::post('part/setSession','ParticipantController@setCurrentUser');

Route::put('/room/isExist', 'ChatRoomController@isExist');
Route::post('/room/create', 'ChatRoomController@createRoom');
Route::get('/room/userRooms', 'ChatRoomController@getUserRooms');
Route::get('/room/allRooms', 'ChatRoomController@getAllRooms');
Route::post('/room/isJoined', 'ChatRoomController@isJoined');
Route::post('/room/join', 'ChatRoomController@joinRoom');

Auth::routes();
Route::get('/chatroom', 'HomeController@index')->name('home');

Route::get('/user/getCurrentUser', 'UserController@getCurrentUser');
Route::post('/user/sendMessage', 'ChatRoomController@sendMessage');
Route::post('/room/getMessages', 'ChatRoomController@getRoomMessages');