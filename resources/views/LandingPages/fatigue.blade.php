@extends('layouts.landing')

@section('title', 'Low energy, clearer context | '.$brandPresentation['name'])

@section('content')
  <main data-landing-identifier="{{ $landingIdentifier }}">
    <section class="landing-hero landing-grid">
      <div class="landing-container grid gap-12 py-14 sm:py-20 lg:grid-cols-[minmax(0,1.05fr)_minmax(22rem,0.95fr)] lg:items-center lg:gap-16 lg:py-24">
        <div class="max-w-2xl">
          <p class="landing-kicker" style="color: var(--brand-primary)">{{ $definition['eyebrow'] }}</p>
          <h1 class="landing-title mt-5">A clearer way to look at low energy.</h1>
          <p class="landing-lede mt-6">{{ $definition['description'] }}</p>
          <div class="mt-8 flex flex-col items-start gap-4 sm:flex-row sm:items-center">
            <a href="{{ route('landing.quiz', ['brandId' => $brandId, 'landingIdentifier' => $landingIdentifier]) }}" class="landing-button" style="background-color: var(--brand-primary)">
              Start the fatigue quiz <span aria-hidden="true">&rarr;</span>
            </a>
            <span class="text-xs font-bold text-heymo-muted">Five short steps - no diagnosis language</span>
          </div>
          <div class="mt-10 flex flex-wrap gap-2 text-xs font-bold text-heymo-navy">
            <span class="landing-chip"><span class="size-2 rounded-full" style="background-color: var(--brand-secondary)"></span> Your energy, in your words</span>
            <span class="landing-chip"><span class="size-2 rounded-full" style="background-color: var(--brand-primary)"></span> A useful starting point</span>
          </div>
        </div>

        <div class="landing-visual landing-visual-fatigue" aria-label="Illustration of an energy pattern becoming easier to read">
          <div class="landing-visual-label">THE ENERGY THREAD</div>
          <div class="landing-signal-card">
            <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-[0.16em] text-heymo-muted">
              <span>Daily rhythm</span><span style="color: var(--brand-primary)">Context first</span>
            </div>
            <div class="mt-8 flex h-36 items-end gap-2">
              <span class="landing-bar h-[42%]"></span><span class="landing-bar h-[68%]"></span><span class="landing-bar h-[52%]"></span><span class="landing-bar h-[80%]"></span><span class="landing-bar h-[35%]"></span><span class="landing-bar h-[58%]"></span><span class="landing-bar h-[74%]"></span>
            </div>
            <div class="mt-3 flex justify-between text-[10px] font-bold text-heymo-muted"><span>Mon</span><span>Sun</span></div>
          </div>
          <div class="landing-visual-note landing-note-one"><span class="landing-note-dot"></span> notice the pattern</div>
          <div class="landing-visual-note landing-note-two" style="color: var(--brand-primary)"><span class="landing-note-line"></span> choose the next question</div>
        </div>
      </div>
    </section>

    <section class="landing-container grid gap-8 py-16 sm:py-20 lg:grid-cols-[0.8fr_1.2fr] lg:gap-20">
      <div>
        <p class="landing-kicker" style="color: var(--brand-primary)">Why this angle</p>
        <h2 class="landing-heading mt-3">You do not need a dramatic story to deserve better context.</h2>
      </div>
      <div class="grid gap-6 sm:grid-cols-3">
        <div class="landing-point"><span class="landing-point-number">01</span><p>Start with what has changed, not a conclusion.</p></div>
        <div class="landing-point"><span class="landing-point-number">02</span><p>Keep the conversation grounded in your routine.</p></div>
        <div class="landing-point"><span class="landing-point-number">03</span><p>Take one clear next step when you are ready.</p></div>
      </div>
    </section>
  </main>
@endsection
