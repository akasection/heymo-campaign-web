<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Campaign unavailable</title>
    @vite(['resources/css/app.css'])
  </head>
  <body class="grid min-h-screen place-items-center bg-heymo-canvas p-6 text-heymo-ink">
    <main class="max-w-md rounded-lg border border-heymo-line bg-white p-8 text-center shadow-panel">
      <p class="text-[11px] font-black uppercase tracking-[0.16em] text-heymo-red">Campaign unavailable</p>
      <h1 class="mt-3 text-2xl font-black text-heymo-navy">This page is not available right now.</h1>
      <p class="mt-3 text-sm leading-6 text-heymo-muted">{{ $message }}</p>
      <a href="/" class="btn btn-primary mt-6">Return home</a>
    </main>
  </body>
</html>
