<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 1 Jan 2000 00:00:00 GMT");

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    die("");
}

$user = $_SESSION['user'];

if ($user['Role'] !== "USER") {
    header("Location: login.php");
    die("");
}

$conn = new PDO("mysql:host=localhost;dbname=salefny;charset=utf8mb4", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$userId = $user['Id'];
$isSubscribed = false;
$userPlanId = null;

$stmt = $conn->prepare("SELECT * FROM subscriptions WHERE user_id = ? AND status = 'active' AND NOW() BETWEEN start_date AND end_date");
$stmt->execute([$userId]);
$sub = $stmt->fetch();

if ($sub) {
    $isSubscribed = true;
    $userPlanId = $sub['plan_id'];
}

if ($isSubscribed) {
    $stmt = $conn->prepare("SELECT * FROM books WHERE plan_id = ?");
    $stmt->execute([$userPlanId]);
} else {
    $stmt = $conn->prepare("SELECT * FROM books");
    $stmt->execute();
}
$books = $stmt->fetchAll();
?>

<?php
// عدد الكتب
$totalBooksStmt = $conn->query("SELECT COUNT(*) AS total_books FROM books");
$totalBooks = $totalBooksStmt->fetch()['total_books'];

// عدد التصنيفات
$totalCategoriesStmt = $conn->query("SELECT COUNT(DISTINCT category_id) AS total_categories FROM books");
$totalCategories = $totalCategoriesStmt->fetch()['total_categories'];

// عدد المؤلفين (نحسبهم من حقل author في جدول الكتب)
$totalAuthorsStmt = $conn->query("SELECT COUNT(DISTINCT author) AS total_authors FROM books");
$totalAuthors = $totalAuthorsStmt->fetch()['total_authors'];
?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'search') {
    $query = trim($_POST['query'] ?? '');

    $sql = "SELECT * FROM books WHERE title LIKE :q OR author LIKE :q OR description LIKE :q";
    $params = [":q" => "%$query%"];

    if ($isSubscribed) {
        $sql .= " AND plan_id = :pid";
        $params[":pid"] = $userPlanId;
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $books = $stmt->fetchAll();

    if (!$books) {
        echo "<p>لا توجد نتائج</p>";
        exit;
    }

    foreach ($books as $book) {
        $badgeClass = ['package-bronze', 'package-silver', 'package-gold'][$book['plan_id'] - 1];
        $badgeLabel = ['برونزية', 'فضية', 'ذهبية'][$book['plan_id'] - 1];
        ?>
        <div class="book-card">
            <img src="data:image/jpeg;base64,<?= base64_encode($book['cover_image']) ?>" alt="غلاف الكتاب" class="book-cover">
            <div class="book-info">
                <div class="book-package <?= $badgeClass ?>"><?= $badgeLabel ?></div>
                <h3 class="book-title"><?= htmlspecialchars($book['title']) ?></h3>
                <p class="book-author"><?= htmlspecialchars($book['author']) ?></p>
                <div class="book-actions">
                    <a href="book-info.php?id=<?= $book['id'] ?>" target="_blank"><button class="btn btn-sm">عرض</button></a>
                    <button class="btn btn-outline btn-sm"><i class="fas fa-heart"></i></button>
                </div>
            </div>
        </div>
        <?php
    }
include_once('check_subscription.php');

    exit; // نوقف تنفيذ باقي الصفحة
}

?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>مكتبة الكتب الكاملة - سلفني</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="../css/uabks.css">
   <link rel="shortcut icon" href="../img/favicon.png" type="image/x-icon">
</head>
<body>
    <?php require_once '../templates/unave.php';?>


<section class="page-header">
    <div class="container">
        <h1 class="page-title">مكتبة الكتب الكاملة</h1>
        <p class="page-subtitle">اكتشف مجموعة ضخمة من الكتب في جميع المجالات والتخصصات</p>
        
        <div class="stats-container">
            <div class="stat-item">
                <span class="stat-number"><?= number_format($totalBooks) ?></span>
                <span class="stat-label">كتاب متاح</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?= number_format($totalCategories) ?></span>
                <span class="stat-label">تصنيف مختلف</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?= number_format($totalAuthors) ?></span>
                <span class="stat-label">مؤلف</span>
            </div>
        </div>
    </div>
</section>


<section class="search-filter-section">
  <div class="container">
    <div class="search-bar">
      <input type="text" id="searchInput" class="search-input" placeholder="ابحث عن الكتب بالعنوان، المؤلف، أو الكلمات المفتاحية...">
      <button id="searchBtn" class="search-btn"><i class="fas fa-search"></i> بحث</button>
    </div>
    <div class="books-grid" id="booksGrid">
    <!-- هنا يتم عرض نتائج البحث -->
</div>

  </div>
</section>




        <?php include_once('../templates/footer.php');?>
        <script>
// يمنع الرجوع للصفحة بعد تسجيل الخروج
window.addEventListener("pageshow", function (event) {
    if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
        // لما المتصفح يرجع لصفحة مخزنة، نعمل رفرش إجباري
        window.location.reload();
    }
});
function fetchBooks(query = "") {
    const formData = new FormData();
    formData.append("action", "search");
    formData.append("query", query);

    fetch("", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        document.getElementById("booksGrid").innerHTML = data;
    })
    .catch(err => {
        document.getElementById("booksGrid").innerHTML = "<p>حدث خطأ أثناء البحث</p>";
        console.error(err);
    });
}

// ✅ عند تحميل الصفحة: عرض جميع الكتب
window.addEventListener("DOMContentLoaded", () => {
    fetchBooks();
});

// ✅ عند الضغط على زر البحث
document.getElementById("searchBtn").addEventListener("click", () => {
    const query = document.getElementById("searchInput").value.trim();
    fetchBooks(query);
});

// ✅ بحث مباشر عند الكتابة
document.getElementById("searchInput").addEventListener("input", () => {
    const query = document.getElementById("searchInput").value.trim();
    fetchBooks(query); // سواء مليان أو فارغ
});
</script>
<script src="../js/uabks.js"></script>
    </html>
    
    
