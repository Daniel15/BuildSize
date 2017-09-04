<?php

namespace App\Http\Controllers;

use App\GithubUtils;
use App\ResultPagerWithCustomField;
use Github\Exception\RuntimeException;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetupController extends Controller {
  public function __construct() {
    $this->middleware('auth');
  }

  public function index(Request $request) {
    $install_id = $request->input('installation_id');

    // TODO: Check if auth token is still valid
    $user = Auth::user();
    $github = GithubUtils::createClientForUser($user);
    $paginator = new ResultPagerWithCustomField($github);
    // This will throw if the user does not have permission to view the installations
    try {
      $repos = $paginator->fetchAllUsingField(
        $github->currentUser(),
        'repositoriesByInstallation',
        'repositories',
        [$install_id]
      );
    } catch (RuntimeException $ex) {
      if ($ex->getCode() === 404) {
        abort(403, 'You do not have permission to view this installation');
      }
      throw $ex;
    }

    $org = null;
    if (count($repos) > 0) {
      $org = $repos[0]['owner'];
    }

    return view('setup', [
      'org' => $org,
      'repos' => $repos,
    ]);
  }

  public function showRepoInstructions(string $org_name, string $repo_name) {
    // Check for supported CI systems
    $files_to_check = [
      'appveyor' => 'appveyor.yml',
      'circle' => 'circle.yml',
      'circle2' => '.circleci/config.yml',
      'travis' => '.travis.yml',
    ];

    $base_url = $url = 'https://raw.githubusercontent.com/' . $org_name . '/' . $repo_name . '/master/';

    $client = new Client();
    $promises = [];
    foreach ($files_to_check as $system => $file) {
      $url = $base_url . $file;
      $promises[$system] = $client->headAsync($url)->then(
        function() { return true; },
        function() { return false; }
      );
    }

    $results = collect(Promise\unwrap($promises));
    $none_supported = $results->every(function ($value) { return !$value; });

    return view('setup-instructions', [
      'results' => $results,
      'none_supported' => $none_supported,
    ]);
  }
}
