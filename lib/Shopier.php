<?php

define('SHOPIER_API_BASE', 'https://api.shopier.com/v1');

function shopierApiGet(string $path): ?array
{
    $ch = curl_init(SHOPIER_API_BASE . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . SHOPIER_PAT,
            'Accept: application/json',
        ],
        CURLOPT_TIMEOUT => 15,
    ]);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $status < 200 || $status >= 300) {
        return null;
    }

    $data = json_decode($response, true);
    return is_array($data) ? $data : null;
}

function grantPremiumDays(int $userId, int $days): void
{
    $stmt = getPDO()->prepare(
        'UPDATE users
         SET is_premium = 1,
             premium_expires_at = DATE_ADD(GREATEST(COALESCE(premium_expires_at, NOW()), NOW()), INTERVAL :days DAY)
         WHERE id = :id'
    );
    $stmt->execute(['days' => $days, 'id' => $userId]);
}

function isShopierOrderProcessed(string $orderId): bool
{
    $stmt = getPDO()->prepare('SELECT 1 FROM shopier_processed_orders WHERE order_id = :order_id');
    $stmt->execute(['order_id' => $orderId]);
    return (bool) $stmt->fetchColumn();
}

function markShopierOrderProcessed(string $orderId, int $userId): void
{
    $stmt = getPDO()->prepare(
        'INSERT IGNORE INTO shopier_processed_orders (order_id, user_id) VALUES (:order_id, :user_id)'
    );
    $stmt->execute(['order_id' => $orderId, 'user_id' => $userId]);
}

function processShopierOrderPayload(array $order): array
{
    if (($order['paymentStatus'] ?? null) !== 'paid') {
        return ['status' => 'ignored'];
    }

    $productMatches = false;
    foreach ($order['lineItems'] ?? [] as $item) {
        $productId = (string) ($item['productId'] ?? $item['id'] ?? '');
        if ($productId !== '' && $productId === (string) SHOPIER_PREMIUM_PRODUCT_ID) {
            $productMatches = true;
            break;
        }
    }
    if (!$productMatches) {
        return ['status' => 'ignored'];
    }

    $orderId = (string) ($order['id'] ?? '');
    if ($orderId === '' || isShopierOrderProcessed($orderId)) {
        return ['status' => 'duplicate'];
    }

    $email = $order['billingInfo']['email'] ?? $order['shippingInfo']['email'] ?? null;
    $user = $email ? findUserByEmail($email) : null;
    if (!$user) {
        return ['status' => 'no_match', 'email' => $email];
    }

    grantPremiumDays((int) $user['id'], PREMIUM_DURATION_DAYS);
    markShopierOrderProcessed($orderId, (int) $user['id']);
    return ['status' => 'granted', 'user_id' => (int) $user['id']];
}
