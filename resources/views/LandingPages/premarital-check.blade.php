@extends('layouts.landing')

@section('title', 'A health conversation for two | '.$brandPresentation['name'])

@section('content')
  <main data-landing-identifier="{{ $landingIdentifier }}">
    <section class="landing-hero landing-grid">
      <div class="landing-container grid gap-12 py-14 sm:py-20 lg:grid-cols-[minmax(22rem,0.9fr)_minmax(0,1.1fr)] lg:items-center lg:gap-16 lg:py-24">
        <div class="landing-visual landing-visual-premarital" aria-label="Illustration of two connected conversation paths">
          <div class="landing-visual-label">A CONVERSATION FOR TWO</div>
          <div class="landing-pair-card">
            <div class="landing-pair-person"><span class="landing-pair-initial" style="background-color: var(--brand-primary)">A</span><span><b>your context</b><small>private to you</small></span></div>
            <div class="landing-pair-bridge" style="background-color: var(--brand-secondary)"></div>
            <div class="landing-pair-person"><span class="landing-pair-initial" style="background-color: var(--brand-secondary)">B</span><span><b>their context</b><small>private to them</small></span></div>
            <div class="landing-pair-caption">shared care<br><span>without shared assumptions</span></div>
          </div>
          <div class="landing-visual-note landing-note-one"><span class="landing-note-dot"></span> take it together</div>
          <div class="landing-visual-note landing-note-two" style="color: var(--brand-primary)"><span class="landing-note-line"></span> keep it private</div>
        </div>

        <div class="max-w-2xl">
          <p class="landing-kicker" style="color: var(--brand-primary)">{{ $definition['eyebrow'] }}</p>
          <h1 class="landing-title mt-5">A thoughtful health conversation for two.</h1>
          <p class="landing-lede mt-6">{{ $definition['description'] }}</p>
          <div class="mt-8 flex flex-col items-start gap-4 sm:flex-row sm:items-center">
            <a href="{{ route('landing.quiz', ['brandId' => $brandId, 'landingIdentifier' => $landingIdentifier]) }}" class="landing-button" style="background-color: var(--brand-primary)">
              Begin the couple check-in <span aria-hidden="true">&rarr;</span>
            </a>
            <span class="text-xs font-bold text-heymo-muted">Private answers - shared intention</span>
          </div>
          <div class="mt-10 flex items-start gap-3 text-sm leading-6 text-heymo-ink"><span class="mt-2 size-2 shrink-0 rounded-full" style="background-color: var(--brand-secondary)"></span><span>Health planning can be serious without making the future feel scary.</span></div>
        </div>
      </div>
    </section>

    <section class="landing-container grid gap-8 py-16 sm:py-20 lg:grid-cols-3">
      <div class="lg:col-span-2"><p class="landing-kicker" style="color: var(--brand-primary)">Before the next chapter</p><h2 class="landing-heading mt-3 max-w-2xl">Make space for the conversation, then take the smallest useful step.</h2></div>
      <div class="border-t border-heymo-line pt-4 text-sm leading-7 text-heymo-muted lg:border-l lg:border-t-0 lg:pl-6 lg:pt-0">The check-in asks what matters to you both. It does not assume what either person needs.</div>
    </section>
  </main>
@endsection
