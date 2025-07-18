<?php


try {
    $pdo = new PDO("mysql:host=localhost;dbname=salefny", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT COUNT(*) FROM verification WHERE status = 0");
    $pendingVerifications = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM messages WHERE status = 'new'");
    $unreadMessages = $stmt->fetchColumn();

} catch (PDOException $e) {
    die("فشل الاتصال: " . $e->getMessage());
}

// تسجيل الخروج
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: Sat, 1 Jan 2000 00:00:00 GMT");
    header("Location: ../login.php");
    exit;
}

?>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="pay-manage.php">
            <img style="border-radius:10px;" src="http://localhost/Salefny/img/favicon.png" width="50" height="50" alt="Logo">
        </a>
        <div class="navbar-collapse collapse w-100 order-3 dual-collapse2">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <button class="dropbtn"><b><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
                    </svg></b> <?php echo htmlspecialchars($admin['name']); ?></button>
                    <div class="dropdown-content">
                       
                        <a href="pay-manage.php">الصفحة الرئيسية</a>
                         <form method="GET">
                        <button type="submit" name="logout" class="btn btn-danger w-100 mt-2">
                            <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
                        </button>
                    </form>
                    </div>
                </li>
            </ul>
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="btn btn-outline-warning nav-link" href="addAdmin.php"><i class="fas fa-book"></i> اضافة أدمين</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline nav-link" href="users.php"><i class="fas fa-users"></i> قائمة المستخدمين</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline nav-link" href="manageAdmin.php"><i class="fas fa-book-reader"></i> ادارة الادمينات</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline nav-link" href="siteManage.php"><i class="fas fa-book-reader"></i> ادارة الموقع</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline nav-link" href="pay-manage.php"><i class="fas fa-book-reader"></i> الاموال و الدفوعات</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline nav-link" href="inbox.php"><i class="fas fa-book-reader"></i> الرسائل الواردة </a>
                </li>
                
            </ul>
        </div>
    </nav>