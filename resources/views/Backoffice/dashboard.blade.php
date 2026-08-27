<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Backoffice | {{ config('app.name', 'Heymo!') }}</title>
    @vite(['resources/css/app.css', 'resources/js/backoffice.ts'])
  </head>
  <body class="antialiased">
    <div id="app"></div>
  </body>
</html>
