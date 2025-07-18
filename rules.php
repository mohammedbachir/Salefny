<?php
session_start(); // ضروري في أول الملف

if (isset($_SESSION['user']) && $_SESSION['role'] === 'USER') {
    header("Location: user/userhome.php");
    exit;
}

// وإذا تحب تحوّل الأدمن أو المشرف لمكان مختلف:
if (isset($_SESSION['admin'])) {
    header("Location: admin/adminPage.php");
    exit;
}
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8" />
    <meta name="description" content="اقرأ شروط وأحكام استخدام منصة سلفني لتسليف الكتب، بما في ذلك الاشتراكات، الخصوصية، السلوكيات الممنوعة وحقوق المستخدمين.">

    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>القوانين والشروط - سلفني</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
   <link rel="stylesheet" href="css/rules.css">
   <link rel="shortcut icon" href="img/favicon.png" type="image/x-icon">
</head>
<body>
<?php include_once('templates/navbar.php');?>


<section class="page-header">
    <div class="container">
        <h1>القوانين والشروط</h1>
        <p>يرجى قراءة هذه الشروط والأحكام بعناية قبل استخدام منصة سلفني</p>
    </div>
</section>

<section class="terms-content">
    <div class="container">
        <div class="terms-container">
            
            <div class="terms-section">
                <h2 class="section-title">
                    <i class="fas fa-info-circle"></i>
                    مرحباً بك في سلفني
                </h2>
                <p class="terms-text">
                    هذه الشروط والأحكام تحكم استخدامك لمنصة "سلفني" للكتب الرقمية. باستخدام هذه المنصة، فإنك توافق على الالتزام بهذه الشروط والأحكام. إذا كنت لا توافق على أي من هذه الشروط، يرجى عدم استخدام المنصة.
                </p>
                <div class="highlight-box">
                    <strong>تاريخ آخر تحديث:</strong> 11 جويلية 2025
                </div>
            </div>

            <div class="terms-section">
                <h2 class="section-title">
                    <i class="fas fa-user-check"></i>
                    شروط الاستخدام العامة
                </h2>
                <ul class="terms-list">
                    <li>يجب أن تكون في سن 18 عاماً أو أكثر لاستخدام هذه المنصة</li>
                    <li>يجب تقديم معلومات صحيحة ودقيقة عند التسجيل</li>
                    <li>أنت مسؤول عن الحفاظ على سرية بيانات حسابك</li>
                    <li>لا يُسمح بمشاركة حسابك مع أشخاص آخرين</li>
                    <li>يجب استخدام المنصة للأغراض القانونية فقط</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2 class="section-title">
                    <i class="fas fa-credit-card"></i>
                    الاشتراكات والمدفوعات
                </h2>
                <p class="terms-text">
                    تقدم سلفني عدة باقات اشتراك شهرية تتيح لك الوصول إلى مكتبة واسعة من الكتب الرقمية:
                </p>
                <ul class="terms-list">
                    <li><strong>الباقة البرونزية:</strong> 500 د.ج شهرياً - وصول للكتب البرونزية</li>
                    <li><strong>الباقة الفضية:</strong> 1000 د.ج شهرياً - وصول للكتب الفضية</li>
                    <li><strong>الباقة الذهبية:</strong> 1500 د.ج شهرياً - وصول كامل لجميع الكتب</li>
                </ul>
                <div class="warning-box">
                    <strong>تنبيه:</strong> يتم ايقاف الاشتراك تلقائيا عند انتهاء فترته والتجديد يكون يدوي من طرف المستخدم
                </div>
            </div>

            <div class="terms-section">
                <h2 class="section-title">
                    <i class="fas fa-book"></i>
                    حقوق الملكية الفكرية
                </h2>
                <p class="terms-text">
                    جميع الكتب والمحتويات المتاحة على منصة سلفني محمية بحقوق الطبع والنشر:
                </p>
                <ul class="terms-list">
                    <li>لا يُسمح بنسخ أو توزيع أو نشر المحتوى خارج المنصة</li>
                    <li>الاستخدام مقتصر على القراءة الشخصية فقط</li>
                    <li>لا يُسمح بالاستخدام التجاري للمحتوى</li>
                    <li>يمنع مشاركة الكتب لأي شخص اخر</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2 class="section-title">
                    <i class="fas fa-shield-alt"></i>
                    الخصوصية والأمان
                </h2>
                <p class="terms-text">
                    نحن ملتزمون بحماية خصوصيتك وأمان بياناتك:
                </p>
                <ul class="terms-list">
                    <li>نجمع فقط البيانات الضرورية لتقديم الخدمة</li>
                    <li>لا نشارك بياناتك الشخصية مع أطراف ثالثة</li>
                    <li>نستخدم تقنيات التشفير لحماية بياناتك</li>
                    <li>يمكنك طلب حذف بياناتك في أي وقت</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2 class="section-title">
                    <i class="fas fa-ban"></i>
                    السلوكيات المحظورة
                </h2>
                <p class="terms-text">يُمنع منعاً باتاً القيام بالأنشطة التالية:</p>
                <ul class="terms-list">
                    <li>محاولة اختراق أو إلحاق الضرر بالمنصة</li>
                    <li>استخدام برامج أو أدوات غير مصرح بها</li>
                    <li> الحاق اي ضرر بالكتب يتبعه تحمل كامل للمسؤلية من المستخدم</li>
                    <li> التأخر في اعادة تسليم الكتاب يتبع تحمل كامل للمسؤلية من طرف المستخدم</li>
                    <li>عدم ارجاع اي كتاب يؤدي الى ايقاف اشتراكك تلقائيا وحظرك تماما من الموقع مع عدم امكانية التسجيل ثانية</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2 class="section-title">
                    <i class="fas fa-times-circle"></i>
                    الإلغاء والاسترداد
                </h2>
                <p class="terms-text">
                    يمكنك إلغاء اشتراكك في أي وقت من خلال إعدادات حسابك:
                </p>
                <ul class="terms-list">
                    <li>الإلغاء يصبح ساري المفعول في نهاية فترة الفوترة الحالية</li>
                    <li>لا توجد استردادات جزئية للفترات غير المستخدمة</li>
                    <li>يمكن إعادة تفعيل الاشتراك في أي وقت</li>
                    <li>الاسترداد متاح فقط في حالات خاصة وبموافقة الإدارة</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2 class="section-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    إخلاء المسؤولية
                </h2>
                <p class="terms-text">
                    منصة سلفني تقدم الخدمة "كما هي" وتخلي مسؤوليتها عن:
                </p>
                <ul class="terms-list">
                    <li>أي انقطاع مؤقت في الخدمة لأسباب تقنية</li>
                    <li>دقة أو اكتمال المحتوى المقدم من أطراف ثالثة</li>
                    <li>أي أضرار مباشرة أو غير مباشرة نتيجة الاستخدام</li>
                    <li>فقدان البيانات بسبب مشاكل تقنية خارجة عن سيطرتنا</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2 class="section-title">
                    <i class="fas fa-edit"></i>
                    تعديل الشروط
                </h2>
                <p class="terms-text">
                    نحتفظ بالحق في تعديل هذه الشروط والأحكام في أي وقت. سيتم إشعارك بأي تغييرات جوهرية قبل تطبيقها بـ 30 يوماً على الأقل من خلال البريد الإلكتروني أو إشعار على المنصة.
                </p>
                <div class="highlight-box">
                    استمرار استخدامك للمنصة بعد التعديلات يعني موافقتك على الشروط الجديدة.
                </div>
            </div>

            <div class="contact-info">
                <h3>تواصل معنا</h3>
                <p>للاستفسارات حول هذه الشروط والأحكام، يمكنك التواصل معنا:</p>
                <div class="contact-details">
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>salefnyhelp@gmail.com</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span>+213 657683768</span>
                    </div>
                  
                </div>
            </div>

        </div>
    </div>
</section>

<?php include_once('templates/footer.php');?>

<script src="js/register.js"></script>
</body>
</html>