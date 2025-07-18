<?php
session_start();

// منع التخزين المؤقت للصفحة
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 1 Jan 2000 00:00:00 GMT");

// التحقق من وجود الجلسة وصلاحية المستخدم
if (!isset($_SESSION['user']) || $_SESSION['user']['Role'] !== "USER") {
    header("Location: ../login.php");
    exit;
}

$user = $_SESSION['user'];
$isVerified = $user['isVerified']; 
if(isset($_POST['update'])){
    $username = "root";
    $password = "";
    $database = new PDO("mysql:host=localhost;dbname=salefny;", $username, $password);
   

    $update = $database->prepare("UPDATE users SET Fname = :fname, Lname = :lname, Email = :email, Phone = :phone WHERE Id = :id");
    $update->bindParam("fname", $_POST['fname']);
    $update->bindParam("lname", $_POST['lname']);
    $update->bindParam("email", $_POST['email']);
    $update->bindParam("phone", $_POST['phone']);
    $update->bindParam("id", $user['Id']); // هنا نربط بالمعرّف الحقيقي للمستخدم

    if($update->execute()){
        $msg = '<div class="alert alert-success alert-dismissible fade show" role="alert">
            تم تحديث معلوماتك بنجاح ✅    
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';

        // تحديث بيانات الجلسة
        $userQuery = $database->prepare("SELECT * FROM users WHERE Id = :id LIMIT 1");
        $userQuery->bindParam("id", $user['Id']);
        $userQuery->execute();
        $_SESSION['user'] = $userQuery->fetch(PDO::FETCH_ASSOC);

        // عمل تحديث تلقائي للصفحة بعد ثانية
        header("refresh:1");
    } else {
        $msg = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
            ⚠️ حدث خطأ أثناء تحديث البيانات
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
    }
}

?>
<?php


try {
    $database = new PDO("mysql:host=localhost;dbname=salefny;", "root", "");
    $checkUser = $database->prepare("SELECT * FROM users WHERE Id = :id LIMIT 1");
    $checkUser->bindParam(":id", $_SESSION['user']['Id']);
    $checkUser->execute();
    $_SESSION['user'] = $checkUser->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// الآن نستخدم البيانات المحدثة
$user = $_SESSION['user'];
$isVerified = $user['isVerified'];
$msg2 = ""; // رسالة لعرض نتيجة التوثيق

if (isset($_POST['submit_verification'])) {
    $identityCard = $_FILES['identity_card'];
    $address = trim($_POST['address']);
    $wilaya = trim($_POST['wilaya']); // ➕ أخذ الولاية من الفورم

    if ($identityCard['error'] === UPLOAD_ERR_OK) {
        $imageData = file_get_contents($identityCard['tmp_name']);

        // تحقق من عدم وجود طلب سابق
        $stmt = $database->prepare("SELECT id FROM verification WHERE user_id = ? AND status = 0");
        $stmt->execute([$user['Id']]);

        if ($stmt->rowCount() > 0) {
            $msg2 = '<div class="alert alert-warning">⚠️ لقد أرسلت طلب توثيق من قبل، وهو قيد المراجعة.</div>';
        } else {
            // إدراج الطلب الجديد مع الولاية
            $insert = $database->prepare("INSERT INTO verification (user_id, identity_card, address, wilaya, status, submitted_at)
                                          VALUES (?, ?, ?, ?, 0, NOW())");
            $insert->execute([$user['Id'], $imageData, $address, $wilaya]);

            $msg2 = '<div class="alert alert-success">✅ تم إرسال طلب التوثيق بنجاح، سيتم مراجعته قريبًا.</div>';
        }
    } else {
        $msg2 = '<div class="alert alert-danger">⚠️ حدث خطأ أثناء رفع صورة بطاقة الهوية.</div>';
    }
}



?>
<?php 
if (isset($_POST['send_support'])) {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    if (!empty($subject) && !empty($message)) {
        $stmt = $database->prepare("INSERT INTO support_messages (user_id, subject, message) VALUES (:uid, :subject, :message)");
        $stmt->bindParam(":uid", $user['Id']);
        $stmt->bindParam(":subject", $subject);
        $stmt->bindParam(":message", $message);
        $stmt->execute();

        $msg3 = '<div class="alert alert-success mt-3">✅ تم إرسال رسالتك بنجاح، سيتم الرد عليك قريباً.</div>';
    } else {
        $msg3 = '<div class="alert alert-warning mt-3">⚠️ يرجى ملء جميع الحقول.</div>';
    }
}

?>
<?php 
$user = $_SESSION['user'];

// الاتصال بقاعدة البيانات
$conn = new PDO("mysql:host=localhost;dbname=salefny;charset=utf8mb4", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

// التحقق من التوثيق
$stmt = $conn->prepare("SELECT isVerified FROM users WHERE Id = ?");
$stmt->execute([$user['Id']]);
$isVerified = (bool)$stmt->fetchColumn();

// التحقق من الاشتراك
$stmt = $conn->prepare("SELECT COUNT(*) FROM subscriptions WHERE user_id = ? AND status = 'active'");
$stmt->execute([$user['Id']]);
$isSubscribed = $stmt->fetchColumn() > 0;

// تعريف المتغيرات افتراضيًا باش ما تطلعش تحذيرات
$planName = 'لا توجد باقة نشطة';
$planDescription = '';
$maxBooks = 0;
$remainingDays = 0;
$endDate = '';
$colorClass = 'default-package';

// إذا كان مشترك، جيب تفاصيل الباقة
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
        $planDescription = $subscription['description'] ?? 'لا يوجد وصف.';
        $maxBooks = $subscription['max_books_borrowed'];
        $endDate = $subscription['end_date'];

        // احسب الأيام المتبقية
        $today = new DateTime();
        $endDateObj = new DateTime($subscription['end_date']);
        $remainingDays = $today > $endDateObj ? 0 : $today->diff($endDateObj)->days;

        // صنّف لون الباقة حسب اسمها
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
// جلب قائمة الكتب التي اختارها المستخدم (باستثناء الكتب التي تم حذفها)
$stmt = $conn->prepare("
    SELECT br.id AS request_id, b.title, b.author, b.pages, br.status
    FROM book_requests br
    JOIN books b ON br.book_id = b.id
    WHERE br.user_id = ? AND br.status IN ('pending', 'confirmed', 'ready_for_delivery', 'in_progress')
    ORDER BY br.order_number ASC
");


$stmt->execute([$user['Id']]);
$userBooks = $stmt->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_request_id'])) {
    $requestId = intval($_POST['delete_request_id']);

    // تأكد أن هذا الطلب فعلاً يخص المستخدم الحالي
    $checkStmt = $conn->prepare("SELECT * FROM book_requests WHERE id = ? AND user_id = ? AND status = 'pending'");
    $checkStmt->execute([$requestId, $user['Id']]);
    $request = $checkStmt->fetch();

    if ($request) {
        $deleteStmt = $conn->prepare("DELETE FROM book_requests WHERE id = ?");
        $deleteStmt->execute([$requestId]);

        // إعادة التوجيه لتجنب إعادة الإرسال عند التحديث
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $deleteError = "لا يمكن حذف هذا الطلب.";
    }
}
$confirmSuccess = false;
$confirmError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_list'])) {
    // جلب الطلبات في حالة pending حسب ترتيب الإضافة
    $stmt = $conn->prepare("SELECT * FROM book_requests WHERE user_id = ? AND status = 'pending' ORDER BY created_at ASC");
    $stmt->execute([$user['Id']]);
    $pendingBooks = $stmt->fetchAll();

    if (count($pendingBooks) == 0) {
        $confirmError = "⚠️ لا توجد كتب لتأكيدها.";
    } else {
        try {
            $conn->beginTransaction();

            foreach ($pendingBooks as $i => $request) {
                $newStatus = ($i == 0) ? 'ready_for_delivery' : 'confirmed';
                $stmtUpdate = $conn->prepare("UPDATE book_requests SET status = ? WHERE id = ?");
                $stmtUpdate->execute([$newStatus, $request['id']]);
            }

            $conn->commit();
            $confirmSuccess = true;
        } catch (Exception $e) {
            $conn->rollBack();
            $confirmError = "حدث خطأ أثناء تأكيد القائمة.";
        }
    }
}
include_once('check_subscription.php');


?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>لوحة التحكم - سلفني</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <link rel="shortcut icon" href="../img/favicon.png" type="image/x-icon">
  <link rel="stylesheet" href="../css/profile.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <link rel="stylesheet" href="../css/register.css">
  <style>
.package-info.bronze {
    background-color: #f4e1d2; /* لون برونزي فاتح */
    border: 2px solid #b87333; /* لون برونزي داكن */
    color: #6a3e17;
    border-radius: 10px;
    padding: 15px;
    margin-top: 10px;
}
.package-info.gold {
    background-color: #fff8dc;
    border: 2px solid goldenrod;
    color: goldenrod;
    border-radius: 10px;
    padding: 15px;
    margin-top: 10px;
}

.package-info.silver {
    background-color: #f0f0f0;
    border: 2px solid silver;
    color: #555;
    border-radius: 10px;
    padding: 15px;
    margin-top: 10px;
}
.toast-modal {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background-color: #28a745;
  color: white;
  padding: 20px 25px;
  border-radius: 10px;
  box-shadow: 0 6px 12px rgba(0,0,0,0.3);
  z-index: 10000;
  min-width: 300px;
  text-align: center;
  animation: fadeIn 0.5s ease;
}

.toast-modal .close-btn {
  position: absolute;
  top: 8px;
  right: 12px;
  background: none;
  border: none;
  color: white;
  font-size: 20px;
  cursor: pointer;
}

@keyframes fadeIn {
  from {opacity: 0; transform: scale(0.9);}
  to {opacity: 1; transform: scale(1);}
}
</style>

</head>
<body>


<?php require_once '../templates/unave.php';?>

<div class="main-wrapper">
  <aside class="sidebar">
    <h3>القائمة</h3>
    <ul>
      <li><a onclick="showSection('account')">حسابي</a></li>
      <li><a onclick="showSection('verification')">توثيق الحساب
       <?php if (!$isVerified): ?>
      <span class="badge bg-warning text-dark">مهم</span>
      <?php endif;?>
      <?php if ($isVerified): ?>
      <span class="badge bg-success text-dark">تم التوثيق</span>
      <?php endif;?>
      </a></li>
      <li><a onclick="showSection('plans')">الباقات</a></li>
      <li><a onclick="showSection('bookList')">قائمة الكتب </a></li>
      <li><a onclick="showSection('support')">الدعم الفني</a></li>
    </ul>
  </aside>

  <div class="content">
    <!-- حسابي -->
    <div id="account" class="content-section active">
      <h2>معلومات الحساب</h2>
      <div class="container">
        <div class="registration-container">
          <form id="registrationForm" method="POST">
              <?php if (!empty($msg)) echo $msg; ?>
            <div class="form-row">
              <div class="form-group">
                <label for="firstName" class="form-label">الاسم الأول</label>
                <input type="text" name="fname" id="firstName" class="form-input" value="<?php echo htmlspecialchars($user['Fname']); ?>">
              </div>
              <div class="form-group">
                <label for="lastName" class="form-label">اللقب</label>
                <input type="text" name="lname" id="lastName" class="form-input" value="<?php echo htmlspecialchars($user['Lname']); ?>">
              </div>
            </div>

            <div class="form-group">
              <label for="email" class="form-label">البريد الإلكتروني</label>
              <input type="email" name="email" id="email" class="form-input" value="<?php echo htmlspecialchars($user['Email']); ?>">
            </div>

            <div class="form-group">
              <label for="phone" class="form-label">رقم الهاتف</label>
              <input type="tel" name="phone" id="phone" class="form-input" value="<?php echo htmlspecialchars($user['Phone']); ?>">
            </div>

            <button type="submit" name="update" id="submitRegistration" class="btn btn-success">تحديث المعلومات</button>
          </form>
        </div>
      </div>
    </div>

    <!-- توثيق الحساب -->
 
<div id="verification" class="content-section">
  <h2>التوثيق</h2>

  <?php if (!$isVerified): ?>
    <div class="alert alert-warning">⚠️ حسابك غير موثق. لن تتمكن من الوصول للاشتراكات حتى يتم التوثيق.</div>
    
    <?php if (!empty($msg2)) echo $msg2; ?>

    <form action="" method="POST" enctype="multipart/form-data">
    <div class="mb-3">
        <label for="identity_card" class="form-label">صورة بطاقة الهوية</label>
        <input type="file" class="form-control" name="identity_card" id="identity_card" accept="image/*" required>
    </div>
<div class="mb-3">
  <label for="wilaya" class="form-label">الولاية</label>
  <select class="form-select" name="wilaya" id="wilaya" required>
    <option value="">-- اختر ولايتك --</option>
    <?php
    $wilayas = [
      "أدرار", "الشلف", "الأغواط", "أم البواقي", "باتنة", "بجاية", "بسكرة", "بشار", "البليدة", "البويرة",
      "تمنراست", "تبسة", "تلمسان", "تيارت", "تيزي وزو", "الجزائر", "الجلفة", "جيجل", "سطيف", "سعيدة",
      "سكيكدة", "سيدي بلعباس", "عنابة", "قالمة", "قسنطينة", "المدية", "مستغانم", "المسيلة", "معسكر",
      "ورقلة", "وهران", "البيض", "إليزي", "برج بوعريريج", "بومرداس", "الطارف", "تندوف", "تيسمسيلت",
      "الوادي", "خنشلة", "سوق أهراس", "تيبازة", "ميلة", "عين الدفلى", "النعامة", "عين تموشنت", "غرداية",
      "غليزان"
    ];
    foreach ($wilayas as $w) {
      echo "<option value=\"$w\">$w</option>";
    }
    ?>
  </select>
</div>

    <div class="mb-3">
        <label for="address" class="form-label">عنوان السكن</label>
        <input type="text" class="form-control" name="address" id="address" placeholder=" ,البلدية,الحي,الشارع" required>
    </div>

   

    <button type="submit" name="submit_verification" class="btn btn-primary">إرسال طلب التوثيق</button>
</form>

  <?php else: ?>
    <div class="alert alert-success" role="alert">
      <b>تم تفعيل حسابك بنجاح، يمكنك اختيار الباقة والكتب التي تريدها الآن</b>
    </div>
    <?php if ($isVerified): 
    // استعلام لجلب بيانات التوثيق
    $stmt = $database->prepare("SELECT * FROM verification WHERE user_id = ? AND status = 1 ORDER BY submitted_at DESC LIMIT 1");
    $stmt->execute([$user['Id']]);
    $verification = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($verification): 
        $imgSrc = 'data:image/jpeg;base64,' . base64_encode($verification['identity_card']);
?>
<hr>
<div class="alert alert-success mt-3"><strong>✔ معلومات التوثيق</strong></div>
<div class="form-group">
    <label class="form-label">الولاية</label>
    <input type="text" class="form-control" value="<?= htmlspecialchars($verification['wilaya']) ?>" disabled>
</div>
<div class="form-group">
    <label class="form-label">العنوان</label>
    <input type="text" class="form-control" value="<?= htmlspecialchars($verification['address']) ?>" disabled>
</div>

<div class="form-group mt-3">
    <label class="form-label">صورة بطاقة الهوية</label><br>
    <img src="<?= $imgSrc ?>" alt="بطاقة الهوية" style="max-width:300px; border: 1px solid #ccc; border-radius:8px;">
</div>
<?php endif; ?>
<?php endif; ?>
  <?php endif; ?>
</div>

    <!-- الاشتراكات -->
    <div id="bookList" class="content-section">
      <h2>قائمة الكتب</h2>
      <?php if (!$isVerified): ?>
        <div class="alert alert-warning">⚠️ حسابك غير موثق. لن تتمكن من اختيار الكتب للاستلاف حتى يتم التوثيق.</div>
      <?php endif; ?>
      <p>عرض تفاصيل الاشتراك الحالي، تاريخ الانتهاء، وخيارات التجديد.</p>
      <div class="list">
  <?php if (!$isSubscribed): ?>
    <div class="alert alert-warning">⚠️ يجب أن تشترك في باقة لتتمكن من اختيار الكتب.</div>
  <?php else: ?>
    <?php if (empty($userBooks)): ?>
      <div class="alert alert-info">📚 لم تختر أي كتب بعد.</div>
    <?php else: ?>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>العنوان</th>
            <th>المؤلف</th>
            <th>الصفحات</th>
            <th>الحالة</th>
            <th>إجراء</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($userBooks as $book): ?>
            <tr>
              <td><?= htmlspecialchars($book['title']) ?></td>
              <td><?= htmlspecialchars($book['author']) ?></td>
              <td><?= htmlspecialchars($book['pages']) ?></td>
              <td>
                <?php
                 switch ($book['status']) {
    case 'pending': echo '⏳ لم تقم بالتأكيد '; break;
    case 'confirmed': echo '✅ مؤكد'; break;
    case 'ready_for_delivery': echo '📦 جاهز للتوصيل'; break;
    case 'in_progress': echo '📖 قيد القراءة'; break;
    default: echo htmlspecialchars($book['status']);
}

                ?>
              </td>
              <td>
  <?php if ($book['status'] == 'pending'): ?>
    <form method="post" onsubmit="return confirm('هل أنت متأكد من حذف هذا الكتاب؟');">
      <input type="hidden" name="delete_request_id" value="<?= $book['request_id'] ?>">
      <button type="submit" class="btn btn-sm btn-danger">🗑 حذف</button>
    </form>
  <?php else: ?>
    <span class="text-muted">🔒 لا يمكن الحذف</span>
  <?php endif; ?>
</td>

            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php if ($isSubscribed && !empty($userBooks)): ?>
   <!-- خارج أي <form> -->
<button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#confirmModal">✅ تأكيد القائمة</button>
<!-- Modal تأكيد القائمة -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmModalLabel">تأكيد القائمة</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
        </div>
        <div class="modal-body">
          <b>📚 سيتم تأكيد قائمة الكتب الخاصة بك. </b>
          <br>
          <br>
          ✅ أول كتاب سيتحول إلى "جاهز للتوصيل" والبقية إلى "مؤكد".<br>
           تأكد من انك اخترت الكتب الاربعة التي تريدها لا يمكن التراجع عن هذه الخطوة كما تأكد من أنك قرأت القوانين والشروط في موقعنا 

           
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
          <button type="submit" name="confirm_list" class="btn btn-success">تأكيد</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php endif; ?>
    <?php endif; ?>
  <?php endif; ?>
</div>

    </div>

    <!-- الدعم الفني -->
    <div id="support" class="content-section">
      <h2>الدعم الفني</h2>
      <p>يمكنك التواصل معنا لأي مشكلة أو استفسار تقني.</p>
      <?php if (!empty($msg3)) echo $msg3; ?>
      <form action="" method="POST">
  <div class="mb-3">
    <label for="supportSubject" class="form-label">عنوان الرسالة</label>
    <input type="text" name="subject" class="form-control" id="supportSubject" required>
  </div>
  <div class="mb-3">
    <label for="supportMessage" class="form-label">تفاصيل الاستفسار</label>
    <textarea name="message" class="form-control" id="supportMessage" rows="4" required></textarea>
  </div>
  <button type="submit" name="send_support" class="btn btn-success">إرسال</button>
</form>

    </div>
 <section>
  <!-- الباقات -->
  <div id="plans" class="content-section">
    <h2>الباقات المتاحة</h2>

    <?php if (!$isVerified): ?>
      <div class="alert alert-warning">⚠️ يجب توثيق الحساب لتفعيل الاشتراك في الباقات.</div>
    <?php endif; ?>

    <?php if ($isSubscribed): ?>
      <div class="alert alert-info">✅ أنت مشترك حاليًا في باقة: <strong><?= htmlspecialchars($planName) ?></strong>. لا يمكنك الاشتراك في باقة جديدة حتى تنتهي الحالية.</div>
      <br>
       <div class="container">
    <?php if (!$isVerified): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>ملاحظة :</strong> يرجى تفعيل حسابك لكي تستطيع اختيار باقتك.
        </div>

    <?php elseif ($isVerified && !$isSubscribed): ?>
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
    <?php else: ?>
      <div class="packages">

        <!-- باقة ذهبية -->
        <div class="package-card">
          <h3 style="color: goldenrod;">الذهبية</h3>
          <p style="color: goldenrod;" class="package-price">1500 د.ج / شهر</p>
          <ul class="package-features">
            <li>يحتوي على كل العناوين في سلفني</li>
            <li>دعم فني مباشر لكل مشكلة</li>
            <li>استعارة <b>4</b> كتب</li>
            <li>امكانية استعارة كتابين في وقت واحد</li>
            <li>امكانية اضافة 5 ايام بعد انتهاء الاشتراك</li>
            <li>الحصول على هدايا واضافات من فريق سلفني</li>
            <li>الاشتراك بشكل متكرر في هذه الباقة يجعلك <b>مستخدم مميز</b></li>
          </ul>
          <a href="user/redirect.php?plan_id=1" class="btn btn-success" <?= !$isVerified ? 'disabled' : '' ?>>اختر الباقة</a>
        </div>

        <!-- باقة فضية -->
        <div class="package-card">
          <h3 style="color: rgb(99, 97, 97);">فضية</h3>
          <p style="color: rgb(99, 97, 97);" class="package-price">1000 د.ج / شهر</p>
          <ul class="package-features">
            <li>الوصول الى الكتب الفضية</li>
            <li>استعارة <b>3</b> كتب</li>
            <li>الاشتراك بشكل متكرر في هذه الباقة يجعلك <b>مستخدم مميز</b></li>
          </ul>
          <a href="user/redirect.php?plan_id=2" class="btn btn-success" <?= !$isVerified ? 'disabled' : '' ?>>اختر الباقة</a>
        </div>

        <!-- باقة برونزية -->
        <div class="package-card">
          <h3 style="color: rgb(126, 26, 26);">برونزية</h3>
          <p style="color: rgb(126, 26, 26);" class="package-price">500 د.ج / شهر</p>
          <ul class="package-features">
            <li>الوصول إلى الكتب البرونزية</li>
            <li>دعم فني عبر البريد الإلكتروني</li>
            <li>استعارة <b>3</b> كتب</li>
          </ul>
          <a href="user/redirect.php?plan_id=3" class="btn btn-success" <?= !$isVerified ? 'disabled' : '' ?>>اختر الباقة</a>
        </div>

      </div>
    <?php endif; ?>
  </div>
</section>

<?php if ($confirmSuccess || !empty($confirmError)): ?>
<div class="toast-container position-fixed bottom-0 end-0 p-3">
  <div class="toast align-items-center text-white bg-<?= $confirmSuccess ? 'success' : 'danger' ?> show" role="alert">
    <div class="d-flex">
      <div class="toast-body">
        <?= $confirmSuccess ? '✅ تم تأكيد القائمة بنجاح!' : htmlspecialchars($confirmError) ?>
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<audio id="success-sound" src="../sounds/success.mp3" preload="auto"></audio>
<audio id="error-sound" src="../sounds/error.mp3" preload="auto"></audio>

<script>
window.onload = function () {
  <?php if ($confirmSuccess): ?>document.getElementById("success-sound").play();<?php endif; ?>
  <?php if (!empty($confirmError)): ?>document.getElementById("error-sound").play();<?php endif; ?>
};
</script>
<?php endif; ?>


<script>
window.addEventListener("pageshow", function (event) {
    if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
        window.location.reload();
    }
});

function showSection(id) {
    const sections = document.querySelectorAll('.content-section');
    sections.forEach(section => section.classList.remove('active'));
    document.getElementById(id).classList.add('active');
}
</script>
<script>
window.onload = function () {
  const successToast = document.querySelector('.toast.bg-success');
  const errorToast = document.querySelector('.toast.bg-danger');

  if (successToast) {
    const audio = document.getElementById('success-sound');
    audio && audio.play();
  }
  if (errorToast) {
    const audio = document.getElementById('error-sound');
    audio && audio.play();
  }
};
</script>
<script>
  if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
  }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-bmbxuPwQa2lc/Fvnf5wCr3e3BuSfN6l9j8vC7i6ajZXA1BqLFFQ8NE2w5cCefGha"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="../js/uhome.js"></script>

</body>
</html>
