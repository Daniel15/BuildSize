<?php

namespace App;

use Laravel\Socialite\Two\GithubProvider as BaseGithubProvider;

class GithubProvider extends BaseGithubProvider {
  // Remove "user:email" scope as it doesn't work with "GitHub Apps"
  protected $scopes = [];
}