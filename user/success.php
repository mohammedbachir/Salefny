<?php
session_start();

// منع التخزين المؤقت للصفحة
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 1 Jan 2000 00:00:00 GMT");

// التحقق من وجود الجلسة وصلاحية المستخدم
if (!isset($_SESSION['user']) || $_SESSION['user']['Role'] !== "USER") {
    header("Location: ../login.php");
    exit;
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تم الدفع بنجاح</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
</head>
<body dir="rtl" style="background-color:#f8f9fa;">
    <div class="container text-center mt-5">
        <div class="alert alert-success">
            <h1>✅ تم الدفع بنجاح!</h1>
            <p>شكراً لاشتراكك في موقع سلفني.</p>
            <a href="userhome.php" class="btn btn-success">العودة إلى لوحة التحكم</a>
        </div>
    </div>
</body>
</html>
