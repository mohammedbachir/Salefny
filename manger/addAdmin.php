<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 1 Jan 2000 00:00:00 GMT");

// التحقق من الجلسة وصلاحية الدخول للسوبر أدمين فقط
if (!isset($_SESSION['admin']) || $_SESSION['admin']['role'] !== "manager") {
    header("Location: ../login.php");
    exit;
}

$admin = $_SESSION['admin'];
?>
<?php
// الاتصال بقاعدة البيانات
try {
    $db = new PDO("mysql:host=localhost;dbname=salefny", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// إضافة أدمين
if (isset($_POST['add_admin'])) {
    $name = htmlspecialchars($_POST['name']);
    $phone = htmlspecialchars($_POST['phone']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    
    $check = $db->prepare("SELECT * FROM admins WHERE email = :email");
    $check->bindParam(":email", $email);
    $check->execute();

    if ($check->rowCount() > 0) {
        echo '<div class="alert alert-danger">⚠️ هذا البريد الإلكتروني مستخدم بالفعل!</div>';
    } else {
        $wilaya = htmlspecialchars($_POST['wilaya']);

$add = $db->prepare("INSERT INTO admins (name, phone, email, password, wilaya, role) 
                     VALUES (:name, :phone, :email, :password, :wilaya, 'super-admin')");

$add->bindParam(":name", $name);
$add->bindParam(":phone", $phone);
$add->bindParam(":email", $email);
$add->bindParam(":password", $password);
$add->bindParam(":wilaya", $wilaya);

        if ($add->execute()) {
            echo '<div class="alert alert-success">✅ تم إضافة الأدمين بنجاح</div>';
        } else {
            echo '<div class="alert alert-danger">❌ حدث خطأ أثناء الإضافة</div>';
        }
    }
}

// تعديل أدمين
if (isset($_POST['update_admin'])) {
    $id = intval($_POST['id']);
    $name = htmlspecialchars($_POST['name']);
    $phone = htmlspecialchars($_POST['phone']);
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $update = $db->prepare("UPDATE admins SET name = :name, phone = :phone, email = :email, password = :password WHERE id = :id");
        $update->bindParam(":password", $hashedPassword);
    } else {
        $wilaya = htmlspecialchars($_POST['wilaya']);

$update = $db->prepare("UPDATE admins SET name = :name, phone = :phone, email = :email, wilaya = :wilaya WHERE id = :id");
    }
    $update->bindParam(":wilaya", $wilaya);
    $update->bindParam(":name", $name);
    $update->bindParam(":phone", $phone);
    $update->bindParam(":email", $email);
    $update->bindParam(":id", $id);

    if ($update->execute()) {
        echo '<div class="alert alert-success">✅ تم تعديل بيانات الأدمين بنجاح</div>';
    } else {
        echo '<div class="alert alert-danger">❌ فشل في تعديل الأدمين</div>';
    }
}

// حذف أدمين
if (isset($_POST['delete_admin'])) {
    $id = intval($_POST['id']);

    $delete = $db->prepare("DELETE FROM admins WHERE id = :id");
    $delete->bindParam(":id", $id);

    if ($delete->execute()) {
        echo '<div class="alert alert-success">✅ تم حذف الأدمين بنجاح</div>';
    } else {
        echo '<div class="alert alert-danger">❌ فشل في حذف الأدمين</div>';
    }
}

// جلب كل الأدمنز
$getAdmins = $db->prepare("SELECT * FROM admins");
$getAdmins->execute();
$admins = $getAdmins->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
       <meta charset="UTF-8">
       <meta name="viewport" content="width=device-width, initial-scale=1.0">
       <title>اضافة ادمين</title>
       <link rel="icon" href="http://localhost/Salefny/img/favicon.png" >
       <link rel="preconnect" href="https://fonts.googleapis.com">
       <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
       <link href="https://fonts.googleapis.com/css2?family=Tajawal&display=swap" rel="stylesheet">
       <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
       <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-4mCdeVHZFH9PPs0KPLghqN5o7Zk7mGn8dZn5JYSD/3dM5KKOvo0TE6JRxv7VlzKQZYZ/JnqHOC0mX5+oyZ6CxA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
       <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
        <link rel="stylesheet" href="../super-admin/css/madmin.css">
</head>
<body dir="rtl" style="padding: 0%;background-color: rgb(204, 204, 204);font-family: 'Tajawal', sans-serif;">
    <!--nav start-->
    <?php require_once'navbar.php';?>
    <!--nav end-->
    <br><br>
    <h2 style="text-align:center;">اضافة ادمين</h2>
    
    <main >
       <button type="button" class="btn btn-primary" style="margin-right: 10px;" data-bs-toggle="modal" data-bs-target="#exampleModal" data-bs-whatever="@mdo">+ اضافة أدمين</button>
       
       <br><br>
       <div  class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">معلومات الأدمين</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST">
          <div class="mb-3">
            <label for="recipient-name" class="col-form-label">اسم الأدمين:</label>
            <input type="text" name="name" class="form-control" id="recipient-name">
          </div>
          <div class="mb-3">
            <label for="recipient-name" class="col-form-label"> رقم الهاتف :</label>
            <input type="number" name="phone" class="form-control" id="recipient-name">
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
            <label for="recipient-name" class="col-form-label"> البريد الالكتروني :</label>
            <input type="email" name="email" class="form-control" id="recipient-name">
          </div>
          
          <div class="mb-3">
            <label for="recipient-name" class="col-form-label"> كلمة مرور خاصة به:</label>
            <input type="text" name="password" class="form-control" id="recipient-name">
          </div>
          <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">الغاء</button>
        <button type="submit" name="add_admin" class="btn btn-primary">اضافة أدمين</button>
      </div>
        </form>
      </div>
      
    </div>
  </div>
</div>


    </main>
   <section>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم الأدمين</th>
                    <th>الإيميل</th>
                    <th>الهاتف</th>
                    <th>الولاية</th>
                    <th>تاريخ التسجيل</th>
                    <th>الوظيفة</th>
                    <th>الإجراء</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($admins as $index => $admin): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td>
                        <?= htmlspecialchars($admin['name']) ?>
                    </td>
                    <td><?= htmlspecialchars($admin['email']) ?></td>
                    <td><?= htmlspecialchars($admin['phone']) ?></td>
                    <td><?= htmlspecialchars($admin['wilaya']) ?></td>
                    <td><?= htmlspecialchars($admin['created_at']) ?></td>
                    <td><?= htmlspecialchars($admin['role']) ?></td>
                    <td>
                        <!-- زر تعديل -->
                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $admin['id'] ?>">
                            تعديل
                        </button>

                        <!-- زر حذف -->
                      <form method="POST" style="display:inline;">
    <input type="hidden" name="id" value="<?= $admin['id'] ?>">
    <button type="submit" name="delete_admin" class="btn btn-sm btn-danger"
    onclick="return confirm('⚠️ هل أنت متأكد أنك تريد حذف هذا الأدمين؟');">
        حذف
    </button>
</form>


                    </td>
                </tr>

                <!-- Modal تعديل -->
                <div class="modal fade" id="editModal<?= $admin['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $admin['id'] ?>" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">تعديل معلومات الأدمين</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                      </div>
                      <div class="modal-body">
                        <form method="POST" >
                            <input type="hidden" name="id" value="<?= $admin['id'] ?>">
                            <div class="mb-3">
                                <label class="form-label">اسم الأدمين:</label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($admin['name']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">رقم الهاتف:</label>
                                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($admin['phone']) ?>" required>
                            </div>
                            <div class="mb-3">
    <label class="form-label">الولاية:</label>
    <select name="wilaya" class="form-select" required>
        <option value="">-- اختر الولاية --</option>
        <?php
        foreach ($wilayas as $w) {
            $selected = ($admin['wilaya'] == $w) ? 'selected' : '';
            echo "<option value=\"$w\" $selected>$w</option>";
        }
        ?>
    </select>
</div>

                            <div class="mb-3">
                                <label class="form-label">البريد الإلكتروني:</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin['email']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">كلمة المرور (اتركها فارغة إذا لا تريد تغييرها):</label>
                                <input type="password" name="password" class="form-control">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                            </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- نهاية Modal تعديل -->

                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>