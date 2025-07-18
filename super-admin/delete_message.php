<?php
session_start();

if (!isset($_SESSION['admin']) || $_SESSION['admin']['role'] !== "super-admin") {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    try {
        $pdo = new PDO("mysql:host=localhost;dbname=salefny", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
        $stmt->execute([$id]);

        header("Location: inbox.php?deleted=1");
        exit;
    } catch (PDOException $e) {
        die("حدث خطأ: " . $e->getMessage());
    }
} else {
    header("Location: inbox.php");
    exit;
}
?>
