@php
    $brand = $brandPresentation;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="landing-identifier" content="{{ $landingIdentifier }}">
    <title>@yield('title', $brand['name'])</title>
    @vite(['resources/css/app.css', 'resources/js/campaign.ts'])
  </head>
  <body
    class="landing-page antialiased"
    style="--brand-primary: {{ $brand['primary_color'] }}; --brand-secondary: {{ $brand['secondary_color'] }}; --brand-heading: {{ e($brand['heading_font']) }}; --brand-body: {{ e($brand['body_font']) }};"
  >
    <header class="landing-header">
      <div class="landing-container flex items-center justify-between gap-4 py-5">
        <a
          href="{{ route('landing.page', ['brandId' => $brandId, 'landingIdentifier' => $landingIdentifier]) }}"
          class="flex min-w-0 items-center gap-3 text-heymo-navy"
        >
          <span class="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-md bg-white ring-1 ring-black/10">
            @if ($brand['logo_url'])
              <img src="{{ $brand['logo_url'] }}" alt="{{ $brand['name'] }} logo" class="size-full object-contain p-1.5">
            @else
              <span class="text-sm font-black" style="color: var(--brand-primary)">{{ str($brand['name'])->substr(0, 2)->upper() }}</span>
            @endif
          </span>
          <span class="truncate text-sm font-black tracking-tight" style="font-family: var(--brand-heading)">{{ $brand['name'] }}</span>
        </a>
        <span class="hidden text-[10px] font-bold uppercase tracking-[0.18em] text-heymo-muted sm:block">Private health context, made clearer</span>
      </div>
    </header>

    @yield('content')

    <footer class="landing-footer">
      <div class="landing-container flex flex-col gap-2 py-7 text-[11px] leading-5 text-heymo-muted sm:flex-row sm:items-center sm:justify-between">
        <span>{{ $brand['name'] }} helps you review information. It does not diagnose or replace professional care.</span>
        <span>Questions are saved only after you choose email updates.</span>
      </div>
    </footer>

    @stack('scripts')
  </body>
</html>
