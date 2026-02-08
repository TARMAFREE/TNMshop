#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

echo "== TNMshop setup =="
echo "This script will create ./backend (Laravel) and ./frontend (Vue) then apply overlays."

if ! command -v composer >/dev/null 2>&1; then
  echo "ERROR: composer not found. Please install Composer first."
  exit 1
fi

if ! command -v npm >/dev/null 2>&1; then
  echo "ERROR: npm not found. Please install Node.js (npm) first."
  exit 1
fi

if [ -d "backend" ] || [ -d "frontend" ]; then
  echo "ERROR: backend/ or frontend/ already exists. Please remove them or run in a fresh folder."
  exit 1
fi

echo "-- Creating Laravel backend --"
composer create-project laravel/laravel backend
echo "-- Applying backend overlay --"
rsync -a templates/backend_overlay/ backend/

echo "-- Creating Vue frontend (Vite) --"
npm create vite@latest frontend -- --template vue
echo "-- Installing frontend deps --"
cd frontend
npm install
npm install vue-router pinia
cd ..

echo "-- Applying frontend overlay --"
rsync -a templates/frontend_overlay/ frontend/

echo "== Done =="
echo "Next:"
echo "1) Configure backend/.env (DB_*, ADMIN_KEY, FRONTEND_URL)"
echo "2) cd backend && composer install && php artisan migrate && php artisan db:seed && php artisan serve"
echo "3) Configure frontend/.env (VITE_API_BASE_URL)"
echo "4) cd frontend && npm run dev"
