<?php

namespace App;

abstract class ArtifactUtils {
  const VERSION_PLACEHOLDER = '[version]';

  /**
   * Finds text that looks like a version number, and replaces it with a placeholder.
   *
   * @param string $name
   * @return string
   */
  public static function generalizeName(string $name): string {
    // Strip version numbers
    $name = preg_replace(
      '/([0-9]+(\.[0-9_-]+)+)/',
      static::VERSION_PLACEHOLDER,
      $name
    );
    return $name;
  }
}
