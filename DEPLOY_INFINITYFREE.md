# InfinityFree Deployment

1. Upload the full project into your site `htdocs` folder.
2. Import `database.sql` into your InfinityFree MySQL database with phpMyAdmin.
3. Copy `.env.example` to `.env.local` and fill in your production values.
4. Set `APP_URL` to your live URL, for example `https://vegihub.ct.ws`.
5. Keep `APP_ENV=production` and `APP_DEBUG=false`.
6. Make sure these directories exist:
   `public/uploads`
   `public/uploads/avatars`
   `public/uploads/categories`
   `public/uploads/products`
7. Test login, cart, checkout, uploads, and email after upload.

Notes:

- Root `.htaccess` rewrites requests into `public/`, which matches InfinityFree shared hosting.
- `public/.htaccess` routes app URLs to `public/index.php`.
- `.env.local` overrides `.env`, so you can keep local and production config separate.
- Replace any local or personal secrets before deployment.
