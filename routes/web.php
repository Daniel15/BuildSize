<?php
Route::get('/', function () {
  return view('welcome');
})->middleware('guest');

Route::get('/dashboard', 'DashboardController');

Route::get('/login/github', 'GithubAuthController@login')->name('login');
Route::get('/login/github/complete', 'GithubAuthController@completeLogin');