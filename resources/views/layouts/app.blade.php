<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
  <meta id="csrf-token" name="csrf-token" content="{{ csrf_token() }}"/>
  <title>
    @hassection('title')
    @yield('title') &mdash; {{ config('app.name') }}
    @else
      {{ config('app.name') }}
    @endif
  </title>
  <link href="{{ mix('css/app.css') }}" rel="stylesheet"/>

  <!-- TODO: analytics -->
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand mr-auto" href="{{ url('/') }}">
      {{ config('app.name') }}
    </a>

    @auth
      <span class="navbar-text">Hi, {{ Auth::user()->name }}!&nbsp;</span>
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="{{ route('logout') }}" id="logout-link">
            Log out
          </a>
        </li>
      </ul>
    @endauth
  </div>
</nav>
<div class="container">
  @yield('content')
</div>
<footer class="footer">
  <div class="container d-flex justify-content-between text-muted">
    <span>&copy; 2017 <a href="https://dan.cx/">Daniel15</a>.</span>
    <span><a href="https://github.com/Daniel15/BuildSize" target="_blank">I'm open source!</a></span>
  </div>
</footer>
<script src="{{ mix('/js/app.js') }}"></script>
</body>
</html>
