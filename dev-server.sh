#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
PORT="${PORT:-8000}"

if ! command -v php >/dev/null 2>&1; then
    echo "Erreur : PHP n'est pas installé ou absent du PATH." >&2
    exit 1
fi

if ! [[ "$PORT" =~ ^[0-9]{1,5}$ ]] || (( PORT < 1 || PORT > 65535 )); then
    echo "Erreur : PORT doit être un nombre entre 1 et 65535." >&2
    exit 1
fi

LAN_IP="$(hostname -I 2>/dev/null | awk '{for (i = 1; i <= NF; i++) if ($i !~ /:/ && $i !~ /^127\./) {print $i; exit}}')"

echo "Serveur de développement : http://localhost:${PORT}"
if [[ -n "$LAN_IP" ]]; then
    echo "Depuis un autre appareil connecté au même Wi-Fi : http://${LAN_IP}:${PORT}"
else
    echo "Adresse Wi-Fi introuvable automatiquement. Trouvez l'adresse IP locale de ce PC."
fi
echo "Arrêt du serveur : Ctrl+C"

exec php -S "0.0.0.0:${PORT}" -t "$ROOT_DIR" "$ROOT_DIR/dev-router.php"
