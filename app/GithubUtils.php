<?php

namespace App;

use App\Models\User;
use Firebase\JWT\JWT;
use Github\Client;
use Illuminate\Support\Facades\Storage;

final class GithubUtils {
  public static function parsePullRequestURL(string $url): array {
    $path = explode('/', parse_url($url, PHP_URL_PATH));
    if ($path[3] !== 'pull') {
      return null;
    }

    return [
      'username' => $path[1],
      'reponame' => $path[2],
      'pr_number' => (int)$path[4],
    ];
  }

  public static function createClientForApp(): Client {
    $private_key = Storage::disk('local')->get('github.key');
    $token = JWT::encode([
      'iat' => time(),
      'exp' => time() + (5 * 60),
      'iss' => env('GITHUB_APP_ID'),
    ], $private_key, 'RS256');

    return app('github.factory')->make([
      'token' => $token,
      'method' => 'jwt',
      // https://developer.github.com/apps/building-integrations/setting-up-and-registering-github-apps/about-authentication-options-for-github-apps/
      'version' => 'machine-man-preview',
    ]);
  }

  public static function createClientForUser(User $user): Client {
    return app('github.factory')->make([
      'token' => $user->github_token,
      'method' => 'token',
      // https://developer.github.com/apps/building-integrations/setting-up-and-registering-github-apps/about-authentication-options-for-github-apps/
      'version' => 'machine-man-preview',
    ]);
  }
}