import React, { useEffect, useState } from "react";

export default function ConnectionStatus() {
  const [isOnline, setIsOnline] = useState(() => navigator.onLine);

  useEffect(() => {
    const goOnline = () => setIsOnline(true);
    const goOffline = () => setIsOnline(false);

    window.addEventListener("online", goOnline);
    window.addEventListener("offline", goOffline);

    return () => {
      window.removeEventListener("online", goOnline);
      window.removeEventListener("offline", goOffline);
    };
  }, []);

  return (
    <div
      style={{
        display: "inline-flex",
        alignItems: "center",
        gap: 8,
        padding: "8px 12px",
        borderRadius: 999,
        border: `1px solid ${isOnline ? "#86efac" : "#fdba74"}`,
        background: isOnline ? "#ecfdf5" : "#fff7ed",
        color: isOnline ? "#166534" : "#9a3412",
        fontWeight: 800,
        fontSize: 13,
      }}
    >
      <span
        style={{
          width: 10,
          height: 10,
          borderRadius: "50%",
          background: isOnline ? "#22c55e" : "#f59e0b",
          display: "inline-block",
        }}
      />
      {isOnline ? "Online" : "Offline"}
    </div>
  );
}