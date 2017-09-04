<?php

namespace App\Services\Build;

use App\GithubUtils;
use App\Models\GithubInstall;
use GuzzleHttp\Promise;

/**
 * Handles builds from CircleCI. Supports both CircleCI 1.0 and 2.0.
 *
 * @package App\Services\Build
 */
class CircleCI extends AbstractBuildService {
  use GetArtifactSizesByDownloading;

  /**
   * Analyzes the build at the given CircleCI URL.
   */
  public function analyzeFromBuildURL(GithubInstall $install, string $url, $payload) {
    $parts = static::parseBuildURL($url);
    list($build, $artifacts) = $this->getBuildFromAPI(
      $parts['owner'],
      $parts['repo'],
      $parts['build']
    );

    // Ignore failed builds
    if ($build->status === 'failed') {
      return;
    }
    // Ignore builds with no artifacts
    if (count($artifacts) === 0) {
      return;
    }

    if (count($payload['branches']) > 0) {
      // Build is on a branch, so save information for that branch
      foreach ($payload['branches'] as $branch) {
        $this->analyzeBuild($install, $payload, $artifacts, [
          'base_branch' => null,
          'base_commit' => null,
          'branch' => $branch['name'],
          'org_name' => $payload['repository']['owner']['login'],
          'pull_request' => null,
          'repo_name' => $payload['repository']['name'],
        ]);
      }
    }

    // TODO: Work out if this is retrievable from GitHub rather than calling CircleCI's API for it
    // https://platform.github.community/t/determining-if-status-payload-is-for-a-pull-request/3176
    if (count($build->pull_requests) > 0) {
      // Build is part of a PR, so save for all PRs too
      foreach ($build->pull_requests as $raw_pull_request) {
        $pull_request_url = GithubUtils::parsePullRequestURL($raw_pull_request->url);
        if ($pull_request_url === null) {
          continue;
        }
        $this->analyzeFromPullRequest(
          $install,
          $pull_request_url['username'],
          $pull_request_url['reponame'],
          $pull_request_url['pr_number'],
          $payload,
          $artifacts
        );
      }
    }
  }

  private function getBuildFromAPI(string $username, string $reponame, int $build_num): array {
    $client = new \GuzzleHttp\Client([
      'base_uri' => sprintf(
        'https://circleci.com/api/v1.1/project/github/%s/%s/%s/',
        $username,
        $reponame,
        $build_num
      ),
    ]);
    $options = [
      'headers' => [
        'Accept' => 'application/json',
        'User-Agent' => 'BuildSize (https://buildsize.org/)',
      ],
    ];
    return Promise\unwrap([
      $client->getAsync('', $options)->then(function ($response) {
        return json_decode((string)$response->getBody());
      }),
      $client->getAsync('artifacts', $options)->then(function ($response) {
        // Convert the raw data into a map of filename => url
        $data = json_decode((string)$response->getBody());
        $artifacts = [];
        foreach ($data as $artifact) {
          $artifacts[basename($artifact->path)] = $artifact->url;
        }
        return $artifacts;
      })
    ]);
  }

  /**
   * Parses the owner, repo and build number from a CircleCI build URL.
   * @param string $url
   * @return array
   */
  public static function parseBuildURL(string $url) {
    $path = parse_url($url, PHP_URL_PATH);
    $parts = explode('/', $path);
    if (count($parts) !== 5 || $parts[1] !== 'gh') {
      throw new \InvalidArgumentException('Unexpected CircleCI URL format: ' . $path);
    }
    return [
      'owner' => $parts[2],
      'repo' => $parts[3],
      'build' => (int)$parts[4],
    ];
  }
}
