# E-Ticaret PHP Hosting Kurulumu ve Stabilizasyon

Bu paket `PHP + MySQL` ile calisir, `Node.js` gerekmez.

## Gereksinimler
- PHP 7.4+ (onerilen 8.0+)
- MySQL / MariaDB
- Apache + mod_rewrite
- PHP extension: `pdo`, `pdo_mysql`, `mysqli`

## SQL Dosyalari
- `database.sql`: yeni kurulum icin tam sema + ornek veriler.
- `database-repair.sql`: canli veriyi koruyarak sema uyumu ve hata onarimi.

## Canliya Alma Sirasi
1. Tam yedek alin:
- Dosyalar (zip)
- Veritabani dump
2. `database-repair.sql` calistirin.
3. Kodu deploy edin.
4. Smoke test yapin:
- `/api`
- `/api/health`
- `/api/settings/public`
- `/api/products`
- `/api/blog`

## cPanel Git Deploy
Bu projede `.cpanel.yml` ve `scripts/cpanel_deploy.sh` vardir.

- cPanel Git ile repoyu `/home/<user>/<repo>` altina clone edin.
- Deploy target icin cPanel ortaminda `DEPLOY_PATH` degiskenini ayarlayin
  (ornek: `/home/<user>/public_html`).
- cPanel "Deploy HEAD Commit" calistirdiginda deploy script kodu hedefe kopyalar.

## Zorunlu Ortam Degiskenleri
`api/config.php` artik hardcoded secret kullanmaz.

Asagidaki env degerleri zorunludur:
- `DB_USER`
- `DB_PASSWORD`
- `DB_NAME`
- `JWT_SECRET`

Opsiyonel:
- `DB_HOST` (varsayilan `localhost`)

## Varsayilan Admin (database.sql)
- E-posta: `admin@eticaret.com`
- Sifre: `admin123`
