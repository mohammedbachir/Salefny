<?php
// الاتصال بقاعدة البيانات
try {
    $pdo = new PDO("mysql:host=localhost;dbname=salefny", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // جلب عدد الطلبات غير المعالجة
    $stmt = $pdo->query("SELECT COUNT(*) FROM verification WHERE status = 0");
    $pendingVerifications = $stmt->fetchColumn();

} catch (PDOException $e) {
    die("فشل الاتصال: " . $e->getMessage());
}
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
   
    exit;
}
?>
<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=salefny", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // عداد طلبات الدعم الغير مجابة
    $pendingMessages = $pdo->query("
        SELECT COUNT(*) FROM support_messages WHERE status = 'pending'
    ")->fetchColumn();

} catch (PDOException $e) {
    die("فشل الاتصال: " . $e->getMessage());
}
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
        <!-- Logo on the left -->
        <a class="navbar-brand" href="http://localhost/Salefny/admin/adminPage.php">
            <img style="border-radius:10px;" src="http://localhost/Salefny_Website/homePage/img/icon.jpg" width="50" height="50" alt="Logo">
        </a>
        <div class="navbar-collapse collapse w-100 order-3 dual-collapse2">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <button class="dropbtn"><b><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
                    </svg></b> <?php echo htmlspecialchars($admin['name']); ?> </button>
                    <div class="dropdown-content">
                        <a href="http://localhost/Salefny/admin/contact.php">التواصل مع السوبر ادمين</a>
                        <a href="http://localhost/Salefny/admin/adminPage.php">الصفحة الرئيسية</a>
                       <a href="?logout=1" class="btn btn-danger w-100 mt-2">
    <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
</a>
                    </div>
                </li>
            </ul>
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="btn btn-outline-warning nav-link" href="http://localhost/Salefny/admin/add-book.php"><i class="fas fa-book"></i> اضافة كتاب</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline nav-link" href="http://localhost/Salefny/admin/managUsers.php"><i class="fas fa-users"></i> طلبات الاستعارة الجاهزة</a>
                </li>
               
</a>
               
            </ul>
        </div>
    </nav>