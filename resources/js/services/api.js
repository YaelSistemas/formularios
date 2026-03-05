const API_BASE = "/api";

function getToken() {
  return localStorage.getItem("token");
}

function authHeaders() {
  const token = getToken();
  return token ? { Authorization: `Bearer ${token}` } : {};
}

async function parseError(res) {
  // intenta leer el body para mensaje bonito
  const data = await res.json().catch(() => null);

  // Laravel ValidationException suele venir así:
  // { message: "...", errors: { field: ["..."] } }
  if (data?.errors) {
    const firstKey = Object.keys(data.errors)[0];
    const firstMsg = data.errors[firstKey]?.[0];
    if (firstMsg) return firstMsg;
  }

  if (data?.message) return data.message;

  return `Error HTTP ${res.status}`;
}

export async function apiLogin(payload) {
  const res = await fetch(`${API_BASE}/login`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
    body: JSON.stringify(payload),
  });

  if (!res.ok) {
    throw new Error(await parseError(res));
  }

  return res.json();
}

export async function apiMe() {
  const res = await fetch(`${API_BASE}/me`, {
    method: "GET",
    headers: {
      ...authHeaders(),
      Accept: "application/json",
    },
  });

  if (!res.ok) {
    throw new Error(await parseError(res));
  }

  return res.json();
}

export async function apiLogout() {
  const res = await fetch(`${API_BASE}/logout`, {
    method: "POST",
    headers: {
      ...authHeaders(),
      Accept: "application/json",
    },
  });

  if (!res.ok) {
    throw new Error(await parseError(res));
  }

  return res.json();
}

// Helpers genéricos (para lo que sigue: usuarios/roles/permisos)
export async function apiGet(path) {
  const res = await fetch(`${API_BASE}${path}`, {
    method: "GET",
    headers: {
      ...authHeaders(),
      Accept: "application/json",
    },
  });

  if (!res.ok) throw new Error(await parseError(res));
  return res.json();
}

export async function apiPost(path, payload) {
  const res = await fetch(`${API_BASE}${path}`, {
    method: "POST",
    headers: {
      ...authHeaders(),
      "Content-Type": "application/json",
      Accept: "application/json",
    },
    body: JSON.stringify(payload ?? {}),
  });

  if (!res.ok) throw new Error(await parseError(res));
  return res.json();
}

export async function apiPut(path, payload) {
  const res = await fetch(`${API_BASE}${path}`, {
    method: "PUT",
    headers: {
      ...authHeaders(),
      "Content-Type": "application/json",
      Accept: "application/json",
    },
    body: JSON.stringify(payload ?? {}),
  });

  if (!res.ok) throw new Error(await parseError(res));
  return res.json();
}

export async function apiDelete(path) {
  const res = await fetch(`${API_BASE}${path}`, {
    method: "DELETE",
    headers: {
      ...authHeaders(),
      Accept: "application/json",
    },
  });

  if (!res.ok) throw new Error(await parseError(res));
  return res.json();
}