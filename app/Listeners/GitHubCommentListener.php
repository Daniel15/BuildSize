<?php

namespace App\Listeners;

use App\Events\BuildCompletedEvent;
use App\GithubUtils;
use App\Helpers\Format;
use App\Models\Build;
use App\Models\Project;
use Illuminate\Support\Collection;

/**
 * When a build has completed for a pull request, posts a comment on the pull request.
 * @package App\Listeners
 */
class GitHubCommentListener {
  /**
   * Handle the event.
   *
   * @param  BuildCompletedEvent $event
   * @return void
   */
  public function handle(BuildCompletedEvent $event) {
    // TODO: Make this configurable (ie. allow disabling comments)
    // Temporarily disabled until it's made configurable
    return;

    if (!$event->has_base_build || empty($event->build->pull_request)) {
      return;
    }

    $artifacts = $this->collectArtifacts($event->base_build_artifacts, $event->build_artifacts);
    $message = $this->buildMessage(
      $artifacts,
      $event->base_total_size,
      $event->total_size
    );

    $github = GithubUtils::createClientForInstall($event->install);

    // Check if a comment already exists
    $comment_id = $this->checkForExistingComment(
      $github,
      $event->project,
      $event->build
    );

    if ($comment_id !== null) {
      $github->issue()->comments()->update(
        $event->project->org_name,
        $event->project->repo_name,
        $comment_id,
        [
          'body' => $message,
        ]
      );
    } else {
      $github->issue()->comments()->create(
        $event->project->org_name,
        $event->project->repo_name,
        $event->build->pull_request,
        [
          'body' => $message,
        ]
      );
    }
  }

  private function collectArtifacts(
    Collection $base_build_artifacts,
    Collection $build_artifacts
  ) {
    $data = [];
    foreach ($base_build_artifacts as $old_artifact) {
      $new_artifact = $build_artifacts->get($old_artifact->project_artifact_id);
      $data[] = [
        'name' => $old_artifact->projectArtifact->name,
        'old_size' => $old_artifact->size,
        'new_size' => $new_artifact
          ? $new_artifact->size
          : null,
      ];
    }

    // Add any new artifacts that didn't exist previously
    foreach ($build_artifacts as $new_artifact) {
      if ($base_build_artifacts->has($new_artifact->project_artifact_id)) {
        continue;
      }
      $data[] = [
        'name' => $new_artifact->projectArtifact->name,
        'old_size' => null,
        'new_size' => $new_artifact->size,
      ];
    }

    return $data;
  }

  private function buildMessage(array $artifacts, int $base_total_size, int $total_size) {
    $diff = $base_total_size - $total_size;
    $message = $diff > 0
      ? (
        'This change will decrease the build size from ' . Format::fileSize($base_total_size) . ' to ' .
        Format::fileSize($total_size) . ', a decrease of ' .
        Format::diffFileSizeWithPercentage($total_size, $base_total_size)
      ) : (
        'This change will increase the build size from ' . Format::fileSize($base_total_size) . ' to ' .
        Format::fileSize($total_size) . ', an increase of ' .
        Format::diffFileSizeWithPercentage($base_total_size, $total_size)
      );

    $message .= <<<EOT


| File name | Previous Size | New Size | Change |
| --------- | ------------- | -------- | ------ |
  
EOT;
    foreach ($artifacts as $artifact) {
      $message .= '| ' . $artifact['name'] . ' | ';
      $message .= ($artifact['old_size'] === null ? '[new file]' : Format::fileSize($artifact['old_size'])) . ' | ';
      $message .= ($artifact['new_size'] === null ? '[deleted]' : Format::fileSize($artifact['new_size'])) . ' | ';
      if ($artifact['old_size'] !== null && $artifact['new_size'] !== null) {
        $message .= Format::diffFileSizeWithPercentage($artifact['old_size'], $artifact['new_size']) . ' | ';
      } else {
        $message .= ' | ';
      }
      $message .= "\n";
    }

    return $message;
  }

  private function checkForExistingComment(
    \Github\Client $github,
    Project $project,
    Build $build
  ) {
    $comments = $github->issue()->comments()->all(
      $project->org_name,
      $project->repo_name,
      $build->pull_request
    );
    foreach ($comments as $comment) {
      if (ends_with($comment['user']['html_url'], '/apps/' . env('GITHUB_APP_ALIAS'))) {
        return $comment['id'];
      }
    }
    return null;
  }
}
