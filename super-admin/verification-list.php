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
?>
<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=salefny", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}

$stmt = $pdo->query("SELECT v.*, u.Fname, u.Lname,u.Phone, u.Email FROM verification v INNER JOIN users u ON v.user_id = u.Id ORDER BY v.submitted_at DESC");
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=salefny", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'] ?? null;
    $email = $_POST['email'] ?? '';
    $fullName = $_POST['name'] ?? '';

    if (isset($_POST['approve'])) {
        $pdo->prepare("UPDATE users SET isVerified = 1 WHERE Id = ?")->execute([$userId]);
        $pdo->prepare("UPDATE verification SET status = 1 WHERE user_id = ?")->execute([$userId]);

        require_once '../mailing.php';
            $mail->addAddress($email);
            $mail->Subject = "مرحبًا بك في سلفني 📚";

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
                    <div class="title">مرحبًا بك في سلفني، ' . htmlspecialchars($fullName) . ' 👋</div>
                    <div class="message">
                    لقد تم توثيق حسابكم بنجاح 
                    </div>
                    <div class="message">
                       توجه الى  لوحة التحكم للتمكن من شراء الباقة واستعارة الكتب.
                    </div>
                    <a href="https://salefny.com/user/userhme.php" class="cta">الذهاب إلى لوحة التحكم</a>
                    <div class="footer">
                        هذا البريد مرسل تلقائيًا من منصة سلفني، لا ترد عليه.
                        إذا لم تكن أنت من قام بالتسجيل، تجاهل هذا البريد.
                    </div>
                </div>
            </body>
            </html>
            ';
            $mail->isHTML(true);
            $mail->send();
    } elseif (isset($_POST['reject'])) {
        $pdo->prepare("UPDATE verification SET status = -1 WHERE user_id = ?")->execute([$userId]);

        require_once '../mailing.php';
            $mail->addAddress($email);
            $mail->Subject = "مرحبًا بك في سلفني 📚";

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
                    <div class="title">مرحبًا بك في سلفني، ' . htmlspecialchars($fullName) . ' 👋</div>
                    <div class="message">
                    لقد تم توثيق حسابكم بنجاح 
                    </div>
                    <div class="message">
                       توجه الى  لوحة التحكم للتمكن من شراء الباقة واستعارة الكتب.
                    </div>
                    <a href="https://salefny.com/user/userhme.php" class="cta">الذهاب إلى لوحة التحكم</a>
                    <div class="footer">
                        هذا البريد مرسل تلقائيًا من منصة سلفني، لا ترد عليه.
                        إذا لم تكن أنت من قام بالتسجيل، تجاهل هذا البريد.
                    </div>
                </div>
            </body>
            </html>
            ';
            $mail->isHTML(true);
            $mail->send();
      }
}

$stmt = $pdo->query("SELECT v.*, u.Fname, u.Lname, u.Phone, u.Email FROM verification v INNER JOIN users u ON v.user_id = u.Id WHERE v.status = 0 ORDER BY v.submitted_at DESC");
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
       <link rel="stylesheet" href="css/verification-list.css">
</head>
<body dir="rtl" style="padding: 0%;background-color: rgb(204, 204, 204);font-family: 'Tajawal', sans-serif;">
    <!--nav start-->
    <?php require_once'navbar.php';?>
    <!--nav end-->
    <main style="text-align:center; ">
       
   
   
    <br><br>
    <h2 style="text-align:center;">طلبات التوثيق</h2>
     <?php if (count($requests) > 0): ?>
        <?php foreach ($requests as $req): ?>
    <main >
   
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
                            <th> اسم المرسل</th>
                            <th> المرتبة </th>
                          
                            <th> تاريخ الرسالة </th>
                           
                            <th> اجراء </th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td>
                              <div class="form-check form-check-muted m-0">
                                <label class="form-check-label">
                                  
                                <i class="input-helper"></i></label>
                              </div>
                            </td>
                            <td>
                              <img src="http://localhost/Salefny_Website/homePage/img/icon.jpg" alt="image">
                              <span class="pl-2"><?= htmlspecialchars($req['Fname'] . ' ' . $req['Lname']) ?> </span>
                            </td>
                            <td>  <?= $req['id'] ?> </td>
                            
                           
                            <td> <?= $req['submitted_at'] ?></td>
                            <td> <!-- Button trigger modal -->
                              <?php foreach ($requests as $req): ?>
    <?php $modalId = 'modal_' . $req['id']; ?>
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#<?= $modalId ?>">
  اتخاذ اجراء
</button>

 
    <?php
    $imgData = base64_encode($req['identity_card']);
  $imgSrc = 'data:image/jpeg;base64,' . $imgData;
    ?>
<div class="modal fade" id="<?= $modalId ?>" tabindex="-1" aria-labelledby="<?= $modalId ?>" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?= htmlspecialchars($req['Fname'] . ' ' . $req['Lname']) ?> </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <main>
          <form method="POST" class="form-container" style="text-align:right;">
          <input type="hidden" name="user_id" value="<?= $req['user_id'] ?>">
    <input type="hidden" name="email" value="<?= $req['Email'] ?>">
    <input type="hidden" name="name" value="<?= $req['Fname'] . ' ' . $req['Lname'] ?>">  
          <div dir="rtl" class="form-row">
              <div class="form-group">
                <label for="inputEmail4">البريد الالكتروني</label>
                <input type="email" class="form-control" id="inputEmail4" value="<?= htmlspecialchars($req['Email']) ?>" disabled>
              </div>
            </div>
            <div dir="rtl" class="form-group">
              <label for="inputAddress">الاسم الكامل</label>
              <input type="text" class="form-control" id="inputAddress" value="<?= htmlspecialchars($req['Fname'] . ' ' . $req['Lname']) ?>" disabled>
            </div>
            <br>
            <!-- Section for ID card -->
            <div class="id-card-container" style="display: flex; justify-content: center; align-items: center; flex-direction: column;">
              <label for="inputAddress"> صورة البطاقة</label>
              <br>
              <img class="idCard large-id-card"  src="<?= $imgSrc ?>" alt="صورة بطاقة تعريف">
            </div>
            
            <br>
            <div dir="rtl" class="form-group">
              <label for="inputAddress"> رقم الهاتف </label>
              <input type="text" class="form-control" id="inputAddress" value="<?= htmlspecialchars($req['Phone']) ?>" disabled>
            </div>
            <br>
          <div dir="rtl" class="form-group">
  <label for="inputAddress">عنوان السكن</label>
  <input type="text" class="form-control" value="<?= htmlspecialchars($req['address']) ?>" disabled>
</div>
<br>
<div dir="rtl" class="form-group">
  <label for="inputResidence">الإقامة الجامعية</label>
  <input type="text" class="form-control" value="<?= htmlspecialchars($req['residence']) ?>" disabled>
</div>
            <br>
          
            <button type="submit" name="approve" class="btn btn-outline-success btn-lg active">تأكيد التوثيق</button>
            <button type="submit" name="reject" class="btn btn-outline-danger btn-lg active"> رفض التوثيق</button>

          </form>
        </main>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
</td>
                            
                          </tr>
                         
                        </tbody>
                      </table>
                    </div>
                  </div>

    <br>
    <br>
     <?php endforeach; ?>
<?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
</body>
</html>
