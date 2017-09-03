<?php

namespace App\Managers;

use App\GithubProvider;
use Laravel\Socialite\SocialiteManager as BaseSocialiteManager;

class SocialiteManager extends BaseSocialiteManager {
  protected function createGithubDriver() {
    $config = $this->app['config']['services.github'];

    return $this->buildProvider(GithubProvider::class, $config);
  }
}