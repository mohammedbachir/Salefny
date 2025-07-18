<?php
ob_start();
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_ad'])) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=salefny", "root", "", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $title = $_POST['title'];
        $price = intval($_POST['price']);
        $link = !empty($_POST['link']) ? $_POST['link'] : null;

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageData = file_get_contents($_FILES['image']['tmp_name']);

            $stmt = $pdo->prepare("INSERT INTO ads (title, image, link, price, is_active) VALUES (?, ?, ?, ?, 1)");
            $stmt->bindParam(1, $title);
            $stmt->bindParam(2, $imageData, PDO::PARAM_LOB);
            $stmt->bindParam(3, $link);
            $stmt->bindParam(4, $price);
            $stmt->execute();

            echo '<div class="alert alert-success mt-3">تمت إضافة الإعلان بنجاح!</div>';
        } else {
            echo '<div class="alert alert-danger mt-3">فشل في رفع الصورة.</div>';
        }
    } catch (PDOException $e) {
        echo '<div class="alert alert-danger mt-3">خطأ في قاعدة البيانات: ' . $e->getMessage() . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مرحبا بك ايها المدير الاسطورة</title>
    <link rel="icon" href="http://localhost/Salefny_Website/homePage/img/favicon.jpg" >
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
        h2{
              
 
              font-size: 40px;
       
              color: black;
              text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
              word-spacing: 5px;
              margin-bottom: 10px;
              }
              .line-text{
                     
           width: 100px;
           height: 4px;
           background: #000;
           margin: 0 auto;
           border-radius: 2px;
              }
          
            
 
              .image-list {
    list-style-type: none;
    padding: 0;
    margin: 0;
  }

  .image-list li {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .image-list li img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 10px;
    margin-right: 20px;
  }

  .image-list li h3 {
    font-size: 18px;
    margin: 0;
    flex-grow: 1;
  }

  .image-list li p {
    margin: 10px 0 0;
  }

  .image-list .buttons {
    display: flex;
    gap: 10px;
  }

  .image-list .buttons button {
    padding: 10px;
    font-size: 14px;
  }
  .form-group {
    margin-bottom: 20px; /* إضافة مسافة بين العناصر */
}

.form-control {
    border-radius: 8px; /* جعل الحواف مستديرة قليلاً */
    border: 1px solid #ced4da;
    transition: border-color 0.3s, box-shadow 0.3s;
    padding: 10px 15px; /* تحسين المسافات داخل الحقل */
    background-color: #f9f9f9; /* لون خلفية فاتح للمزيد من الجمالية */
}

.form-control:focus {
    border-color: #ffcc00; /* اللون الأصفر عند التركيز */
    box-shadow: 0 0 8px rgba(255, 204, 0, 0.5); /* تأثير ظل عند التركيز */
    background-color: #fff; /* تغيير لون الخلفية عند التركيز */
}

button {
    padding: 12px 20px;
    border-radius: 8px; /* جعل الأزرار مستديرة قليلاً */
    transition: background-color 0.3s, color 0.3s;
}

button:hover {
    background-color: #ffcc00; /* تغيير اللون عند التمرير */
    color: #fff;
}

.btn-outline-warning {
    margin-right: 10px; /* مسافة بين الأزرار */
}

    </style>
</head>
<body dir="rtl" style="padding: 0%;background-color: rgb(204, 204, 204);font-family: 'Tajawal', sans-serif;">
    <!--nav start-->
    <?php require_once'navbar.php';?>
    <!--nav end-->
    <br>
    <h2 style="text-align:center;">ادارة المشرفين</h2>

    <br>
    <div dir="rtl" class="container" style="margin:30px;"> 
    <div class="d-flex align-items-start">
        <div class="nav flex-column nav-pills me-3" style="margin:30px;" id="v-pills-tab" role="tablist" aria-orientation="vertical">
            <button class="nav-link active" id="v-pills-site-elements-tab" data-bs-toggle="pill" data-bs-target="#v-pills-site-elements" type="button" role="tab" aria-controls="v-pills-site-elements" aria-selected="true"><b>عناصر الموقع</b></button>
            <br>
            <button class="nav-link" id="v-pills-ads-management-tab" data-bs-toggle="pill" data-bs-target="#v-pills-ads-management" type="button" role="tab" aria-controls="v-pills-ads-management" aria-selected="false">
               <b>إدارة الإعلانات</b>
            </button>
            <br>
            <button class="nav-link" id="v-pills-other-actions-tab" data-bs-toggle="pill" data-bs-target="#v-pills-other-actions" type="button" role="tab" aria-controls="v-pills-other-actions" aria-selected="false"><b>إجراءات أخرى</b></button>
        </div>
        <div style="margin:40px;" class="tab-content container" id="v-pills-tabContent">
            <div class="tab-pane fade show active" id="v-pills-site-elements" role="tabpanel" aria-labelledby="v-pills-site-elements-tab" style="background-color: #f8f9fa; border-radius: 10px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                <!-- محتوى "عناصر الموقع" -->
                <form class="form-container" style="text-align:right;">
                    <!-- يمكنك إضافة حقول النموذج والمحتويات هنا حسب حاجتك -->
                    <div dir="rtl" class="form-row">
                        <div class="form-group col-md-6">
                            <label for="inputElement1">عنوان الموقع</label>
                            <input type="text" class="form-control" id="inputElement1">
                        </div>
                    </div>
                    <hr>
                    <br>
                    <div dir="rtl" class="form-group">
                        <label for="inputElement2">وصف الموقع</label>
                        <input type="text" class="form-control" id="inputElement2">
                    </div>
                  <hr>
                    <br>
                    <div dir="rtl" class="form-group">
                        <h3>صور الكراسول</h3>
                        <form action="">
                        <div dir="rtl" class="form-row">
                        <div class="form-group col-md-6">
                        <label for="inputElement2">عنوان الصورة</label>
                            <input type="text" name="" id="">
                            </div>
                            </div>

                            <div dir="rtl" class="form-row">
                        <div class="form-group col-md-6">
                        <label for="inputElement2">وصف الصورة</label>
                            <input type="text" name="" id="">
                            </div>
                            </div>
                            <div dir="rtl" class="form-row">
                        <div class="form-group col-md-6">
                        <label for="inputElement2">عنوان الصورة</label>
                            <input type="file" name="" id="">
                            </div>
                            </div>
                      <button type="submit" class="btn btn-outline-dark"> تحميل الصورة</button>
                        </form>
                        <br>
                    
                     <br>
                    
                        <div dir="rtl">
  <ul class="image-list">
    <li>
      <img src="http://localhost/Salefny_Website/homePage/img/carousel1.jpg" alt="Image 1" />
      <div>
        <h3>ماهو موقع سلفني ؟</h3>
        <p>هو موقع يجمع محبي القراءة والمطالعة ليملىء رفوفهم بالكتب</p>
        <div class="buttons">
          <button type="submit" class="btn btn-danger">حذف</button>
          <button type="submit" class="btn btn-success">تعديل</button>
        </div>
      </div>
    </li>
  </ul>
</div>

<hr>
<br>
         <div><label for="inputElement2"> الباقات</label></div>
         <br><br>
         
         <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
  اتخاذ اجراء على الباقات
</button>
<br><br><br>
<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">التعديل على المعلومات</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <main >
    <form class="form-container" style="text-align:right;">
        <div dir="rtl" class="form-row">
       <h4>الباقة الاولى :</h4>
            <div class="form-group">
             
                <label for="inputEmail4">الاسم :</label>
                <input type="email" class="form-control" id="inputEmail4" value="mihaelscofildnazi@gmai.com">
            </div>
          
        </div>
        <div dir="rtl" class="form-group">
            <label for="inputAddress"> السعر</label>
            <input type="text" class="form-control" id="inputAddress" value=" 54
            54">
        </div>
       
        <div dir="rtl" class="form-row">
            <div class="form-group">
                <label for="inputZip">المميزات</label>
                <textarea type="text" class="form-control"  >

                </textarea>
            </div>
        </div>
        
        <br>
        <form class="form-container" style="text-align:right;">
        <div dir="rtl" class="form-row">
       <h4>الباقة الثانية :</h4>
            <div class="form-group">
             
                <label for="inputEmail4">الاسم :</label>
                <input type="email" class="form-control" id="inputEmail4" value="mihaelscofildnazi@gmai.com">
            </div>
          
        </div>
        <div dir="rtl" class="form-group">
            <label for="inputAddress"> السعر</label>
            <input type="text" class="form-control" id="inputAddress" value=" 54
            54">
        </div>
       
        <div dir="rtl" class="form-row">
            <div class="form-group">
                <label for="inputZip">المميزات</label>
                <textarea type="text" class="form-control"  >

                </textarea>
            </div>
        </div>
        <br>
        <form class="form-container" style="text-align:right;">
        <div dir="rtl" class="form-row">
       <h4>الباقة الثالثة :</h4>
            <div class="form-group">
             
                <label for="inputEmail4">الاسم :</label>
                <input type="email" class="form-control" id="inputEmail4" value="mihaelscofildnazi@gmai.com">
            </div>
          
        </div>
        <div dir="rtl" class="form-group">
            <label for="inputAddress"> السعر</label>
            <input type="text" class="form-control" id="inputAddress" value=" 54
            54">
        </div>
       
        <div dir="rtl" class="form-row">
            <div class="form-group">
                <label for="inputZip">المميزات</label>
                <textarea type="text" class="form-control"  >

                </textarea>
            </div>
        </div>
        
        <button type="submit" class="btn btn-outline-warning btn-lg active ">حفظ التغيرات  </button>

</form>
</main>
      </div>
     
    </div>
  </div>

                    </div>
                    <br>
                    <hr>
                    <br>
                    <button type="submit" class="btn btn-outline-warning btn-lg active">حفظ التغيرات</button>
                </form>
            </div>
</div>
           <form action="" method="POST" enctype="multipart/form-data">
    <label for="title">إضافة اسم المعلن:</label>
    <input type="text" class="form-control" id="title" name="title" required>
    <br>

    <label for="image">إضافة صورة الإعلان:</label>
    <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
    <br>

    <label for="price">السعر المقدم من المعلن:</label>
    <input type="number" class="form-control" id="price" name="price" required>
    <br>

    <label for="link">رابط الإعلان (اختياري):</label>
    <input type="url" class="form-control" id="link" name="link">
    <br>

    <button type="submit" class="btn btn-outline-warning btn-lg active" name="submit_ad">إضافة الإعلان</button>
</form>

            <div class="tab-pane fade" style="text-align:right; background-color: #f8f9fa; border-radius: 10px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);" id="v-pills-other-actions" role="tabpanel" aria-labelledby="v-pills-other-actions-tab">
                <!-- محتوى "إجراءات أخرى" -->
                <div class="alert alert-info" role="alert">
                    قم بتنفيذ الإجراءات الإضافية المطلوبة.
                </div>
                <button type="button" class="btn btn-outline-info">إجراء 1</button>
                <button type="button" class="btn btn-outline-info">إجراء 2</button>
            </div>
        </div>
    </div>
</div>

   
<br>
    <br>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
</body>
<?php ob_end_flush(); ?>

</html>
