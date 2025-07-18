<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

// ربط قاعدة البيانات
$pdo = new PDO("mysql:host=localhost;dbname=salefny;charset=utf8", "root", "");

// جلب بيانات المستخدم
$user = $_SESSION['user'];
$user_email = $user['Email'];
$user_id = $user['Id'];
$plan_id = isset($_GET['plan_id']) ? (int)$_GET['plan_id'] : 1;

// جلب تفاصيل الباقة من قاعدة البيانات
$stmt = $pdo->prepare("SELECT * FROM plans WHERE id = ?");
$stmt->execute([$plan_id]);
$plan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$plan) {
    die("الباقة غير موجودة");
}

$amount = $plan['price'] * 100; // السعر بـ سنتيم لأن Chargily تستعمل السنتيم
$plan_name = $plan['name'];

// إعداد الاتصال بـ Chargily Checkout V2
$api_key = "test_sk_cJYQnkzvQld4EwqZnKDXdP2pV3BqaxmdSqr2OG6L"; // 🔁 عوّض بـ مفتاحك
$success_url = "https://rings-poverty-stainless-travelers.trycloudflare.com/Salefny/user/success.php";

// إنشاء جلسة الدفع
$data = [
    "amount" => (int)$amount,
    "currency" => "dzd",
    "success_url" => $success_url,
    "description" => "الاشتراك في الباقة: $plan_name",
    "metadata" => [
        "user_id" => $user_id,
        "plan_id" => $plan_id
    ]
];


$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://pay.chargily.net/test/api/v2/checkouts",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $api_key",
        "Content-Type: application/json"
    ],
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if (isset($result['checkout_url'])) {
    header("Location: " . $result['checkout_url']);
    exit;
} else {
    echo "خطأ أثناء إنشاء جلسة الدفع:";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
}
