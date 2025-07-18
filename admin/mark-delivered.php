<?php
session_start();

// تحقق من الجلسة
if (!isset($_SESSION['admin']) || $_SESSION['admin']['role'] !== "admin") {
    header("Location: ../login.php");
    exit;
}

$admin = $_SESSION['admin'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'])) {
    $requestId = intval($_POST['request_id']);

    try {
        $pdo = new PDO("mysql:host=localhost;dbname=salefny;charset=utf8mb4", "root", "", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        $stmt = $pdo->prepare("SELECT * FROM book_requests WHERE id = ? AND admin_id = ? AND status = 'ready_for_delivery'");
        $stmt->execute([$requestId, $admin['id']]);
        $request = $stmt->fetch();

        if ($request) {
            $update = $pdo->prepare("UPDATE book_requests SET status = 'in_progress' WHERE id = ?");
            $update->execute([$requestId]);

            $log = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, target_id, target_type) VALUES (?, ?, ?, ?)");
            $log->execute([$admin['id'], 'تسليم كتاب', $requestId, 'book_request']);
        }

    } catch (PDOException $e) {
        die("خطأ في قاعدة البيانات: " . $e->getMessage());
    }
}

header("Location: managUsers.php");
exit;
