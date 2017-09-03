@extends('layouts.app')
@section('title', 'Dashboard')

<h2>Repositories</h2>
@if (count($installs) === 0)
  <p>
    You haven't enabled BuildSize for any projects yet!
    <a href="https://github.com/apps/{{ env('GITHUB_APP_ALIAS') }}">Get started</a>.
  </p>
@else
  <p>
    BuildSize has been enabled on the following repositories you are a member of. Want to add another one?
    <a href="https://github.com/apps/{{ env('GITHUB_APP_ALIAS') }}">Configure BuildSize on GitHub</a>.
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
          {{ $project->repo_name }}
        </li>
      @empty
        <li>
          None yet! <a href="https://github.com/apps/{{ env('GITHUB_APP_ALIAS') }}/installations/new">Get started</a>.
        </li>
      @endforelse
    </ul>
  @endforeach
@endif
