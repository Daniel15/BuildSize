<?php

namespace App;

abstract class FilesystemUtils {
  /**
   * Creates a temporary directory with a unique name.
   */
  public static function createTempDir(string $prefix) {
    $tempdir = tempnam(sys_get_temp_dir(), $prefix);
    // tempnam() creates a temp *file*, but we want a temp *directory*.
    unlink($tempdir);
    mkdir($tempdir);
    return $tempdir.'/';
  }

  /**
   * Recursively deletes the specified directory.
   * @param string $dir
   */
  public static function recursiveRmDir(string $dir) {
    $iterator = new \RecursiveIteratorIterator(
      new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
      \RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $filename => $fileInfo) {
      if ($fileInfo->isDir()) {
        rmdir($filename);
      } else {
        unlink($filename);
      }
    }
  }
}