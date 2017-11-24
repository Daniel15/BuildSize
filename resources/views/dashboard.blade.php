@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
  <h2>Repositories</h2>
  @if (count($installs) === 0)
    <p>
      You haven't enabled {{ config('app.name') }} for any projects yet!
      <a href="{{ \App\UrlHelper::getGithubAppURL() }}">Get started</a>.
    </p>
  @else
    <p>
      {{ config('app.name') }} has been enabled on the following repositories you are a member of. Want to add another one?
      <a href="{{ \App\UrlHelper::getGithubAppURL() }}">Configure BuildSize on GitHub</a>.
    </p>

    @foreach ($installs as $install)
      <h3 style="display: inline; margin-right: 0.2em">
        {{ $install['account']['login'] }}
      </h3>
      @if (count($projects->get($install['account']['login'], [])) > 0)
        <a href="{{ action('SetupController@index') }}?installation_id={{ $install['id'] }}">Get Started</a> &bull;
        <a href="{{ $install['html_url'] }}">Manage</a>
      @endif
      <ul>
        @forelse ($projects->get($install['account']['login'], []) as $project)
          <li>
            {{ $project->repo_name }}<br />
            <a href="{{ action('RepoSettingsController', [
              'org_name' => $project->org_name,
              'repo_name' => $project->repo_name,
            ]) }}">
              Configure
            </a>
          </li>
        @empty
          <li>
            None yet! <a href="{{ \App\UrlHelper::getGithubAppInstallURL() }}">Get started</a>.
          </li>
        @endforelse
      </ul>
    @endforeach
  @endif
@endsection
