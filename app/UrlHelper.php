<?php

namespace App;

/**
 * Utility methods for building various system URLs.
 */
abstract class UrlHelper {
  public static function getGithubAppURL() {
    return 'https://github.com/apps/' . env('GITHUB_APP_ALIAS');
  }

  public static function getGithubAppInstallURL() {
    return static::getGithubAppURL() . '/installations/new';
  }
}
