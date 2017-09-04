<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider {
  /**
   * The event listener mappings for the application.
   *
   * @var array
   */
  protected $listen = [
    \App\Events\BuildCompletedEvent::class => [
      \App\Listeners\GitHubStatusListener::class,
      \App\Listeners\GitHubCommentListener::class,
    ],
  ];

  /**
   * Register any events for your application.
   *
   * @return void
   */
  public function boot() {
    parent::boot();

    //
  }
}
