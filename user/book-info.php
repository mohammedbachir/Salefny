<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 1 Jan 2000 00:00:00 GMT");

if (!isset($_SESSION['user']) || $_SESSION['user']['Role'] !== "USER") {
    header("Location: ../login.php");
    exit;
}

$conn = new PDO("mysql:host=localhost;dbname=salefny;charset=utf8mb4", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
$user = $_SESSION['user'];
$bookId = $_GET['id'] ?? null;

if (!$bookId) {
    header("Location: userhome.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$bookId]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);
$catStmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
$catStmt->execute([$book['category_id']]);
$category = $catStmt->fetch();
$planStmt = $conn->prepare("SELECT name FROM plans WHERE id = ?");
$planStmt->execute([$book['plan_id']]);
$plan = $planStmt->fetch();
$similarStmt = $conn->prepare("SELECT id, title, author, cover_image FROM books WHERE category_id = ? AND id != ? LIMIT 5");
$similarStmt->execute([$book['category_id'], $book['id']]);
$similarBooks = $similarStmt->fetchAll();

if (!$book) {
    echo "الكتاب غير موجود";
    exit;
}

$userId = $user['Id'];
$isSubscribed = false;
$stmt = $conn->prepare("SELECT * FROM subscriptions WHERE user_id = ? AND status = 'active' AND NOW() BETWEEN start_date AND end_date");
$stmt->execute([$userId]);
$sub = $stmt->fetch();

if ($sub) {
    $isSubscribed = true;
    $userPlanId = $sub['plan_id'];
    if ($book['plan_id'] != $userPlanId) {
        echo "هذا الكتاب غير متاح في باقتك.";
        exit;
    }
} 
?>
<?php

$conn = new PDO("mysql:host=localhost;dbname=salefny;charset=utf8mb4", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$user = $_SESSION['user'];
$user_id = $user['Id'];
$bookId = $_GET['id'] ?? null;

$borrowSuccess = false;
$borrowError = '';

if (isset($_SESSION['borrow_success'])) {
    $borrowSuccess = true;
    unset($_SESSION['borrow_success']);
}

if (isset($_SESSION['borrow_error'])) {
    $borrowError = $_SESSION['borrow_error'];
    unset($_SESSION['borrow_error']);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
    $book_id = intval($_POST['book_id']);

    $stmt = $conn->prepare("SELECT * FROM subscriptions WHERE user_id = ? AND end_date >= NOW()");
    $stmt->execute([$user_id]);
    $subscription = $stmt->fetch();

    if (!$subscription) {
        $borrowError = "❌ لا تملك باقة حالياً.";
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM book_requests WHERE user_id = ? AND status IN ('pending','confirmed', 'ready_for_delivery', 'in_progress')");
        $stmt->execute([$user_id]);
        $chosenCount = $stmt->fetchColumn();

        $stmt = $conn->prepare("SELECT COUNT(*) FROM book_requests WHERE user_id = ? AND status = 'in_progress'");
        $stmt->execute([$user_id]);
        $hasActive = $stmt->fetchColumn();

        if ($chosenCount >= 4) {
           $_SESSION['borrow_error'] = "❌ لقد وصلت إلى الحد الأقصى من الكتب.";
header("Location: book-info.php?id=" . $book_id);
exit;

        } elseif ($hasActive > 0) {
            $borrowError = "❌ لا يمكنك استعارة كتاب جديد حتى تُرجع الكتاب الحالي.";
        } else {
            $stmt = $conn->prepare("SELECT wilaya FROM verification WHERE user_id = ? AND status = 1 ORDER BY submitted_at DESC LIMIT 1");
            $stmt->execute([$user_id]);
            $userWilaya = $stmt->fetchColumn();

            $stmt = $conn->prepare("SELECT id FROM admins WHERE wilaya = ? LIMIT 1");
            $stmt->execute([$userWilaya]);
            $admin_id = $stmt->fetchColumn();

            if (!$admin_id) {
                $borrowError = "❌ لم يتم العثور على مكتبة في ولايتك.";
            } else {
                $stmt = $conn->prepare("SELECT MAX(order_number) FROM book_requests WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $order = $stmt->fetchColumn();
                $order = $order ? $order + 1 : 1;

                $stmt = $conn->prepare("INSERT INTO book_requests (user_id, book_id, admin_id, order_number, status, created_at)
                                        VALUES (?, ?, ?, ?, 'pending', NOW())");
                $stmt->execute([$user_id, $book_id, $admin_id, $order]);

                $_SESSION['borrow_success'] = true;
header("Location: book-info.php?id=" . $book_id);
exit;

            }
        }
    }
}
?>
<?php
$stmt = $conn->prepare("SELECT COUNT(*) FROM book_requests WHERE user_id = ? AND status IN ('pending','confirmed', 'ready_for_delivery', 'in_progress')");
$stmt->execute([$user['Id']]);
$chosenCount = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM book_requests WHERE user_id = ? AND status = 'in_progress'");
$stmt->execute([$user['Id']]);
$hasActive = $stmt->fetchColumn();

$disableBorrowing = false;
$borrowReason = '';

// إذا عنده 4 كتب نشطة
if ($chosenCount >= 4) {
    $disableBorrowing = true;
    $borrowReason = 'لقد وصلت إلى الحد الأقصى للكتب (4).';

// إذا عنده كتاب جاري
} elseif ($hasActive > 0) {
    $disableBorrowing = true;
    $borrowReason = 'يجب إرجاع الكتاب الحالي قبل استعارة آخر.';

} else {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM book_requests WHERE user_id = ? AND status = 'returned'");
    $stmt->execute([$user['Id']]);
    $returnedCount = $stmt->fetchColumn();

    if ($returnedCount >= 4) {
        $disableBorrowing = true;
        $borrowReason = 'لقد أكملت قائمة استعارات. الرجاء انتظار دورة جديدة.';
    }
}
include_once('check_subscription.php');

?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= htmlspecialchars($book['title']) ?>  - سلفني</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

   <link rel="shortcut icon" href="../img/favicon.png" type="image/x-icon">
   <link rel="stylesheet" href="../css/binfo.css">
   <style>
.alert {
    padding: 10px 15px;
    margin: 10px 0;
    border-radius: 5px;
    font-weight: bold;
    text-align: center;
}
.alert-success {
    background-color: #d4edda;
    color: #155724;
}
.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
}
</style>

</head>
<body>
 <?php require_once '../templates/unave.php';?>


<?php if ($borrowSuccess): ?>
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div class="toast align-items-center text-white bg-success show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">✅ تمت عملية الاستعارة بنجاح.</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
<?php elseif (!empty($borrowError)): ?>
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div class="toast align-items-center text-white bg-danger show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">' . htmlspecialchars($borrowError) . '</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
<?php endif; ?>

<section class="book-details">
    <div class="container">
        <div class="book-main-info">
            <div class="book-cover-section">
         <img src="data:image/jpeg;base64,<?= base64_encode($book['cover_image']) ?>" alt="غلاف الكتاب" class="book-cover-image" />

            <?php
            $badgeClass = '';
switch ($book['plan_id']) {
    case 1:
        $badgeClass = 'package-gold';
        break;
    case 2:
        $badgeClass = 'package-silver';
        break;
    case 3:
        $badgeClass = 'package-bronze';
        break;
    default:
        $badgeClass = 'package-default';
}

            ?>
<?php if ($plan): ?>
    <div class="book-package-badge <?= $badgeClass ?>">
        <?= htmlspecialchars($plan['name']) ?>
    </div>
<?php endif; ?>
           

    <div class="book-actions">
        <?php if ($isSubscribed): ?>
                   <form method="POST" style="display:inline-block;" >
    <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
    <button type="submit" class="btn btn-small btn-success" 
        <?= $disableBorrowing ? 'disabled title="' . $borrowReason . '"' : '' ?>>
        استعارة
        <i class="fas fa-book-open"></i> 
    </button>
</form>


        <?php else: ?>
            <button class="btn btn-disabled" disabled>
                <i class="fas fa-lock"></i> اشترك للاستعارة
            </button>
        <?php endif; ?>
    </div>
</div>

            <div class="book-info-section">
                <h1 class="book-title"><?= htmlspecialchars($book['title']) ?></h1>

                <p class="book-subtitle">تحفة أدبية من أروع الروايات العالمية</p>

                <div class="book-rating">
                    <div class="stars">
                        <i class="fas fa-star star"></i>
                        <i class="fas fa-star star"></i>
                        <i class="fas fa-star star"></i>
                        <i class="fas fa-star star"></i>
                        <i class="fas fa-star star"></i>
                    </div>
                    <span class="rating-text">4.8 (342 تقييم)</span>
                </div>

                <div class="book-meta">
                    <div class="meta-item">
                        <i class="fas fa-user meta-icon"></i>
                        <span><strong>المؤلف:</strong> <?= htmlspecialchars($book['author']) ?>  </span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-calendar meta-icon"></i>
                        <span><strong>تاريخ اضافته لدينا:</strong> <?= htmlspecialchars($book['created_at']) ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-book meta-icon"></i>
                        <span><strong>عدد الصفحات:</strong> <?= htmlspecialchars($book['pages']) ?> صفحة</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-globe meta-icon"></i>
                        <span><strong>اللغة:</strong> العربية (مترجمة)</span>
                    </div>
                   <div class="meta-item">
                        <i class="fas fa-tag meta-icon"></i>
                        <span><strong>الفئة:</strong> <?= htmlspecialchars($category['name']) ?></span>
                    </div>

                    
                </div>

                <div class="book-description">
                    <p>
                        <?= htmlspecialchars($book['description']) ?>
                    </p>
                   
                </div>

                <div class="book-tags">
                   
                    <span class="tag"><?= htmlspecialchars($category['name']) ?> </span>
                </div>
            </div>
        </div>
<!-- اراء المستدمين منا وجاي!-->
       <!-- <div class="additional-sections">
            <div class="main-content">
                <div class="section-card reviews-section">
                    <h2 class="section-title">
                        <i class="fas fa-comments section-icon"></i>
                        آراء القراء
                    </h2>
                    
                    <div class="review-item">
                        <div class="review-header">
                            <span class="reviewer-name">أحمد محمد</span>
                            <span class="review-date">منذ أسبوعين</span>
                        </div>
                        <div class="review-rating">
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star"></i>
                        </div>
                        <p class="review-text">
                            رواية استثنائية حقاً! أسلوب ماركيز في الكتابة يأخذك إلى عالم آخر مليء بالسحر والخيال. لا أستطيع أن أوصف مدى تأثير هذه الرواية عليّ.
                        </p>
                    </div>

                    <div class="review-item">
                        <div class="review-header">
                            <span class="reviewer-name">فاطمة العلي</span>
                            <span class="review-date">منذ شهر</span>
                        </div>
                        <div class="review-rating">
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star empty"></i>
                        </div>
                        <p class="review-text">
                            ترجمة ممتازة وقصة غنية بالتفاصيل. قد تبدو معقدة في البداية لكن مع التقدم في القراءة تصبح أكثر وضوحاً وإثارة.
                        </p>
                    </div>

                    <div class="review-item">
                        <div class="review-header">
                            <span class="reviewer-name">خالد السعيد</span>
                            <span class="review-date">منذ شهرين</span>
                        </div>
                        <div class="review-rating">
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star"></i>
                            <i class="fas fa-star star"></i>
                        </div>
                        <p class="review-text">
                            من أجمل الروايات التي قرأتها على الإطلاق. كل شخصية لها عمق وتاريخ، والأحداث متداخلة بطريقة رائعة.
                        </p>
                    </div>
                </div>
            </div>

            <div class="sidebar-content">
                <div class="section-card author-section">
                    <h2 class="section-title">
                        <i class="fas fa-user-edit section-icon"></i>
                        عن المؤلف
                    </h2>
                    
                    <img src="https://picsum.photos/100/100?random=200" alt="غابرييل غارسيا ماركيز" class="author-avatar" />
                    <h3 class="author-name">غابرييل غارسيا ماركيز</h3>
                    <p class="author-bio">
                        كاتب وصحفي كولومبي، حائز على جائزة نوبل للأدب عام 1982. يعتبر من أهم رواد الواقعية السحرية في الأدب العالمي.
                    </p>
                    
                    <div class="author-stats">
                        <div class="stat-item">
                            <span class="stat-number">15</span>
                            <span class="stat-label">كتاب</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">4.6</span>
                            <span class="stat-label">التقييم</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">2.1M</span>
                            <span class="stat-label">قارئ</span>
                        </div>
                    </div>
                    
                    <button class="btn btn-outline">المزيد من كتب المؤلف</button>
                </div>

                <div class="section-card">
                    <h2 class="section-title">
                        <i class="fas fa-chart-line section-icon"></i>
                        إحصائيات الكتاب
                    </h2>
                    
                    <div class="meta-item" style="margin-bottom: 15px;">
                        <i class="fas fa-eye meta-icon"></i>
                        <span><strong>مرات المشاهدة:</strong> 15,429</span>
                    </div>
                    <div class="meta-item" style="margin-bottom: 15px;">
                        <i class="fas fa-download meta-icon"></i>
                        <span><strong>مرات التحميل:</strong> 3,241</span>
                    </div>
                    <div class="meta-item" style="margin-bottom: 15px;">
                        <i class="fas fa-heart meta-icon"></i>
                        <span><strong>في المفضلة:</strong> 892</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-clock meta-icon"></i>
                        <span><strong>وقت القراءة:</strong> ~12 ساعة</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    !-->
</section>

<section class="similar-books">
    <div class="container">
        <h2 class="section-title">
            <i class="fas fa-books section-icon"></i>
            كتب مشابهة قد تعجبك
        </h2>

        <div class="similar-books-grid">
            <?php foreach ($similarBooks as $simBook): ?>
                <div class="similar-book-card">
                    <a href="book-info.php?id=<?= $simBook['id'] ?>">
                        <img src="data:image/jpeg;base64,<?= base64_encode($simBook['cover_image']) ?>" alt="<?= htmlspecialchars($simBook['title']) ?>" class="similar-book-cover" />
                        <h4 class="similar-book-title"><?= htmlspecialchars($simBook['title']) ?></h4>
                        <p class="similar-book-author"><?= htmlspecialchars($simBook['author']) ?></p>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
    <?php include '../templates/footer.php';?>

<script>
// يمنع الرجوع للصفحة بعد تسجيل الخروج
window.addEventListener("pageshow", function (event) {
    if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
        // لما المتصفح يرجع لصفحة مخزنة، نعمل رفرش إجباري
        window.location.reload();
    }
});

</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="../js/uabks.js"></script>
</body>
</html>