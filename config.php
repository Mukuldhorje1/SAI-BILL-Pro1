<?php
$host = "localhost";
$user = "root"; 
$pass = ""; 
$db = "sai_bill_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Razorpay — https://dashboard.razorpay.com/app/keys (use Test keys for development)
$razorpay_key_id = 'rzp_test_SYi5jxh3PdiMXu';
$razorpay_key_secret = 'd6lX4K1anlVqGCk5dsYMplwO';
?>