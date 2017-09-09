<?php

namespace Tests\Unit;

use App\Models\GithubInstall;
use App\Services\Build\CircleCI;
use Tests\TestCase;

class CircleCITest extends TestCase {
  /**
   * @vcr circleci1.yml
   */
  public function testExample() {
    $circleci = \Mockery::mock(CircleCI::class)->makePartial();
    $circleci->shouldReceive('analyzeBuild')->andReturnNull();
    $circleci->shouldReceive('analyzeFromPullRequest')->andReturnNull();

    $payload = json_decode(
      file_get_contents(__DIR__ . '/../fixtures/github-webhook/circleci1.json'),
      true
    );
    $install = \Mockery::mock(GithubInstall::class);
    $circleci->analyzeFromBuildURL(
      $install,
      $payload['target_url'],
      $payload
    );

    $circleci->shouldHaveReceived('analyzeBuild')->once()->with(
      $install,
      $payload,
      [
        'example.txt' => 'https://19-102289952-gh.circle-artifacts.com/0/tmp/circle-artifacts.nyqtnIF/example.txt',
      ],
      [
        'base_branch' => null,
        'base_commit' => null,
        'branch' => 'Daniel15-patch-8',
        'org_name' => 'BuildSizeTest',
        'pull_request' => null,
        'repo_name' => 'CircleCI1Test',
      ]
    );

    $circleci->shouldHaveReceived('analyzeFromPullRequest')->once()->with(
      $install,
      'BuildSizeTest',
      'CircleCI1Test',
      11,
      $payload,
      [
        'example.txt' => 'https://19-102289952-gh.circle-artifacts.com/0/tmp/circle-artifacts.nyqtnIF/example.txt',
      ]
    );
  }
}
