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
    $pdo = new PDO("mysql:host=localhost;dbname=salefny", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

   
    $stmt = $pdo->query("
        SELECT support_messages.*, users.Fname AS user_name
        FROM support_messages
        JOIN users ON support_messages.user_id = users.id
        ORDER BY support_messages.created_at DESC
    ");
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $pendingMessages = $pdo->query("
        SELECT COUNT(*) FROM support_messages WHERE status = 'pending'
    ")->fetchColumn();

} catch (PDOException $e) {
    die("فشل الاتصال: " . $e->getMessage());
}
?>
<?php 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_message'])) {
    $messageId = intval($_POST['message_id']);
    $reply = $_POST['reply_message'];

   
    $stmt = $pdo->prepare("
        SELECT support_messages.*, users.email, users.Fname 
        FROM support_messages 
        JOIN users ON support_messages.user_id = users.id 
        WHERE support_messages.id = ?
    ");
    $stmt->execute([$messageId]);
    $message = $stmt->fetch();

    if ($message) {
        
        $update = $pdo->prepare("UPDATE support_messages SET reply_message = ?, status = 'answered' WHERE id = ?");
        $update->execute([$reply, $messageId]);

        require_once '../mailing.php';
            $mail->addAddress($message['email']);
            $mail->Subject = " مرحبًا بك في سلفني 📚 الرد على سؤالك";

            $mail->Body = '
            <!DOCTYPE html>
            <html lang="ar" dir="rtl">
            <head>
                <meta charset="UTF-8">
                <style>
                    body {
                        font-family: "Tahoma", sans-serif;
                        background-color: #f9f9f9;
                        direction: rtl;
                        color: #333;
                    }
                    .container {
                        background-color: #ffffff;
                        margin: 0 auto;
                        padding: 30px;
                        max-width: 600px;
                        border-radius: 10px;
                        box-shadow: 0 0 15px rgba(0,0,0,0.1);
                    }
                    .logo {
                        text-align: center;
                        margin-bottom: 20px;
                    }
                    .logo img {
                        height: 80px;
                    }
                    .title {
                        color: #2c3e50;
                        font-size: 22px;
                        font-weight: bold;
                        margin-bottom: 15px;
                    }
                    .message {
                        font-size: 16px;
                        line-height: 1.8;
                    }
                    .cta {
                        display: inline-block;
                        margin-top: 25px;
                        padding: 12px 25px;
                        background-color: #28a745;
                        color: white;
                        text-decoration: none;
                        border-radius: 8px;
                    }
                    .footer {
                        margin-top: 30px;
                        font-size: 14px;
                        color: #888;
                        text-align: center;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="logo">
                        <img src="img/favicon.jpg" alt="شعار سلفني">
                    </div>
                    <div class="title">مرحبًا بك في سلفني، ' . $message['Fname'] . ' 👋</div>
                    <div class="message">
                     "بخصوص طلب الدعم الخاص بك بعنوان: ' . $message['subject'] . '
                     الرد من فريق الدعم:
                     '.$reply.'  
                    </div>
                   
                    
                </div>
            </body>
            </html>
            ';
            $mail->isHTML(true);
            $mail->send();
$log = $pdo->prepare("
        INSERT INTO admin_logs (admin_id, action, target_type, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    $log->execute([
        $admin['id'],
        "رد على رسالة الدعم بعنوان: '{$message['subject']}' للمستخدم {$message['Fname']}",
        "support_message"
    ]);
        header("Location: inbox.php?answered=1");
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>إدارة طلبات الدعم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
img{    width: 40px;
    height: 40px;
    border-radius: 100%;
}
.large-id-card {
    width: 80% !important; /* تحديد عرض الصورة */
    max-width: 400px !important; /* أقصى عرض للصورة */
    height: auto !important; /* تعديل الارتفاع تلقائياً */
    border: 2px solid #ddd;
    border-radius: 0;
    padding: 5px;
    box-shadow: 0px 0px 5px rgba(0,0,0,0.2);
}


    </style>
</head>
<body dir="rtl" style="padding: 0%;background-color: rgb(204, 204, 204);font-family: 'Tajawal', sans-serif;">
    <!--nav start-->
   

    <?php require_once'navbar.php';?>
    <!--nav end-->
    <main style="text-align:center; ">
       

<div class="container mt-5">
    <h2 class="mb-4">إدارة طلبات الدعم</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>اسم المرسل</th>
                <th>عنوان الرسالة</th>
                <th>تاريخ الإرسال</th>
                <th>الحالة</th>
                <th>الإجراء</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($messages as $msg): ?>
                <tr>
                    <td><?= htmlspecialchars($msg['id']) ?></td>
                    <td><?= htmlspecialchars($msg['user_name']) ?></td>
                    <td><?= htmlspecialchars($msg['subject']) ?></td>
                    <td><?= htmlspecialchars($msg['created_at']) ?></td>
                    <td>
                        <?php if ($msg['status'] == 'pending'): ?>
                            <span class="badge bg-warning text-dark">قيد الانتظار</span>
                        <?php else: ?>
                            <span class="badge bg-success">تم الرد</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <!-- زر عرض التفاصيل -->
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal<?= $msg['id'] ?>">عرض</button>

                        <!-- زر حذف -->
                        <a href="delete_support.php?id=<?= $msg['id'] ?>" 
                           onclick="return confirm('هل أنت متأكد من حذف هذه الرسالة؟');"
                           class="btn btn-danger">
                            حذف
                        </a>
                    </td>
                </tr>

                <!-- مودال التفاصيل -->
                <!-- المودال الخاص بكل رسالة -->
<div class="modal fade" id="modal<?= $msg['id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><?= htmlspecialchars($msg['subject']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>من:</strong> <?= htmlspecialchars($msg['user_name']) ?></p>
                    <p><strong>محتوى الرسالة:</strong></p>
                    <p><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                    <p><strong>تاريخ الإرسال:</strong> <?= htmlspecialchars($msg['created_at']) ?></p>

                    <div class="mb-3">
                        <label class="form-label">ردك على الرسالة:</label>
                        <textarea name="reply_message" class="form-control" rows="4" required><?= htmlspecialchars($msg['reply_message'] ?? '') ?></textarea>
                    </div>

                    <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">إرسال الرد</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                </div>
            </form>
        </div>
    </div>
</div>

            <?php endforeach; ?>
        </tbody>
    </table>
</div>
 <?php if (isset($_GET['answered'])): ?>
    <div class="alert alert-success text-center">
        تم تحديد الرسالة كـ "تم الرد" بنجاح!
    </div>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
   