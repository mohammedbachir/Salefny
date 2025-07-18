
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>تسجيل الدخول - سلفني</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
       <link rel="shortcut icon" href="img/favicon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="css/login.css">

</head>
<body>
<?php include_once('templates/navbar.php');?>
<?php
session_start(); // ضروري في أول الملف

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $database = new PDO("mysql:host=localhost;dbname=salefny;charset=utf8", "root", "");
        $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // تحقق أولاً في جدول users
        $login = $database->prepare("SELECT * FROM users WHERE Email = :email LIMIT 1");
        $login->bindParam(":email", $email);
        $login->execute();

        if ($login->rowCount() === 1) {
            $user = $login->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $user['Password'])) {
                $_SESSION['user'] = $user;
                header("Location: user/userhome.php");
                exit;
            } else {
                $msg = '<div class="alert alert-warning">⚠️ كلمة المرور غير صحيحة.</div>';
            }

        } else {
            // إذا لم يكن في جدول users، ابحث في جدول admins
            $loginAdmin = $database->prepare("SELECT * FROM admins WHERE email = :email LIMIT 1");
            $loginAdmin->bindParam(":email", $email);
            $loginAdmin->execute();

            if ($loginAdmin->rowCount() === 1) {
                $admin = $loginAdmin->fetch(PDO::FETCH_ASSOC);

                if (password_verify($password, $admin['password'])) {
                    $_SESSION['admin'] = $admin;

                    // توجيه حسب نوع الدور
                    switch ($admin['role']) {
                        case 'super-admin':
                            header("Location: super-admin/super-admin.php");
                            break;
                        case 'admin':
                            header("Location: admin/adminPage.php");
                            break;
                        case 'manager':
                            header("Location: manger/pay-manage.php");
                            break;
                        default:
                            $msg = '<div class="alert alert-danger">⚠️ صلاحيات غير معروفة.</div>';
                    }
                    exit;

                } else {
                    $msg = '<div class="alert alert-warning">⚠️ كلمة المرور غير صحيحة.</div>';
                }

            } else {
                $msg = '<div class="alert alert-warning">⚠️ البريد الإلكتروني غير مسجل.</div>';
            }
        }

    } catch (PDOException $e) {
        $msg = '<div class="alert alert-danger">فشل الاتصال بقاعدة البيانات: ' . $e->getMessage() . '</div>';
    }
}
?>






<section class="login-section">
    <div class="container">
        <div class="login-container">
            <h2 class="login-title">تسجيل الدخول</h2>
 <?php if (!empty($msg)) echo $msg; ?>
            <form id="loginForm" method="POST">
                <div class="form-group">
                    <label for="email" class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" id="email" class="form-input" required>
                    <span class="error-message" id="emailError" style="display:none; color: var(--accent-color); font-size:14px; margin-top:5px;">يرجى إدخال بريد إلكتروني صحيح</span>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">كلمة المرور</label>
                    <input type="password" name="password" id="password" class="form-input" required>
                    <span class="error-message" id="passwordError" style="display:none; color: var(--accent-color); font-size:14px; margin-top:5px;">يرجى إدخال كلمة المرور</span>
                </div>

                <button type="submit" name="login" class="btn submit-btn">دخول</button>
            </form>

            <div class="register-link">
                <p>هل نسيت كلمة المرور ؟<a href="rest.php">اعادة تعيين كلمة المرور</a></p>
            </div>
        </div>
    </div>
</section>


<?php include_once('templates/footer.php');?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="js/login.js"></script>


</body>
</html>
