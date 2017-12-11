<?php

namespace App;

abstract class ArtifactUtils {
  const HASH_PLACEHOLDER = '[hash]';
  const VERSION_PLACEHOLDER = '[version]';

  /**
   * Finds text that looks like a version number, and replaces it with a placeholder.
   *
   * @param string $name
   * @return string
   */
  public static function generalizeName(string $name): string {
    // Strip hashes
    // Match hex hashes betwen 20 (webpack truncated default) and 64 (sha256) characters.
    $name = preg_replace(
      '/([a-f0-9]{20,64})/i',
      static::HASH_PLACEHOLDER,
      $name
    );

    // Strip version numbers
    $name = preg_replace(
      '/([0-9]+(\.[0-9_-]+)+)/',
      static::VERSION_PLACEHOLDER,
      $name
    );
    return $name;
  }
}
