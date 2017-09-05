<?php

namespace App\Services\Build;

use App\ArtifactUtils;
use App\Events\BuildCompletedEvent;
use App\GithubUtils;
use App\Models\Branch;
use App\Models\Build;
use App\Models\BuildArtifact;
use App\Models\GithubInstall;
use App\Models\Project;
use App\Models\ProjectArtifact;

/**
 * Represents a continuous integration build system, such as CircleCI, Travis, or AppVeyor.
 * Contains functionality to retrieve builds from these systems, and analyze them.
 *
 * @package App\Services\Build
 */
abstract class AbstractBuildService {
  private $artifact_size_cache = array();

  /**
   * Analyzes the build provided in the GitHub webhook payload.
   */
  public function analyzeFromGitHubPayload(GithubInstall $install, $payload) {
    $this->analyzeFromBuildURL(
      $install,
      $payload['target_url'],
      $payload
    );
  }

  /**
   * Analyzes the build at the given third-party service URL. For example, if analyzing a CIrcleCI
   * build, $url will be the CircleCI URL.
   */
  abstract function analyzeFromBuildURL(GithubInstall $install, string $url, $payload);

  /**
   * Analyzes the specified pull request.
   */
  public function analyzeFromPullRequest(
    GithubInstall $install,
    string $org_name,
    string $repo_name,
    int $number,
    $payload,
    array $artifacts
  ) {
    $github = GithubUtils::createClientForInstall($install);
    $pull_request = $github->pullRequest()->show($org_name, $repo_name, $number);
    $this->analyzeBuild($install, $payload, $artifacts, [
      'base_branch' => $pull_request['base']['ref'],
      'base_commit' => $pull_request['base']['sha'],
      'branch' => $pull_request['head']['ref'],
      'org_name' => $org_name,
      'pull_request' => $pull_request,
      'repo_name' => $repo_name,
    ]);
  }

  /**
   * Analyzes the specified build.
   *
   * @todo Document the shape of $build_info
   */
  public function analyzeBuild(GithubInstall $install, $payload, array $artifacts, $build_info) {
    //$sizes = $this->getAndCacheArtifactSizes($artifacts);

    // Load the project
    // TODO: This should handle default_branch too
    $project = Project::with('artifacts')->firstOrNew(
      [
        'host' => 'github',
        'org_name' => $build_info['org_name'],
        'repo_name' => $build_info['repo_name'],
      ]
    );
    if (!$project->exists) {
      $project->active = false;
      $project->save();
    }

    // Load (or update) the build. This can be an update if the build job was re-ran, or if a new
    // commit was pushed to the branch.
    $build = Build::updateOrCreate(
      [
        'project_id' => $project->id,
        'identifier' => $this->computeIdentifier($payload, $build_info),
      ],
      [
        'base_branch' => $build_info['base_branch'],
        'base_commit' => $build_info['base_commit'],
        'branch' => $build_info['branch'],
        'commit' => $payload['commit']['sha'],
        'committer' => $payload['commit']['author']['login'],
        'pull_request' => empty($build_info['pull_request'])
          ? null
          : $build_info['pull_request']['number'],
      ]
    );

    // Find artifacts that have previously existed for this project
    // A build artifact is a unique artifact for the build, whereas a project artifact is a general
    // file name used across multiple builds for a single project.
    $project_artifact_ids = [];
    foreach ($project->artifacts as $artifact) {
      $project_artifact_ids[$artifact->name] = $artifact->id;
    }

    // Determine if any artifacts for this build are artifacts that we've never seen before
    $artifact_names = [];
    $new_project_artifacts = [];
    foreach ($artifacts as $filename => $_) {
      $generalized_name = ArtifactUtils::generalizeName($filename);
      $artifact_names[$filename] = $generalized_name;
      if (!array_key_exists($generalized_name, $project_artifact_ids)) {
        // This is the first time we've seen this artifact!
        $new_project_artifacts[] = new ProjectArtifact([
            'name' => $generalized_name,
          ]
        );
      }
    }

    // Save the new project artifacts
    $project->artifacts()->saveMany($new_project_artifacts);
    foreach ($new_project_artifacts as $artifact) {
      $project_artifact_ids[$artifact->name] = $artifact->id;
    }

    // Now that we have all the project artifacts (either newly-created or existing project artifacts),
    // we can save the build artifacts.
    $sizes = $this->getAndCacheArtifactSizes($artifacts);
    $build_artifacts = [];
    foreach ($artifacts as $filename => $_) {
      $build_artifacts[] = BuildArtifact::updateOrCreate(
        [
          'build_id' => $build->id,
          'project_artifact_id' =>
            $project_artifact_ids[$artifact_names[$filename]],
        ],
        [
          'filename' => $filename,
          'size' => $sizes[$filename],
        ]
      );
    }

    $base_build = $this->findBaseBuild($build, $project);
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

  /**
   * Computes a permalink identifier for the current build.
   */
  private function computeIdentifier($payload, $build_info): string {
    $identifier = $build_info['branch'] . '/' . $payload['commit']['sha'];
    if (!empty($build_info['pull_request'])) {
      $identifier = 'pr/' . $build_info['pull_request']['number'];
    }
    return $identifier;
  }

  /**
   * Given a build, find the build that it was based off. For a pull request, this is the most
   * recent build for the branch where it is going to be merged into. If this build is not available
   * (eg. it hasn't been analyzed), returns `null`.
   *
   * @param Build $build
   * @param Project $project
   * @return Build
   */
  private function findBaseBuild(Build $build, Project $project): ?Build {
    if (empty($build->base_commit)) {
      return null;
    }

    // See if we have the base commit to compare against

    $base_build = Build::where('project_id', $project->id)
      ->where('commit', $build->base_commit)
      ->with('buildArtifacts')
      ->first();
    if ($base_build !== null) {
      return $base_build;
    }

    if (!empty($build->base_branch)) {
      // Don't have this exact build, so check if we have the most recent build on the same
      // branch.
      $latest_branch = Branch::where('org_name', $project->org_name)
        ->where('repo_name', $project->repo_name)
        ->where('branch', $build->base_branch)
        ->first();
      if ($latest_branch !== null) {
        return Build::where('project_id', $project->id)
          ->where('commit', $latest_branch->latest_commit)
          ->with('buildArtifacts')
          ->first();

        // TODO: Should this just use the most recently stored build on the branch if this fails?
      }
    }

    return null;
  }

  /**
   * Calls `getArtifactSizes` and caches the data. If this function is called again for the exact same
   * file name, returns the cached info.
   *
   * @param array $artifacts
   * @return array
   */
  private function getAndCacheArtifactSizes(array $artifacts): array {
    $needed = [];
    $result = [];
    foreach ($artifacts as $name => $url) {
      if (array_key_exists($name, $this->artifact_size_cache)) {
        $result[$name] = $this->artifact_size_cache[$name];
      } else {
        $needed[$name] = $url;
      }
    }

    if (count($needed) > 0) {
      // Not all sizes are cached yet, so fetch the uncached ones and cache them
      $new_sizes = $this->getArtifactSizes($needed);
      $this->artifact_size_cache = array_merge($this->artifact_size_cache, $new_sizes);
      $result = array_merge($result, $new_sizes);
    }

    return $result;
  }

  /**
   * Given an array of file name => URL, returns an array of file name => size in bytes.
   *
   * @param array $artifacts
   * @return array
   */
  abstract function getArtifactSizes(array $artifacts): array;
}
