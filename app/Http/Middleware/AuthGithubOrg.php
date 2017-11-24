<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Checks that the current user has access to the GitHub org specified in the org_name route field.
 */
class AuthGithubOrg {
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request $request
   * @param  \Closure $next
   * @return mixed
   */
  public function handle(Request $request, Closure $next) {
    $user = $request->user();
    $installs = $user->getGithubInstalls();
    $org_names = array_pluck($installs, 'account.login');
    if (!in_array($request->route('org_name'), $org_names)) {
      abort(403, 'Access denied to this GitHub org');
    }

    return $next($request);
  }
}
