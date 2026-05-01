<?php
/**
 * E-Ticaret API Router
 */

require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = preg_replace('#^/api/?#', '', $uri);
$uri = preg_replace('#^index\\.php/?#', '', $uri);
$parts = explode('/', trim($uri, '/'));
$endpoint = $parts[0] ?? '';
$id = $parts[1] ?? null;
$subEndpoint = $parts[2] ?? null;
$handled = false;

try {
    switch ($endpoint) {
        case '':
            $handled = true;
            sendJSON(['status' => 'ok', 'endpoint' => 'api-root', 'timestamp' => date('c')]);
            break;

        case 'products':
            if ($method === 'GET' && !$id) {
                $handled = true;
                require_once __DIR__ . '/products/list.php';
                apiProductsList();
            } elseif ($method === 'GET' && $id) {
                $handled = true;
                require_once __DIR__ . '/products/single.php';
                apiProductsSingle($id);
            } elseif ($method === 'POST' && !$id) {
                $handled = true;
                require_once __DIR__ . '/products/create.php';
                apiProductsCreate();
            } elseif ($method === 'PUT' && $id) {
                $handled = true;
                require_once __DIR__ . '/products/update.php';
                apiProductsUpdate($id);
            } elseif ($method === 'DELETE' && $id) {
                $handled = true;
                require_once __DIR__ . '/products/delete.php';
                apiProductsDelete($id);
            }
            break;

        case 'auth':
            if ($id === 'login' && $method === 'POST') {
                $handled = true;
                require_once __DIR__ . '/auth/login.php';
                apiLogin();
            } elseif ($id === 'register' && $method === 'POST') {
                $handled = true;
                require_once __DIR__ . '/auth/register.php';
                apiRegister();
            } elseif ($id === 'me' && $method === 'GET') {
                $handled = true;
                require_once __DIR__ . '/auth/me.php';
                apiMe();
            } elseif ($id === 'me' && $method === 'PUT') {
                $handled = true;
                require_once __DIR__ . '/auth/update.php';
                apiUpdateMe();
            }
            break;

        case 'orders':
            if ($method === 'GET' && !$id) {
                $handled = true;
                require_once __DIR__ . '/orders/list.php';
                apiOrdersList();
            } elseif ($method === 'GET' && $id === 'my') {
                $handled = true;
                require_once __DIR__ . '/orders/my.php';
                apiOrdersMy();
            } elseif ($method === 'GET' && $id) {
                $handled = true;
                require_once __DIR__ . '/orders/single.php';
                apiOrdersSingle($id);
            } elseif ($method === 'POST' && !$id) {
                $handled = true;
                require_once __DIR__ . '/orders/create.php';
                apiOrdersCreate();
            } elseif ($method === 'POST' && $id && $subEndpoint === 'payment-notify') {
                $handled = true;
                require_once __DIR__ . '/orders/payment-notify.php';
                apiPaymentNotify($id);
            } elseif ($method === 'PUT' && $id && $subEndpoint === 'status') {
                $handled = true;
                require_once __DIR__ . '/orders/status.php';
                apiOrdersStatus($id);
            } elseif ($method === 'PUT' && $id && $subEndpoint === 'tracking') {
                $handled = true;
                require_once __DIR__ . '/orders/tracking.php';
                apiOrdersTracking($id);
            } elseif ($method === 'PUT' && $id && $subEndpoint === 'payment-status') {
                $handled = true;
                require_once __DIR__ . '/orders/payment-status.php';
                apiPaymentStatus($id);
            }
            break;

        case 'admin':
            if ($id === 'stats' && $method === 'GET') {
                $handled = true;
                require_once __DIR__ . '/admin/stats.php';
                apiAdminStats();
            } elseif ($id === 'users' && $method === 'GET') {
                $handled = true;
                require_once __DIR__ . '/admin/users.php';
                apiAdminUsers();
            } elseif ($id === 'categories' && $method === 'GET') {
                $handled = true;
                require_once __DIR__ . '/admin/categories-list.php';
                apiCategoriesList();
            } elseif ($id === 'categories' && $method === 'POST') {
                $handled = true;
                require_once __DIR__ . '/admin/categories-create.php';
                apiCategoriesCreate();
            } elseif ($id === 'categories' && $subEndpoint && $method === 'DELETE') {
                $handled = true;
                require_once __DIR__ . '/admin/categories-delete.php';
                apiCategoriesDelete($subEndpoint);
            } elseif ($id === 'coupons' && $method === 'GET') {
                $handled = true;
                require_once __DIR__ . '/admin/coupons-list.php';
                apiCouponsList();
            } elseif ($id === 'coupons' && $method === 'POST') {
                $handled = true;
                require_once __DIR__ . '/admin/coupons-create.php';
                apiCouponsCreate();
            } elseif ($id === 'coupons' && $subEndpoint && $method === 'PUT') {
                $handled = true;
                require_once __DIR__ . '/admin/coupons-update.php';
                apiCouponsUpdate($subEndpoint);
            } elseif ($id === 'products' && $method === 'POST') {
                $handled = true;
                require_once __DIR__ . '/admin/products-create.php';
                apiAdminProductsCreate();
            } elseif ($id === 'products' && $subEndpoint && $method === 'PUT') {
                $handled = true;
                require_once __DIR__ . '/admin/products-update.php';
                apiAdminProductsUpdate($subEndpoint);
            } elseif ($id === 'products' && $subEndpoint && $method === 'DELETE') {
                $handled = true;
                require_once __DIR__ . '/admin/products-delete.php';
                apiAdminProductsDelete($subEndpoint);
            } elseif ($id === 'posts' && $method === 'POST') {
                $handled = true;
                require_once __DIR__ . '/admin/posts-create.php';
                apiPostsCreate();
            } elseif ($id === 'posts' && $subEndpoint && $method === 'PUT') {
                $handled = true;
                require_once __DIR__ . '/admin/posts-update.php';
                apiPostsUpdate($subEndpoint);
            } elseif ($id === 'posts' && $subEndpoint && $method === 'DELETE') {
                $handled = true;
                require_once __DIR__ . '/admin/posts-delete.php';
                apiPostsDelete($subEndpoint);
            }
            break;

        case 'blog':
            if ($method === 'GET' && !$id) {
                $handled = true;
                require_once __DIR__ . '/blog/list.php';
                apiBlogList();
            } elseif ($id === 'featured' && $method === 'GET') {
                $handled = true;
                require_once __DIR__ . '/blog/featured.php';
                apiBlogFeatured();
            } elseif ($id === 'categories' && $method === 'GET') {
                $handled = true;
                require_once __DIR__ . '/blog/categories.php';
                apiBlogCategories();
            } elseif ($method === 'GET' && $id) {
                $handled = true;
                require_once __DIR__ . '/blog/single.php';
                apiBlogSingle($id);
            }
            break;

        case 'reviews':
            if ($method === 'GET' && $id) {
                $handled = true;
                require_once __DIR__ . '/reviews/list.php';
                apiReviewsList($id);
            } elseif ($method === 'POST' && $id) {
                $handled = true;
                require_once __DIR__ . '/reviews/create.php';
                apiReviewsCreate($id);
            } elseif ($method === 'DELETE' && $id) {
                $handled = true;
                require_once __DIR__ . '/reviews/delete.php';
                apiReviewsDelete($id);
            }
            break;

        case 'contact':
            if ($method === 'GET') {
                $handled = true;
                require_once __DIR__ . '/contact/list.php';
                apiContactList();
            } elseif ($method === 'POST') {
                $handled = true;
                require_once __DIR__ . '/contact/create.php';
                apiContactCreate();
            } elseif ($id && $subEndpoint === 'read' && $method === 'PUT') {
                $handled = true;
                require_once __DIR__ . '/contact/read.php';
                apiContactRead($id);
            }
            break;

        case 'newsletter':
            if ($method === 'POST' && $id === 'subscribe') {
                $handled = true;
                require_once __DIR__ . '/newsletter/subscribe.php';
                apiNewsletterSubscribe();
            } elseif ($method === 'POST' && $id === 'unsubscribe') {
                $handled = true;
                require_once __DIR__ . '/newsletter/unsubscribe.php';
                apiNewsletterUnsubscribe();
            }
            break;

        case 'returns':
            if ($method === 'POST' && !$id) {
                $handled = true;
                require_once __DIR__ . '/returns/create.php';
                apiReturnsCreate();
            } elseif ($id === 'my-returns' && $method === 'GET') {
                $handled = true;
                require_once __DIR__ . '/returns/my.php';
                apiReturnsMy();
            } elseif ($id === 'admin' && $subEndpoint === 'all' && $method === 'GET') {
                $handled = true;
                require_once __DIR__ . '/returns/admin-list.php';
                apiReturnsAdminList();
            } elseif ($id === 'admin' && $subEndpoint && $method === 'PUT') {
                $handled = true;
                require_once __DIR__ . '/returns/admin-update.php';
                apiReturnsAdminUpdate($subEndpoint);
            } elseif ($id === 'admin' && $subEndpoint && $method === 'DELETE') {
                $handled = true;
                require_once __DIR__ . '/returns/admin-delete.php';
                apiReturnsAdminDelete($subEndpoint);
            }
            break;

        case 'settings':
            if ($id === 'banners' && $method === 'GET' && !$subEndpoint) {
                $handled = true;
                require_once __DIR__ . '/settings/banners-list.php';
                apiBannersList();
            } elseif ($id === 'banners' && $method === 'GET' && $subEndpoint === 'all') {
                $handled = true;
                require_once __DIR__ . '/settings/banners-admin.php';
                apiBannersAdmin();
            } elseif ($method === 'POST' && $id === 'banners') {
                $handled = true;
                require_once __DIR__ . '/settings/banners-create.php';
                apiBannersCreate();
            } elseif ($method === 'PUT' && $id === 'banners' && $subEndpoint) {
                $handled = true;
                require_once __DIR__ . '/settings/banners-update.php';
                apiBannersUpdate($subEndpoint);
            } elseif ($method === 'DELETE' && $id === 'banners' && $subEndpoint) {
                $handled = true;
                require_once __DIR__ . '/settings/banners-delete.php';
                apiBannersDelete($subEndpoint);
            } elseif ($id === 'menu' && $method === 'GET') {
                $handled = true;
                require_once __DIR__ . '/settings/menu.php';
                apiMenuGet();
            } elseif ($id === 'menu' && $method === 'PUT') {
                $handled = true;
                require_once __DIR__ . '/settings/menu.php';
                apiMenuUpdate();
            } elseif ($method === 'GET' && (!$id || $id === 'public')) {
                $handled = true;
                require_once __DIR__ . '/settings/public.php';
                apiSettingsPublic();
            } elseif ($method === 'PUT' && (!$id || $id === 'public')) {
                $handled = true;
                require_once __DIR__ . '/settings/update.php';
                apiSettingsUpdate();
            }
            break;

        case 'gallery':
            if ($method === 'GET' && (!$id || $id === 'public')) {
                $handled = true;
                require_once __DIR__ . '/gallery/list.php';
                apiGalleryList();
            } elseif ($method === 'POST' && !$id) {
                $handled = true;
                require_once __DIR__ . '/gallery/create.php';
                apiGalleryCreate();
            } elseif ($id && $method === 'PUT') {
                $handled = true;
                require_once __DIR__ . '/gallery/update.php';
                apiGalleryUpdate($id);
            } elseif ($id && $method === 'DELETE') {
                $handled = true;
                require_once __DIR__ . '/gallery/delete.php';
                apiGalleryDelete($id);
            }
            break;

        case 'tracking':
            if ($method === 'GET') {
                $handled = true;
                require_once __DIR__ . '/tracking/index.php';
                apiTracking();
            }
            break;

        case 'upload':
            if ($method === 'POST') {
                $handled = true;
                require_once __DIR__ . '/upload.php';
                apiUpload();
            }
            break;

        case 'health':
            $handled = true;
            sendJSON(['status' => 'ok', 'timestamp' => date('c')]);
            break;
    }

    if (!$handled) {
        sendJSON(['error' => 'Endpoint bulunamadi'], 404);
    }
} catch (Throwable $e) {
    logError("API Error: " . $e->getMessage());
    sendJSON(['error' => 'Sunucu hatasi'], 500);
}
