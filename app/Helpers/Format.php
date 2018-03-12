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
    if ($size === 0) {
      return '0 bytes';
    }
    $negative = false;
    if ($size < 0) {
      $negative = true;
      $size = -$size;
    }
    $base = log($size, 1024);
    $suffixes = [' bytes', ' KB', ' MB', ' GB', ' TB'];
    $result = round(pow(1024, $base - floor($base)), 2) . $suffixes[floor($base)];
    return $negative ? ('-' . $result) : $result;
  }

  /**
   * Displays a new file size along with the percentage change compared to the old
   * size.
   *
   * @param int $old
   * @param int $new
   * @return string
   */
  public static function newFileSizeWithPercentage(int $old, int $new): string {
    $percent = static::percentChange($old, $new);
    return static::fileSize($new) . ' (' . $percent . ')';
  }

  /**
   * Displays the difference between two values, as both the raw difference and a
   * percentage.
   *
   * @param int $old
   * @param int $new
   * @return string
   */
  public static function diffFileSizeWithPercentage(int $old, int $new): string {
    $percent = static::percentChange($old, $new);
    return static::fileSize($new - $old) . ' (' . $percent . ')';
  }

  /**
   * Formats the change between two values as a percentage.
   *
   * @param int $old
   * @param int $new
   * @return string
   */
  public static function percentChange(int $old, int $new): string {
    return round(abs($old - $new) / $old * 100.0) . '%';
  }
}
