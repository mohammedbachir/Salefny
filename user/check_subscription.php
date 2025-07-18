<?php

// تأكد من أن المستخدم موجود
if (!isset($_SESSION['user'])) {
    exit; // لا يوجد مستخدم
}

$user_id = $_SESSION['user']['Id']; 

// اتصال بقاعدة البيانات
$db = new PDO("mysql:host=localhost;dbname=salefny;charset=utf8mb4", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$today = date("Y-m-d");

$stmt = $db->prepare("SELECT id, end_date FROM subscriptions WHERE user_id = ? AND status = 'active' ORDER BY end_date DESC LIMIT 1");
$stmt->execute([$user_id]);
$subscription = $stmt->fetch();

if ($subscription && $subscription['end_date'] < $today) {
    // الاشتراك منتهي، نقوم بتحديث حالته
    $update = $db->prepare("UPDATE subscriptions SET status = 'expired' WHERE id = ?");
    $update->execute([$subscription['id']]);
}
