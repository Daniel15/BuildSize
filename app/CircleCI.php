<?php

namespace App;

// TODO: Test CircleCI 2.0

use App\Models\Build;
use App\Models\BuildArtifact;
use App\Models\Project;
use App\Models\ProjectArtifact;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

abstract class CircleCI {
  public static function analyzeBuildFromURL(string $url) {
    $parts = static::parseBuildURL($url);
    static::analyzeBuild($parts['owner'], $parts['repo'], $parts['build']);
  }

  public static function analyzeBuild(string $username, string $reponame, int $build_num) {
    // Retrieve this build from CircleCI's API to verify that it's legit
    // TODO: Parallelize these calls
    $build = static::call(
      'project/github/%s/%s/%s',
      $username,
      $reponame,
      $build_num
    );

    $artifacts = static::call(
      'project/github/%s/%s/%s/artifacts',
      $username,
      $reponame,
      $build_num
    );
    $sizes = static::getArtifactSizes($artifacts);

    $last_commit = $build->all_commit_details[count($build->all_commit_details) - 1];

    if (!empty($build->branch)) {
      // Build is on a branch, so save information for that branch
      static::saveArtifactsForProjectBuild($build, $artifacts, $sizes, $last_commit, [
        'org_name' => $build->username,
        'repo_name' => $build->reponame,
        'identifier' => $build->branch . '/' . $last_commit->commit,
        'build_data' => [],
      ]);
    }

    if (count($build->pull_requests) > 0) {
      // Build is part of a PR, so save for all PRs too
      foreach ($build->pull_requests as $pull_request) {
        $pull_request_url = GithubUtils::parsePullRequestURL($pull_request->url);
        if ($pull_request_url !== null) {
          static::saveArtifactsForProjectBuild($build, $artifacts, $sizes, $last_commit, [
            'org_name' => $pull_request_url['username'],
            'repo_name' => $pull_request_url['reponame'],
            'identifier' => 'pr/' . $pull_request_url['pr_number'],
            'build_data' => [
              'pull_request' => $pull_request_url['pr_number'],
            ],
          ]);
        }
      }
    }
  }

  private static function saveArtifactsForProjectBuild(
    $build,
    $artifacts,
    $sizes,
    $commit,
    $metadata
  ) {
    $project = Project::firstOrCreate(
      [
        'org_name' => $metadata['org_name'],
        'repo_name' => $metadata['repo_name'],
      ],
      [
        'host' => 'github',
        'url' => $build->vcs_url,
      ]
    );

    $build = Build::updateOrCreate(
      [
        'project_id' => $project->id,
        'identifier' => $metadata['identifier'],
      ],
      array_merge([
        'commit' => $commit->commit,
        'committer' => $commit->author_login,
      ], $metadata['build_data'])
    );

    $project_artifact_ids = [];
    $new_project_artifacts = [];
    $artifact_names = [];

    foreach ($project->artifacts as $artifact) {
      $project_artifact_ids[$artifact->name] = $artifact->id;
    }

    foreach ($artifacts as $artifact) {
      // TODO: generalize name
      $filename = basename($artifact->path);
      $artifact_names[$artifact->path] = $filename;
      if (!array_key_exists($filename, $project_artifact_ids)) {
        // This is the first time we've seen this artifact!
        $new_project_artifacts[] = new ProjectArtifact([
            'name' => $filename,
          ]
        );
      }
    }

    $project->artifacts()->saveMany($new_project_artifacts);

    // Add IDs for newly-added project artifacts
    foreach ($new_project_artifacts as $artifact) {
      $project_artifact_ids[$artifact->name] = $artifact->id;
    }

    foreach ($artifacts as $artifact) {
      $filename = basename($artifact->path);

      // TODO: generalize name
      BuildArtifact::updateOrCreate(
        [
          'build_id' => $build->id,
          'project_artifact_id' =>
            $project_artifact_ids[$artifact_names[$artifact->path]],
        ],
        [
          'filename' => $filename,
          'size' => $sizes[$filename],
        ]
      );
    }
  }

  private static function getArtifactSizes(array $artifacts): array {
    $dir = FilesystemUtils::createTempDir('buildartifacts');

    // Download the artifacts in parallel
    $artifact_client = new Client();
    $requests = [];
    $file_handles = [];

    try {
      foreach ($artifacts as $artifact) {
        $filename = basename($artifact->path);
        $file_handle = fopen($dir . $filename, 'w');
        $requests[$filename] = $artifact_client->getAsync($artifact->url, [
            'sink' => $file_handle,
          ]
        );
        $file_handles[$filename] = $file_handle;
      }
      Promise\unwrap($requests);

      $sizes = [];
      foreach ($artifacts as $artifact) {
        $filename = basename($artifact->path);
        $sizes[$filename] = fstat($file_handles[$filename])['size'];
      }
      return $sizes;
    } finally {
      try {
        foreach ($file_handles as $file_handle) {
          fclose($file_handle);
        }
      } catch (Exception $e) {
        // Could be locked or something... Just ignore it.
      }
      FilesystemUtils::recursiveRmDir($dir);
    }
  }

  public static function call(string $uri, ...$uri_args) {
    // TODO: Use API key?
    $client = new Client([
      'base_uri' => 'https://circleci.com/api/v1.1/',
    ]);
    $response = $client->get(vsprintf($uri, $uri_args), [
      'headers' => [
        'Accept' => 'application/json',
      ],
    ]);
    return json_decode((string)$response->getBody());
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