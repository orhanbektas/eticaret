<?php
// GET /api/admin/stats

function apiAdminStats() {
    try {
        adminAuth();
        $db = getDB();

        // Total orders
        $stmt = $db->query("SELECT COUNT(*) as total FROM orders");
        $totalOrders = (int)$stmt->fetch()['total'];

        // Total revenue (exclude cancelled)
        $stmt = $db->query("SELECT COALESCE(SUM(total), 0) as revenue FROM orders WHERE status != 'cancelled'");
        $totalRevenue = (float)$stmt->fetch()['revenue'];

        // Total active products
        $stmt = $db->query("SELECT COUNT(*) as total FROM products WHERE active = 1");
        $totalProducts = (int)$stmt->fetch()['total'];

        // Total users (role=user)
        $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
        $totalUsers = (int)$stmt->fetch()['total'];

        // Low stock products (stock < 10, active)
        $stmt = $db->query("SELECT id, name, stock FROM products WHERE stock < 10 AND active = 1 ORDER BY stock ASC LIMIT 5");
        $lowStock = $stmt->fetchAll();

        sendJSON([
            'totalOrders' => $totalOrders,
            'totalRevenue' => $totalRevenue,
            'totalProducts' => $totalProducts,
            'totalUsers' => $totalUsers,
            'lowStock' => $lowStock
        ]);
    } catch (PDOException $e) {
        logError("Admin stats error: " . $e->getMessage());
        sendJSON(['error' => 'İstatistikler alınamadı'], 500);
    } catch (Exception $e) {
        logError("Admin stats error: " . $e->getMessage());
        sendJSON(['error' => 'Sunucu hatası'], 500);
    }
}
