<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// التأكد من وجود المستخدم ودوره
if (!isset($_SESSION['user']) || $_SESSION['user']['Role'] !== "USER") {
    header("Location: ../login.php");
    exit;
}

$user = $_SESSION['user'];

// لو المستخدم ضغط على logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
   
    header("Location: ../login.php");
    exit;
}
?>

<style>
    .nav-link {
  color:black !important; /* أبيض */
}
</style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

<header>
        <div class="container header-container">
            <div class="logo"><span>سلف</span>ني</div>
            <nav>
                <ul class="nav-links" id="navLinks">
                    <li><a href="userhome.php" class="nav-link active">الرئيسية</a></li>
                    <li><a href="http://localhost/Salefny/user/user-all-books.php" class="nav-link">المكتبة</a></li>
                    <li><a href="http://localhost/Salefny/user/profile.php" class="nav-link">اتصل بنا</a></li>
                    <li><a href="http://localhost/Salefny/user/rules-user.php" class="nav-link">الشروط والاحكام</a></li>

                </ul>
            </nav>
            <div class="user-menu">
                <div class="user-info" id="userInfo">
                    <div class="user-avatar">أ</div>
                    <span class="user-name"><?php echo htmlspecialchars($user['Fname']); ?></span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-menu p-3" id="dropdownMenu" style="min-width: 180px;">
    <a href="profile.php" class="dropdown-item"><i class="fas fa-user"></i> الملف الشخصي</a>

    <form  method="GET">
        <button type="submit" name="logout" class="btn btn-danger w-100 mt-2">
            <i class="fas fa-sign-out-alt"></i> 
            تسجيل الخروج
        </button>
    </form>
</div>
            </div>
            <button class="mobile-menu-toggle" id="mobileMenuToggle"><i class="fa fa-bars"></i></button>
        </div>
    </header>