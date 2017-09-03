<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Build;
use App\Models\BuildArtifact;
use App\Models\Project;
use App\Models\ProjectArtifact;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Http\Request;

class CircleCIController extends Controller {
  public function __invoke(Request $request) {

    // Retrieve this build from CircleCI's API to verify that it's legit
    // TODO: Parallelize these calls
    $build = $this->call(
      'project/github/%s/%s/%s',
      $request->input('payload.username'),
      $request->input('payload.reponame'),
      $request->input('payload.build_num')
    );

    $artifacts = $this->call(
      'project/github/%s/%s/%s/artifacts',
      $request->input('payload.username'),
      $request->input('payload.reponame'),
      $request->input('payload.build_num')
    );
    $sizes = $this->getArtifactSizes($artifacts);

    $last_commit = $build->all_commit_details[count($build->all_commit_details) - 1];

    if (!empty($build->branch)) {
      // Build is on a branch, so save information for that branch
      $this->saveArtifactsForProjectBuild($build, $artifacts, $sizes, $last_commit, [
        'org_name' => $build->username,
        'repo_name' => $build->reponame,
        'identifier' => $build->branch . '/' . $last_commit->commit,
        'build_data' => [],
      ]);
    }

    if (count($build->pull_requests) > 0) {
      // Build is part of a PR, so save for all PRs too
      foreach ($build->pull_requests as $pull_request) {
        $pull_request_url = $this->parsePullRequestURL($pull_request->url);
        if ($pull_request_url !== null) {
          $this->saveArtifactsForProjectBuild($build, $artifacts, $sizes, $last_commit, [
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

  private function parsePullRequestURL(string $url): array {
    $path = explode('/', parse_url($url, PHP_URL_PATH));
    if ($path[3] !== 'pull') {
      return null;
    }

    return [
      'username' => $path[1],
      'reponame' => $path[2],
      'pr_number' => (int)$path[4],
    ];
  }

  private function saveArtifactsForProjectBuild(
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

  private function getArtifactSizes(array $artifacts): array {
    $dir = static::createTempDir('buildartifacts');

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
      static::recursiveRmDir($dir);
    }
  }

  public function call(string $uri, ...$uri_args) {
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
   * Creates a temporary directory with a unique name.
   */
  public static function createTempDir(string $prefix) {
    $tempdir = tempnam(sys_get_temp_dir(), $prefix);
    // tempnam() creates a temp *file*, but we want a temp *directory*.
    unlink($tempdir);
    mkdir($tempdir);
    return $tempdir.'/';
  }

  private static function recursiveRmDir($dir) {
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
