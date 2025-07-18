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
try {
    $pdo = new PDO("mysql:host=localhost;dbname=salefny", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $pdo->prepare("DELETE FROM users WHERE Id = ?")->execute([$_POST['user_id']]);
}

$stmt = $pdo->query("SELECT * FROM users ORDER BY Id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>إدارة المستخدمين</title>
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
    <style>
        /* Style The Dropdown Button */
        .dropbtn {
            background-color: #FFF;
            color: black;
            padding: 16px;
            font-size: 16px;
            border: none;
            cursor: pointer;
        }

        /* The container <div> - needed to position the dropdown content */
        .dropdown {
            position: relative;
            display: inline-block;
        }

        /* Dropdown Content (Hidden by Default) */
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
        }

        /* Links inside the dropdown */
        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        /* Change color of dropdown links on hover */
        .dropdown-content a:hover {background-color: #f1f1f1}

        /* Show the dropdown menu on hover */
        .dropdown:hover .dropdown-content {
            display: block;
        }

        /* Change the background color of the dropdown button when the dropdown content is shown */
        .dropdown:hover .dropbtn {
            background-color: #3e8e41;
        }

        /* Navbar shadow */
        .navbar {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Styling for charts container */
        .charts-container {
            display: flex;
            justify-content: center;
            gap: 20px; /* Adds spacing between the charts */
        }

        .chart-item {
            width: 500px;
        }
        .card {
        border-radius: 12px;
        transition: transform 0.2s ease-in-out;
        margin: 20px;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .icon-box-success, .icon-box-danger {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        background-color: rgba(0, 123, 255, 0.1);
        border-radius: 50%;
      
    }

    .icon-box-danger {
        background-color: rgba(220, 53, 69, 0.1);
    }

    .icon-item {
        font-size: 24px;
        color: #007bff;
    }

    .icon-box-danger .icon-item {
        color: #dc3545;
        
    }
    </style>

</head>
<body dir="rtl">

<!-- navbar -->
<?php require_once 'navbar.php'; ?>

<main class="container mt-5">
    <h2 class="text-center">إدارة المستخدمين</h2>
    <div class="table-responsive mt-4">
        <table class="table table-striped table-bordered">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>الاسم الكامل</th>
                    <th>رقم التعريف</th>
                    <th>تاريخ التسجيل</th>
                     <th>موثق؟</th>
                    <th>إجراء</th>
                   
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $index => $user): 
                    $modalId = "modal_" . $user['Id'];
                ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td>
                        <img class="user-img" src="http://localhost/Salefny_Website/homePage/img/icon.jpg" alt="user">
                        <?= htmlspecialchars($user['Fname'] . ' ' . $user['Lname']) ?>
                    </td>
                    <td><?= $user['Id'] ?></td>
                    <td><?= $user['created_at'] ?? 'غير متوفر' ?></td>
                    <td>
    <?php if ($user['isVerified']): ?>
        <span class="badge bg-success">موثق</span>
    <?php else: ?>
        <span class="badge bg-secondary">غير موثق</span>
    <?php endif; ?>
</td>
                    <td>
                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#<?= $modalId ?>">حذف</button>
                    </td>
                    
                </tr>

                <!-- Modal حذف المستخدم -->
                <div class="modal fade" id="<?= $modalId ?>" tabindex="-1" aria-labelledby="<?= $modalId ?>Label" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content text-end">
                            <div class="modal-header">
                                <h5 class="modal-title">تأكيد الحذف</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                            </div>
                            <div class="modal-body">
                                <p>هل أنت متأكد أنك تريد حذف المستخدم: <strong><?= htmlspecialchars($user['Fname'] . ' ' . $user['Lname']) ?></strong>؟</p>
                            </div>
                            <div class="modal-footer">
                                <form method="POST">
                                    <input type="hidden" name="user_id" value="<?= $user['Id'] ?>">
                                    <button type="submit" name="delete_user" class="btn btn-danger">نعم، احذف</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
