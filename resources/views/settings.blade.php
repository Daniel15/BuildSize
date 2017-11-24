@extends('layouts.app')
@section('title', 'Settings for '.$repo_name)

@section('content')
  <h2>Settings for {{ $repo_name }}</h2>

  @include('partials.validation-errors')
  @if ($was_saved)
    <div class="alert alert-success">
      Your changes were saved!
    </div>
  @endif


  <form method="POST" action="{{
    action('RepoSettingsController', [
      'org_name' => $org_name,
      'repo_name' => $repo_name,
    ])
  }}">
    {{ csrf_field() }}

    <p>
      Only comment on pull requests if:
      <label>
        Change in file size is greater than
        <input
          name="min_change_for_comment"
          type="number"
          value="{{ old('min_change_for_comment') ?? $project->min_change_for_comment }}"
        />
        bytes
      </label>
    </p>
    <p>
      <button type="submit" class="btn btn-primary">Save</button>
    </p>
  </form>
@endsection
