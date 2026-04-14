<?php
include 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['bill_no']) || !isset($data['total']) || !isset($data['cashier'])) {
    echo json_encode(["status" => "error", "message" => "Missing required data"]);
    exit;
}

$bill_no = $data['bill_no'];
$total = floatval($data['total']);
$cashier = $data['cashier'];
$subtotal = isset($data['subtotal']) ? floatval($data['subtotal']) : $total * 0.8475; // 18% GST reverse calc
$gst = $total - $subtotal;
$discount = isset($data['discount']) ? floatval($data['discount']) : 0;
$payment_method = $data['payment_method'] ?? 'Cash';
$customer = $data['customer'] ?? 'Walk-in Customer';

// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare("INSERT INTO sales (bill_no, date, subtotal, gst, discount, total, cashier, payment_method, customer) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sdddsdss", $bill_no, $subtotal, $gst, $discount, $total, $cashier, $payment_method, $customer);

if ($stmt->execute()) {
    $sale_id = $conn->insert_id;
    echo json_encode([
        "status" => "success", 
        "message" => "Bill #$bill_no saved", 
        "sale_id" => $sale_id
    ]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}

$stmt->close();
$conn->close();
?>
