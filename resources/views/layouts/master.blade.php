<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta id="csrf-token" name="csrf-token" content="{{ csrf_token() }}" />
  <title>
    @hassection('title')
    @yield('title') &mdash; {{ config('app.name') }}
    @else
      {{ config('app.name') }}
    @endif
  </title>
  <link href="{{ mix('css/app.css') }}" rel="stylesheet" />

  <!-- TODO: analytics -->
</head>
<body>
@include('layouts._partial.navbar')
@yield('body')
<footer class="footer">
  <div class="container d-flex justify-content-between text-muted">
    <span>&copy; 2017 <a href="https://dan.cx/">Daniel15</a>.</span>
    <span><a href="https://github.com/Daniel15/BuildSize" target="_blank">I'm open source!</a></span>
  </div>
</footer>
<script src="{{ mix('/js/app.js') }}"></script>
</body>
</html>
