<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 1 Jan 2000 00:00:00 GMT");


if (!isset($_SESSION['admin']) || $_SESSION['admin']['role'] !== "admin") {
    header("Location: ../login.php");
    exit;
}

$admin = $_SESSION['admin'];


try {
    $pdo = new PDO("mysql:host=localhost;dbname=salefny;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}


$stmt = $pdo->prepare("
    SELECT br.*, u.Fname, u.Lname, u.phone AS user_phone, b.title AS book_title
    FROM book_requests br
    JOIN users u ON br.user_id = u.Id
    JOIN books b ON br.book_id = b.id
    WHERE (br.status = 'ready_for_delivery' OR br.status = 'in_progress') 
        AND br.admin_id = ?
    ORDER BY br.created_at DESC
");
$stmt->execute([$admin['id']]);
$requests = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>طلبات الاستعارة  </title>
     <link rel="icon" href="http://localhost/Salefny/img/favicon.png" >
       <link rel="preconnect" href="https://fonts.googleapis.com">
       <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
       <link href="https://fonts.googleapis.com/css2?family=Tajawal&display=swap" rel="stylesheet">
       <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
       <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-4mCdeVHZFH9PPs0KPLghqN5o7Zk7mGn8dZn5JYSD/3dM5KKOvo0TE6JRxv7VlzKQZYZ/JnqHOC0mX5+oyZ6CxA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
       <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
       <link rel="stylesheet" href="css/verification-list.css"><style>
        body { font-family: 'Tajawal', sans-serif; background-color: #ccc; }
        img.user-img { width: 40px; height: 40px; border-radius: 50%; margin-left: 10px; }
    </style>
</head>
<body dir="rtl">

<!-- navbar -->
<?php require_once 'navbar.php'; ?>

<main class="container mt-5">
    <h2 class="text-center">إدارة المستخدمين</h2>
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>اسم المستخدم</th>
                    <th>الهاتف</th>
                    <th>الكتاب</th>
                    <th>تاريخ الطلب</th>
                    <th>الحالة</th>
                    <th>إجراء</th>
                </tr>
            </thead>
            <tbody>
<?php if (count($requests) > 0): ?>
    <?php foreach ($requests as $index => $req): ?>
        <tr>
            <td><?= $index + 1 ?></td>
            <td><?= htmlspecialchars($req['Fname'] . ' ' . $req['Lname']) ?></td>
            <td><?= htmlspecialchars($req['user_phone']) ?></td>
            <td><?= htmlspecialchars($req['book_title']) ?></td>
            <td><?= htmlspecialchars($req['created_at']) ?></td>
            <td>
                <?php if ($req['status'] === 'ready_for_delivery'): ?>
                    <span class="badge bg-warning text-dark">جاهز للتوصيل</span>
                <?php elseif ($req['status'] === 'in_progress'): ?>
                    <span class="badge bg-info text-dark">مسلم للمستخدم</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($req['status'] === 'ready_for_delivery'): ?>
                    <form method="POST" action="mark-delivered.php">
                        <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                        <button type="submit" class="btn btn-success btn-sm">تم التسليم</button>
                    </form>
                <?php elseif ($req['status'] === 'in_progress'): ?>
                    <form method="POST" action="mark-returned.php">
                        <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                        <button type="submit" class="btn btn-primary btn-sm">تم الإرجاع</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr><td colspan="7">لا توجد طلبات حالياً</td></tr>
<?php endif; ?>
</tbody>

        </table>
    </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
