<?php
// ربط قاعدة البيانات
$pdo = new PDO("mysql:host=localhost;dbname=salefny;charset=utf8", "root", "");

// استدعاء ملف الإيميل
require_once '../mailing.php';
global $mail;

// Chargily Secret Key
$apiSecretKey = 'test_sk_cJYQnkzvQld4EwqZnKDXdP2pV3BqaxmdSqr2OG6L'; // 👈 بدله بمفتاحك

// استلام التوقيع والبيانات
$signature = $_SERVER['HTTP_SIGNATURE'] ?? null;
$payload = file_get_contents("php://input");

// تحقق من وجود التوقيع
if (!$signature) {
    http_response_code(400);
    exit;
}

// تحقق من صحة التوقيع
$computedSignature = hash_hmac('sha256', $payload, $apiSecretKey);
if (!hash_equals($signature, $computedSignature)) {
    http_response_code(401);
    exit;
}

// فك البيانات
$data = json_decode($payload, true);

// تحقق من نوع الحدث
if (!isset($data['type']) || $data['type'] !== 'checkout.paid') {
    http_response_code(200);
    exit;
}

// -------------------- استخراج بيانات الدفع --------------------
$checkout = $data['data'];

$amount = $checkout['amount'];
$transaction_id = $checkout['id'];
$method = $checkout['payment_method'] ?? 'unknown';

$metadata = $checkout['metadata'] ?? [];
$plan_id = (int)($metadata['plan_id'] ?? 1);
$user_id = (int)($metadata['user_id'] ?? 0);

// -------------------- جلب معلومات المستخدم --------------------
$stmt = $pdo->prepare("SELECT Fname, Email FROM users WHERE Id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    echo json_encode(['status' => 'user_not_found']);
    exit;
}

$fname = $user['Fname'];
$client_email = $user['Email'];

// -------------------- تسجيل الدفع --------------------
$stmt = $pdo->prepare("INSERT INTO payments (user_id, plan_id, amount, payment_method, transaction_id, status) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$user_id, $plan_id, $amount, $method, $transaction_id, 'completed']);
$payment_id = $pdo->lastInsertId();

// -------------------- تسجيل الاشتراك --------------------
$start = date("Y-m-d");
$end = date("Y-m-d", strtotime("+30 days"));

$stmt = $pdo->prepare("INSERT INTO subscriptions (user_id, plan_id, payment_id, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$user_id, $plan_id, $payment_id, $start, $end, 'active']);

// -------------------- تحديث حالة المستخدم --------------------
$stmt = $pdo->prepare("UPDATE users SET is_subscribed = 1 WHERE Id = ?");
$stmt->execute([$user_id]);

// -------------------- إرسال الإيميل --------------------
$mail->addAddress($client_email);
$mail->Subject = "تم دفع اشتراكك بنجاح  📚";
$mail->isHTML(true);
$mail->Body = '
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: "Tahoma", sans-serif;
            background-color: #f9f9f9;
            direction: rtl;
            color: #333;
        }
        .container {
            background-color: #ffffff;
            margin: 0 auto;
            padding: 30px;
            max-width: 600px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            height: 80px;
        }
        .title {
            color: #2c3e50;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .message {
            font-size: 16px;
            line-height: 1.8;
        }
        .cta {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 25px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }
        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #888;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="https://salefny.com/img/favicon.jpg" alt="شعار سلفني">
        </div>
        <div class="title">مرحبًا بك في سلفني، ' . htmlspecialchars($fname) . ' 👋</div>
        <div class="message">
            شكرًا لاشتراكك معنا في منصة <strong>سلفني</strong>، وجهتك الجديدة لاستكشاف الكتب والروايات 🎓📚.
        </div>
        <div class="message">
            تم تفعيل اشتراكك لمدة <strong>30 يوم</strong>. يمكنك الآن استعارة الكتب من خطتك، كتاب واحد فقط في كل مرة.
        </div>
        <a href="https://route-vocational-heritage-summer.trycloudflare.com/Salefny/user/userhome.php" class="cta">الدخول إلى لوحة التحكم</a>
        <div class="footer">
            هذا البريد مرسل تلقائيًا من منصة سلفني، لا ترد عليه. <br>
            إذا لم تكن أنت من قام بالدفع، يرجى تجاهل هذا البريد أو مراسلتنا.
        </div>
    </div>
</body>
</html>';

try {
    $mail->send();
} catch (Exception $e) {
    file_put_contents("mail_error_log.txt", $mail->ErrorInfo);
}

// ✅ رد نهائي لشارجيلي
http_response_code(200);
echo json_encode(['status' => 'success']);
?>