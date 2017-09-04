@extends('layouts.app')
@section('title', 'Setup')

@section('content')
  @if (count($repos) === 0)
    This installation has no repositories enabled! That's probably not what you want.
    <a href="{{ \App\UrlHelper::getGithubAppURL() }}">Go configure it on GitHub</a>.
  @else
    <h2>Get started with {{ config('app.name') }} for {{ $org['login'] }}</h2>
    <p>Awesome! These repositories have been enabled for BuildSize:</p>
    @foreach ($repos as $repo)
      <h3>{{ $repo['name'] }}</h3>
      <div data-url="{{
        action('SetupController@showRepoInstructions', [
          'org_name' => $repo['owner']['login'],
          'repo_name' => $repo['name'],
        ])
      }}">
        Loading...
      </div>
    @endforeach
  @endif
@endsection
