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

    $admins_books = $db->query("
        SELECT admins.name AS admin_name, COUNT(books.id) AS book_count
        FROM books
        JOIN admins ON books.added_by_admin_id = admins.id
        GROUP BY admins.id
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
try {
    $db = new PDO("mysql:host=localhost;dbname=salefny", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $wilaya_borrowings = $db->query("
        SELECT a.wilaya, COUNT(br.id) AS total_requests
        FROM book_requests br
        JOIN admins a ON br.admin_id = a.id
        GROUP BY a.wilaya
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error (wilaya): " . $e->getMessage());
}

?>
<?php
// عدد الكتب
$count_books = $db->query("SELECT COUNT(*) FROM books")->fetchColumn();

// عدد الأصناف
$count_categories = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();

// عدد المستخدمين
$count_users = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();

// عدد الحجوزات (طلبات استعارة الكتب)
$count_reservations = $db->query("SELECT COUNT(*) FROM book_requests")->fetchColumn();
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
<style>
        .dropbtn {
            background-color: #FFF;
            color: black;
            padding: 16px;
            font-size: 16px;
            border: none;
            cursor: pointer;
        }

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
<body dir="rtl" style="padding: 0%;background-color: rgb(204, 204, 204);font-family: 'Tajawal', sans-serif;">
    <!--nav start-->
    <?php require_once'navbar.php';?>
    
    <!--nav end-->
    <main style="text-align:center; padding-top:170px;">
        <strong><h3>مرحبا بك مجددا يا <b><?php echo htmlspecialchars($admin['name']); ?></b></h3></strong>
        <br>
        <b><h4>هنا تظهر المهمات التي يرسلها لك مدير الموقع</h4></b>
    </main>
    <br><br>
    <div class="row">
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-9">
                        <div class="d-flex align-items-center">
                            <h3 class="mb-0 font-weight-bold"><?php echo $count_books; ?></h3>

                            <p class="text-success ml-3 mb-0 font-weight-medium"></p>
                        </div>
                    </div>
                    <div class="col-3 text-center">
                        <div class="icon icon-box-success">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M560-564v-68q33-14 67.5-21t72.5-7q26 0 51 4t49 10v64q-24-9-48.5-13.5T700-600q-38 0-73 9.5T560-564Zm0 220v-68q33-14 67.5-21t72.5-7q26 0 51 4t49 10v64q-24-9-48.5-13.5T700-380q-38 0-73 9t-67 27Zm0-110v-68q33-14 67.5-21t72.5-7q26 0 51 4t49 10v64q-24-9-48.5-13.5T700-490q-38 0-73 9.5T560-454ZM260-320q47 0 91.5 10.5T440-278v-394q-41-24-87-36t-93-12q-36 0-71.5 7T120-692v396q35-12 69.5-18t70.5-6Zm260 42q44-21 88.5-31.5T700-320q36 0 70.5 6t69.5 18v-396q-33-14-68.5-21t-71.5-7q-47 0-93 12t-87 36v394Zm-40 118q-48-38-104-59t-116-21q-42 0-82.5 11T100-198q-21 11-40.5-1T40-234v-482q0-11 5.5-21T62-752q46-24 96-36t102-12q58 0 113.5 15T480-740q51-30 106.5-45T700-800q52 0 102 12t96 36q11 5 16.5 15t5.5 21v482q0 23-19.5 35t-40.5 1q-37-20-77.5-31T700-240q-60 0-116 21t-104 59ZM280-494Z"/></svg>                        </div>
                    </div>
                </div>
                <h6 class="text-muted font-weight-normal mt-3">عدد الكتب في الموقع</h6>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-9">
                        <div class="d-flex align-items-center">
                           <h3 class="mb-0 font-weight-bold"><?php echo $count_categories; ?></h3>

                            <p class="text-success ml-3 mb-0 font-weight-medium"></p>
                        </div>
                    </div>
                    <div class="col-3 text-center">
                        <div class="icon icon-box-success">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="m260-520 220-360 220 360H260ZM700-80q-75 0-127.5-52.5T520-260q0-75 52.5-127.5T700-440q75 0 127.5 52.5T880-260q0 75-52.5 127.5T700-80Zm-580-20v-320h320v320H120Zm580-60q42 0 71-29t29-71q0-42-29-71t-71-29q-42 0-71 29t-29 71q0 42 29 71t71 29Zm-500-20h160v-160H200v160Zm202-420h156l-78-126-78 126Zm78 0ZM360-340Zm340 80Z"/></svg>                        </div>
                    </div>
                </div>
                <h6 class="text-muted font-weight-normal mt-3">عدد الاصناف</h6>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-9">
                        <div class="d-flex align-items-center">
                            <h3 class="mb-0 font-weight-bold"><?php echo $count_users; ?></h3>

                            <p class="text-danger ml-3 mb-0 font-weight-medium"></p>
                        </div>
                    </div>
                    <div class="col-3 text-center">
                        <div class="icon icon-box-danger">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M0-240v-63q0-43 44-70t116-27q13 0 25 .5t23 2.5q-14 21-21 44t-7 48v65H0Zm240 0v-65q0-32 17.5-58.5T307-410q32-20 76.5-30t96.5-10q53 0 97.5 10t76.5 30q32 20 49 46.5t17 58.5v65H240Zm540 0v-65q0-26-6.5-49T754-397q11-2 22.5-2.5t23.5-.5q72 0 116 26.5t44 70.5v63H780Zm-455-80h311q-10-20-55.5-35T480-370q-55 0-100.5 15T325-320ZM160-440q-33 0-56.5-23.5T80-520q0-34 23.5-57t56.5-23q34 0 57 23t23 57q0 33-23 56.5T160-440Zm640 0q-33 0-56.5-23.5T720-520q0-34 23.5-57t56.5-23q34 0 57 23t23 57q0 33-23 56.5T800-440Zm-320-40q-50 0-85-35t-35-85q0-51 35-85.5t85-34.5q51 0 85.5 34.5T600-600q0 50-34.5 85T480-480Zm0-80q17 0 28.5-11.5T520-600q0-17-11.5-28.5T480-640q-17 0-28.5 11.5T440-600q0 17 11.5 28.5T480-560Zm1 240Zm-1-280Z"/></svg>                        </div>
                    </div>
                </div>
                <h6 class="text-muted font-weight-normal mt-3">عدد المسجلين في الموقع</h6>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-9">
                        <div class="d-flex align-items-center">
                            <h3 class="mb-0 font-weight-bold"><?php echo $count_reservations; ?></h3>

                            <p class="text-success ml-3 mb-0 font-weight-medium"></p>
                        </div>
                    </div>
                    <div class="col-3 text-center">
                        <div class="icon icon-box-success">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M320-280q17 0 28.5-11.5T360-320q0-17-11.5-28.5T320-360q-17 0-28.5 11.5T280-320q0 17 11.5 28.5T320-280Zm0-160q17 0 28.5-11.5T360-480q0-17-11.5-28.5T320-520q-17 0-28.5 11.5T280-480q0 17 11.5 28.5T320-440Zm0-160q17 0 28.5-11.5T360-640q0-17-11.5-28.5T320-680q-17 0-28.5 11.5T280-640q0 17 11.5 28.5T320-600Zm120 320h240v-80H440v80Zm0-160h240v-80H440v80Zm0-160h240v-80H440v80ZM200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Zm0-560v560-560Z"/></svg>                        </div>
                    </div>
                </div>
                <h6 class="text-muted font-weight-normal mt-3">عدد الحجوزات</h6>
            </div>
        </div>
    </div>
</div>


    <br>
    <br>
    <div class="charts-container">
        <div class="chart-item">
            <canvas id="AdminsAnalyse"></canvas>
            <div id="chartTitle" style="text-align:center; font-size: 18px; font-weight: bold;">
                نشاط الادمينات
            </div>
        </div>
        <div class="chart-item">
            <canvas id="visitors"></canvas>
            <div id="chartTitle" style="text-align:center; font-size: 18px; font-weight: bold;">
                عدد الزوار
            </div>
        </div>
        <div class="chart-item">
            <canvas id="borrow"></canvas>
            <div id="chartTitle" style="text-align:center; font-size: 18px; font-weight: bold;">
                نشاط الاعارة
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
      const ctx1 = document.getElementById('AdminsAnalyse');
      const ctx2 = document.getElementById('visitors');
      const ctx3 = document.getElementById('borrow');

      new Chart(ctx1, {
    type: 'doughnut',
    data: {
        labels: [
            <?php foreach($admins_books as $row) { echo "'".$row['admin_name']."',"; } ?>
        ],
        datasets: [{
            label: 'عدد الكتب المنشورة',
            data: [
                <?php foreach($admins_books as $row) { echo $row['book_count'].","; } ?>
            ],
            backgroundColor: [
                '#007bff',
                '#28a745',
                '#ffc107',
                '#dc3545',
                '#6f42c1'
            ],
            borderColor: '#fff',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    color: '#333',
                    font: {
                        size: 14
                    }
                }
            }
        }
    }
});

      new Chart(ctx2, {
    type: 'bar',
    data: {
        labels: [
            <?php foreach($visitors as $row) { echo "'".$row['month']."',"; } ?>
        ],
        datasets: [{
            label: 'عدد الزوار',
            data: [
                <?php foreach($visitors as $row) { echo $row['count'].","; } ?>
            ],
            backgroundColor: '#007bff'
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
new Chart(ctx3, {
    type: 'bar',
    data: {
        labels: [
            <?php foreach($wilaya_borrowings as $row) { echo "'".$row['wilaya']."',"; } ?>
        ],
        datasets: [{
            label: 'عدد عمليات الإعارة',
            data: [
                <?php foreach($wilaya_borrowings as $row) { echo $row['total_requests'].","; } ?>
            ],
            backgroundColor: '#dc3545'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return `عدد الإعارات: ${context.raw}`;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'عدد الإعارات'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'الولاية'
                }
            }
        }
    }
});
    </script>
    <br>
    <br>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
</body>
</html>
