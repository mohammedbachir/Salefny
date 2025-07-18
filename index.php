<?php
session_start(); // ضروري في أول الملف

if (isset($_SESSION['user']) && $_SESSION['role'] === 'USER') {
    header("Location: user/userhome.php");
    exit;
}

if (isset($_SESSION['admin'])) {
    header("Location: admin/adminPage.php");
    exit;
}

try {
    $db = new PDO("mysql:host=localhost;dbname=salefny", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];

    $stmt = $db->prepare("INSERT INTO visitors_logs (ip_address, user_agent) VALUES (:ip, :user_agent)");
    $stmt->execute([
        'ip' => $ip,
        'user_agent' => $userAgent
    ]);

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}


?>
<?php
try {
    $stmt = $db->prepare("SELECT * FROM ads WHERE is_active = 1 ORDER BY created_at DESC");
    $stmt->execute();
    $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching ads: " . $e->getMessage();
}
?>
<?php
try {
   $limit = 8;
$stmt = $db->prepare("SELECT books.*, plans.name AS plan_name 
                      FROM books 
                      JOIN plans ON books.plan_id = plans.id
                      WHERE copies_available > 0 
                      ORDER BY created_at DESC 
                      LIMIT $limit");

    $stmt->execute();
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching books: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- داخل <head> -->
    <meta name="description" content="منصة سلفني لاستعارة الكتب الورقية للطلبة باشتراك شهري.">
    <meta property="og:title" content="سلفني - منصة استعارة الكتب للطلبة">
    <meta property="og:description" content="باقة شهرية، توصيل سريع، واختيار كتب حسب رغبتك.">
    <meta property="og:image" content="img/favicon.png">

    <title>سلفني - منصة الكتب الرقمية</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="css/index.css">
   <link rel="shortcut icon" href="img/favicon.png" type="image/x-icon">
 <style>
    /* أضفها إلى CSS */
.hero-btn {
    background-color:#27ae60;
    color: white;
    padding: 12px 25px;
    font-size: 1.2rem;
    border-radius: 8px;
    transition: background-color 0.3s;
}
.hero-btn:hover {
    background-color:rgb(0, 195, 52);
}
.ads-slide img {
    max-width: 100%;
    height: auto;
    border-radius: 10px;
    object-fit: cover;
}
/* CSS تبع الخطوات */
.how-it-works .steps {
  display: flex;
  gap: 2rem;
  flex-wrap: wrap;
  justify-content: center;
  margin-top: 2rem;
}
.how-it-works .step {
  background:rgb(109, 222, 105);
  padding: 1.5rem;
  border-radius: 10px;
  text-align: center;
  flex: 1 1 200px;
}
.package-ذهبية { background-color: gold; color: black; }
.package-فضية { background-color: silver; color: black; }
.package-برونزية { background-color: peru; color: white; }
.packages {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 2rem;
  margin-top: 2rem;
}

.package-card {
  flex: 1 1 300px;
  background-color: #f9f9f9;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
  max-width: 350px;
  transition: transform 0.3s ease;
}

.package-card:hover {
  transform: translateY(-5px);
}

.package-features {
  list-style: none;
  padding: 0;
  margin-top: 1rem;
}

.package-features li {
  margin-bottom: 0.5rem;
}



 </style>
</head>
<body>
<?php include_once('templates/navbar.php');?>

<section class="hero">
    <div class="container">
        <h1>مرحبا بك في موقع سلفني</h1>
        <p>منصة سلفني تفتح مكتبتها الكبيرة للطلبة لاقتناء العناوين المرغوبة باشتراكات رمزية ومناسبة للجميع</p>
<a href="register.php"><button class="btn hero-btn">ابدأ الآن</button></a>
    </div>
</section>

<section class="ads-section">
    <div class="container">
        <h2 class="section-title">إعلانات</h2>
        <div class="ads-slider" id="adsSlider">
            <?php foreach ($ads as $index => $ad): ?>
                <div class="ads-slide">
                    <?php if (!empty($ad['link'])): ?>
                        <a href="<?= htmlspecialchars($ad['link']) ?>" target="_blank">
                    <?php endif; ?>

                    <?php if (!empty($ad['image'])): ?>
                        <?php
                            $imgSrc = 'data:image/jpeg;base64,' . base64_encode($ad['image']);
                        ?>
                        <img src="<?= $imgSrc ?>" alt="إعلان <?= $index + 1 ?>" />
                    <?php else: ?>
                        <div class="no-image"><?= htmlspecialchars($ad['title']) ?></div>
                    <?php endif; ?>

                    <?php if (!empty($ad['link'])): ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- النقاط المتحركة -->
        <div class="slider-controls" id="sliderControls">
            <?php for ($i = 0; $i < count($ads); $i++): ?>
                <span class="slider-dot<?= $i === 0 ? ' active' : '' ?>" data-index="<?= $i ?>"></span>
            <?php endfor; ?>
        </div>
    </div>
</section>



<section class="latest-books" id="latest-books">
    <div class="container">
        <h2 class="section-title">أحدث الكتب</h2>
       <div class="books-grid">
    <?php foreach ($books as $book): ?>
        <div class="book-card">
            <div class="book-cover">
                <?php if (!empty($book['cover_image'])): ?>
                    <img src="data:image/jpeg;base64,<?= base64_encode($book['cover_image']) ?>" 
                         alt="<?= htmlspecialchars($book['title']) ?>" />
                <?php else: ?>
                    <img src="img/default_cover.jpg" alt="غلاف افتراضي" />
                <?php endif; ?>
            </div>
            <div class="book-info">
                <h3 class="book-title"><?= htmlspecialchars($book['title']) ?></h3>
                <p class="book-author">المؤلف: <?= htmlspecialchars($book['author'] ?? 'غير معروف') ?></p>
                <span class="book-package package-<?= strtolower($book['plan_name']) ?>">
                    باقة <?= htmlspecialchars($book['plan_name']) ?>
                </span>
            </div>
        </div>
    <?php endforeach; ?>
</div>

        <div class="view-all-btn">
            <a href="booksPg.php" class="btn btn-secondary">عرض جميع الكتب</a>

        </div>
    </div>
</section>
<hr>
<br>
<section class="how-it-works">
  <div class="container">
    <h2 class="section-title">كيف تعمل منصة سلفني؟</h2>
    <div class="steps">
      <div class="step">
        <i class="fas fa-user-plus fa-2x"></i>
        <h3>سجّل حسابك</h3>
        <p>أنشئ حسابًا جديدًا وفعّله بسهولة.</p>
      </div>
      <div class="step">
        <i class="fas fa-book fa-2x"></i>
        <h3>اختر باقتك</h3>
        <p>اختر باقة اشتراك تناسب احتياجاتك الشهرية.</p>
      </div>
      <div class="step">
        <i class="fas fa-truck fa-2x"></i>
        <h3>ابدأ الاستعارة</h3>
        <p>استعر الكتب الورقية مباشرة إلى مقر إقامتك.</p>
      </div>
    </div>
  </div>
</section>
<br>
<hr>
<br>
 <section>
    <div id="plans" class="content-section">
        <div class="packages ">

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
                <a href="register.php" class="btn btn-success">اشتراك الان</a>
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
                               <a href="register.php" class="btn btn-success">اشتراك الان</a>

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
                              <a href="register.php" class="btn btn-success">اشتراك الان</a>

            </div>

        </div>
    </div>
</section>
<br><br>


<?php include_once('templates/footer.php');?>
<script src="js/index.js"></script>
</body>
</html>
