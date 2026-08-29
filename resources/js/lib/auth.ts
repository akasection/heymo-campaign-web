type AuthOrganization = {
  id: number;
  name: string;
  slug: string;
};

type AuthUser = {
  id: number;
  name: string;
  email: string;
  role: string;
  organization: AuthOrganization | null;
  capabilities: string[];
};

type AuthenticationResponse = {
  access_token: string;
  token_type: "Bearer";
  expires_in: number;
  user: AuthUser;
};

class ApiError extends Error {
  public readonly status: number;

  public readonly data: Record<string, unknown>;

  constructor(message: string, status: number, data: Record<string, unknown> = {}) {
    super(message);
    this.name = "ApiError";
    this.status = status;
    this.data = data;
  }
}

const accessTokenKey = "heymo.access_token";

function getAccessToken(): string | null {
  return typeof globalThis === "undefined" ? null : globalThis.sessionStorage.getItem(accessTokenKey);
}

function saveAccessToken(token: string): void {
  globalThis.sessionStorage.setItem(accessTokenKey, token);
}

function clearAccessToken(): void {
  if (typeof globalThis !== "undefined") {
    globalThis.sessionStorage.removeItem(accessTokenKey);
  }
}

async function apiFetch<T>(url: string, options: RequestInit = {}): Promise<T> {
  const headers = new Headers(options.headers);
  headers.set("Accept", "application/json");

  if (options.body && !(typeof FormData !== "undefined" && options.body instanceof FormData) && !headers.has("Content-Type")) {
    headers.set("Content-Type", "application/json");
  }

  const token = getAccessToken();
  if (token) {
    headers.set("Authorization", `Bearer ${token}`);
  }

  if (options.method && options.method.toUpperCase() !== "GET") {
    const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content;
    if (csrfToken) {
      headers.set("X-CSRF-TOKEN", csrfToken);
    }
  }

  const response = await fetch(url, {
    ...options,
    credentials: "same-origin",
    headers,
  });
  const data = (await response.json().catch(() => ({}))) as Record<string, unknown>;

  if (!response.ok) {
    if (response.status === 401) {
      clearAccessToken();
    }

    throw new ApiError(typeof data.message === "string" ? data.message : "Something went wrong.", response.status, data);
  }

  return data as T;
}

export { ApiError, apiFetch, clearAccessToken, getAccessToken, saveAccessToken };
export type { AuthenticationResponse, AuthOrganization, AuthUser };
