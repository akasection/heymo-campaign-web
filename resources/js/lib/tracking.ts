import { UAParser } from "ua-parser-js";

const FINGERPRINT_KEY = "heymo.fingerprint";
const LANDED_AT_KEY = "heymo.landed_at";
const LANDED_KEY = "heymo.landed";
const ATTRIBUTION_KEY = "heymo.attribution";
const DEVICE_KEY = "heymo.device";

export type Attribution = {
  utm_source?: string;
  utm_medium?: string;
  utm_campaign?: string;
  utm_term?: string;
  utm_content?: string;
  referrer?: string;
};

export type DeviceInfo = {
  type?: string;
  os?: string;
  browser?: string;
};

export type CaptureMetadata = {
  fingerprint: string;
  session_duration_seconds: number;
  attribution: Attribution;
  device: DeviceInfo;
};

function readStorage(key: string, storage: Storage): string | null {
  try {
    return storage.getItem(key);
  } catch {
    return null;
  }
}

function writeStorage(key: string, value: string, storage: Storage): void {
  try {
    storage.setItem(key, value);
  } catch {
    // Storage unavailable; tracking is best-effort only.
  }
}

function readJson<T>(key: string): T | null {
  try {
    const raw = sessionStorage.getItem(key);

    return raw ? (JSON.parse(raw) as T) : null;
  } catch {
    return null;
  }
}

export function ensureFingerprint(): string {
  const existing = readStorage(FINGERPRINT_KEY, localStorage);

  if (existing) {
    return existing;
  }

  const fingerprint = crypto.randomUUID();
  writeStorage(FINGERPRINT_KEY, fingerprint, localStorage);

  return fingerprint;
}

export function readAttribution(): Attribution {
  const params = new URLSearchParams(window.location.search);
  const attribution: Attribution = {};

  for (const key of ["utm_source", "utm_medium", "utm_campaign", "utm_term", "utm_content"] as const) {
    const value = params.get(key);

    if (value) {
      attribution[key] = value;
    }
  }

  if (document.referrer) {
    attribution.referrer = document.referrer;
  }

  return attribution;
}

export function detectDevice(): DeviceInfo {
  try {
    const data = (
      navigator as Navigator & {
        userAgentData?: {
          platform?: string;
          mobile?: boolean;
          brands?: { brand?: string }[];
        };
      }
    ).userAgentData;

    if (data) {
      const platform = data.platform ?? "";
      const mobile = data.mobile ?? false;
      const browser = data.brands?.find(brand => brand.brand && !/Not.?A.?Brand/i.test(brand.brand ?? ""))?.brand;

      return {
        type: mobile ? "mobile" : "desktop",
        os: platform || undefined,
        browser: browser || undefined,
      };
    }
  } catch {
    // Fall through to UA-string parsing.
  }

  try {
    const parser = new UAParser(navigator.userAgent);
    const result = parser.getResult();

    return {
      type: result.device?.type ?? (result.device?.vendor || result.device?.model ? "mobile" : "desktop"),
      os: result.os?.name,
      browser: result.browser?.name,
    };
  } catch {
    return {};
  }
}

export function ensureSessionStart(): number {
  const existing = readStorage(LANDED_AT_KEY, sessionStorage);

  if (existing) {
    return Number(existing) || Date.now();
  }

  const startedAt = String(Date.now());
  writeStorage(LANDED_AT_KEY, startedAt, sessionStorage);

  return Number(startedAt);
}

function sessionDurationSeconds(): number {
  const startedAt = Number(readStorage(LANDED_AT_KEY, sessionStorage) ?? 0);
  const startMs = startedAt > 0 ? startedAt : Date.now();

  return Math.max(0, Math.round((Date.now() - startMs) / 1000));
}

function saveLandingContext(): void {
  try {
    sessionStorage.setItem(ATTRIBUTION_KEY, JSON.stringify(readAttribution()));
    sessionStorage.setItem(DEVICE_KEY, JSON.stringify(detectDevice()));
  } catch {
    // Best-effort only.
  }
}

export async function sendLandingBeacon(brandId: number, landingIdentifier: string): Promise<void> {
  try {
    if (sessionStorage.getItem(LANDED_KEY)) {
      return;
    }

    sessionStorage.setItem(LANDED_KEY, "1");
    ensureFingerprint();
    ensureSessionStart();
    saveLandingContext();

    const csrf = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content;

    await fetch("/api/landing", {
      method: "POST",
      credentials: "same-origin",
      keepalive: true,
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        ...(csrf ? { "X-CSRF-TOKEN": csrf } : {}),
      },
      body: JSON.stringify({
        fingerprint: ensureFingerprint(),
        brand_id: brandId,
        landing_identifier: landingIdentifier,
        attribution: readAttribution(),
        device: detectDevice(),
      }),
    });
  } catch {
    // Fire-and-forget; a landing event must never block the page.
  }
}

export function captureMetadata(): CaptureMetadata {
  return {
    fingerprint: ensureFingerprint(),
    session_duration_seconds: sessionDurationSeconds(),
    attribution: readJson<Attribution>(ATTRIBUTION_KEY) ?? readAttribution(),
    device: readJson<DeviceInfo>(DEVICE_KEY) ?? detectDevice(),
  };
}
