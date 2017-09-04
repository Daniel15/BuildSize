<?php

namespace App\Events;

use App\Models\Build;
use App\Models\GithubInstall;
use App\Models\Project;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Collection;

class BuildCompletedEvent {
  use Dispatchable, SerializesModels;

  /**
   * @var GithubInstall
   */
  public $install;

  /**
   * @var Project
   */
  public $project;

  // Properties of the current build
  /**
   * @var Build
   */
  public $build;
  /**
   * @var Collection
   */
  public $build_artifacts;
  /**
   * @var int
   */
  public $total_size;

  // Properties of the previous build on the same branch, if available
  /**
   * @var boolean
   */
  public $has_base_build;
  /**
   * @var Build
   */
  public $base_build;
  /**
   * @var Collection
   */
  public $base_build_artifacts;
  /**
   * @var int
   */
  public $base_total_size;

  public function __construct(array $data) {
    $this->install = $data['install'];

    $this->project = $data['project'];
    $this->build = $data['build'];
    $this->build_artifacts = $data['build_artifacts'];
    $this->total_size = $data['total_size'];

    $this->has_base_build = $data['has_base_build'];
    $this->base_build = $data['base_build'];
    $this->base_build_artifacts = $data['base_build_artifacts'];
    $this->base_total_size = $data['base_total_size'];
  }
}
