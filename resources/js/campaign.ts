import { createApp } from "vue";
import QuizPage from "./pages/LandingPages/QuizPage.vue";
import type { AgeGroupOption, BrandPresentation, QuizDefinition } from "./lib/landing";
import { sendLandingBeacon } from "./lib/tracking";

const appElement = document.querySelector<HTMLElement>("#quiz-app");

function parseJson<T>(value: string | undefined): T | null {
  if (!value) {
    return null;
  }

  try {
    return JSON.parse(value) as T;
  } catch {
    return null;
  }
}

if (appElement) {
  const { ageGroups: rawAgeGroups, brandId: rawBrandId, presentation: rawPresentation, quiz: rawQuiz, landingIdentifier } = appElement.dataset;
  const brandId = Number(rawBrandId);
  const ageGroups = parseJson<AgeGroupOption[]>(rawAgeGroups);
  const presentation = parseJson<BrandPresentation>(rawPresentation);
  const quiz = parseJson<QuizDefinition>(rawQuiz);

  if (Number.isSafeInteger(brandId) && ageGroups && presentation && quiz && landingIdentifier) {
    createApp(QuizPage, {
      ageGroups,
      brandId,
      landingIdentifier,
      presentation,
      quiz,
    }).mount(appElement);
  }
}

const brandMeta = document.querySelector<HTMLMetaElement>('meta[name="brand-id"]')?.content;
const landingMeta = document.querySelector<HTMLMetaElement>('meta[name="landing-identifier"]')?.content;
const beaconBrandId = Number(brandMeta);

if (Number.isSafeInteger(beaconBrandId) && beaconBrandId > 0 && landingMeta) {
  void sendLandingBeacon(beaconBrandId, landingMeta);
}
