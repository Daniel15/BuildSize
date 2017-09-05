<?php

namespace App\Managers;

use App\GithubProvider;
use Laravel\Socialite\SocialiteManager as BaseSocialiteManager;

class SocialiteManager extends BaseSocialiteManager {
  protected function createGithubDriver() {
    $config = $this->app['config']['services.github'];
    $config['redirect'] = action('GithubAuthController@completeLogin');
    return $this->buildProvider(GithubProvider::class, $config);
  }
}
