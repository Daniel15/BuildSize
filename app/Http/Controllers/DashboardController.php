<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller {
  public function __construct() {
    $this->middleware('auth');
  }

  public function __invoke() {
    // TODO: Check if auth token is still valid
    $user = Auth::user();
    $installs = $user->getGithubInstalls();
    $org_names = array_pluck($installs, 'account.login');

    // Find projects in all orgs that the user has installed the
    $projects = Project::whereIn('org_name', $org_names)
      ->where('active', true)
      ->get()
      ->groupBy(function($project) {
        return $project->org_name;
      });

    return view('dashboard', [
      'installs' => $installs,
      'projects' => $projects,
    ]);
  }
}
