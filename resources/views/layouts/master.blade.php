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

  <script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-25623237-10', 'auto');
    ga('send', 'pageview');
  </script>
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
