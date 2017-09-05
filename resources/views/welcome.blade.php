@extends('layouts.master')

@section('body')
  <div class="jumbotron">
    <div class="container hero">
      <div>
        <h1 class="display-3">{{ config('app.name') }}</h1>
        <p>
          Automatically track the size of your build artifacts
        </p>
        <p>
          <a class="btn btn-primary btn-lg" href="{{ \App\UrlHelper::getGithubAppURL() }}" role="button">
            Get Started &raquo;
          </a>
        </p>
      </div>
      <img class="hero-image" src="/images/hero.png" width="513" height="152" alt="Screenshot of {{ config('app.name') }}" />
    </div>
  </div>

  <div class="container">
    <div class="row">
      <div class="col-md-4">
        <h2>No Configuration Necessary</h2>
        <p>
          If you are using CircleCI* and it is configured to archive your artifacts, getting started
          with {{ config('app.name') }} is easy! Just add the GitHub App to your repo and that's it! All artifacts will
          be automatically monitored.
        </p>
        <p class="text-muted">* Support for other CI systems coming soon</p>
      </div>
      <div class="col-md-4">
        <h2>Monitors Pull Requests</h2>
        <p>
          If a pull request significantly increases the size of any of your build artifacts,
          {{ config('app.name') }} will automatically set a build status, and optionally post a comment
          to the PR.
        </p>
      </div>
      <div class="col-md-4">
        <h2>It's Open Source</h2>
        <p>
          {{ config('app.name') }} is open source, so you can do whatever you want with it. Contribute some bug fixes,
          or even run your own version.
        </p>
      </div>
      <!--<div class="col-md-4">
        <h2>Historical Data</h2>
        <p>View how the size of your build has changed over time</p>
        <p class="text-muted">Coming soon</p>
      </div>-->
    </div>
  </div>
@endsection
