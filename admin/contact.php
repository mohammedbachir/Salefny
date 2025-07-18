<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 1 Jan 2000 00:00:00 GMT");

// التحقق من الجلسة وصلاحية الدخول للسوبر أدمين فقط
if (!isset($_SESSION['admin']) || $_SESSION['admin']['role'] !== "admin") {
    header("Location: ../login.php");
    exit;
}

$admin = $_SESSION['admin'];
?>
<?php 
try {
    $db = new PDO("mysql:host=localhost;dbname=salefny", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_POST['send_message'])) {
        $subject = trim($_POST['subject']);
        $message = trim($_POST['message']);
        $admin_id = $admin['id'];

        if (!empty($subject) && !empty($message)) {
            $stmt = $db->prepare("INSERT INTO messages (admin_id, subject, message) VALUES (?, ?, ?)");
            $stmt->execute([$admin_id, $subject, $message]);

            echo "<script>alert('✅ تم إرسال رسالتك بنجاح إلى السوبر أدمين');</script>";
        } else {
            echo "<script>alert('⚠️ يرجى ملء جميع الحقول');</script>";
        }
    }
} catch (PDOException $e) {
    die("فشل الاتصال: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
       <meta charset="UTF-8">
       <meta name="viewport" content="width=device-width, initial-scale=1.0">
       <title>ارسال رسالة للسوبر ادمين</title>
       <link rel="icon" href="http://localhost/Salefny/img/favicon.png" >
       <link rel="preconnect" href="https://fonts.googleapis.com">
       <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
       <link href="https://fonts.googleapis.com/css2?family=Tajawal&display=swap" rel="stylesheet">
       <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
       <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-4mCdeVHZFH9PPs0KPLghqN5o7Zk7mGn8dZn5JYSD/3dM5KKOvo0TE6JRxv7VlzKQZYZ/JnqHOC0mX5+oyZ6CxA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
       <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
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
    .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 40px auto;
        }
        .form-group label {
            color: #555;
        }
        .form-control {
            border-radius: 5px;
            border: 1px solid #ced4da;
            transition: border-color 0.3s;
        }
        .form-control:focus {
            border-color: #ffcc00;
            box-shadow: 0 0 0 0.2rem rgba(255, 204, 0, 0.25);
        }
        .btn-outline-warning {
            color: #ffcc00;
            border-color: #ffcc00;
            transition: background-color 0.3s, color 0.3s;
        }
        .btn-outline-warning:hover {
            color: #fff;
            background-color: #ffcc00;
        }
        .table th img, .jsgrid .jsgrid-table th img, .table td img, .jsgrid .jsgrid-table td img {
    width: 30px;
    height: 30px;
    border-radius: 100%;
}

    </style>
</head>
<body dir="rtl" style="padding: 0%;background-color: rgb(204, 204, 204);font-family: 'Tajawal', sans-serif;">
    <!--nav start-->
    <?php require_once'navbar.php';?>
    <!--nav end-->
    <main style="text-align:center; ">
       
   
   
    <br><br>
    <h2 style="text-align:center;"> التواصل مع المدير</h2>
    <main >
    <br><br>
    <form class="form-container" method="POST">
    <div class="form-group">
        <label>عنوان الرسالة</label>
        <input type="text" class="form-control" name="subject" placeholder="أدخل عنوان الرسالة" required>
    </div>
    <div class="form-group">
        <label>محتوى الرسالة</label>
        <textarea class="form-control" name="message" rows="5" placeholder="أدخل محتوى الرسالة" required></textarea>
    </div>
    <br>
    <button type="submit" name="send_message" class="btn btn-outline-warning btn-lg active">إرسال</button>
</form>
    <br>
    <br>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
</body>
</html>
