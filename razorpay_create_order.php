<?php
/**
 * Creates a Razorpay order (server-side). Amount is in paise (₹1 = 100).
 * POST JSON: { "amount": 105000 }  (₹1050.00)
 */
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Use POST']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$amount = isset($input['amount']) ? (int) $input['amount'] : 0;

if ($amount < 100) {
    echo json_encode(['status' => 'error', 'message' => 'Amount must be at least ₹1 (100 paise)']);
    exit;
}

if (empty($razorpay_key_id) || empty($razorpay_key_secret)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Configure $razorpay_key_id and $razorpay_key_secret in config.php (Razorpay dashboard → API keys)',
    ]);
    exit;
}

$payload = json_encode([
    'amount'   => $amount,
    'currency' => 'INR',
    'receipt'  => 'bill_' . time() . '_' . mt_rand(1000, 9999),
]);

$ch = curl_init('https://api.razorpay.com/v1/orders');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_USERPWD        => $razorpay_key_id . ':' . $razorpay_key_secret,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
]);
$response = curl_exec($ch);
$httpcode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpcode !== 200) {
    $err = json_decode($response, true);
    $msg = $err['error']['description'] ?? $err['message'] ?? ('HTTP ' . $httpcode);
    echo json_encode(['status' => 'error', 'message' => $msg]);
    exit;
}

$data = json_decode($response, true);
if (empty($data['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Razorpay response']);
    exit;
}

echo json_encode([
    'status'   => 'success',
    'key_id'   => $razorpay_key_id,
    'order_id' => $data['id'],
    'amount'   => (int) $data['amount'],
]);
