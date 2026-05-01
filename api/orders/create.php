<?php
/**
 * POST /api/orders - Create new order
 */

require_once __DIR__ . '/../config.php';

function apiOrdersCreate() {
    try {
        $user = auth();
        $body = getBody();

        $db = getDB();
        $userId = $user['id'];

        $items = $body['items'] ?? [];
        if (empty($items) || !is_array($items)) {
            sendJSON(['error' => 'Sepette urun bulunmamaktadir'], 400);
        }

        $shippingAddress = $body['shipping_address'] ?? null;
        $billingAddress = $body['billing_address'] ?? $shippingAddress;
        $paymentMethod = $body['payment_method'] ?? 'cod';
        $notes = $body['notes'] ?? null;
        $couponCode = $body['coupon_code'] ?? null;

        if (empty($shippingAddress)) {
            sendJSON(['error' => 'Teslimat adresi gereklidir'], 400);
        }

        $subtotal = 0;
        $processedItems = [];

        foreach ($items as $item) {
            $productId = (int)($item['product_id'] ?? 0);
            $quantity = (int)($item['quantity'] ?? 1);
            $variants = $item['variants'] ?? null;

            if ($productId <= 0 || $quantity <= 0) {
                sendJSON(['error' => 'Gecersiz urun bilgisi'], 400);
            }

            $productStmt = $db->prepare("
                SELECT id, name, price, stock, active, images
                FROM products
                WHERE id = ?
            ");
            $productStmt->execute([$productId]);
            $product = $productStmt->fetch();

            if (!$product) {
                sendJSON(['error' => 'Urun bulunamadi: ' . $productId], 404);
            }

            if ($product['active'] != 1) {
                sendJSON(['error' => 'Urun aktif degil: ' . $product['name']], 400);
            }

            if ($product['stock'] < $quantity) {
                sendJSON(['error' => 'Yetersiz stok: ' . $product['name'] . ' (Mevcut: ' . $product['stock'] . ')'], 400);
            }

            $itemTotal = $product['price'] * $quantity;
            $subtotal += $itemTotal;

            $processedItems[] = [
                'product_id' => $productId,
                'product_name' => $product['name'],
                'quantity' => $quantity,
                'price' => $product['price'],
                'total' => $itemTotal,
                'variants' => $variants
            ];
        }

        $shippingFee = $subtotal >= 500 ? 0 : 29.90;
        $discount = 0;
        $couponId = null;

        if (!empty($couponCode)) {
            $couponStmt = $db->prepare("
                SELECT id, code, discount_type, discount_value, min_order_amount, max_uses, used_count, valid_from, valid_until, active
                FROM coupons
                WHERE code = ? AND active = 1
            ");
            $couponStmt->execute([strtoupper($couponCode)]);
            $coupon = $couponStmt->fetch();

            if ($coupon) {
                $validUntilOk = empty($coupon['valid_until']) || $coupon['valid_until'] > date('Y-m-d H:i:s');
                $validFromOk = empty($coupon['valid_from']) || $coupon['valid_from'] <= date('Y-m-d H:i:s');
                $usesOk = empty($coupon['max_uses']) || $coupon['used_count'] < $coupon['max_uses'];

                if ($validUntilOk && $validFromOk && $usesOk) {
                    if ($subtotal >= ($coupon['min_order_amount'] ?? 0)) {
                        if ($coupon['discount_type'] === 'percent') {
                            $discount = ($subtotal * $coupon['discount_value']) / 100;
                            $discount = min($discount, $subtotal * 0.5);
                        } else {
                            $discount = $coupon['discount_value'];
                        }
                        $couponId = $coupon['id'];
                    }
                }
            }
        }

        $total = $subtotal + $shippingFee - $discount;
        if ($total < 0) {
            $total = 0;
        }

        $paymentStatus = ($paymentMethod === 'havale') ? 'pending' : 'paid';
        $paymentNotification = null;

        $db->beginTransaction();

        try {
            $insertOrder = $db->prepare("
                INSERT INTO orders (
                    user_id, status, payment_method, payment_status, total,
                    shipping_address, billing_address, tracking_number, notes, payment_notification, created_at
                ) VALUES (
                    ?, 'pending', ?, ?, ?,
                    ?, ?, '', ?, ?, NOW()
                )
            ");
            $insertOrder->execute([
                $userId,
                $paymentMethod,
                $paymentStatus,
                $total,
                json_encode($shippingAddress, JSON_UNESCAPED_UNICODE),
                json_encode($billingAddress, JSON_UNESCAPED_UNICODE),
                $notes,
                $paymentNotification ? json_encode($paymentNotification, JSON_UNESCAPED_UNICODE) : null
            ]);

            $orderId = $db->lastInsertId();

            $insertItem = $db->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price, variants)
                VALUES (?, ?, ?, ?, ?)
            ");

            foreach ($processedItems as $item) {
                $insertItem->execute([
                    $orderId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price'],
                    $item['variants'] ? json_encode($item['variants'], JSON_UNESCAPED_UNICODE) : null
                ]);
            }

            if ($couponId) {
                $updateCoupon = $db->prepare("
                    UPDATE coupons SET used_count = used_count + 1 WHERE id = ?
                ");
                $updateCoupon->execute([$couponId]);
            }

            $updateStock = $db->prepare("
                UPDATE products SET stock = stock - ? WHERE id = ?
            ");

            foreach ($processedItems as $item) {
                $updateStock->execute([$item['quantity'], $item['product_id']]);
            }

            $db->commit();

            sendJSON([
                'orderId' => (int)$orderId,
                'message' => 'Siparisiniz basariyla olusturuldu',
                'total' => round($total, 2)
            ], 201);

        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

    } catch (Exception $e) {
        logError("Orders Create Error: " . $e->getMessage());
        sendJSON(['error' => 'Siparis olusturulurken hata olustu'], 500);
    }
}
