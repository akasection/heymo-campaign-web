<template>
  <main class="relative min-h-screen overflow-hidden bg-heymo-canvas text-heymo-ink">
    <div class="pointer-events-none absolute inset-0 opacity-70" aria-hidden="true">
      <div class="absolute -left-24 top-16 size-72 rotate-12 border border-heymo-line/70 hex-tile"></div>
      <div class="absolute right-[-7rem] top-[-5rem] size-80 -rotate-12 border border-heymo-line/70 hex-tile"></div>
      <div class="absolute bottom-[-10rem] left-1/3 size-96 rotate-45 border border-heymo-line/60 hex-tile"></div>
    </div>

    <div class="relative mx-auto grid min-h-screen max-w-[1440px] lg:grid-cols-[minmax(0,0.9fr)_minmax(420px,0.7fr)]">
      <section class="relative hidden overflow-hidden bg-heymo-navy px-10 py-10 text-white lg:flex lg:flex-col xl:px-16">
        <div class="absolute inset-0 opacity-80" aria-hidden="true">
          <div
            class="absolute inset-0 bg-[linear-gradient(30deg,transparent_49%,rgba(233,241,251,0.1)_50%,transparent_51%),linear-gradient(120deg,transparent_49%,rgba(233,241,251,0.08)_50%,transparent_51%)] bg-[length:180px_150px]"
          ></div>
          <div class="absolute -right-32 top-20 size-[30rem] rounded-full border border-white/10"></div>
          <div class="absolute right-16 top-56 size-64 rotate-30 border border-heymo-red/50 hex-tile"></div>
          <div class="absolute bottom-20 left-10 size-40 -rotate-12 border border-white/10 hex-tile"></div>
          <div class="absolute bottom-28 right-40 size-3 rounded-full bg-heymo-red shadow-[0_0_0_10px_rgba(217,45,58,0.12)]"></div>
          <div class="absolute right-24 top-44 size-2 rounded-full bg-white/60 shadow-[0_0_0_8px_rgba(255,255,255,0.08)]"></div>
        </div>

        <div class="relative flex items-center gap-3">
          <span class="flex size-10 items-center justify-center rounded-xl bg-heymo-red text-white shadow-lg shadow-black/10">
            <Drop :size="25" weight="fill" aria-hidden="true" />
          </span>
          <span class="text-2xl font-extrabold tracking-[0.02em]">heymo<span class="text-heymo-red">!</span></span>
        </div>

        <div class="relative mt-auto max-w-xl pb-8 pt-24">
          <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-blue-200">Campaign operations</p>
          <h1 class="mt-5 max-w-lg text-4xl font-extrabold leading-[1.05] tracking-[-0.02em] text-white xl:text-5xl">
            Keep every health conversation connected.
          </h1>
          <p class="mt-6 max-w-md text-base leading-7 text-blue-100/80">
            A focused workspace for the people, angles, and campaigns behind more relevant health journeys.
          </p>
          <div class="mt-12 flex items-center gap-3 text-xs font-bold text-blue-100/70">
            <span class="flex size-8 items-center justify-center rounded-full border border-white/20 bg-white/10">
              <ShieldCheck :size="17" weight="bold" aria-hidden="true" />
            </span>
            Access is protected by a one-time email code
          </div>
        </div>
      </section>

      <section class="flex min-h-screen items-center justify-center px-5 py-8 sm:px-10 lg:px-12 xl:px-20">
        <div class="w-full max-w-[500px]">
          <div class="mb-10 flex items-center gap-3 lg:hidden">
            <span class="flex size-9 items-center justify-center rounded-lg bg-heymo-red text-white">
              <Drop :size="22" weight="fill" aria-hidden="true" />
            </span>
            <span class="text-xl font-extrabold tracking-[0.02em] text-heymo-navy">heymo<span class="text-heymo-red">!</span></span>
          </div>

          <div class="mb-8">
            <div class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.16em] text-heymo-red">
              <span class="flex size-7 items-center justify-center rounded-md bg-heymo-red/10">
                <Envelope :size="16" weight="bold" aria-hidden="true" />
              </span>
              Secure workspace access
            </div>
            <h2 class="mt-4 text-3xl font-extrabold tracking-[-0.02em] text-heymo-navy sm:text-4xl">Sign in to Heymo</h2>
            <p class="mt-3 max-w-md text-sm leading-6 text-heymo-muted">Use your work email and we&apos;ll send a fresh sign-in code.</p>
          </div>

          <div class="card card-border overflow-hidden bg-base-100 text-base-content shadow-panel">
            <div class="flex items-center gap-2 border-b border-heymo-line bg-heymo-sky/60 px-6 py-4 text-xs font-bold text-heymo-navy sm:px-8">
              <span class="flex size-6 items-center justify-center rounded-full bg-white text-heymo-red shadow-sm">
                <span class="text-[10px]">{{ step === "email" ? "1" : "2" }}</span>
              </span>
              <span>{{ step === "email" ? "Confirm your work email" : "Enter your sign-in code" }}</span>
              <span class="ml-auto text-[11px] font-semibold text-heymo-muted">{{ step === "email" ? "Email" : "Verify" }}</span>
            </div>

            <div class="p-6 sm:p-8">
              <Transition name="slide" mode="out-in">
                <form v-if="step === 'email'" key="email" class="space-y-6" @submit.prevent="requestCode">
                  <div>
                    <label for="email" class="text-sm font-bold text-heymo-navy">Work email</label>
                    <div class="relative mt-2">
                      <Envelope class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-heymo-muted" :size="19" aria-hidden="true" />
                      <input
                        id="email"
                        v-model="email"
                        type="email"
                        name="email"
                        autocomplete="email"
                        required
                        autofocus
                        placeholder="you@company.com"
                        class="input input-lg w-full pl-10 placeholder:text-slate-300"
                      />
                    </div>
                  </div>

                  <p v-if="errorMessage" class="alert alert-soft alert-error px-3 py-2.5 text-sm leading-5" role="alert">
                    {{ errorMessage }}
                  </p>

                  <button type="submit" :disabled="isLoading" class="btn btn-primary btn-lg w-full">
                    {{ isLoading ? "Sending code..." : "Send sign-in code" }}
                    <ArrowRight v-if="!isLoading" :size="18" weight="bold" aria-hidden="true" />
                  </button>
                </form>

                <form v-else key="code" class="space-y-6" @submit.prevent="verifyCode">
                  <div>
                    <p class="text-sm leading-6 text-heymo-muted">
                      We sent a six-digit code to
                      <strong class="font-bold text-heymo-navy">{{ email }}</strong
                      >.
                    </p>
                    <label for="code" class="mt-5 block text-sm font-bold text-heymo-navy">Sign-in code</label>
                    <input
                      id="code"
                      :value="code"
                      type="text"
                      name="code"
                      inputmode="numeric"
                      autocomplete="one-time-code"
                      maxlength="7"
                      autofocus
                      placeholder="000-000"
                      aria-describedby="code-help"
                      class="input input-lg mt-2 h-14 w-full text-center text-2xl font-extrabold tracking-[0.22em] text-heymo-navy placeholder:tracking-[0.16em] placeholder:text-slate-300"
                      @input="formatCodeInput"
                    />
                    <p id="code-help" class="mt-2 flex items-center gap-1.5 text-xs text-heymo-muted">
                      <Clock :size="15" weight="bold" aria-hidden="true" />
                      Code expires in {{ formatSeconds(expiresIn) }}
                    </p>
                  </div>

                  <p v-if="message" class="alert alert-soft alert-success px-3 py-2.5 text-sm leading-5" role="status">
                    {{ message }}
                  </p>
                  <p v-if="errorMessage" class="alert alert-soft alert-error px-3 py-2.5 text-sm leading-5" role="alert">
                    {{ errorMessage }}
                  </p>

                  <button type="submit" :disabled="isLoading || codeDigits.length !== 6" class="btn btn-primary btn-lg w-full">
                    {{ isLoading ? "Checking code..." : "Open workspace" }}
                    <ArrowRight v-if="!isLoading" :size="18" weight="bold" aria-hidden="true" />
                  </button>

                  <div class="flex flex-wrap items-center justify-between gap-3 text-xs font-bold">
                    <button
                      type="button"
                      class="inline-flex items-center gap-1.5 text-heymo-muted transition hover:text-heymo-navy focus:outline-2 focus:outline-offset-2 focus:outline-heymo-red"
                      @click="changeEmail"
                    >
                      <ArrowLeft :size="15" weight="bold" aria-hidden="true" />
                      Use a different email
                    </button>
                    <button
                      type="button"
                      :disabled="isLoading || retryAfter > 0"
                      class="text-heymo-red transition hover:text-heymo-red-dark focus:outline-2 focus:outline-offset-2 focus:outline-heymo-red disabled:cursor-not-allowed disabled:text-heymo-muted"
                      @click="requestCode"
                    >
                      {{ retryAfter > 0 ? `Resend in ${retryAfter}s` : "Send a new code" }}
                    </button>
                  </div>
                </form>
              </Transition>
            </div>
          </div>

          <section
            v-if="step === 'email' && showDemoAccounts"
            class="card card-border mt-5 bg-base-100/80 p-4 sm:p-5"
            aria-labelledby="demo-accounts-title"
          >
            <div class="flex items-start gap-3">
              <span class="flex size-8 shrink-0 items-center justify-center rounded-md bg-heymo-sky text-heymo-navy">
                <Buildings :size="17" weight="bold" aria-hidden="true" />
              </span>
              <div>
                <h3 id="demo-accounts-title" class="text-xs font-extrabold uppercase tracking-[0.12em] text-heymo-navy">Seeded workspace accounts</h3>
                <p class="mt-1 text-xs leading-5 text-heymo-muted">Choose an account to fill its work email.</p>
              </div>
            </div>

            <ul class="mt-3 divide-y divide-heymo-line" role="list">
              <li v-for="account in registeredAccounts" :key="account.email">
                <button
                  type="button"
                  class="group flex w-full items-center gap-3 py-3 text-left focus:outline-2 focus:outline-offset-2 focus:outline-heymo-red"
                  @click="selectAccount(account.email)"
                >
                  <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-heymo-navy text-[10px] font-extrabold text-white">
                    {{ account.initials }}
                  </span>
                  <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-bold text-heymo-navy transition group-hover:text-heymo-red">{{ account.email }}</span>
                    <span class="mt-0.5 block truncate text-xs text-heymo-muted">{{ account.organization }} / {{ account.role }}</span>
                  </span>
                  <ArrowRight :size="16" weight="bold" class="shrink-0 text-heymo-muted transition group-hover:text-heymo-red" aria-hidden="true" />
                </button>
              </li>
            </ul>
          </section>

          <p class="mt-6 flex items-start gap-2 text-xs leading-5 text-heymo-muted">
            <ShieldCheck class="mt-0.5 shrink-0 text-heymo-positive" :size="16" weight="bold" aria-hidden="true" />
            Only invited company users can access campaign operations. Never share your sign-in code.
          </p>
        </div>
      </section>
    </div>
  </main>
</template>

<script setup lang="ts">
import {
  PhArrowLeft as ArrowLeft,
  PhArrowRight as ArrowRight,
  PhBuildings as Buildings,
  PhClock as Clock,
  PhDrop as Drop,
  PhEnvelopeSimple as Envelope,
  PhShieldCheck as ShieldCheck,
} from "@phosphor-icons/vue";
import { computed, onBeforeUnmount, ref } from "vue";
import { ApiError, apiFetch, saveAccessToken } from "../lib/auth";
import type { AuthenticationResponse } from "../lib/auth";

defineProps<{
  showDemoAccounts: boolean;
}>();

type LoginStep = "email" | "code";
type ChallengeResponse = {
  challenge_id: string;
  expires_in: number;
  retry_after: number;
};

const registeredAccounts = [
  {
    email: "admin@heymo.test",
    organization: "Heymo Org",
    role: "Admin",
    initials: "ME",
  },
  {
    email: "ops@lexical.test",
    organization: "Lexical Labs",
    role: "Member",
    initials: "NW",
  },
  {
    email: "review@eje.test",
    organization: "EJE Science",
    role: "Member",
    initials: "SP",
  },
  {
    email: "team@xohealth.test",
    organization: "XO Health Group",
    role: "Member",
    initials: "JB",
  },
];

const email = ref("");
const code = ref("");
const challengeId = ref("");
const step = ref<LoginStep>("email");
const expiresIn = ref(0);
const retryAfter = ref(0);
const isLoading = ref(false);
const errorMessage = ref("");
const message = ref("");
let countdownTimer: number | undefined = undefined;

const codeDigits = computed(() => code.value.replace(/\D/g, ""));

function selectAccount(accountEmail: string) {
  email.value = accountEmail;
  errorMessage.value = "";
}

async function requestCode() {
  if (step.value === "code" && retryAfter.value > 0) {
    return;
  }

  isLoading.value = true;
  errorMessage.value = "";
  message.value = "";

  try {
    const response = await apiFetch<ChallengeResponse>("/api/auth/request-code", {
      method: "POST",
      body: JSON.stringify({ email: email.value }),
    });

    challengeId.value = response.challenge_id;
    expiresIn.value = response.expires_in;
    retryAfter.value = response.retry_after;
    step.value = "code";
    code.value = "";
    message.value = "If your email is provisioned, your code is on its way.";
    startCountdown();
  } catch (error) {
    errorMessage.value = error instanceof ApiError ? error.message : "We could not send a code right now.";
  } finally {
    isLoading.value = false;
  }
}

async function verifyCode() {
  if (codeDigits.value.length !== 6 || !challengeId.value) {
    errorMessage.value = "Enter all six digits from the email.";
    return;
  }

  isLoading.value = true;
  errorMessage.value = "";
  message.value = "";

  try {
    const response = await apiFetch<AuthenticationResponse>("/api/auth/verify-code", {
      method: "POST",
      body: JSON.stringify({
        challenge_id: challengeId.value,
        email: email.value,
        code: code.value,
      }),
    });

    saveAccessToken(response.access_token);
    globalThis.location.assign("/admin");
  } catch (error) {
    errorMessage.value = error instanceof ApiError ? error.message : "We could not verify that code.";
  } finally {
    isLoading.value = false;
  }
}

function formatCodeInput(event: Event) {
  const input = event.target as HTMLInputElement;
  const digits = input.value.replace(/\D/g, "").slice(0, 6);
  code.value = digits.length > 3 ? `${digits.slice(0, 3)}-${digits.slice(3)}` : digits;
  errorMessage.value = "";
}

function changeEmail() {
  step.value = "email";
  code.value = "";
  challengeId.value = "";
  retryAfter.value = 0;
  expiresIn.value = 0;
  message.value = "";
  errorMessage.value = "";
  stopCountdown();
}

function startCountdown() {
  stopCountdown();
  countdownTimer = globalThis.setInterval(() => {
    if (expiresIn.value > 0) {
      expiresIn.value -= 1;
    }
    if (retryAfter.value > 0) {
      retryAfter.value -= 1;
    }
    if (expiresIn.value === 0 && retryAfter.value === 0) {
      stopCountdown();
    }
  }, 1000);
}

function stopCountdown() {
  if (countdownTimer !== undefined) {
    globalThis.clearInterval(countdownTimer);
    countdownTimer = undefined;
  }
}

function formatSeconds(seconds: number): string {
  const minutes = Math.floor(seconds / 60);
  const remainder = seconds % 60;
  return `${minutes}:${remainder.toString().padStart(2, "0")}`;
}

onBeforeUnmount(stopCountdown);
</script>

<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition:
    opacity 160ms ease,
    transform 160ms ease;
}

.slide-enter-from {
  opacity: 0;
  transform: translateX(10px);
}

.slide-leave-to {
  opacity: 0;
  transform: translateX(-10px);
}
</style>
