<?php 
// Start session (if needed)
session_start();
require './mysql/conn.php'; // Adjust path to conn.php if necessary

// Check if the 'SmartSpot' cookie exists
if (!isset($_COOKIE['SmartSpot'])) {
    // Redirect to login page if the cookie does not exist
    header("Location: pages-login.php");
    exit;
}

// Retrieve the email from the cookie
$user_data = json_decode($_COOKIE['SmartSpot'], true);
$user_email = $user_data['email'];
$user_type = $user_data['user_type'];

// Initialize customer details
$customer_fname = $customer_mname = $customer_lname = '';

// Query to select customer details
$stmt = $conn->prepare("SELECT fname, mname, lname, user_type, profile FROM user WHERE email = ?");
if ($stmt) {
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();

        $customer_fname = $customer['fname'];
        $customer_mname = $customer['mname'];
        $customer_lname = $customer['lname'];

        // Get the first letter of the first name
        $first_letter_fname = substr(htmlspecialchars($customer_fname), 0, 1);
    } else {
        // Handle the case where no customer is found
        echo "No customer found for this email.";
    }
    
    $stmt->close();
} else {
    // Handle SQL prepare error
    echo "SQL prepare error: " . $conn->error;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sign_out'])) {
    // Delete the user_email cookie
    setcookie('SmartSpot', '', time() - 3600, '/'); // Set expiration time to the past to delete

    // Optionally, destroy the session if you are using one
    session_unset();
    session_destroy();

    // Redirect to login page
    header("Location: pages-login.php");
    exit;
}

$vacant_space = $conn->query("SELECT vacant_space FROM config")->fetch_row()[0];
$occupied_space = $conn->query("SELECT occupied_space FROM config")->fetch_row()[0];

$total_space = $vacant_space + $occupied_space;

// Close the connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Live Feed</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/ss-favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">

    <!-- Include SweetAlert CSS and JS -->
  <link rel="stylesheet" href="assets/plugins/sweetalert2/sweetalert2.min.css">
  <script src="assets/plugins/sweetalert2/sweetalert2.js"></script>

  <!-- =======================================================
  * Template Name: NiceAdmin
  * Updated: May 30 2023 with Bootstrap v5.3.0
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
  <style>
        /* Ensure the body and html take up the full viewport height */
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
        }

        /* Make the iframe fill the entire screen */
        iframe {
            width: 100%;
            height: 100%;
            border: none; /* Optional: removes iframe border */
        }
    </style>
</head>

<body>

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="dashboard.php" class="logo d-flex align-items-center">
        <img src="assets/img/ss.png" alt="">
        <span class="d-none d-lg-block">SmartSpot</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <img src="assets/img/profile/<?php echo $customer['profile']?>" alt="Profile" class="rounded-circle">
            <span class="d-none d-md-block dropdown-toggle ps-2"><?php echo $first_letter_fname . '. ' . htmlspecialchars($customer_lname); ?></span>
          </a><!-- End Profile Iamge Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6><?php echo htmlspecialchars($customer_fname) . ' ' . htmlspecialchars($customer_mname) . ' ' . htmlspecialchars($customer_lname); ?></h6>
              <span><?php echo htmlspecialchars($customer['user_type']); ?></span>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <form method="POST">
                    <button type="submit" name="sign_out" class="dropdown-item d-flex align-items-center" style="background: none; border: none; cursor: pointer;">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Sign Out</span>
                    </button>
                </form>
            </li>

          </ul><!-- End Profile Dropdown Items -->
        </li><!-- End Profile Nav -->

      </ul>
    </nav><!-- End Icons Navigation -->

  </header><!-- End Header -->

  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link collapsed" href="dashboard.php">
          <i class="bi bi-grid"></i>
          <span>Dashboard</span>
        </a>
      </li><!-- End Dashboard Nav -->

      <?php if ($user_type === 'admin'): ?>
        <li class="nav-item">
          <a class="nav-link collapsed" href="pages-staff-management.php">
            <i class="bi bi-person"></i>
            <span>Staff Management</span>
          </a>
        </li>

      <!-- <li class="nav-item">
        <a class="nav-link collapsed" href="pages-customer-management.php">
          <i class="bi bi-person"></i>
          <span>Customer Management</span>
        </a>
      </li> -->
      <?php endif; ?>

      <li class="nav-item">
        <a class="nav-link collapsed" href="pages-parking-management.php">
          <i class="bi bi-car-front-fill"></i>
          <span>Parking Management</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link " href="pages_livefeed.php">
          <i class="bi bi-camera-video"></i>
          <span>Live Feed</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="pages-config.php">
          <i class="bi bi-gear"></i>
          <span>Configuration</span>
        </a>
      </li>

    </ul>

  </aside><!-- End Sidebar-->

<main id="main" class="main" style="height: 100vh; display: flex; flex-direction: column; overflow: hidden;">
    <div class="pagetitle d-flex justify-content-between align-items-center" style="z-index: 10; padding: 20px;">
        <h1>Live Feed</h1>
        <h5 class="card-title mb-0" id="space-status">Vacant Space: Loading... / Loading...</h5>
    </div>

    <!-- Video container with USB camera feed -->
    <div class="embed-responsive embed-responsive-16by9" style="height: calc(100vh - 80px);">
        <video id="camera-feed" autoplay style="width: 100%; height: auto;">
            Your browser does not support the video element.
        </video>
    </div>
</main>

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.min.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jsmpeg@0.2.0/jsmpeg.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

  <script>
        // Access the webcam
        async function startCamera() {
            try {
                const videoElement = document.getElementById('camera-feed');
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: {
                        width: { ideal: 1920 },
                        height: { ideal: 1080 },
                        facingMode: "environment" // Prefer rear camera if available
                    }
                });
                
                videoElement.srcObject = stream;

                // Handle errors
                videoElement.onerror = function(err) {
                    console.error("Video Error: ", err);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to access camera: ' + err.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                };

            } catch (err) {
                console.error("Error accessing camera: ", err);
                Swal.fire({
                    title: 'Camera Error',
                    text: 'Unable to access camera. Please make sure a camera is connected and you have granted permission to use it.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        }

        // Start camera when page loads
        document.addEventListener('DOMContentLoaded', function() {
            startCamera();
            
            // Check if camera disconnects
            navigator.mediaDevices.ondevicechange = function(event) {
                console.log("Media devices changed");
                startCamera(); // Attempt to reconnect
            };
        });

        // Function to fetch parking status and update the UI
        function fetchParkingStatus() {
            $.ajax({
                url: 'mysql/fetch_parking_status.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.error) {
                        console.log("Error: " + response.error);
                    } else {
                        var vacantSpace = response.vacant_space;
                        var totalSpace = response.total_space;
                        $('#space-status').text('Vacant Space: ' + vacantSpace + ' / ' + totalSpace);
                    }
                },
                error: function(xhr, status, error) {
                    console.log("AJAX Error: " + error);
                }
            });
        }

        // Call the function to fetch the parking status when the page loads
        $(document).ready(function() {
            fetchParkingStatus();
            setInterval(fetchParkingStatus, 15000);  // Update every 15 seconds
        });
    </script>
</body>

</html>

