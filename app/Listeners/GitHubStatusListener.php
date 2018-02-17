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
    $description = 'Build size: ' . Format::fileSize($event->total_size);
    $state = 'success';

    if ($event->has_base_build) {
      // Can compare to base
      $diff = $event->base_total_size - $event->total_size;
      if (abs($diff) == 0) {
       $description .= ' (no change)';
      } else if (abs($diff) < config('buildsize.github.trivial_size')) {
        $description .= ' (no significant change)';
      } else {
        $diff_percent = round($diff / $event->base_total_size * 100.0, 2);
        if ($diff > 0) {
          $description .= ' (decreased by ' . Format::fileSize($diff) . ', ' . $diff_percent . '%)';
        } else {
          $description .= ' (increased by ' . Format::fileSize(-$diff) . ', ' . -$diff_percent . '%)';
          if ($diff < static::WARN_THRESHOLD) {
            //$state = 'failure';
          }
        }
      }
    }

    $github = GithubUtils::createClientForInstall($event->install);
    $github->repo()->statuses()->create(
      $event->project->org_name,
      $event->project->repo_name,
      $event->build->commit,
      [
        'context' => config('buildsize.github.status_context_prefix') . '/total',
        'description' => $description,
        'state' => $state,
        'target_url' => 'https://buildsize.org/', // TODO
      ]
    );
  }
}
