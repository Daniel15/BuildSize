<?php
Route::view('/', 'welcome')->middleware('guest');
Route::get('/dashboard', 'DashboardController');
Route::get('/setup', 'SetupController@index');
Route::get('/setup/repo/{org_name}/{repo_name}', 'SetupController@showRepoInstructions');
Route::match(['get', 'post'], '/{org_name}/{repo_name}/settings', 'RepoSettingsController');

Route::get('/login/github', 'GithubAuthController@login')->name('login');
Route::get('/login/github/complete', 'GithubAuthController@completeLogin');
Route::post('/logout', 'GithubAuthController@logout')->name('logout');

Route::get('/docs/{path}', 'DocsController')->where('path', '[A-Za-z0-9/_\-]+');
Route::get('/docs/', 'DocsController');
