<?php
/**
 * POST /api/orders/{id}/payment-notify - Payment notification for bank transfer
 * Customer submits bank transfer notification
 */

require_once __DIR__ . '/../config.php';

function apiPaymentNotify($id) {
    try {
        // Authentication required
        $user = auth();
        $body = getBody();

        $db = getDB();
        $orderId = (int)$id;

        // Get order
        $orderStmt = $db->prepare("
            SELECT id, user_id, status, payment_status, payment_method, total
            FROM orders
            WHERE id = ?
        ");
        $orderStmt->execute([$orderId]);
        $order = $orderStmt->fetch();

        if (!$order) {
            sendJSON(['error' => 'Siparis bulunamadi'], 404);
        }

        // Check ownership
        if ($order['user_id'] !== $user['id']) {
            sendJSON(['error' => 'Bu siparis uzerinde islem yapma yetkiniz yok'], 403);
        }

        // Only allow notification for havale (bank transfer) orders
        if ($order['payment_method'] !== 'havale') {
            sendJSON(['error' => 'Bu bildirim sadece havale siparisleri icin gonderilebilir'], 400);
        }

        // Validate required fields
        $senderName = trim($body['sender_name'] ?? '');
        $senderBank = trim($body['sender_bank'] ?? '');
        $amount = (float)($body['amount'] ?? 0);
        $date = trim($body['date'] ?? '');
        $note = trim($body['note'] ?? '');

        if (empty($senderName)) {
            sendJSON(['error' => 'Gonderici adi gereklidir'], 400);
        }

        if (empty($senderBank)) {
            sendJSON(['error' => 'Gonderici banka adi gereklidir'], 400);
        }

        if ($amount <= 0) {
            sendJSON(['error' => 'Gecerli bir tutar giriniz'], 400);
        }

        if (empty($date)) {
            sendJSON(['error' => 'Transfer tarihi gereklidir'], 400);
        }

        // Create payment notification
        $paymentNotification = [
            'sender_name' => $senderName,
            'sender_bank' => $senderBank,
            'amount' => $amount,
            'date' => $date,
            'note' => $note,
            'submitted_at' => date('Y-m-d H:i:s')
        ];

        $setClause = "payment_notification = ?, payment_status = 'notified'";
        if (dbHasColumn($db, 'orders', 'updated_at')) {
            $setClause .= ", updated_at = NOW()";
        }

        $updateStmt = $db->prepare("
            UPDATE orders
            SET $setClause
            WHERE id = ?
        ");
        $updateStmt->execute([
            json_encode($paymentNotification),
            $orderId
        ]);

        sendJSON([
            'success' => true,
            'message' => 'Odeme bildiriminiz alindi. En kisa surede kontrol edilecektir.'
        ]);

    } catch (Exception $e) {
        logError("Payment Notify Error: " . $e->getMessage());
        sendJSON(['error' => 'Odeme bildirimi gonderilirken hata olustu'], 500);
    }
}
