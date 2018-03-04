<?php

namespace App\Listeners;

use App\Events\BuildCompletedEvent;
use App\GithubUtils;
use App\Helpers\Format;

/**
 * When a build has completed, posts a status update to GitHub.
 * @package App\Listeners
 */
class GitHubStatusListener {
  const WARN_THRESHOLD = 10000; // TODO make this configurable

  /**
   * Handle the event.
   *
   * @param  BuildCompletedEvent $event
   * @return void
   */
  public function handle(BuildCompletedEvent $event) {
    $state = 'success';
    if ($event->has_base_build) {

      $largestChange = null;
      $largestDiff = 0;

      foreach ($event->build_artifacts as $artifact) {
        if (!$event->base_build_artifacts->has($artifact->project_artifact_id)) {
          // No base version, so nothing to compare to
          continue;
        }
        $base_artifact = $event->base_build_artifacts->get($artifact->project_artifact_id);
        $diff = $base_artifact->size - $artifact->size;
        if(abs($diff) > $largestDiff) {
          $largestChange = $artifact;
          $largestDiff = $diff;
        }
      }

      if($largestDiff === 0) {
        $description = 'No change';
      } else if (abs($diff) < config('buildsize.github.trivial_size')) {
        $description = 'No significant change';
      } else {
        $diff_percent = round($diff / $event->base_total_size * 100.0, 2);
        if ($diff > 0) {
          $description = 'Significant change of ' . $largestChange->filename . ' down by ' . Format::fileSize($diff) . ' (' . $diff_percent . '%)';
        } else {
          $description = 'Significant change of ' . $largestChange->filename . ' up by ' . Format::fileSize(-$diff) . ' (' . -$diff_percent . '%)';
          if ($diff < static::WARN_THRESHOLD) {
            //$state = 'failure';
          }
        }
      }
    } else {
      $description = 'No prior size to compare - ' . Format::fileSize($event->total_size);
    }

    $github = GithubUtils::createClientForInstall($event->install);
    $github->repo()->statuses()->create(
      $event->project->org_name,
      $event->project->repo_name,
      $event->build->commit,
      [
        'context' => config('buildsize.github.status_context_prefix'),
        'description' => $description,
        'state' => $state,
        'target_url' => 'https://buildsize.org/', // TODO
      ]
    );
  }
}
