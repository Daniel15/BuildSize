<?php

namespace App\Services\Build;

use App\FilesystemUtils;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;


/**
 * Determines the size of the specified artifact files by downloading them. This should ONLY be
 * used for providers that don't properly return data in HEAD requests.
 *
 * @package App\Services\Build
 */
trait GetArtifactSizesByDownloading {
  /**
   * Given an array of file name => URL, returns an array of file name => size in bytes.
   *
   * @param array $artifacts
   * @return array
   */
  public function getArtifactSizes(array $artifacts): array {
    $dir = FilesystemUtils::createTempDir('buildartifacts');

    // Download the artifacts in parallel
    $artifact_client = new Client();
    $requests = [];
    $file_handles = [];

    try {
      foreach ($artifacts as $path => $url) {
        $file_handle = fopen($dir . static::cleanFilePath($path), 'w');
        $requests[$path] = $artifact_client->getAsync($url, [
            'sink' => $file_handle,
          ]
        );
        $file_handles[$path] = $file_handle;
      }
      Promise\unwrap($requests);

      $sizes = [];
      foreach ($artifacts as $path => $url) {
        $sizes[$path] = fstat($file_handles[$path])['size'];
      }
      return $sizes;
    } finally {
      foreach ($file_handles as $file_handle) {
        try {
          fclose($file_handle);
        } catch (\Exception $e) {
          // Could be locked or something... Just ignore it.
        }
      }
      FilesystemUtils::recursiveRmDir($dir);
    }
  }

  private static function cleanFilePath(string $path): string {
    return str_replace(['/', '\\'], '__', $path);
  }
}
