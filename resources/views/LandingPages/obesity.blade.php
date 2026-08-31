@extends('layouts.landing')

@section('title', 'Weight and wellness context | '.$brandPresentation['name'])

@section('content')
  <main data-landing-identifier="{{ $landingIdentifier }}">
    <section class="landing-hero landing-grid">
      <div class="landing-container grid gap-12 py-14 sm:py-20 lg:grid-cols-[minmax(0,0.95fr)_minmax(22rem,1.05fr)] lg:items-center lg:gap-16 lg:py-24">
        <div class="order-2 max-w-2xl lg:order-1">
          <p class="landing-kicker" style="color: var(--brand-primary)">{{ $definition['eyebrow'] }}</p>
          <h1 class="landing-title mt-5">Progress feels different when you have context.</h1>
          <p class="landing-lede mt-6">{{ $definition['description'] }}</p>
          <div class="mt-8 flex flex-col items-start gap-4 sm:flex-row sm:items-center">
            <a href="{{ route('landing.quiz', ['brandId' => $brandId, 'landingIdentifier' => $landingIdentifier]) }}" class="landing-button" style="background-color: var(--brand-primary)">
              Find your starting point <span aria-hidden="true">&rarr;</span>
            </a>
            <span class="text-xs font-bold text-heymo-muted">A respectful, five-step check-in</span>
          </div>
          <div class="mt-10 max-w-md border-l-2 pl-4 text-sm leading-6 text-heymo-ink" style="border-color: var(--brand-secondary)">
            No extreme promises. No body story written for you. Just a clearer way to name what you want to work on next.
          </div>
        </div>

        <div class="order-1 landing-visual landing-visual-obesity lg:order-2" aria-label="Illustration of a steady wellness progress pattern">
          <div class="landing-visual-label">A STEADIER START</div>
          <div class="landing-rhythm-card">
            <div class="flex items-end justify-between gap-4">
              <div><p class="text-[10px] font-black uppercase tracking-[0.16em] text-heymo-muted">Your baseline</p><p class="mt-2 text-4xl font-black tracking-tight text-heymo-navy">01<span style="color: var(--brand-primary)">.</span></p></div>
              <span class="flex size-14 items-center justify-center rounded-full border-4 bg-white text-xs font-black" style="border-color: var(--brand-secondary); color: var(--brand-primary)">steady</span>
            </div>
            <div class="mt-8 space-y-4">
              <div class="flex items-center gap-3"><span class="w-20 text-[10px] font-black uppercase tracking-[0.12em] text-heymo-muted">Routine</span><span class="h-2 flex-1 rounded-full bg-heymo-sky"><span class="block h-full w-[74%] rounded-full" style="background-color: var(--brand-primary)"></span></span></div>
              <div class="flex items-center gap-3"><span class="w-20 text-[10px] font-black uppercase tracking-[0.12em] text-heymo-muted">Goal</span><span class="h-2 flex-1 rounded-full bg-heymo-sky"><span class="block h-full w-[52%] rounded-full" style="background-color: var(--brand-secondary)"></span></span></div>
              <div class="flex items-center gap-3"><span class="w-20 text-[10px] font-black uppercase tracking-[0.12em] text-heymo-muted">Next</span><span class="h-2 flex-1 rounded-full bg-heymo-sky"><span class="block h-full w-[63%] rounded-full bg-heymo-navy"></span></span></div>
            </div>
          </div>
          <div class="landing-visual-note landing-note-one"><span class="landing-note-dot"></span> no all-or-nothing</div>
          <div class="landing-visual-note landing-note-two" style="color: var(--brand-primary)"><span class="landing-note-line"></span> useful beats perfect</div>
        </div>
      </div>
    </section>

    <section class="landing-container grid gap-8 py-16 sm:py-20 lg:grid-cols-[1.1fr_0.9fr] lg:items-end lg:gap-20">
      <div>
        <p class="landing-kicker" style="color: var(--brand-primary)">A different starting point</p>
        <h2 class="landing-heading mt-3">Your goal can be specific without becoming a judgment.</h2>
      </div>
      <p class="max-w-md text-sm leading-7 text-heymo-muted">The quiz captures the context around your goal so a future conversation can stay practical, respectful, and yours.</p>
    </section>
  </main>
@endsection
