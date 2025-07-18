<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 1 Jan 2000 00:00:00 GMT");

if (!isset($_SESSION['admin']) || $_SESSION['admin']['role'] !== "manager") {
    header("Location: ../login.php");
    exit;
}

$admin = $_SESSION['admin'];
?>
<?php
// الاتصال بقاعدة البيانات
try {
    $pdo = new PDO("mysql:host=localhost;dbname=salefny", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// جلب الأدمنات من قاعدة البيانات
$stmt = $pdo->query("SELECT * FROM admins ORDER BY created_at DESC");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
       <meta charset="UTF-8">
       <meta name="viewport" content="width=device-width, initial-scale=1.0">
       <title>مرحبا ايها السوبر ادمين</title>
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
    <h2 style="text-align:center;">ادارة الموقع</h2>
    <main >
    <br><br>
                    <div class="table-responsive">
                      <table class="table">
                        <thead>
                          <tr>
                            <th>
                              <div class="form-check form-check-muted m-0">
                                <label class="form-check-label">
                                  
                                <i class="input-helper"></i></label>
                              </div>
                            </th>
                            <th> اسم الأدمين</th>
                            <th> رقم التعريف</th>
                            <th> نسبة النشاط </th>
                            <th> المرتبة </th>
                            <th> تاريخ التسجيل </th>
                           
                            <th> اجراء </th>
                          </tr>
                        </thead>
                        <tbody>
<?php foreach ($admins as $admin): ?>
  <tr>
    <td>
      <div class="form-check form-check-muted m-0">
        <label class="form-check-label">
          <i class="input-helper"></i>
        </label>
      </div>
    </td>
    <td>
     
      <span class="pl-2"><?= htmlspecialchars($admin['name']) ?></span>
    </td>
    <td><?= $admin['id'] ?></td>
    <td>90%</td> <!-- يمكن تغييره لاحقًا بنظام نقاط -->
    <td><?= htmlspecialchars($admin['role']) ?></td>
    <td><?= $admin['created_at'] ?></td>
    <td>
      <!-- زر لفتح المودال -->
      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#adminModal<?= $admin['id'] ?>">
        اتخاذ إجراء
      </button>

      <!-- Modal -->
      <div class="modal fade" id="adminModal<?= $admin['id'] ?>" tabindex="-1" aria-labelledby="adminModalLabel<?= $admin['id'] ?>" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="adminModalLabel<?= $admin['id'] ?>">التعديل على معلومات <?= htmlspecialchars($admin['name']) ?></h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body">
              <form class="form-container" style="text-align:right;">
                <div class="form-group">
                  <label>البريد الإلكتروني</label>
                  <input type="email" class="form-control" value="<?= htmlspecialchars($admin['email']) ?>" disabled>
                </div>
                <div class="form-group">
                  <label>الاسم الكامل</label>
                  <input type="text" class="form-control" value="<?= htmlspecialchars($admin['name']) ?>" disabled>
                </div>
                <div class="form-group">
                  <label>الرتبة</label>
                  <select class="form-control" disabled>
                    <option selected><?= htmlspecialchars($admin['role']) ?></option>
                  </select>
                </div>
                <br>
                <button type="submit" class="btn btn-outline-warning btn-lg active" disabled>حفظ التغيرات</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </td>
  </tr>
<?php endforeach; ?>
</tbody>

                      </table>
                    </div>
                  </div>

    <br>
    <br>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
</body>
</html>
