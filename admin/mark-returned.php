<?php
session_start();

if (!isset($_SESSION['admin']) || $_SESSION['admin']['role'] !== 'admin') {
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

        // التحقق من وجود الطلب الصحيح
        $stmt = $pdo->prepare("SELECT * FROM book_requests WHERE id = ? AND admin_id = ? AND status = 'in_progress'");
        $stmt->execute([$requestId, $admin['id']]);
        $current = $stmt->fetch();

        if ($current) {
            $userId = $current['user_id'];
            $currentOrder = $current['order_number'];

            $update = $pdo->prepare("UPDATE book_requests SET status = 'returned' WHERE id = ?");
            $update->execute([$requestId]);

            $log = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, target_id, target_type) VALUES (?, ?, ?, ?)");
            $log->execute([$admin['id'], 'إرجاع كتاب', $requestId, 'book_request']);

            $next = $pdo->prepare("
                SELECT id FROM book_requests 
                WHERE user_id = ? AND order_number > ? AND status = 'confirmed'
                ORDER BY order_number ASC LIMIT 1
            ");
            $next->execute([$userId, $currentOrder]);
            $nextRequest = $next->fetch();

            if ($nextRequest) {
                $updateNext = $pdo->prepare("UPDATE book_requests SET status = 'ready_for_delivery' WHERE id = ?");
                $updateNext->execute([$nextRequest['id']]);
            }
        }

    } catch (PDOException $e) {
        die("خطأ: " . $e->getMessage());
    }
}

header("Location: managUsers.php");
exit;
