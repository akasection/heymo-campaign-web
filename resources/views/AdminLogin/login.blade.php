<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign in | {{ config('app.name', 'Heymo!') }}</title>
    @vite(['resources/css/app.css', 'resources/js/admin-login.ts'])
  </head>
  <body class="antialiased">
    <div id="app" data-show-demo-accounts="{{ app()->environment('local') ? 'true' : 'false' }}"></div>
  </body>
</html>
