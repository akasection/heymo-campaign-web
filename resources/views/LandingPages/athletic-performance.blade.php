@extends('layouts.landing')

@section('title', 'Train with more context | '.$brandPresentation['name'])

@section('content')
  <main data-landing-identifier="{{ $landingIdentifier }}">
    <section class="landing-hero landing-grid">
      <div class="landing-container grid gap-12 py-14 sm:py-20 lg:grid-cols-[minmax(0,1.05fr)_minmax(22rem,0.95fr)] lg:items-center lg:gap-16 lg:py-24">
        <div class="max-w-2xl">
          <p class="landing-kicker" style="color: var(--brand-primary)">{{ $definition['eyebrow'] }}</p>
          <h1 class="landing-title mt-5">Train with more context, not more noise.</h1>
          <p class="landing-lede mt-6">{{ $definition['description'] }}</p>
          <div class="mt-8 flex flex-col items-start gap-4 sm:flex-row sm:items-center">
            <a href="{{ route('landing.quiz', ['brandId' => $brandId, 'landingIdentifier' => $landingIdentifier]) }}" class="landing-button" style="background-color: var(--brand-primary)">
              Map your training context <span aria-hidden="true">&rarr;</span>
            </a>
            <span class="text-xs font-bold text-heymo-muted">Built for your next conversation</span>
          </div>
          <div class="mt-10 grid max-w-md grid-cols-3 gap-2 text-center text-[10px] font-black uppercase tracking-[0.12em] text-heymo-muted"><span class="border-t-2 pt-2" style="border-color: var(--brand-primary)">Train</span><span class="border-t-2 pt-2" style="border-color: var(--brand-secondary)">Recover</span><span class="border-t-2 border-heymo-navy pt-2">Adjust</span></div>
        </div>

        <div class="landing-visual landing-visual-athletic" aria-label="Illustration of a training block with recovery context">
          <div class="landing-visual-label">NEXT TRAINING BLOCK</div>
          <div class="landing-training-card">
            <div class="flex items-center justify-between"><span class="text-[10px] font-black uppercase tracking-[0.16em] text-heymo-muted">Block 04</span><span class="rounded-full px-2 py-1 text-[10px] font-black" style="background-color: color-mix(in srgb, var(--brand-secondary) 22%, white); color: var(--brand-primary)">in progress</span></div>
            <div class="mt-8 grid grid-cols-7 items-end gap-2"><span class="h-12 rounded-t bg-heymo-navy"></span><span class="h-20 rounded-t" style="background-color: var(--brand-secondary)"></span><span class="h-16 rounded-t" style="background-color: var(--brand-primary)"></span><span class="h-28 rounded-t bg-heymo-navy"></span><span class="h-10 rounded-t" style="background-color: var(--brand-secondary)"></span><span class="h-24 rounded-t" style="background-color: var(--brand-primary)"></span><span class="h-14 rounded-t bg-heymo-navy"></span></div>
            <div class="mt-3 flex justify-between text-[10px] font-bold text-heymo-muted"><span>load</span><span>recovery</span><span>repeat</span></div>
          </div>
          <div class="landing-visual-note landing-note-one"><span class="landing-note-dot"></span> keep the signal useful</div>
          <div class="landing-visual-note landing-note-two" style="color: var(--brand-primary)"><span class="landing-note-line"></span> no performance promises</div>
        </div>
      </div>
    </section>

    <section class="landing-container grid gap-8 py-16 sm:py-20 lg:grid-cols-[0.7fr_1.3fr] lg:gap-20"><div><p class="landing-kicker" style="color: var(--brand-primary)">The athlete angle</p><h2 class="landing-heading mt-3">More data is not the goal. Better questions are.</h2></div><div class="grid gap-4 sm:grid-cols-3"><div class="landing-point"><span class="landing-point-number">01</span><p>Say what you are training toward.</p></div><div class="landing-point"><span class="landing-point-number">02</span><p>Name what changed before you looked.</p></div><div class="landing-point"><span class="landing-point-number">03</span><p>Bring useful context into the next plan.</p></div></div></section>
  </main>
@endsection
