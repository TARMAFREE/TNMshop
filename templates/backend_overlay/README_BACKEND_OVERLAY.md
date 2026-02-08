# Backend overlay notes

This overlay adds:
- Models, Controllers, Middleware
- API routes
- Migrations & Seeder
- Admin auth by X-Admin-Key

After applying overlay, ensure `app/Http/Kernel.php` has:
```php
protected $middlewareAliases = [
  // ...
  'admin' => \App\Http\Middleware\RequireAdminKey::class,
];
```
