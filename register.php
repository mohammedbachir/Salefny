<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>تسجيل حساب جديد - سلفني</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="shortcut icon" href="img/favicon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/register.css">
</head>
<body>

<?php include_once('templates/navbar.php'); ?>

<?php
$msg = "";
require_once 'including/db.php';

if (isset($_POST['submit'])) {
    $email = $_POST['email'];

    // تحقق إذا كان البريد الإلكتروني مسجلاً بالفعل
    $check = $database->prepare("SELECT * FROM users WHERE Email = :email");
    $check->bindParam(":email", $email);
    $check->execute();

    if ($check->rowCount() > 0) {
        $msg = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>⚠️ تنبيه:</strong> هذا البريد الإلكتروني مسجل من قبل.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    } else {
        $fname = $_POST['fname'];
        $lname = $_POST['lname'];
        $phone = $_POST['phone'];
        $password = $_POST['password'];

        // ✅ تشفير كلمة المرور
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $addUser = $database->prepare("INSERT INTO users (Fname, Lname, Email, Phone, Password, Role, isVerified) 
                                        VALUES (:fname, :lname, :email, :phone, :password, 'USER', 0)");

        $addUser->bindParam(":fname", $fname);
        $addUser->bindParam(":lname", $lname);
        $addUser->bindParam(":email", $email);
        $addUser->bindParam(":phone", $phone);
        $addUser->bindParam(":password", $hashedPassword); // 🔐

        if ($addUser->execute()) {
            $msg = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                تم إنشاء حسابك بنجاح ✅    
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';

            // ✅ إرسال بريد ترحيبي
            require_once 'mailing.php';
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
                        <img src="img/favicon.png" alt="شعار سلفني">
                    </div>
                    <div class="title">مرحبًا بك في سلفني، ' . htmlspecialchars($fname) . ' 👋</div>
                    <div class="message">
                        شكرًا لتسجيلك معنا في منصة <strong>سلفني</strong>، وجهتك الجديدة لاستكشاف الكتب والروايات والموارد التعليمية 🎓📚.
                        نأمل أن تستفيد من خدماتنا.
                    </div>
                    <div class="message">
                       يجب عليك تفعيل حسابك من خلال لوحة التحكم للتمكن من شراء الباقة واستعارة الكتب.
                    </div>
                    <a href="https://salefny.com/dashboard.php" class="cta">الذهاب إلى لوحة التحكم</a>
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

            header("refresh:3;url=login.php");
        } else {
            $msg = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                حدث خطأ أثناء التسجيل، يرجى المحاولة لاحقًا.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
        }
    }
}
?>

<section class="registration-section">
    <div class="container">
        <div class="registration-container">
            <h2 class="registration-title">إنشاء حساب جديد</h2>

            <!-- ✅ مكان الرسالة -->
            <?php if (!empty($msg)) echo $msg; ?>

            <form id="registrationForm" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstName" class="form-label">الاسم الأول</label>
                        <input type="text" name="fname" id="firstName" class="form-input" required>
                        <span class="error-message" id="firstNameError">يرجى إدخال الاسم الأول</span>
                    </div>
                    <div class="form-group">
                        <label for="lastName" class="form-label">اللقب</label>
                        <input type="text" name="lname" id="lastName" class="form-input" required>
                        <span class="error-message" id="lastNameError">يرجى إدخال اللقب</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" id="email" class="form-input" required>
                    <span class="error-message" id="emailError">يرجى إدخال بريد إلكتروني صحيح</span>
                </div>

                

                <div class="form-group">
                    <label for="phone" class="form-label">رقم الهاتف</label>
                    <input type="tel" name="phone" id="phone" class="form-input" required>
                    <span class="error-message" id="phoneError">يرجى إدخال رقم هاتف صحيح</span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password" class="form-label">كلمة المرور</label>
                        <input type="password" name="password" id="password" class="form-input" required>
                        <span class="error-message" id="passwordError">يجب أن تحتوي كلمة المرور على 8 أحرف على الأقل</span>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword" class="form-label">تأكيد كلمة المرور</label>
                        <input type="password" id="confirmPassword" class="form-input" required>
                        <span class="error-message" id="confirmPasswordError">كلمة المرور غير متطابقة</span>
                    </div>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="termsAgreement" required>
                    <label for="termsAgreement">أوافق على <a href="rules.php" target="_blank" rel="noopener noreferrer">الشروط والأحكام</a></label>
                    <span class="error-message" id="termsError">يجب الموافقة على الشروط والأحكام</span>
                </div>

                <button type="submit" name="submit" id="submitRegistration" class="btn submit-btn">إنشاء الحساب</button>
            </form>

            <div class="login-link">
                <p>لديك حساب بالفعل؟ <a href="login.php">تسجيل الدخول</a></p>
            </div>
        </div>
    </div>
</section>

<?php include_once('templates/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/register.js"></script>

</body>
</html>
