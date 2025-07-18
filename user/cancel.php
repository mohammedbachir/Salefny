<?php
session_start();

// منع التخزين المؤقت للصفحة
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 1 Jan 2000 00:00:00 GMT");

if (!isset($_SESSION['user']) || $_SESSION['user']['Role'] !== "USER") {
    header("Location: ../login.php");
    exit;
}

$user = $_SESSION['user'];
$isVerified = $user['isVerified']; 
if(isset($_POST['update'])){
    $username = "root";
    $password = "";
    $database = new PDO("mysql:host=localhost;dbname=salefny;", $username, $password);
   }
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تم إلغاء العملية</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
</head>
<body dir="rtl" style="background-color:#f8f9fa;">
    <div class="container text-center mt-5">
        <div class="alert alert-danger">
            <h1>❌ تم إلغاء الدفع</h1>
            <p>لم يتم إتمام عملية الدفع، يمكنك المحاولة مرة أخرى.</p>
            <a href="userhome.php" class="btn btn-danger">العودة إلى لوحة التحكم</a>
        </div>
    </div>
</body>
</html>
