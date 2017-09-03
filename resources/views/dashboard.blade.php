@extends('layouts.app')
@section('title', 'Dashboard')

<h2>Repositories</h2>
<p>
  BuildSize has been enabled on the following repositories you are a member of. Want to add another one?
  <a href="https://github.com/apps/{{ env('GITHUB_APP_ALIAS') }}">Configure BuildSize on GitHub</a>
</p>

@foreach ($installs as $install)
  <h3>{{ $install['account']['login'] }}</h3>
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