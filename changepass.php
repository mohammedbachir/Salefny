<?php
session_start();
require_once 'including/db.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $database->prepare("SELECT * FROM users WHERE reset_token = :token AND reset_token_expiry > NOW() LIMIT 1");
    $stmt->bindParam(":token", $token);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (isset($_POST['change'])) {
            $newPass = $_POST['password'];
            $hashedPass = password_hash($newPass, PASSWORD_DEFAULT); // تشفير
            $update = $database->prepare("UPDATE users SET Password = :pass, reset_token = NULL, reset_token_expiry = NULL WHERE Id = :id");
            $update->execute([
                ":pass" => $hashedPass,
                ":id" => $user['Id']
            ]);
            $msg = "<div class='alert alert-success'>✅ تم تغيير كلمة المرور بنجاح. سيتم توجيهك لصفحة تسجيل الدخول.</div>";
             header("refresh:2;url=login.php");
          }
    } else {
        $msg = "<div class='alert alert-danger'>❌ الرابط غير صالح أو منتهي.</div>";
        $hideForm = true;
    }
} else {
    $msg = "<div class='alert alert-danger'>❌ الرابط غير صالح.</div>";
    $hideForm = true;
}
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>اعادة تعيين كلمة المرور - سلفني</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
       <link rel="shortcut icon" href="img/favicon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="css/login.css">

</head>
<body>
<?php include_once('templates/navbar.php');?>
<div class="container mt-5">
  <h2>إعادة تعيين كلمة المرور</h2>
  <?php if (isset($msg)) echo $msg; ?>

  <?php if (!isset($hideForm)): ?>
  <section class="login-section">
    <div class="container">
        <div class="login-container">
            <h2 class="login-title">اعادة تعيين كلمة المرور</h2>
 <?php if (isset($msg)) echo $msg; ?>
            <form id="loginForm" method="POST">
                <div class="form-group">
                    <label for="email" class="form-label">كلمة المرور الجديدة </label>
                    <input type="text" name="password" id="password" class="form-input" required>
                    <span class="error-message" id="emailError" style="display:none; color: var(--accent-color); font-size:14px; margin-top:5px;">يرجى إدخال بريد إلكتروني صحيح</span>
                </div>

               
                <button type="submit" name="change" class="btn submit-btn">تعيين</button>
            </form>

           
        </div>
    </div>
</section>
  <?php endif; ?>
</div>

</body>
</html>
