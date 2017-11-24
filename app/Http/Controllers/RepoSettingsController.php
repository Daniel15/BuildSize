<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class RepoSettingsController extends Controller {
  public function __construct() {
    $this->middleware(['auth', 'auth.githuborg']);
  }

  public function __invoke(string $org_name, string $repo_name, Request $request) {
    $was_saved = false;
    $project = Project::where('org_name', $org_name)
      ->where('repo_name', $repo_name)
      ->firstOrFail();

    if ($request->isMethod('post')) {
      $validated_data = $request->validate([
        'min_change_for_comment' => 'required|numeric'
      ]);
      $project->min_change_for_comment = $validated_data['min_change_for_comment'];
      $project->save();
      $was_saved = true;
    }

    return view('settings', [
      'org_name' => $org_name,
      'project' => $project,
      'repo_name' => $repo_name,
      'was_saved' => $was_saved,
    ]);
  }
}
