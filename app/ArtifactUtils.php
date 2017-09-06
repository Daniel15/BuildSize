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

    // Find last continuous alpha sequence beginning with a dot, and assume it's the extension
    $has_extension = preg_match('/((\.[a-z][a-z0-9]+)+)$/i', $name, $matches);
    if ($has_extension !== 1) {
      $extension = '';
      $basename = $name;
    } else {
      $extension = $matches[0];
      $basename = substr($name, 0, -strlen($extension));
    }

    // Strip version numbers just from the basename
    $basename = preg_replace(
      '/([0-9][0-9\.\-_]+)/',
      static::VERSION_PLACEHOLDER,
      $basename
    );
    return $basename . $extension;
  }
}
