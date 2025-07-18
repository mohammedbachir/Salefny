<?php
session_start(); 

if (isset($_SESSION['user']) && $_SESSION['role'] === 'USER') {
    header("Location: user/userhome.php");
    exit;
}

if (isset($_SESSION['admin'])) {
    header("Location: admin/adminPage.php");
    exit;
}
 $db = new PDO("mysql:host=localhost;dbname=salefny", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $db->prepare("SELECT books.*, plans.name AS plan_name 
                      FROM books 
                      JOIN plans ON books.plan_id = plans.id
                      WHERE copies_available > 0
                      ORDER BY created_at DESC 
                      LIMIT 24");
$stmt->execute();
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $keyword  = $_POST['keyword'] ?? '';
    $category = $_POST['category'] ?? '';
    $package  = $_POST['package'] ?? '';

    $query = "SELECT books.*, plans.name AS plan_name 
              FROM books 
              JOIN plans ON books.plan_id = plans.id 
              WHERE copies_available > 0";
    $params = [];

    if (!empty($keyword)) {
        $query .= " AND (books.title LIKE :kw OR books.author LIKE :kw)";
        $params[':kw'] = '%' . $keyword . '%';
    }

    // تصفية بالتصنيف
    if (!empty($category)) {
        $query .= " AND books.category_id = :category";
        $params[':category'] = $category;
    }

    // تصفية بالباقة
    if (!empty($package)) {
        $query .= " AND plans.name = :plan";
        $params[':plan'] = $package;
    }

    $query .= " ORDER BY books.created_at DESC LIMIT 24";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $filteredBooks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($filteredBooks as $book) {
        echo '<div class="book-card">';
        echo '<img src="data:image/jpeg;base64,' . base64_encode($book['cover_image']) . '" class="book-cover">';
        echo '<div class="book-info">';
        echo '<div class="book-package package-' . strtolower($book['plan_name']) . '">' . htmlspecialchars($book['plan_name']) . '</div>';
        echo '<h3 class="book-title">' . htmlspecialchars($book['title']) . '</h3>';
        echo '<p class="book-author">' . htmlspecialchars($book['author']) . '</p>';
        echo '</div></div>';
    }
    exit;
}


?>
<?php

// ... جلسة المستخدم والتوجيه محفوظة

$db = new PDO("mysql:host=localhost;dbname=salefny", "root", "");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// جلب التصنيفات من القاعدة
$catStmt = $db->prepare("SELECT id, name FROM categories");
$catStmt->execute();
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// جلب أولي للكتب (24 كتاب)
$bookStmt = $db->prepare("SELECT books.*, plans.name AS plan_name 
                          FROM books 
                          JOIN plans ON books.plan_id = plans.id
                          WHERE copies_available > 0
                          ORDER BY created_at DESC 
                          LIMIT 24");
$bookStmt->execute();
$books = $bookStmt->fetchAll(PDO::FETCH_ASSOC);

// جلب عدد الكتب المتاحة
$booksCountStmt = $db->query("SELECT COUNT(*) FROM books WHERE copies_available > 0");
$booksCount = $booksCountStmt->fetchColumn();

// جلب عدد التصنيفات
$categoriesCountStmt = $db->query("SELECT COUNT(*) FROM categories");
$categoriesCount = $categoriesCountStmt->fetchColumn();

// جلب عدد المؤلفين (تفريد حسب الاسم)
$authorsCountStmt = $db->query("SELECT COUNT(DISTINCT author) FROM books");
$authorsCount = $authorsCountStmt->fetchColumn();

?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>مكتبة الكتب الكاملة - سلفني</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="css/bookPg.css">
   <link rel="shortcut icon" href="img/favicon.png" type="image/x-icon">
   <style>
    .container {
    padding-right: 15px;
    padding-left: 15px;
}
.books-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    padding: 20px;
}

    .package-ذهبية { background-color: gold; color: black; }
.package-فضية { background-color: silver; color: black; }
.package-برونزية { background-color: peru; color: white; }

   </style>
</head>
<body>
<?php include_once('templates/navbar.php');?>




<section class="page-header">
    <div class="container">
        <h1 class="page-title">مكتبة الكتب الكاملة</h1>
        <p class="page-subtitle">اكتشف مجموعة ضخمة من الكتب في جميع المجالات والتخصصات</p>
        
        <div class="stats-container">
           <div class="stat-item">
    <span class="stat-number"><?= number_format($booksCount) ?></span>
    <span class="stat-label">كتاب متاح</span>
</div>
<div class="stat-item">
    <span class="stat-number"><?= number_format($categoriesCount) ?></span>
    <span class="stat-label">تصنيف مختلف</span>
</div>
<div class="stat-item">
    <span class="stat-number"><?= number_format($authorsCount) ?></span>
    <span class="stat-label">مؤلف</span>
</div>

           
        </div>
    </div>
</section>

<section class="search-filter-section">
    <div class="container">
        <div class="search-bar">
       <input type="text" class="search-input" id="searchKeyword" placeholder="ابحث عن الكتب...">
            <button class="search-btn"><i class="fas fa-search"></i> بحث</button>
        </div>
        
        <div class="filters-container">
            <div class="filter-group">
                <span class="filter-label">التصنيف:</span>
               <select class="filter-select" id="categoryFilter">
    <option value="">جميع التصنيفات</option>
    <?php foreach ($categories as $category): ?>
        <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
    <?php endforeach; ?>
</select>

            </div>
            
            <div class="filter-group">
                <span class="filter-label">الباقة:</span>
                <select class="filter-select" id="packageFilter">
    <option value="">جميع الباقات</option>
    <option value="برونزية">برونزية</option>
    <option value="فضية">فضية</option>
    <option value="ذهبية">ذهبية</option>
</select>
            </div>
            
            
           
        </div>
    </div>
</section>

<section class="main-content">
   

        <!-- عرض الشبكة -->
        <div class="books-grid" id="booksGrid">
          <?php foreach ($books as $book): ?>
  <div class="book-card">
    <img src="data:image/jpeg;base64,<?= base64_encode($book['cover_image']) ?>" class="book-cover">
    <div class="book-info">
        <div class="book-package package-<?= strtolower($book['plan_name']) ?>">
            <?= htmlspecialchars($book['plan_name']) ?>
        </div>
        <h3 class="book-title"><?= htmlspecialchars($book['title']) ?></h3>
        <p class="book-author"><?= htmlspecialchars($book['author']) ?></p>
        <!-- نجوم التقييم ثابتة حالياً -->
       
    </div>
  </div>
<?php endforeach; ?>


        </div>
</section>
        <?php include_once('templates/footer.php');?>
      <script>
document.addEventListener("DOMContentLoaded", function () {
    const keywordInput = document.getElementById("searchKeyword");
    const categoryFilter = document.getElementById("categoryFilter");
    const packageFilter = document.getElementById("packageFilter");
    const booksGrid = document.getElementById("booksGrid");

    function fetchBooks() {
        const keyword = keywordInput.value;
        const category = categoryFilter.value;
        const packageName = packageFilter.value;

        const formData = new FormData();
        formData.append('ajax', '1');
        formData.append('keyword', keyword);
        formData.append('category', category);
        formData.append('package', packageName);

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            booksGrid.innerHTML = html;
        });
    }

    keywordInput.addEventListener('input', fetchBooks);
    categoryFilter.addEventListener('change', fetchBooks);
    packageFilter.addEventListener('change', fetchBooks);
});
</script>


</body>
    </html>
    
    
