<?php
ob_start();
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin']['role'] !== "super-admin") {
    header("Location: ../login.php");
    exit;
   
}
try {
    $pdo = new PDO("mysql:host=localhost;dbname=salefny", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $stmt = $pdo->query("
    SELECT br.id, 
           CONCAT(u.Fname, ' ', u.Lname) AS user_name, 
           b.title AS book_title, 
           br.status, 
           br.created_at, 
           br.updated_at,
           a.name AS admin_name
    FROM book_requests br
    JOIN users u ON br.user_id = u.Id
    JOIN books b ON br.book_id = b.id
    LEFT JOIN admins a ON br.admin_id = a.id
    ORDER BY br.created_at DESC
");


    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("فشل الاتصال: " . $e->getMessage());
}
?>
<?php

$admin = $_SESSION['admin'];
?>
<?php
$db = new PDO("mysql:host=localhost;dbname=salefny", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$visitors = $db->query("
    SELECT DATE_FORMAT(visit_date, '%M') AS month, COUNT(*) AS count
    FROM visitors_logs
    GROUP BY month
    ORDER BY STR_TO_DATE(month, '%M')
")->fetchAll(PDO::FETCH_ASSOC);
?>
<?php
try {
    $db = new PDO("mysql:host=localhost;dbname=salefny", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ✅ جلب عدد الكتب حسب الأدمن
    $admins_books = $db->query("
        SELECT admins.name AS admin_name, COUNT(books.id) AS book_count
        FROM books
        JOIN admins ON books.added_by_admin_id = admins.id
        GROUP BY admins.id
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>حياة الاستعارات - سلفني</title>
       <link rel="icon" href="http://localhost/Salefny/img/favicon.png" >
       <link rel="preconnect" href="https://fonts.googleapis.com">
       <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
       <link href="https://fonts.googleapis.com/css2?family=Tajawal&display=swap" rel="stylesheet">
       <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
       <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-4mCdeVHZFH9PPs0KPLghqN5o7Zk7mGn8dZn5JYSD/3dM5KKOvo0TE6JRxv7VlzKQZYZ/JnqHOC0mX5+oyZ6CxA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
       <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    <style>
        body { font-family: 'Tajawal', sans-serif; background: #f2f2f2; padding: 20px; }
        table { background: #fff; border-radius: 8px; overflow: hidden; }
        h2 { text-align: center; margin-bottom: 30px; }
        .status-label {
            padding: 5px 10px;
            border-radius: 6px;
            font-weight: bold;
        }
        .waiting      { background-color: #f8d7da; color: #721c24; }
        .confirmed    { background-color: #d1ecf1; color: #0c5460; }
        .ready        { background-color: #fff3cd; color: #856404; }
        .in_progress  { background-color: #cce5ff; color: #004085; }
        .returned     { background-color: #d4edda; color: #155724; }

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
 <?php require_once'navbar.php';?>
<body dir="rtl" style="padding: 0%;background-color: rgb(204, 204, 204);font-family: 'Tajawal', sans-serif;">
   
   
<h2>متابعة حالة الاستعارات</h2>
    <div class="container">
        <table class="table table-bordered text-center">
            <thead class="table-dark">
                <tr>
                    <th>رقم</th>
                    <th>المستخدم</th>
                    <th>الكتاب</th>
                      <th>المشرف المسؤول</th>
                    <th>الحالة الحالية</th>
                    <th>تاريخ الطلب</th>
                    <th>آخر تحديث</th>
                </tr>
            </thead>
            <tbody>
    <?php foreach ($requests as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['user_name']) ?></td>
            <td><?= htmlspecialchars($row['book_title']) ?></td>
            <td><?= htmlspecialchars($row['admin_name'] ?? '---') ?></td>
            <td>
                <span class="status-label <?= strtolower($row['status']) ?>">
                    <?= status_arabic($row['status']) ?>
                </span>
            </td>
            <td><?= htmlspecialchars($row['created_at']) ?></td>
            <td><?= htmlspecialchars($row['updated_at']) ?></td>
        </tr>
    <?php endforeach; ?>
</tbody>

        </table>
    </div>
</body>
<?php ob_end_flush(); ?>
</html>

<?php
function status_arabic($status) {
    switch ($status) {
        case 'waiting':         return 'في انتظار التأكيد';
        case 'confirmed':       return 'تم التأكيد';
        case 'ready_for_delivery': return 'جاهز للتوصيل';
        case 'in_progress':     return 'قيد الإعارة';
        case 'returned':        return 'تم الإرجاع';
        default:                return $status;
    }
}
?>
