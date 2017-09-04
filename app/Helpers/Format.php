<?php

namespace App\Helpers;

/**
 * Utilities for formatting strings.
 * @package App\Helpers
 */
abstract class Format {
  /**
   * Displays the file size in a human-readable format.
   *
   * @param int $size in bytes
   * @return string
   */
  public static function fileSize(int $size): string {
    $base = log($size, 1024);
    $suffixes = ['', ' KB', ' MB', ' GB', ' TB'];
    return round(pow(1024, $base - floor($base)), 2) . $suffixes[floor($base)];
  }
}
