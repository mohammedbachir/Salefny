<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 1 Jan 2000 00:00:00 GMT");

if (!isset($_SESSION['admin']) || $_SESSION['admin']['role'] !== "super-admin") {
    header("Location: ../login.php");
    exit;
}

$admin = $_SESSION['admin'];

try {
    $db = new PDO("mysql:host=localhost;dbname=salefny", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    function logAction($db, $admin_id, $action, $target_id, $target_type) {
        $stmt = $db->prepare("INSERT INTO admin_logs (admin_id, action, target_id, target_type) VALUES (?, ?, ?, ?)");
        $stmt->execute([$admin_id, $action, $target_id, $target_type]);
    }

    $categories = $db->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
    $plans = $db->query("SELECT * FROM plans")->fetchAll(PDO::FETCH_ASSOC);

    if (isset($_POST['add_category'])) {
        $categoryName = trim($_POST['category_name']);
        if (!empty($categoryName)) {
            $stmt = $db->prepare("INSERT INTO categories (name) VALUES (:name)");
            $stmt->execute(['name' => $categoryName]);

            $cat_id = $db->lastInsertId();
            logAction($db, $admin['id'], 'add_category', $cat_id, 'category');

            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }

    if (isset($_GET['delete_category'])) {
        $id = intval($_GET['delete_category']);
        $stmt = $db->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);

        logAction($db, $admin['id'], 'delete_category', $id, 'category');

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if (isset($_POST['add_book'])) {
        $title = trim($_POST['title']);
        $author = trim($_POST['author']);
        $pages = intval($_POST['pages']);
        $category_id = intval($_POST['category_id']);
        $plan_id = intval($_POST['plan_id']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);
        $copies_available = intval($_POST['copies']);
        $added_by_admin_id = $admin['id'];

        $cover = null;
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $cover = file_get_contents($_FILES['cover_image']['tmp_name']);
        }

        $stmt = $db->prepare("INSERT INTO books 
            (title, author, pages, category_id, plan_id, description, price, cover_image, copies_available, added_by_admin_id) 
            VALUES 
            (:title, :author, :pages, :category_id, :plan_id, :description, :price, :cover_image, :copies_available, :added_by_admin_id)");

        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':author', $author);
        $stmt->bindParam(':pages', $pages);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':plan_id', $plan_id);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':cover_image', $cover, PDO::PARAM_LOB);
        $stmt->bindParam(':copies_available', $copies_available);
        $stmt->bindParam(':added_by_admin_id', $added_by_admin_id);
        $stmt->execute();

        $book_id = $db->lastInsertId();
        logAction($db, $added_by_admin_id, 'add_book', $book_id, 'book');

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if (isset($_POST['update_book'])) {
        $book_id = intval($_POST['book_id']);
        $title = trim($_POST['title']);
        $author = trim($_POST['author']);
        $pages = intval($_POST['pages']);
        $category_id = intval($_POST['category_id']);
        $plan_id = intval($_POST['plan_id']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);
        $copies = intval($_POST['copies']);

        if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
            $cover = file_get_contents($_FILES['cover']['tmp_name']);
            $stmt = $db->prepare("UPDATE books SET 
                title = :title, 
                author = :author, 
                pages = :pages, 
                category_id = :category_id, 
                plan_id = :plan_id, 
                description = :description, 
                price = :price, 
                cover_image = :cover_image,
                copies_available = :copies
                WHERE id = :id
            ");
            $stmt->bindParam(':cover_image', $cover, PDO::PARAM_LOB);
        } else {
            $stmt = $db->prepare("UPDATE books SET 
                title = :title, 
                author = :author, 
                pages = :pages, 
                category_id = :category_id, 
                plan_id = :plan_id, 
                description = :description, 
                price = :price, 
                copies_available = :copies
                WHERE id = :id
            ");
        }

        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':author', $author);
        $stmt->bindParam(':pages', $pages);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':plan_id', $plan_id);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':copies', $copies);
        $stmt->bindParam(':id', $book_id);
        $stmt->execute();

        logAction($db, $admin['id'], 'update_book', $book_id, 'book');

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // ✅ حذف الكتاب
    if (isset($_GET['delete_book'])) {
        $book_id = intval($_GET['delete_book']);

        $stmt = $db->prepare("DELETE FROM books WHERE id = :id");
        $stmt->execute(['id' => $book_id]);

        logAction($db, $admin['id'], 'delete_book', $book_id, 'book');

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    $books = $db->query("
        SELECT books.*, 
               categories.name AS category_name, 
               plans.name AS plan_name
        FROM books 
        JOIN categories ON books.category_id = categories.id
        JOIN plans ON books.plan_id = plans.id
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("⚠️ خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="ar">
<head>
       <meta charset="UTF-8">
       <meta name="viewport" content="width=device-width, initial-scale=1.0">
       <title>اضافة كتب</title>
       <link rel="icon" href="http://localhost/Salefny/img/favicon.png" >
       <link rel="preconnect" href="https://fonts.googleapis.com">
       <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
       <link href="https://fonts.googleapis.com/css2?family=Tajawal&display=swap" rel="stylesheet">
       <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
       <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-4mCdeVHZFH9PPs0KPLghqN5o7Zk7mGn8dZn5JYSD/3dM5KKOvo0TE6JRxv7VlzKQZYZ/JnqHOC0mX5+oyZ6CxA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
       <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
        <link rel="stylesheet" href="css/madmin.css">
</head>
<body dir="rtl" style="padding: 0%;background-color: rgb(204, 204, 204);font-family: 'Tajawal', sans-serif;">
    <!--nav start-->
    <?php require_once'navbar.php';?>
    <!--nav end-->
    <br><br>
    <h2 style="text-align:center;">اضافة كتب</h2>
    
    <main >
       <button type="button" class="btn btn-primary" style="margin-right: 10px;" data-bs-toggle="modal" data-bs-target="#exampleModal" data-bs-whatever="@mdo">+ اضافة كتب</button>
       <button type="button" class="btn btn-primary" style="margin-right: 10px;" data-bs-toggle="modal" data-bs-target="#exampleModal3" data-bs-whatever="@mdo">+ اضافة تصنيف</button>
       
       <br><br>
       <div  class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">معلومات الكتاب</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" enctype="multipart/form-data">
  <div class="mb-3">
    <label class="col-form-label">اسم الكتاب:</label>
    <input type="text" class="form-control" name="title" required>
  </div>
  <div class="mb-3">
    <label class="col-form-label">اسم الكاتب:</label>
    <input type="text" class="form-control" name="author" required>
  </div>
  <div class="mb-3">
    <label class="col-form-label">عدد الصفحات:</label>
    <input type="number" class="form-control" name="pages" required>
  </div>
  <div class="mb-3">
    <label class="col-form-label">التصنيف:</label>
    <select class="form-control" name="category_id" required>
  <option value="" disabled selected>اختر الصنف</option>
  <?php foreach($categories as $category): ?>
      <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
  <?php endforeach; ?>
</select>

  </div>
  <div class="mb-3">
    <label class="col-form-label">الباقة المناسبة:</label>
    <select class="form-control" name="plan_id" required>
  <option value="" disabled selected>اختر الصنف</option>
  <?php foreach($plans as $plan): ?>
      <option value="<?= $plan['id'] ?>"><?= htmlspecialchars($plan['name']) ?></option>
  <?php endforeach; ?>
</select>
  </div>
  <div class="mb-3">
    <label class="col-form-label">وصف الكتاب:</label>
    <textarea class="form-control" name="description" required></textarea>
  </div>
  <div class="mb-3">
    <label class="col-form-label">السعر:</label>
    <input type="number" class="form-control" name="price" step="0.01" required>
  </div>
  <div class="mb-3">
    <label class="col-form-label">غلاف الكتاب:</label>
    <input type="file" class="form-control" name="cover_image" required>
  </div>
  <div class="mb-3">
    <label class="col-form-label">عدد النسخ:</label>
    <input type="number" class="form-control" name="copies" required>
  </div>

  <div class="modal-footer">
    <button type="submit" name="add_book" class="btn btn-primary">إضافة كتاب</button>
  </div>
</form>

      </div>
      
    </div>
  </div>
</div>
<div class="modal fade" id="exampleModal3" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
    
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">إدارة التصنيفات</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
      </div>
      
      <div class="modal-body">
        
        <!-- إضافة تصنيف جديد -->
        <form method="POST">
          <div class="mb-3">
            <label class="col-form-label">اسم التصنيف:</label>
            <input type="text" name="category_name" class="form-control" id="recipient-name" placeholder="أدخل اسم التصنيف">
          </div>
          <button type="submit" name="add_category" class="btn btn-primary">إضافة التصنيف</button>
        </form>

        <hr>

        <!-- قائمة التصنيفات -->
        <h6 class="mt-3">قائمة التصنيفات الحالية:</h6>
        <ul class="list-group">
<?php foreach($categories as $category): ?>
  <li class="list-group-item d-flex justify-content-between align-items-center">
    <?= htmlspecialchars($category['name']) ?>
    <a href="?delete_category=<?= $category['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا التصنيف؟');">حذف</a>
  </li>
<?php endforeach; ?>
</ul>

      </div>
      
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
      </div>

    </div>
  </div>
</div>

    </main>
    <section>
        <div class="table-responsive">
                    <div class="table-responsive">
    <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>اسم الكتاب</th>
                <th>التصنيف</th>
                <th>الباقة</th>
                <th>تاريخ الإضافة</th>
                <th>الإجراء</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $books = $db->query("
                SELECT books.*, 
                       categories.name AS category_name, 
                       plans.name AS plan_name
                FROM books 
                JOIN categories ON books.category_id = categories.id
                JOIN plans ON books.plan_id = plans.id
            ")->fetchAll(PDO::FETCH_ASSOC);

            $counter = 1;
            foreach($books as $book):
            ?>
            <tr>
                <td><?= $counter++ ?></td>
                <td>
    <img src="data:image/jpeg;base64,<?= base64_encode($book['cover_image']); ?>" 
         width="90" height="130" 
         style="border-radius:10px; margin-left:12px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
    <div style="display:inline-block; vertical-align:top;">
        <strong style="font-size:18px;"><?= htmlspecialchars($book['title']) ?></strong>
    </div>
</td>

                <td><?= htmlspecialchars($book['category_name']) ?></td>
                <td><?= htmlspecialchars($book['plan_name']) ?></td>
                <td><?= htmlspecialchars($book['created_at']) ?></td>
                <td>
                    <button type="button" 
        class="btn btn-sm btn-warning" 
        data-bs-toggle="modal" 
        data-bs-target="#editModal<?= $book['id'] ?>">
    تعديل / حذف
</button>
                </td>
            </tr>
            <?php endforeach; ?>
   
<!-- Modal -->
<!-- Modal -->
<!-- Modal -->
<?php foreach($books as $book): ?>
<!-- Modal تعديل الكتاب -->
<div class="modal fade" id="editModal<?= $book['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title">تعديل الكتاب: <?= htmlspecialchars($book['title']) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
      </div>
      
      <div class="modal-body">
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="book_id" value="<?= $book['id'] ?>">

          <div class="mb-3">
            <label class="col-form-label">اسم الكتاب:</label>
            <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($book['title']) ?>" required>
          </div>

          <div class="mb-3">
            <label class="col-form-label">اسم الكاتب:</label>
            <input type="text" class="form-control" name="author" value="<?= htmlspecialchars($book['author']) ?>" required>
          </div>

          <div class="mb-3">
            <label class="col-form-label">عدد الصفحات:</label>
            <input type="number" class="form-control" name="pages" value="<?= htmlspecialchars($book['pages']) ?>" required>
          </div>

          <div class="mb-3">
            <label class="col-form-label">التصنيف:</label>
            <select class="form-control" name="category_id" required>
              <?php foreach($categories as $category): ?>
                <option value="<?= $category['id'] ?>" <?= $category['id'] == $book['category_id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($category['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="col-form-label">الباقة:</label>
            <select class="form-control" name="plan_id" required>
              <?php foreach($plans as $plan): ?>
                <option value="<?= $plan['id'] ?>" <?= $plan['id'] == $book['plan_id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($plan['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="col-form-label">وصف الكتاب:</label>
            <textarea class="form-control" name="description" required><?= htmlspecialchars($book['description']) ?></textarea>
          </div>

          <div class="mb-3">
            <label class="col-form-label">السعر:</label>
            <input type="number" class="form-control" name="price" step="0.01" value="<?= htmlspecialchars($book['price']) ?>" required>
          </div>

          <div class="mb-3">
            <label class="col-form-label">غلاف الكتاب (اتركه فارغ إن لم ترغب في تغييره):</label>
            <input type="file" class="form-control" name="cover">
          </div>

          <div class="mb-3">
            <label class="col-form-label">عدد النسخ:</label>
            <input type="number" class="form-control" name="copies" value="<?= htmlspecialchars($book['copies_available']) ?>" required>
          </div>

          <div class="modal-footer">
            <button type="submit" name="update_book" class="btn btn-success">تعديل</button>
            <a href="?delete_book=<?= $book['id'] ?>" class="btn btn-danger" onclick="return confirm('هل أنت متأكد أنك تريد حذف هذا الكتاب؟')">حذف</a>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
          </div>

        </form>
      </div>

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

    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>