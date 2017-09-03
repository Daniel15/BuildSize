<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

/**
 * Handles logging in via GitHub
 * @package App\Http\Controllers
 * @see https://developer.github.com/apps/building-integrations/setting-up-and-registering-github-apps/identifying-users-for-github-apps/
 */
class GithubAuthController extends Controller {
  public function __construct() {
    $this->middleware('guest');
  }

  public function login() {
    // TODO: Work out why this shows the auth UI every single time
    return Socialite::driver('github')->redirect();
  }

  public function completeLogin() {
    $user = Socialite::driver('github')->user();
    $auth_user = User::updateOrCreate(
      [
        'github_id' => $user->id,
      ],
      [
        'github_token' => $user->token,
        'github_username' => $user->nickname,
        'name' => $user->name,
      ]
    );
    Auth::login($auth_user, false);
    return redirect()->action('DashboardController');
  }
}
