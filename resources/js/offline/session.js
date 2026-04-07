const OFFLINE_SESSION_KEY = "offline_session_v1";

export function saveOfflineSession(user) {
  if (!user?.id) return;

  localStorage.setItem(
    OFFLINE_SESSION_KEY,
    JSON.stringify({
      user,
      user_id: Number(user.id),
      saved_at: new Date().toISOString(),
    })
  );
}

export function getOfflineSession() {
  try {
    const raw = localStorage.getItem(OFFLINE_SESSION_KEY);
    return raw ? JSON.parse(raw) : null;
  } catch {
    return null;
  }
}

export function getOfflineUser() {
  return getOfflineSession()?.user || null;
}

export function clearOfflineSession() {
  localStorage.removeItem(OFFLINE_SESSION_KEY);
}