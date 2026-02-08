\
# TNMshop setup (Windows PowerShell)
# Requires: Composer, Node.js, Git Bash or rsync alternative (robocopy)
# Steps:
# 1) composer create-project laravel/laravel backend
# 2) Copy-Item -Recurse -Force templates/backend_overlay/* backend/
# 3) npm create vite@latest frontend -- --template vue
# 4) cd frontend; npm install; npm install vue-router pinia; cd ..
# 5) Copy-Item -Recurse -Force templates/frontend_overlay/* frontend/

Write-Host "== TNMshop setup (PowerShell) =="

if (Test-Path "backend" -or Test-Path "frontend") {
  Write-Error "backend/ or frontend/ already exists. Please remove them and rerun."
  exit 1
}

Write-Host "-- Creating Laravel backend --"
composer create-project laravel/laravel backend
Write-Host "-- Applying backend overlay --"
Copy-Item -Recurse -Force "templates/backend_overlay/*" "backend/"

Write-Host "-- Creating Vue frontend (Vite) --"
npm create vite@latest frontend -- --template vue
Set-Location "frontend"
npm install
npm install vue-router pinia
Set-Location ".."

Write-Host "-- Applying frontend overlay --"
Copy-Item -Recurse -Force "templates/frontend_overlay/*" "frontend/"

Write-Host "== Done =="
Write-Host "Next: configure backend/.env and frontend/.env then run dev servers."
