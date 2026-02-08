# TNMshop Deployment Guide (Step-by-step)

## 1) Requirements
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8+
- Nginx or Apache

## 2) Backend (Laravel API)
### Setup database
Create DB `tnmshop_db` and a user with permissions.

### Configure
Edit `backend/.env`:
- `APP_URL`
- `DB_*`
- `ADMIN_KEY`
- `FRONTEND_URL`

### Install & build caches
```bash
cd backend
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
```

### Nginx example
Point root to `backend/public` and route all requests to `index.php`.

## 3) Frontend (Vue)
Edit `frontend/.env`:
- `VITE_API_BASE_URL=https://your-api-domain.com/api`

Build:
```bash
cd frontend
npm ci
npm run build
```

Deploy `frontend/dist` to static hosting (Nginx/Netlify/Cloudflare Pages).
Make sure SPA fallback routes to `index.html`.

## 4) CORS
If frontend and backend are different domains, enable CORS (Laravel):
- Add `FRONTEND_URL` and configure CORS in `config/cors.php` (Laravel default supports it)
