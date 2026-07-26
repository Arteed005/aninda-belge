<?php
require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);

if (!is_array($body)) {
    http_response_code(200);
    exit;
}

$orderId = $body['id'] ?? $body['orderId'] ?? ($body['data']['id'] ?? null) ?? ($body['order']['id'] ?? null);

if ($orderId === null) {
    error_log('Shopier webhook: sipariş ID bulunamadı. Payload: ' . $raw);
    http_response_code(200);
    exit;
}

$order = shopierApiGet('/orders/' . urlencode((string) $orderId));

if ($order === null) {
    error_log('Shopier webhook: sipariş sorgulanamadı, order_id=' . $orderId);
    http_response_code(200);
    exit;
}

$result = processShopierOrderPayload($order);

if ($result['status'] === 'no_match') {
    $email = $order['billingInfo']['email'] ?? $order['shippingInfo']['email'] ?? '-';
    error_log('Shopier webhook: kullanıcı eşleşmedi, order_id=' . $orderId . ' email=' . $email);
}

http_response_code(200);
