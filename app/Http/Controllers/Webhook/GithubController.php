<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\GithubInstall;
use App\Models\Project;
use App\Services\Build\CircleCI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GithubController extends Controller {
  public function __invoke(Request $request) {
    // Validate signature
    $sig_check = 'sha1=' . hash_hmac('sha1', $request->getContent(), env('GITHUB_WEBHOOK_SECRET'));
    if ($sig_check !== $request->header('x-hub-signature')) {
      abort(403, 'Invalid token');
    }

    $event_name = $request->header('X-GitHub-Event');
    $handler_name = 'handle' . studly_case($event_name);
    if (method_exists($this, $handler_name)) {
      return $this->{$handler_name}($request);
    } else {
      return 'No handler for ' . $event_name;
    }
  }

  // https://developer.github.com/v3/activity/events/types/#installationevent
  /** @noinspection PhpUnusedPrivateMethodInspection */
  private function handleInstallation(Request $request) {
    $org_name = $request->input('installation.account.login');
    $install = GithubInstall::firstOrCreate(
      [
        'org_name' => $org_name,
      ],
      [
        'install_id' => $request->input('installation.id'),
      ]
    );

    switch ($request->input('action')) {
      case 'created':
        // Add every repo that the app was installed to
        $repos = $request->input('repositories') ?? [];
        $this->addRepositories($org_name, $repos);
        break;

      case 'deleted':
        Project::where('org_name', $org_name)
          ->update(['active' => false]);
        $install->delete();
        break;
    }
  }

  // https://developer.github.com/v3/activity/events/types/#installationrepositoriesevent

  /** @noinspection PhpUnusedPrivateMethodInspection */
  private function handleInstallationRepositories(Request $request) {
    // Add new repositories, and remove old ones
    $org_name = $request->input('installation.account.login');
    $this->addRepositories(
      $org_name,
      $request->input('repositories_added')
    );

    $removed_repos = $request->input('repositories_removed');
    $removed_repo_names = [];
    foreach ($removed_repos as $repo) {
      $removed_repo_names[] = $repo['name'];
    }

    // Deactivate any removed repositories
    Project::whereIn('repo_name', $removed_repo_names)
      ->where('org_name', $org_name)
      ->update(['active' => false]);
  }

  /** @noinspection PhpUnusedPrivateMethodInspection */
  private function handleStatus(Request $request) {
    // See if we have a GitHub app configured for this repo
    $install_id = $request->input('installation.id');
    $install = GithubInstall::where('install_id', $install_id)
      ->first();

    if ($install === null) {
      // Somehow we got a push for an installation that doesn't actually exist. wat.
      Log::warning('Received webhook for invalid installation %s!', $install_id);
      return 'Invalid installation';
    }

    $handler = null;
    if (starts_with($request->input('context'), 'ci/circleci')) {
      $handler = new CircleCI();
    }
    if ($handler === null) {
      return 'Unknown context "' . $request->input('context') . '"';
    }

    $handler->analyzeFromGitHubPayload($install, $request->all());
    return 'Handled status payload.';
  }

  // https://developer.github.com/v3/activity/events/types/#pushevent
  /** @noinspection PhpUnusedPrivateMethodInspection */
  private function handlePush(Request $request) {
    // We only care about pushes to branches
    $ref = $request->input('ref');
    if (!preg_match('~^refs/heads/(?<branch>.+)$~', $ref, $matches)) {
      return;
    }
    $branch = $matches['branch'];

    // Ignore deletes
    if (
      $request->input('deleted') ||
      $request->input('head_commit.id') === null
    ) {
      return;
    }

    // Save the latest commit info for this branch
    Branch::updateOrCreate(
      [
        'branch' => $branch,
        'org_name' => $request->input('repository.owner.name'),
        'repo_name' => $request->input('repository.name'),
      ],
      [
        'author' => $request->input('head_commit.author.username'),
        'latest_commit' => $request->input('head_commit.id'),
      ]
    );
  }

  private function addRepositories(string $org_name, array $repos) {
    foreach ($repos as $repo) {
      Project::updateOrCreate(
        [
          'host' => 'github',
          'org_name' => $org_name,
          'repo_name' => $repo['name'],
        ],
        [
          'active' => true,
        ]
      );
    }
  }
}
