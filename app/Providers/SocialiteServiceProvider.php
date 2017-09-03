<?php

namespace App\Providers;

use App\Managers\SocialiteManager;
use Laravel\Socialite\Contracts\Factory;
use Laravel\Socialite\SocialiteServiceProvider as BaseSocialiteServiceProvider;

// Based on https://binary-studio.com/2015/05/25/laravel-oauth2/
class SocialiteServiceProvider extends BaseSocialiteServiceProvider {
  public function register() {
    $this->app->singleton(Factory::class, function ($app) {
      return new SocialiteManager($app);
    });
  }
}