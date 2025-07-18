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
<?php
session_start();
require_once 'including/db.php';
require_once 'mailing.php'; // تهيئة $mail مسبقًا

if (isset($_POST['rest'])) {
    $email = $_POST['email'];
    $stmt = $database->prepare("SELECT * FROM users WHERE Email = :email LIMIT 1");
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', time() + 3600); // صالح لمدة ساعة

        $update = $database->prepare("UPDATE users SET reset_token = :token, reset_token_expiry = :expiry WHERE Email = :email");
        $update->execute([
            ":token" => $token,
            ":expiry" => $expiry,
            ":email" => $email
        ]);

        $link = "http://localhost/Salefny/changepass.php?token=$token";

        $mail->addAddress($email);
        $mail->Subject = "🔐 رابط إعادة تعيين كلمة المرور";
        $mail->Body = "اضغط على الرابط التالي لإعادة تعيين كلمة المرور:\n$link\n\nصالح لمدة ساعة فقط.";
        $mail->send();

        $msg = "<div class='alert alert-success'>تم إرسال الرابط إلى بريدك الإلكتروني.</div>";
    } else {
        $msg = "<div class='alert alert-warning'>هذا البريد غير مسجل.</div>";
    }
}
?>



<section class="login-section">
    <div class="container">
        <div class="login-container">
            <h2 class="login-title">اعادة تعيين كلمة المرور</h2>
 <?php if (isset($msg)) echo $msg; ?>
            <form id="loginForm" method="POST">
                <div class="form-group">
                    <label for="email" class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" id="email" class="form-input" required>
                    <span class="error-message" id="emailError" style="display:none; color: var(--accent-color); font-size:14px; margin-top:5px;">يرجى إدخال بريد إلكتروني صحيح</span>
                </div>

               
                <button type="submit" name="rest" class="btn submit-btn">ارسال رابط التعيين</button>
            </form>

           
        </div>
    </div>
</section>


<?php include_once('templates/footer.php');?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="js/login.js"></script>


</body>
</html>
