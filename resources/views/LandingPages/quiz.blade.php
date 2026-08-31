@extends('layouts.landing')

@section('title', $definition['quiz']['title'].' | '.$brandPresentation['name'])

@section('content')
  @php
    $ageGroups = collect(config('capture.age_groups', []))->map(
        fn (array $group, string $value): array => [
            'value' => $value,
            'label' => $group['label'],
            'description' => $group['description'],
        ],
    )->values()->all();
  @endphp

  <main class="landing-container py-10 sm:py-14">
    <div
      id="quiz-app"
      data-brand-id="{{ $brandId }}"
      data-landing-identifier="{{ $landingIdentifier }}"
      data-presentation="{{ json_encode($brandPresentation, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}"
      data-quiz="{{ json_encode($definition['quiz'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}"
      data-age-groups="{{ json_encode($ageGroups, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}"
    ></div>
  </main>
@endsection
