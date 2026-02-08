# TNMshop (Vue + Laravel + MySQL) — Minimal Shopify-like E-commerce

> หมายเหตุ: ไฟล์ zip นี้เป็น **Source Code + สคริปต์ติดตั้ง** (ไม่ได้รวม `vendor/` และ `node_modules/` ตามมาตรฐานการ deploy)
> คุณต้องมี: **PHP 8.2+**, **Composer**, **Node.js 18+**, **MySQL 8+**

## โครงสร้างใน zip
- `templates/backend_overlay/` : โค้ดส่วนที่ต้องวางทับใน Laravel
- `templates/frontend_overlay/` : โค้ด Vue (Vite) ที่พร้อม build
- `database/tnmshop_schema.sql` : SQL schema ชื่อ DB/table แบบ fix
- `scripts/setup.sh` : สคริปต์สร้างโปรเจกต์ `backend/` และ `frontend/` แล้ว copy ไฟล์ให้ครบ
- `docs/API.md` : เอกสาร API
- `docs/DEPLOYMENT.md` : คู่มือ Deploy แบบ step-by-step

## ติดตั้งแบบเร็ว (แนะนำ)
### macOS / Linux
```bash
cd TNMshop
bash scripts/setup.sh
```

### Windows (PowerShell)
เปิด PowerShell ที่โฟลเดอร์ `TNMshop` แล้วทำตาม `scripts/setup.ps1`

## รันบนเครื่อง (Local)
### Backend
```bash
cd backend
cp .env.example .env
# แก้ DB_* และ ADMIN_KEY ใน .env
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve --host=127.0.0.1 --port=8000
```

### Frontend
```bash
cd frontend
cp .env.example .env
npm install
npm run dev
```

เปิดเว็บ: http://localhost:5173

## Admin Dashboard
- URL: `/admin`
- ต้องใส่ Admin Key ในหน้า Admin (เก็บไว้ใน Local Storage)
- Backend ตรวจด้วย Header: `X-Admin-Key: <ADMIN_KEY>`
