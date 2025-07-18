<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 1 Jan 2000 00:00:00 GMT");

// التحقق من الجلسة وصلاحية الدخول للسوبر أدمين فقط
if (!isset($_SESSION['admin']) || $_SESSION['admin']['role'] !== "admin") {
    header("Location: ../login.php");
    exit;
}

$admin = $_SESSION['admin'];
try {
    $pdo = new PDO("mysql:host=localhost;dbname=salefny", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = $pdo->prepare("DELETE FROM support_messages WHERE id = ?");
        $stmt->execute([$id]);
    }

    header("Location: inbox.php");
    exit;

} catch (PDOException $e) {
    die("خطأ: " . $e->getMessage());
}
?>
