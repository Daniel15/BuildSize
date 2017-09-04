const mix = require('laravel-mix');

mix
  .sass('resources/assets/sass/app.scss', 'public/css')
  .js('resources/assets/js/app.js', 'public/js');

if (mix.inProduction()) {
  mix.version();
}
