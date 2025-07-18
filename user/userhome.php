<?php
session_start();
if (isset($_SESSION['borrow_success'])) {
    $borrowSuccess = true;
    unset($_SESSION['borrow_success']);
}
// منع التخزين المؤقت
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 1 Jan 2000 00:00:00 GMT");

// التحقق من الجلسة
if (!isset($_SESSION['user']) || $_SESSION['user']['Role'] !== "USER") {
    header("Location: ../login.php");
    exit;
}

$user = $_SESSION['user'];
$conn = new PDO("mysql:host=localhost;dbname=salefny;charset=utf8mb4", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
$stmt = $conn->prepare("SELECT isVerified FROM users WHERE Id = ?");
$stmt->execute([$user['Id']]);
$isVerified = (bool)$stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM subscriptions WHERE user_id = ? AND status = 'active'");
$stmt->execute([$user['Id']]);
$isSubscribed = $stmt->fetchColumn() > 0;




$planName = 'لا توجد باقة نشطة';
$planDescription = '';
$maxBooks = 0;
$remainingDays = 0;
$endDate = '';
$colorClass = 'default-package';

if ($isSubscribed) {
    $stmt = $conn->prepare("SELECT s.plan_id, s.start_date, s.end_date, p.name as plan_name, p.description, p.max_books_borrowed
                            FROM subscriptions s
                            JOIN plans p ON s.plan_id = p.id
                            WHERE s.user_id = ? AND s.status = 'active'
                            ORDER BY s.end_date DESC
                            LIMIT 1");
    $stmt->execute([$user['Id']]);
    $subscription = $stmt->fetch();

    if ($subscription) {
        $planName = $subscription['plan_name'];
        $planDescription = $subscription['description'] ?? 'لا يوجد وصف للباقة.';
        $maxBooks = $subscription['max_books_borrowed'];
        $endDate = date("Y/m/d", strtotime($subscription['end_date']));

        $today = new DateTime();
        $endDateObj = new DateTime($subscription['end_date']);
        $remainingDays = $today > $endDateObj ? 0 : $today->diff($endDateObj)->days;

        // تحديد اللون حسب الاسم
        $lowerPlan = strtolower($planName);
        if (str_contains($lowerPlan, 'ذهبي') || str_contains($lowerPlan, 'gold')) {
            $colorClass = 'gold';
        } elseif (str_contains($lowerPlan, 'فضي') || str_contains($lowerPlan, 'silver')) {
            $colorClass = 'silver';
        } elseif (str_contains($lowerPlan, 'برونز') || str_contains($lowerPlan, 'bronze')) {
            $colorClass = 'bronze';
        } else {
            $colorClass = 'default-package';
        }
    }
}

?>
<?php
$recentBooks = [];

$oneWeekAgo = date('Y-m-d H:i:s', strtotime('-7 days'));

if ($isSubscribed && isset($subscription['plan_id'])) {
    $userPlanId = $subscription['plan_id'];

    $stmt = $conn->prepare("SELECT id, title, cover_image 
                            FROM books 
                            WHERE created_at >= ? 
                              AND plan_id = ? 
                            ORDER BY created_at DESC 
                            LIMIT 6");
    $stmt->execute([$oneWeekAgo, $userPlanId]);
} else {
    $stmt = $conn->prepare("SELECT id, title, cover_image 
                            FROM books 
                            WHERE created_at >= ? 
                            ORDER BY created_at DESC 
                            LIMIT 6");
    $stmt->execute([$oneWeekAgo]);
}

$recentBooks = $stmt->fetchAll();

?>

<?php
$searchResultsHTML = '';

if (isset($_GET['ajax_search']) && !empty(trim($_GET['q']))) {
    $search = '%' . trim($_GET['q']) . '%';

    // إذا مشترك، نقيّد بالباقة
    if ($isSubscribed) {
        $stmt = $conn->prepare("SELECT plan_id FROM subscriptions WHERE user_id = ? AND status = 'active' ORDER BY end_date DESC LIMIT 1");
        $stmt->execute([$user['Id']]);
        $sub = $stmt->fetch();

        $planId = $sub ? $sub['plan_id'] : 0;

        $stmt = $conn->prepare("SELECT id, title, author, cover_image 
                                FROM books 
                                WHERE (title LIKE :search OR author LIKE :search)
                                AND plan_id = :plan_id
                                LIMIT 10");
        $stmt->execute([
            'search' => $search,
            'plan_id' => $planId
        ]);
    } else {
        // غير مشترك => يشوف كل الكتب
        $stmt = $conn->prepare("SELECT id, title, author, cover_image 
                                FROM books 
                                WHERE title LIKE :search OR author LIKE :search 
                                LIMIT 10");
        $stmt->execute(['search' => $search]);
    }

    $books = $stmt->fetchAll();

    if (count($books) === 0) {
        $searchResultsHTML = "<p>لا توجد نتائج مطابقة.</p>";
    } else {
        foreach ($books as $book) {
            $imageSrc = $book['cover_image']
                ? 'data:image/jpeg;base64,' . base64_encode($book['cover_image'])
                : 'https://via.placeholder.com/120x160';

            $searchResultsHTML .= '
                <div class="search-result-item">
                    <img src="' . $imageSrc . '" alt="غلاف الكتاب" style="width:60px;height:80px;margin-left:10px;">
                    <span><strong>' . htmlspecialchars($book['title']) . '</strong><br>' . htmlspecialchars($book['author']) . '</span>
                </div>
                <hr>';
        }
    }

    echo $searchResultsHTML;
    exit;
}

?>
<?php
$user_id = $user['Id']; 
$borrowSuccess = isset($borrowSuccess) ? $borrowSuccess : false;

$borrowError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
    $book_id = intval($_POST['book_id']);

    // تحقق من الاشتراك
    $stmt = $conn->prepare("SELECT * FROM subscriptions WHERE user_id = ? AND end_date >= NOW()");
    $stmt->execute([$user_id]);
    $subscription = $stmt->fetch();

    if (!$subscription) {
        $borrowError = "❌ لا تملك باقة حالياً.";
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM book_requests WHERE user_id = ? AND status IN ('confirmed', 'ready_for_delivery', 'in_progress')");
        $stmt->execute([$user_id]);
        $chosenCount = $stmt->fetchColumn();

        $stmt = $conn->prepare("SELECT COUNT(*) FROM book_requests WHERE user_id = ? AND status = 'in_progress'");
        $stmt->execute([$user_id]);
        $hasActive = $stmt->fetchColumn();

        if ($chosenCount >= 4) {
            $borrowError = "❌ لقد وصلت إلى الحد الأقصى من الكتب.";
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
                header("Location: userhome.php"); // ✳️ غيّرها إذا اسم الملف مختلف
                exit;
                $borrowSuccess = true;
            }
        }
    }
}

$stmt = $conn->prepare("SELECT COUNT(*) FROM book_requests WHERE user_id = ? AND status IN ('confirmed', 'ready_for_delivery', 'in_progress')");
$stmt->execute([$user_id]);
$chosenCount = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM book_requests WHERE user_id = ? AND status = 'in_progress'");
$stmt->execute([$user_id]);
$hasActive = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM subscriptions WHERE user_id = ? AND end_date >= NOW()");
$stmt->execute([$user_id]);
$hasSubscription = $stmt->fetchColumn();

$disableBorrowing = false;
$borrowReason = '';

if ($chosenCount >= 4) {
    $disableBorrowing = true;
    $borrowReason = 'لقد وصلت إلى الحد الأقصى للكتب (4).';

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
    <title>سلفني - صفحة المستخدم الرئيسية</title>
    <link rel="shortcut icon" href="../img/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="../css/uhome.css">
    <style>
        .package-info {
    border-radius: 10px;
    padding: 20px;
    color: #fff;
    margin-top: 20px;
}

.package-info.gold {
    background: linear-gradient(45deg, #d4af37, #b8860b);
}

.package-info.silver {
    background: linear-gradient(45deg, #c0c0c0, #a9a9a9);
}

.package-info.bronze {
    background: linear-gradient(45deg, #cd7f32, #8b4513);
}

.package-info.default-package {
    background: #666;
}

.upgrade-btn {
    display: inline-block;
    margin-top: 15px;
    background: #fff;
    color: #333;
    padding: 8px 15px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
}

.upgrade-btn:hover {
    background: #f0f0f0;
}
.search-results {
    margin-top: 15px;
    background: #f9f9f9;
    border: 1px solid #ccc;
    padding: 10px;
    max-height: 300px;
    overflow-y: auto;
    border-radius: 6px;
}

.search-result-item {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.toast-box {
  position: fixed;
  bottom: 30px;
  right: 30px;
  background-color: #28a745;
  color: #fff;
  padding: 16px 20px;
  border-radius: 8px;
  box-shadow: 0 6px 16px rgba(0,0,0,0.2);
  z-index: 9999;
  min-width: 280px;
  font-family: 'Segoe UI', sans-serif;
  animation: slideIn 0.4s ease, fadeOut 0.4s ease 4.5s forwards;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.toast-box .toast-message {
  flex: 1;
}

.toast-box .toast-close {
  background: none;
  border: none;
  color: white;
  font-size: 18px;
  font-weight: bold;
  margin-left: 10px;
  cursor: pointer;
}

@keyframes slideIn {
  from {opacity: 0; transform: translateY(50px);}
  to {opacity: 1; transform: translateY(0);}
}

@keyframes fadeOut {
  to {opacity: 0; transform: translateY(50px);}
}
</style>

    
</head>
<body>
    <?php require_once '../templates/unave.php';?>
    <section class="welcome-section">
        <div class="container">
            <div class="welcome-content">
                <div class="welcome-text">
                    <h1>مرحباً بك،  <?php echo htmlspecialchars($user['Fname']); ?>!</h1>
                    <p>قم بتصفح الكتب والباقات واستمتع باستعارة الكتب التي تميل اليها</p>
                </div>
                
    </section>

 <?php
$stmt = $conn->prepare("SELECT COUNT(*) FROM book_requests 
                        WHERE user_id = ? 
                        AND status IN ('pending', 'confirmed', 'ready_for_delivery', 'in_progress')");
$stmt->execute([$user['Id']]);
$borrowedCount = $stmt->fetchColumn();
?>

<section class="stats-section">
    <div class="container">
        <div class="stats-grid">

            <!--الكتب في القائمة-->
            <div class="stat-card reading">
                <div class="stat-header">
                    <i class="fas fa-book-open stat-icon" style="color: var(--info-color);"></i>
                    <span class="stat-value"><?= $borrowedCount ?></span>
                </div>
                <div class="stat-title"> الكتب في القائمة </div>
            </div>

            <!-- اسم الباقة -->
            <div class="stat-card favorite">
                <div class="stat-header">
                    <i class="fas fa-heart stat-icon" style="color: var(--accent-color);"></i>
                    <span class="stat-value">
                        <?= $isSubscribed ? htmlspecialchars($planName) : "لا توجد باقة" ?>
                    </span>
                </div>
                <div class="stat-title">الباقة المختارة</div>
            </div>

            <!-- عدد الكتب المسموح بها -->
            <div class="stat-card downloaded">
                <div class="stat-header">
                    <i class="fas fa-book stat-icon" style="color: var(--warning-color);"></i>
                    <span class="stat-value">
                        <?= $isSubscribed ? $maxBooks : 0 ?>
                    </span>
                </div>
                <div class="stat-title">الكتب المتاح استعارتها</div>
            </div>

            <!-- الأيام المتبقية -->
            <div class="stat-card">
                <div class="stat-header">
                    <i class="fas fa-clock stat-icon"></i>
                    <span class="stat-value">
                        <?= $isSubscribed ? $remainingDays : 0 ?>
                    </span>
                </div>
                <div class="stat-title">يوم متبقي في الباقة</div>
            </div>

        </div>
    </div>
</section>


    <section class="current-package">
<div class="container">
    <?php if (!$isVerified): ?>
        <!-- الحالة: الحساب غير مفعل -->
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>ملاحظة :</strong> يرجى تفعيل حسابك لكي تستطيع اختيار باقتك.
        </div>

    <?php elseif ($isVerified && !$isSubscribed): ?>
        <!-- الحالة: مفعل لكن لم يشترك بعد -->
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-circle"></i>
            <strong>تم تفعيل حسابك، لكنك لم تشترك بعد في أي باقة.</strong><br>
            يرجى اختيار باقة للتمكن من استعارة الكتب.
        </div>
        <a href="subscriptions.php" class="btn btn-primary mt-3">
            <i class="fas fa-crown"></i> اختيار الباقة الآن
        </a>
    <?php endif; ?>
</div>


        <?php if ($isSubscribed): ?>
    <div class="package-info <?= htmlspecialchars($colorClass) ?>">
        <div class="package-details">
            <h3><i class="fas fa-crown"></i> <?= htmlspecialchars($planName) ?></h3>
            <p><?= htmlspecialchars($planDescription) ?></p>
            <div class="package-expires">
                <i class="fas fa-calendar-alt"></i>
                تنتهي في: <?= htmlspecialchars($endDate) ?>
            </div>
        </div>
        
    </div>
<?php endif; ?>

       
    </section>

    <section class="recent-books">
        <div class="container">
            <h2 class="section-title">
                <i class="fas fa-history"></i>
                أحدث الكتب في سلفني
                <a href="user-all-books.php" style="margin-right: auto; font-size: 14px;">عرض الكل</a>
            </h2>
            <div class="recent-books-list">
    <?php if (count($recentBooks) === 0): ?>
        <p>لا توجد كتب جديدة مضافة هذا الأسبوع.</p>
    <?php else: ?>
        <?php foreach ($recentBooks as $book): ?>
            <div class="recent-book-item">
                <div class="recent-book-cover">
                    <img src="data:image/jpeg;base64,<?= base64_encode($book['cover_image']) ?>" 
                         alt="<?= htmlspecialchars($book['title']) ?>" width="120" height="160" />
                </div>
                <div class="recent-book-title"><?= htmlspecialchars($book['title']) ?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>


        </div>
    </section>

    <section class="search-section">
        <div class="container">
            <h2 class="section-title">
                <i class="fas fa-search"></i>
                ابحث في مكتبتك
            </h2>
            <div class="search-container">
    <input type="text" placeholder="ابحث عن كتاب، مؤلف، أو موضوع..." class="search-input" id="searchInput" />
    <button class="search-btn" id="searchBtn">
        <i class="fas fa-search"></i>
    </button>
</div>

<!-- نتائج البحث تظهر هنا -->
<div id="searchResults" class="search-results"></div>
        </div>
    </section>
<?php
// الاتصال بقاعدة البيانات
$conn = new PDO("mysql:host=localhost;dbname=salefny;charset=utf8mb4", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);


$recommendedBooks = [];
$userId = $user['Id'];
if ($isSubscribed && $userId) {
    // نجلب باقة الاشتراك
    $stmt = $conn->prepare("SELECT plan_id FROM subscriptions WHERE user_id = ? AND status = 'active' ORDER BY end_date DESC LIMIT 1");
    $stmt->execute([$userId]);
    $subscription = $stmt->fetch();
    $userPlanId = $subscription ? $subscription['plan_id'] : 0;

    // نجلب كتب من نفس الباقة
    $stmt = $conn->prepare("SELECT id, title, author, cover_image FROM books WHERE plan_id = ? ORDER BY RAND() LIMIT 6");
    $stmt->execute([$userPlanId]);
} else {
    // غير مشترك: كتب عشوائية من جميع الباقات
    $stmt = $conn->prepare("SELECT id, title, author, cover_image FROM books ORDER BY RAND() LIMIT 6");
    $stmt->execute();
}

$recommendedBooks = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

    <section class="recommendations-section">
    <div class="container">
        <h2 class="section-title">
            <i class="fas fa-star"></i>
            مقترحات لك
            <a href="user-all-books.php" style="margin-right: auto; font-size: 14px;">عرض المزيد</a>
        </h2>

        <?php if ($borrowSuccess): ?>
             <div class="toast-box">
    <span class="toast-message"> 
    تمت اضافة الكتاب الى قائمتك عليك تأكيد القائمة في لوحة التحكم ✅
    </span>
    <button class="toast-close">&times;</button>
  </div>
        <?php elseif (!empty($borrowError)): ?>
            <div class="alert alert-danger"><?= $borrowError ?></div>
        <?php endif; ?>

        <div class="books-grid">
            <?php foreach ($recommendedBooks as $book): ?>
                <div class="book-card fade-in">
                    <div class="book-cover">
                        <img src="<?= $book['cover_image'] ? 'data:image/jpeg;base64,' . base64_encode($book['cover_image']) : 'https://via.placeholder.com/200x300' ?>" alt="غلاف الكتاب" />
                        <div class="book-badge badge-recommended">مقترح</div>
                    </div>
                    <div class="book-info">
                        <h3 class="book-title"><?= htmlspecialchars($book['title']) ?></h3>
                        <p class="book-author"><?= htmlspecialchars($book['author']) ?></p>

                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                            <button type="submit" class="btn btn-small btn-success" <?= $disableBorrowing ? 'disabled' : '' ?>>
                                استعارة
                            </button>
                        </form>

                        <a href="book-info.php?id=<?= $book['id'] ?>" class="btn btn-info" target="_blank">معلومات أكثر</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
   <section>
    <div id="plans" class="content-section">
        <h2>الباقات المتاحة</h2>

        <?php if (!$isVerified): ?>
            <div class="alert alert-warning">⚠️ يجب توثيق الحساب لتفعيل الاشتراك في الباقات.</div>
        <?php endif; ?>

        <div class="packages d-flex justify-content-center flex-wrap gap-4">

            <!-- 🥇 الباقة الذهبية -->
            <div class="package-card">
                <h3 style="color: goldenrod;">الذهبية</h3>
                <p style="color: goldenrod;" class="package-price">1500 د.ج / شهر</p>
                <ul class="package-features">
                    <a href="#"><li>يحتوي على كل العناوين في سلفني</li></a>
                    <li>دعم فني مباشر لكل مشكلة</li>
                    <li>استعارة <b>4</b> كتب</li>
                    <li>إمكانية استعارة كتابين في وقت واحد</li>
                    <li>إمكانية إضافة 5 أيام بعد انتهاء الاشتراك</li>
                    <li>الحصول على هدايا وإضافات من فريق سلفني</li>
                    <li>الاشتراك المتكرر يجعلك <b>مستخدم مميز</b></li>
                </ul>
                <?php if (!$isVerified): ?>
                    <button class="btn btn-secondary" disabled>توثيق الحساب أولاً</button>
                <?php elseif ($isSubscribed): ?>
                    <button class="btn btn-success" disabled>أنت مشترك حالياً</button>
                <?php else: ?>
                    <a href="https://rings-poverty-stainless-travelers.trycloudflare.com/Salefny/user/checkout.php?plan_id=1" class="btn btn-success" target="_blank">اختر الباقة</a>
                <?php endif; ?>
            </div>

            <!-- 🥈 الباقة الفضية -->
            <div class="package-card">
                <h3 style="color: rgb(99, 97, 97);">فضية</h3>
                <p style="color: rgb(99, 97, 97);" class="package-price">1000 د.ج / شهر</p>
                <ul class="package-features">
                    <a href="#"><li>الوصول إلى الكتب الفضية</li></a>
                    <li>استعارة <b>3</b> كتب</li>
                    <li>الاشتراك المتكرر يجعلك <b>مستخدم مميز</b></li>
                </ul>
                <?php if (!$isVerified): ?>
                    <button class="btn btn-secondary" disabled>توثيق الحساب أولاً</button>
                <?php elseif ($isSubscribed): ?>
                    <button class="btn btn-success" disabled>أنت مشترك حالياً</button>
                <?php else: ?>
                    <a href="https://rings-poverty-stainless-travelers.trycloudflare.com/Salefny/user/checkout.php?plan_id=2" class="btn btn-success" target="_blank">اختر الباقة</a>
                <?php endif; ?>
            </div>

            <!-- 🥉 الباقة البرونزية -->
            <div class="package-card">
                <h3 style="color: rgb(126, 26, 26);">برونزية</h3>
                <p style="color: rgb(126, 26, 26);" class="package-price">500 د.ج / شهر</p>
                <ul class="package-features">
                    <a href="#"><li>الوصول إلى الكتب البرونزية</li></a>
                    <li>دعم فني عبر البريد الإلكتروني</li>
                    <li>استعارة <b>3</b> كتب</li>
                </ul>
                <?php if (!$isVerified): ?>
                    <button class="btn btn-secondary" disabled>توثيق الحساب أولاً</button>
                <?php elseif ($isSubscribed): ?>
                    <button class="btn btn-success" disabled>أنت مشترك حالياً</button>
                <?php else: ?>
                    <a href="https://rings-poverty-stainless-travelers.trycloudflare.com/Salefny/user/checkout.php?plan_id=3" class="btn btn-success" target="_blank">اختر الباقة</a>
                <?php endif; ?>
            </div>

        </div>
    </div>
</section>

    <br>
    <?php include '../templates/footer.php';?>
    
    <script>
// يمنع الرجوع للصفحة بعد تسجيل الخروج
window.addEventListener("pageshow", function (event) {
    if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
        // لما المتصفح يرجع لصفحة مخزنة، نعمل رفرش إجباري
        window.location.reload();
    }
});
document.getElementById("searchInput").addEventListener("input", function () {
    const query = this.value.trim();
    const resultsContainer = document.getElementById("searchResults");

    if (query.length === 0) {
        resultsContainer.innerHTML = "";
        return;
    }

    fetch(`?ajax_search=1&q=${encodeURIComponent(query)}`)
        .then(response => response.text())
        .then(data => {
            resultsContainer.innerHTML = data;
        })
        .catch(error => {
            resultsContainer.innerHTML = "<p>حدث خطأ أثناء البحث.</p>";
        });
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function() {
  const toast = document.querySelector(".toast-box");
  if (toast) {
    const closeBtn = toast.querySelector(".toast-close");
    if (closeBtn) {
      closeBtn.addEventListener("click", () => toast.remove());
    }

    // إزالة تلقائية بعد 5 ثوانٍ
    setTimeout(() => toast.remove(), 5000);
  }
});
</script>

   <script src="../js/uhome.js"></script>
</body>
</html>
