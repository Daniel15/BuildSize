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
      foreach ($artifacts as $filename => $url) {
        $file_handle = fopen($dir . $filename, 'w');
        $requests[$filename] = $artifact_client->getAsync($url, [
            'sink' => $file_handle,
          ]
        );
        $file_handles[$filename] = $file_handle;
      }
      Promise\unwrap($requests);

      $sizes = [];
      foreach ($artifacts as $filename => $url) {
        $sizes[$filename] = fstat($file_handles[$filename])['size'];
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
}
