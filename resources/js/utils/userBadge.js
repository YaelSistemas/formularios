// resources/js/utils/userBadge.js

function safeStr(v) {
  return String(v || "").trim();
}

/**
 * Reglas que pediste:
 * - "Juan Carlos Cruz Gonzalez" => J + C (primer apellido = token[len-2])
 * - "Juan Cruz" => J + C (2 tokens => token[1])
 * - "Juan Carlos Gonzalez Vite Campos" => J + G (token[len-2] = Gonzalez)
 */
export function getInitialsFromName(fullName) {
  const name = safeStr(fullName);
  if (!name) return "??";

  const parts = name.split(/\s+/).filter(Boolean);
  if (parts.length === 1) {
    const a = parts[0][0] || "?";
    return (a + a).toUpperCase();
  }

  const first = parts[0];
  let surname;

  if (parts.length === 2) {
    surname = parts[1];
  } else {
    surname = parts[parts.length - 2]; // primer apellido típico
  }

  const a = (first[0] || "?").toUpperCase();
  const b = (surname?.[0] || "?").toUpperCase();
  return a + b;
}

function hashToInt(str) {
  // hash simple determinístico
  let h = 0;
  for (let i = 0; i < str.length; i++) {
    h = (h * 31 + str.charCodeAt(i)) >>> 0;
  }
  return h;
}

/**
 * Colores pastel determinísticos por usuario (por email/id/name)
 */
export function getAvatarColors(user) {
  const key =
    safeStr(user?.email) ||
    safeStr(user?.id) ||
    safeStr(user?.name) ||
    "user";

  const palette = [
    { bg: "#E0F2FE", fg: "#075985", ring: "#BAE6FD" }, // sky
    { bg: "#E0E7FF", fg: "#3730A3", ring: "#C7D2FE" }, // indigo
    { bg: "#ECFCCB", fg: "#3F6212", ring: "#D9F99D" }, // lime
    { bg: "#DCFCE7", fg: "#166534", ring: "#BBF7D0" }, // green
    { bg: "#FFE4E6", fg: "#9F1239", ring: "#FECDD3" }, // rose
    { bg: "#FAE8FF", fg: "#6B21A8", ring: "#F5D0FE" }, // purple
    { bg: "#FEF3C7", fg: "#92400E", ring: "#FDE68A" }, // amber
    { bg: "#FFE4D6", fg: "#9A3412", ring: "#FED7AA" }, // orange
    { bg: "#E5E7EB", fg: "#111827", ring: "#D1D5DB" }, // gray
  ];

  const idx = hashToInt(key.toLowerCase()) % palette.length;
  return palette[idx];
}