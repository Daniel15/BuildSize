<?php

namespace App;

// TODO: Generalize this

use App\Events\BuildCompletedEvent;
use App\Models\Branch;
use App\Models\Build;
use App\Models\BuildArtifact;
use App\Models\GithubInstall;
use App\Models\Project;
use App\Models\ProjectArtifact;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Support\Facades\Log;

abstract class CircleCI {
  public static function analyzeBuildFromURL(string $url, $payload) {
    $parts = static::parseBuildURL($url);
    static::analyzeBuild($parts['owner'], $parts['repo'], $parts['build'], $payload);
  }

  public static function analyzeBuild(string $username, string $reponame, int $build_num, $payload) {
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

    // Ignore failed builds
    if ($build->status === 'failed') {
      return;
    }

    // Ignore builds with no artifacts
    if (count($artifacts) === 0) {
      return;
    }

    $sizes = static::getArtifactSizes($artifacts);

    // See if we have a GitHub app configured for this repo
    $install_id = $payload['installation']['id'];
    $install = GithubInstall::where('install_id', $install_id)
      ->first();

    if ($install === null) {
      // Somehow we got a push for an installation that doesn't actually exist. wat.
      Log::warning('Received webhook for invalid installation %s!', $install_id);
      return;
    }

    $github = GithubUtils::createClientForInstall($install);

    // TODO: Clean up all this handling, make it more generic and reusable
    // TODO: Unit test all of this stuff!
    if (count($payload['branches']) > 0) {
      // Build is on a branch, so save information for that branch
      foreach ($payload['branches'] as $branch) {
        static::saveArtifactsForProjectBuild($artifacts, $sizes, $payload, $install, [
          'org_name' => $payload['repository']['owner']['login'],
          'repo_name' => $payload['repository']['name'],
          'identifier' => $branch['name'] . '/' . $payload['commit']['sha'],
          'build_data' => [
            'branch' => $branch['name'],
          ],
        ]);
      }
    }

    // TODO: Work out if this is retrievable from GitHub rather than calling CircleCI's API for it
    if (count($build->pull_requests) > 0) {
      // Build is part of a PR, so save for all PRs too
      foreach ($build->pull_requests as $pull_request) {
        $pull_request_url = GithubUtils::parsePullRequestURL($pull_request->url);
        if ($pull_request_url !== null) {
          $pull_request_data = $github->pullRequest()->show(
            $pull_request_url['username'],
            $pull_request_url['reponame'],
            $pull_request_url['pr_number']
          );
          static::saveArtifactsForProjectBuild($artifacts, $sizes, $payload, $install, [
            'org_name' => $pull_request_url['username'],
            'repo_name' => $pull_request_url['reponame'],
            'pull_request' => $pull_request_url['pr_number'],
            // TODO: saveArtifactsForProjectBuild should just infer this rather than having to explicitly pass it
            'identifier' => 'pr/' . $pull_request_url['pr_number'],
            'build_data' => [
              'base_branch' => $pull_request_data['base']['ref'],
              'base_commit' => $pull_request_data['base']['sha'],
              'branch' => $pull_request_data['head']['ref'],
              'pull_request' => $pull_request_url['pr_number'],
            ],
          ]);
        }
      }
    }
  }

  private static function saveArtifactsForProjectBuild(
    $artifacts,
    $sizes,
    $payload,
    GithubInstall $install,
    $metadata
  ) {
    // TODO: This should handle default_branch too
    $project = Project::firstOrNew(
      [
        'host' => 'github',
        'org_name' => $metadata['org_name'],
        'repo_name' => $metadata['repo_name'],
      ]
    );
    if (!$project->exists) {
      $project->active = false;
      $project->save();
    }

    $build = Build::updateOrCreate(
      [
        'project_id' => $project->id,
        'identifier' => $metadata['identifier'],
      ],
      array_merge([
        'commit' => $payload['commit']['sha'],
        'committer' => $payload['commit']['author']['login'],
      ], $metadata['build_data'])
    );

    $project_artifact_ids = [];
    $new_project_artifacts = [];
    $artifact_names = [];

    foreach ($project->artifacts as $artifact) {
      $project_artifact_ids[$artifact->name] = $artifact->id;
    }

    foreach ($artifacts as $artifact) {
      $filename = ArtifactUtils::generalizeName(basename($artifact->path));
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

    $build_artifacts = [];
    foreach ($artifacts as $artifact) {
      $filename = basename($artifact->path);

      $build_artifacts[] = BuildArtifact::updateOrCreate(
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

    $base_build = null;
    if (!empty($metadata['build_data']['base_commit'])) {
      // See if we have the base commit to compare against
      $base_build = Build::where('project_id', $project->id)
        ->where('commit', $metadata['build_data']['base_commit'])
        ->first();

      if ($base_build === null && !empty($metadata['build_data']['base_branch'])) {
        // Don't have this exact build, so check if we have the most recent build on the same
        // branch.
        $latest_branch = Branch::where('org_name', $metadata['org_name'])
          ->where('repo_name', $metadata['repo_name'])
          ->where('branch', $metadata['build_data']['base_branch'])
          ->first();
        if ($latest_branch !== null) {
          $base_build = Build::where('project_id', $project->id)
            ->where('commit', $latest_branch->latest_commit)
            ->with('buildArtifacts')
            ->first();

          // TODO: Should this just use the most recently stored build on the branch if this fails?
        }
      }
    }

    $base_build_artifacts = $base_build
      ? $base_build->buildArtifacts->keyBy('project_artifact_id')
      : null;

    event(new BuildCompletedEvent([
      'install' => $install,

      'project' => $project,
      'build' => $build,
      'build_artifacts' => collect($build_artifacts)->keyBy('project_artifact_id'),
      'total_size' => array_sum($sizes),

      'has_base_build' => (bool)$base_build && (bool)$base_build_artifacts,
      'base_build' => $base_build,
      'base_build_artifacts' => $base_build_artifacts,
      'base_total_size' => $base_build_artifacts
        ? $base_build_artifacts
            ->map(function ($x) { return $x->size; })
            ->sum()
        : null,
    ]));
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
      } catch (\Exception $e) {
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
