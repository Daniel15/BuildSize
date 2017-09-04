<nav class="navbar navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand mr-auto" href="{{ url('/') }}">
      {{ config('app.name') }}
    </a>

    @auth
      <span class="navbar-text">Hi, {{ Auth::user()->name }}!&nbsp;</span>
    @endauth
    <ul class="navbar-nav">
      <li class="nav-item">
        @auth
          <a class="nav-link" href="{{ route('logout') }}" id="logout-link">
            Log out
          </a>
          @else
            <a class="nav-link" href="{{ action('GithubAuthController@login') }}">
              Log in with GitHub
            </a>
          @endif
      </li>
    </ul>
  </div>
</nav>
