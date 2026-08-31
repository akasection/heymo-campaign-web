import { createApp } from "vue";
import QuizPage from "./pages/LandingPages/QuizPage.vue";
import type { BrandPresentation, QuizDefinition } from "./lib/landing";

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
  const { brandId: rawBrandId, presentation: rawPresentation, quiz: rawQuiz, landingIdentifier } = appElement.dataset;
  const brandId = Number(rawBrandId);
  const presentation = parseJson<BrandPresentation>(rawPresentation);
  const quiz = parseJson<QuizDefinition>(rawQuiz);

  if (Number.isSafeInteger(brandId) && presentation && quiz && landingIdentifier) {
    createApp(QuizPage, {
      brandId,
      landingIdentifier,
      presentation,
      quiz,
    }).mount(appElement);
  }
}
