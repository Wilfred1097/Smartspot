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
        $user_type = $customer['user_type'];

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

// Query to select customer details
$vacant_space = $conn->query("SELECT COUNT(id) FROM `parking_status` WHERE status = 0")->fetch_row()[0];
$occupied_space = $conn->query("SELECT COUNT(id) FROM `parking_status` WHERE status = 1")->fetch_row()[0];

$total_space = $vacant_space + $occupied_space;


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

// Assuming you have a database connection established
$query = "SELECT COUNT(*) AS badge_count FROM parking_only WHERE TIMESTAMP(date_in, time_in) <= NOW() - INTERVAL 12 HOUR AND date_out = ''";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$badgeCount = $row['badge_count'];

$query1 = "SELECT COUNT(*) AS badge_count1 FROM parking_only WHERE TIMESTAMP(date_in, time_in) >= NOW() - INTERVAL 12 HOUR AND date_out = ''";
$result1 = mysqli_query($conn, $query1);
$row = mysqli_fetch_assoc($result1);
$badgeCount1 = $row['badge_count1'];

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>SmartSpot - Parking Management</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/ss-favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

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

  <style>
    th {
            cursor: pointer;
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

    <!-- <div class="search-bar">
      <form class="search-form d-flex align-items-center" method="POST" action="#">
        <input type="text" name="query" placeholder="Search" title="Enter search keyword">
        <button type="submit" title="Search"><i class="bi bi-search"></i></button>
      </form>
    </div> -->
    <!-- End Search Bar -->

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
              <span><?php echo htmlspecialchars($user_type); ?></span>
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
        <a class="nav-link " href="pages-parking-management.php">
          <i class="bi bi-car-front-fill"></i>
          <span>Parking Management</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="pages_livefeed.php">
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

  <main id="main" class="main">

    <div class="pagetitle">
      <!-- <h1>Customer Management</h1> -->
     <!--  <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
          <li class="breadcrumb-item">Tables</li>
          <li class="breadcrumb-item active">Data</li>
        </ol>
      </nav> -->
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body">

              <div class="d-flex justify-content-between align-items-center">
                  <h5 class="card-title">Parking Management</h5>
                  <!-- <h5 class="card-title">Vacant Space: <label id="total_space">0</label></h5> -->
                  <h5 class="card-title">Vacant Space: <?php echo $vacant_space?>/<?php echo $total_space?></h5>
              </div>

              <!-- Default Tabs -->
              <ul class="nav nav-pills" id="myTab" role="tablist">
                  <li class="nav-item" role="presentation">
                      <button class="nav-link active" id="new-parking-tab" data-bs-toggle="tab" data-bs-target="#new-parking" type="button" role="tab" aria-controls="new-parking" aria-selected="true">New Parking</button>
                  </li><!-- 
                  <li class="nav-item" role="presentation">
                      <button class="nav-link" id="active-parking-tab" data-bs-toggle="tab" data-bs-target="#active-parking" type="button" role="tab" aria-controls="active-parking" aria-selected="false">Active Vehicle</button>
                  </li> -->
                  <li class="nav-item" role="presentation">
                      <button class="nav-link d-flex align-items-center" id="active-parking-tab" data-bs-toggle="tab" data-bs-target="#active-parking" type="button" role="tab" aria-controls="active-parking" aria-selected="false">
                          <span class="me-2">Active Hourly Parking</span> 
                          <?php if ($badgeCount1 > 0): ?>
                              <span class="badge bg-primary badge-number"><?php echo $badgeCount1; ?></span>
                          <?php endif; ?>
                      </button>
                  </li>
                  <li class="nav-item" role="presentation">
                      <button class="nav-link d-flex align-items-center" id="overtime-parking-tab" data-bs-toggle="tab" data-bs-target="#overtime-parking" type="button" role="tab" aria-controls="overtime-parking" aria-selected="false">
                          <span class="me-2">Active Overtime Parking</span> 
                          <?php if ($badgeCount > 0): ?>
                              <span class="badge bg-primary badge-number"><?php echo $badgeCount; ?></span>
                          <?php endif; ?>
                      </button>
                  </li>
                  <li class="nav-item" role="presentation">
                      <button class="nav-link" id="parking-history-tab" data-bs-toggle="tab" data-bs-target="#parking-history" type="button" role="tab" aria-controls="parking-history" aria-selected="false">Parking History</button>
                  </li>
              </ul>

              <div class="tab-content pt-2" id="myTabContent">
                <!-- Parking Only -->
                <div class="tab-pane fade show active" id="new-parking" role="tabpanel" aria-labelledby="new-parking-tab">
                  <div class="container d-flex justify-content-center">
                    <form id="park-only">
                      <div class="row mt-4">
                        <!-- Owner's/Driver's Information (Left side) -->
                        <div class="col-md-12">
                          <h5 class="fw-bold">Vehicle Description</h5>
                          <div class="col-md-12">
                              <div class="form-floating mb-3">
                                  <?php
                                      include 'mysql/conn.php'; // Include your database connection

                                      // Query to get vehicle categories
                                      $query = "SELECT * FROM vehicle_category ORDER BY vehicle_type";
                                      $vehicle_category = mysqli_query($conn, $query);

                                      if ($vehicle_category && mysqli_num_rows($vehicle_category) > 0) {
                                          echo '<select name="new-parking-vehicle-type" class="form-select" id="new-parking-vehicle-type" aria-label="Vehicle Type" required>';
                                          echo '<option value="" disabled selected>Select Vehicle Type</option>';

                                          // Loop through the results and create an option for each
                                          while ($row = mysqli_fetch_assoc($vehicle_category)) {
                                              $vehicle_type = $row['vehicle_type'];
                                              $vehicle_amount = $row['amount'];
                                              echo '<option value="' . htmlspecialchars($vehicle_type) . '">' . htmlspecialchars($vehicle_type) . ' wheeled' . '</option>';
                                          }

                                          echo '</select>';
                                      } else {
                                          echo 'No vehicle categories found.';
                                      }
                                  ?>
                              </div>
                          </div>

                          <!-- Check Make/Model Input -->
                          <div class="row">
                            <div class="col-md-6 mb-3">
                              <label for="makeModel" class="form-label">Make/Model</label>
                              <input type="text" class="form-control" id="makeModel" placeholder="Enter make/model" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="parking-license" class="form-label">License No.</label>
                                <input type="text" class="form-control" id="parking-license" placeholder="Enter license number" required="required">
                            </div>
                          </div>

                          <!-- Check Car Color Input -->
                          <div class="mb-3">
                            <label for="color" class="form-label">Color</label>
                            <input type="text" class="form-control" id="color" placeholder="Enter color" required>
                          </div>

                          <!-- Row for Date (in) and Time (in) -->
                          <div class="row">
                            <div class="col-md-6 mb-3">
                              <label for="dateIn" class="form-label">Date (in)</label>
                              <input type="date" class="form-control" id="dateIn" required>
                            </div>
                            <div class="col-md-6 mb-3">
                              <label for="timeIn" class="form-label">Time (in)</label>
                              <input type="time" class="form-control" id="timeIn" required>
                            </div>
                          </div>
                        </div>
                      </div>
                      <button id="parking-only-btn" type="submit" class="btn btn-primary d-flex float-end">Submit</button>
                    </form>
                  </div>
                </div>
                <!-- End Parking Only -->
                <!-- Active Parking -->
                <div class="tab-pane fade" id="active-parking" role="tabpanel" aria-labelledby="active-parking-tab">
                  <table id="active-parking-data-table" class="table table-hover table-borderless table-striped">
                      <thead>
                          <tr>
                              <th onclick="sortTable(0)">Vehicle Type</th>
                              <th onclick="sortTable(1)">Make/Model</th>
                              <th onclick="sortTable(2)">License Number</th>
                              <th onclick="sortTable(3)">Color</th>
                              <th onclick="sortTable(4)">Date & time</th>
                              <th>Action</th>
                          </tr>
                      </thead>
                      <tbody>
                          <!-- Add Parking Only records here -->
                      </tbody>
                  </table>
                </div>
                <!-- End Active Parking -->
                <!-- Overtime Parking -->
                <div class="tab-pane fade" id="overtime-parking" role="tabpanel" aria-labelledby="overtime-parking-tab">
                  <table id="overtime-parking-data-table" class="table table-hover table-borderless table-striped">
                      <thead>
                          <tr>
                              <th onclick="sortTable(0)">Vehicle Type</th>
                              <th onclick="sortTable(1)">Make/Model</th>
                              <th onclick="sortTable(2)">License Number</th>
                              <th onclick="sortTable(3)">Color</th>
                              <th onclick="sortTable(4)">Date & time</th>
                              <th>Action</th>
                          </tr>
                      </thead>
                      <tbody>
                          <!-- Add Parking Only records here -->
                      </tbody>
                  </table>
                </div>
                <!-- End Overtime Parking -->
                <!-- Overtime Parking -->
                <div class="tab-pane fade" id="parking-history" role="tabpanel" aria-labelledby="parking-history-tab">
                  <div class="d-flex justify-content-between mb-2" style="max-width: 100%;">
                      <input type="text" id="overnight-search-box" class="form-control" placeholder="Search..." style="max-width: 400px;">
                      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ExportOvernightParkingModal">Export</button>
                  </div>
                  <!-- Modal -->
                  <div class="modal fade" id="ExportOvernightParkingModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title">Export Parking History</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <input type="checkbox" id="exportAllOvernightData" checked>
                                <label for="exportAllOvernightData">Export All Data</label>
                            </div>
                            <div class="form-group">
                                <label for="OPstartDate">Start Date</label>
                                <input type="date" class="form-control" id="OPstartDate" required disabled>
                            </div>
                            <div class="form-group">
                                <label for="OPendDate">End Date</label>
                                <input type="date" class="form-control" id="OPendDate" required disabled>
                            </div>
                            <div class="form-group">
                                <label for="OPexportFormat">Export as</label>
                                <select class="form-control" id="OPexportFormat">
                                    <option value="csv">CSV</option>
                                    <option value="pdf">PDF</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="button" class="btn btn-primary" id="submitOvernightParkingButton">Export</button>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- Table for Overnight Parking -->
                  <table id="parking-history-data-table" class="table table-hover table-borderless table-striped">
                      <thead>
                          <tr>
                              <th onclick="sortTable(1)">Owner</th>
                              <th onclick="sortTable(0)">Vehicle Type</th>
                              <th onclick="sortTable(1)">Make/Model</th>
                              <th onclick="sortTable(2)">License Number</th>
                              <th onclick="sortTable(3)">Color</th>
                              <th onclick="sortTable(4)">Start Parking</th>
                              <th onclick="sortTable(4)">End Parking</th>
                              <th onclick="sortTable(4)">Duration</th>
                              <th onclick="sortTable(4)">Payment Type</th>
                              <th onclick="sortTable(4)">Amount</th>
                          </tr>
                      </thead>
                      <tbody>
                          <!-- Add Parking Only records here -->
                      </tbody>
                  </table>
                </div>
                <!-- End Overtime Parking -->
              </div><!-- End Default Tabs -->

            </div>
          </div>

          <!-- Release Modal -->
          <div class="modal fade" id="ReleaseActiveModal" tabindex="-1">
            <div class="modal-dialog modal-md modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Release Vehicle</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <form id="release-active-parking">
                    <div class="row">
                        <input type="hidden" class="form-control" id="release-id">
                        <input type="hidden" class="form-control" id="release-payment-method" value="cash">
                       
                        <div class="mb-3">
                            <label for="overnight-makeModel" class="form-label">Make/Model</label>
                            <input type="text" class="form-control" id="release-make_model" placeholder="Enter make/model" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="overnight-color" class="form-label">Color</label>
                            <input type="text" class="form-control" id="release-color" placeholder="Enter color" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="vehicle-type" class="form-label">Vehicle Type</label>

                            <div class="input-group">
                                <input name="release-vehicle_type" type="number" class="form-control" id="release-vehicle_type" readonly>
                              <span class="input-group-text">Wheeled</span>
                            </div>
                        </div>
                        <!-- Row for Date (out) and Time (out) -->
                        <div class="col-md-6 mb-3">
                                <label for="overnightdateIn" class="form-label">Date (in)</label>
                                <input type="date" class="form-control" id="release-date_in" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="overnighttimeIn" class="form-label">Time (in)</label>
                                <input type="time" class="form-control" id="release-time_in" readonly>
                            </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="release-date_out" class="form-label">Date (out)</label>
                                <input type="date" class="form-control" id="release-date_out" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="release-time_out" class="form-label">Time (out)</label>
                                <input type="time" class="form-control" id="release-time_out" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="release-active-amount" class="form-label">Amount</label>
                            <input type="number" class="form-control" id="release-active-amount" placeholder="Parking Amount" readonly>
                        </div>
                        
                        <div class="col-12 d-flex justify-content-center mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="release-parking-validation" required>
                                <label class="form-check-label" for="release-parking-validation">
                                    Receive the payment in exact amount
                                </label>
                            </div>
                        </div>

                    </div>
                  <button type="submit" class="btn btn-primary w-100">Release</button>
                </form>
                </div>
              </div>
            </div>
          </div>
          <!-- End Release Modal -->

          <!-- Release Overtime Modal -->
          <div class="modal fade" id="ReleaseOvertimeModal" tabindex="-1">
            <div class="modal-dialog modal-md modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Release Overtime Vehicle</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <form id="release-overnight-parking">
                    <div class="row">
                        <input type="hidden" class="form-control" id="release-overnight-id">
                        <input type="hidden" class="form-control" id="release-overnight-payment-method" value="cash">

                        <div class="mb-3">
                            <label for="overnight-owner" class="form-label">Driver's Name</label>
                            <input type="text" class="form-control" id="release-overnight-owner" placeholder="Enter driver's name">
                        </div>
                       
                        <div class="mb-3">
                            <label for="overnight-makeModel" class="form-label">Make/Model</label>
                            <input type="text" class="form-control" id="release-overnight-make_model" placeholder="Enter make/model" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="overnight-color" class="form-label">Color</label>
                            <input type="text" class="form-control" id="release-overnight-color" placeholder="Enter color" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="vehicle-type" class="form-label">Vehicle Type</label>

                            <div class="input-group">
                                <input name="release-vehicle_type" type="number" class="form-control" id="release-overnight-vehicle_type" readonly>
                              <span class="input-group-text">Wheeled</span>
                            </div>
                        </div>
                        <!-- Row for Date (out) and Time (out) -->
                        <div class="col-md-6 mb-3">
                                <label for="overnightdateIn" class="form-label">Date (in)</label>
                                <input type="date" class="form-control" id="release-overnight-date_in" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="overnighttimeIn" class="form-label">Time (in)</label>
                                <input type="time" class="form-control" id="release-overnight-time_in" readonly>
                            </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="release-date_out" class="form-label">Date (out)</label>
                                <input type="date" class="form-control" id="release-overnight-date_out" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="release-time_out" class="form-label">Time (out)</label>
                                <input type="time" class="form-control" id="release-overnight-time_out" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="release-overnight-amount" class="form-label">Amount</label>
                            <input type="number" class="form-control" id="release-overnight-amount" placeholder="Parking Amount" readonly>
                        </div>
                        
                        <div class="col-12 d-flex justify-content-center mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="release-overnight-parking-validation" required>
                                <label class="form-check-label" for="release-parking-validation">
                                    Receive the payment in exact amount
                                </label>
                            </div>
                        </div>

                    </div>
                  <button type="submit" class="btn btn-primary w-100">Release</button>
                </form>
                </div>
              </div>
            </div>
          </div>
          <!-- End Release Overtime Modal -->

        </div>
      </div>
    </section>

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

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>
<!-- 
  <script>
    // Polling every 1 second
    setInterval(function() {
        $.ajax({
            url: 'mysql/fetch_vacant.php', // PHP file to fetch the data
            type: 'GET',
            success: function(response) {
                // Update the total space in the HTML
                $('#total_space').text(response);
            },
            error: function() {
                console.log('Error fetching data.');
            }
        });
    }, 10000); // Poll every 1000ms (1 second)
</script> -->

  <script>
      // Get the value of vacant_space from PHP
      var vacantSpace = <?php echo json_encode($vacant_space); ?>;

      // Function to disable buttons if vacant space is 0
      function disableButtonsIfNoSpace() {
          if (vacantSpace == 0) {
              document.getElementById('parking-only-btn').disabled = true;
              document.getElementById('overnight-parking-btn').disabled = true;
          }
      }

      // Call the function on page load
      disableButtonsIfNoSpace();

      $(document).ready(function() {
        // Initial state check for exportAllData checkbox
        toggleDateInputs($('#exportAllData').is(':checked'));

        // Listen to changes on the exportAllData checkbox
        $('#exportAllData').change(function() {
            const isChecked = $(this).is(':checked');
            toggleDateInputs(isChecked);
        });

        // Function to enable/disable the date inputs based on checkbox state
        function toggleDateInputs(isChecked) {
            if (isChecked) {
                // Disable date inputs if exportAllData is checked
                $('#POstartDate').prop('disabled', true).val('');
                $('#POendDate').prop('disabled', true).val('');
            } else {
                // Enable date inputs if exportAllData is not checked
                $('#POstartDate').prop('disabled', false);
                $('#POendDate').prop('disabled', false);
            }
        }
    });
  </script>

  <script>
      $(document).ready(function() {
        // Initial state check for exportAllOvernightData checkbox
        toggleDateInputs($('#exportAllOvernightData').is(':checked'));

        // Listen to changes on the exportAllOvernightData checkbox
        $('#exportAllOvernightData').change(function() {
            const isChecked = $(this).is(':checked');
            toggleDateInputs(isChecked);
        });

        // Function to enable/disable the date inputs based on checkbox state
        function toggleDateInputs(isChecked) {
            if (isChecked) {
                // Disable date inputs if exportAllOvernightData is checked
                $('#OPstartDate').prop('disabled', true).val('');
                $('#OPendDate').prop('disabled', true).val('');
            } else {
                // Enable date inputs if exportAllOvernightData is not checked
                $('#OPstartDate').prop('disabled', false);
                $('#OPendDate').prop('disabled', false);
            }
        }

        $('#submitOvernightParkingButton').click(function() {
            const startDate = $('#OPstartDate').val();
            const endDate = $('#OPendDate').val();
            const exportFormat = $('#OPexportFormat').val();

            // Validation
            if (!$('#exportAllOvernightData').is(':checked') && (!startDate || !endDate)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: 'Please select both start and end dates.',
                    confirmButtonText: 'OK'
                });
                return; // Stop further execution and keep the modal open
            }

            // console.log(startDate);
            // console.log(endDate);
            // console.log(exportFormat);

            $.ajax({
                url: 'mysql/export-parking-history.php',  // Your PHP file
                type: 'POST',
                data: {
                    startDate: startDate,
                    endDate: endDate,
                    exportFormat: exportFormat
                },
                success: function(response) {
                    if (response.error) {
                        // Show SweetAlert if no data is found
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Data Found',
                            text: response.error,
                            confirmButtonText: 'OK'
                        });
                        return; // Stop further execution and keep the modal open
                    }

                    if (exportFormat === 'csv') {
                        // Create a link to download CSV
                        const blob = new Blob([response], { type: 'text/csv' });
                        const url = URL.createObjectURL(blob);
                        const link = document.createElement('a');
                        link.href = url;
                        link.download = 'parking_history.csv';  // Name of the CSV file
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    } else {
                        // Construct the URL with query parameters
                        let queryParams = `?startDate=${encodeURIComponent(startDate)}&endDate=${encodeURIComponent(endDate)}`;
                        
                        // Open print_parking_only.php in a new tab
                        window.open('mysql/print_parking_history.php' + queryParams, '_blank');
                    }

                    $('#ExportOvernightParkingModal').modal('hide'); // Hide modal only on successful submission
                },
                error: function(xhr, status, error) {
                    console.error("Error: " + error);
                }
            });
        });
    });
  </script>

   <!-- Release Overtime Parked Vehicle -->
  <script>
      $(document).ready(function() {
          let total_duration = 0;

          // Function to calculate total amount based on the provided times
          function calculateTotalAmount() {
              // Gather form data
              const vehicle_type = $('#release-overnight-vehicle_type').val();  // Get the vehicle type
              const formData = {
                  id: $('#release-overnight-id').val(),
                  make_model: $('#release-overnight-make_model').val(),
                  color: $('#release-overnight-color').val(),
                  vehicle_type: vehicle_type,  // Add vehicle type to formData
                  date_in: $('#release-overnight-date_in').val(),
                  time_in: $('#release-overnight-time_in').val(),
                  date_out: $('#release-overnight-date_out').val(),
                  time_out: $('#release-overnight-time_out').val(),
                  amount: $('#release-overnight-amount').val(),
                  payment_method: $('#release-overnight-payment-method').val(),
                  parking_validation: $('#release-overnight-parking-validation').prop('checked'),
              };

              // Only proceed if both date_out and time_out have values
              if (formData.date_out && formData.time_out) {
                  // AJAX call to get amount and overtime based on vehicle_type
                  $.ajax({
                      url: 'mysql/get_amount.php',
                      type: 'POST',
                      data: { vehicle_type: vehicle_type },
                      dataType: 'json',
                      success: function(response) {
                          // Check if response contains valid amount and overtime
                          if (response && response.amount !== undefined && response.overtime !== undefined) {
                              formData.amount = response.amount;
                              formData.overtime = response.overtime;

                              // Get the parking start and end times (combine date and time)
                              const dateInTime = new Date(formData.date_in + ' ' + formData.time_in);
                              const dateOutTime = new Date(formData.date_out + ' ' + formData.time_out);
                              
                              // Calculate the total parking time in milliseconds
                              const totalTimeMillis = dateOutTime - dateInTime;

                              // Convert milliseconds to hours (and round up if necessary)
                              let totalTimeHours = totalTimeMillis / 1000 / 60 / 60; // Convert to hours
                              if (totalTimeHours % 1 !== 0) { // If there are excess minutes, round up
                                  totalTimeHours = Math.ceil(totalTimeHours);
                              }

                              // Calculate amount
                              let totalAmount = 0;
                              // Add overtime for every 12 hours
                              const overtimePeriods = Math.floor(totalTimeHours / 12);
                              totalAmount += overtimePeriods * response.overtime;

                              // Add regular amount for each hour
                              const remainingHours = totalTimeHours % 12; // Hours after the last full 12-hour period
                              totalAmount += remainingHours * response.amount;

                              // Log the final calculated total amount
                              // console.log('Total time in hours:', totalTimeHours);
                              // console.log('Total calculated amount:', totalAmount);

                              total_duration = totalTimeHours;

                              // Update the amount field in the form with the calculated total amount
                              $('#release-overnight-amount').val(totalAmount);

                              // Log the updated formData with the calculated total amount
                              // console.log('Updated FormData:', formData);
                          } else {
                              console.error('Error fetching amount and overtime');
                          }
                      },
                      error: function(xhr, status, error) {
                          console.error('AJAX error:', error);
                          console.log('Response Text:', xhr.responseText); // Log the response text for debugging
                      }
                  });
              }
          }

          // Event listener for changes in date_out and time_out
          $('#release-overnight-date_out, #release-overnight-time_out').on('change', function() {
              // Trigger the calculation when both date_out and time_out are filled
              if ($('#release-overnight-date_out').val() && $('#release-overnight-time_out').val()) {
                  calculateTotalAmount();
              }
          });

          // Form submission handler
          $('#release-overnight-parking').on('submit', function(event) {
              event.preventDefault();

              // Gather form data
              const vehicle_type = $('#release-overnight-vehicle_type').val();  // Get the vehicle type
              const formData = {
                  id: $('#release-overnight-id').val(),
                  owner: $('#release-overnight-owner').val(),
                  make_model: $('#release-overnight-make_model').val(),
                  color: $('#release-overnight-color').val(),
                  vehicle_type: vehicle_type,  // Add vehicle type to formData
                  date_in: $('#release-overnight-date_in').val(),
                  time_in: $('#release-overnight-time_in').val(),
                  date_out: $('#release-overnight-date_out').val(),
                  time_out: $('#release-overnight-time_out').val(),
                  amount: $('#release-overnight-amount').val(),
                  payment_method: $('#release-overnight-payment-method').val(),
                  parking_validation: $('#release-overnight-parking-validation').prop('checked'),
                  duration: total_duration,
              };

              // Log the formData to the console
              // console.log('FormData:', formData);

              // Optionally, you can send formData using AJAX if needed
                $.ajax({
                  url: 'mysql/update_overnight_parking.php', // Path to your PHP script
                  type: 'POST',
                  data: JSON.stringify(formData), // Send data as JSON
                  contentType: 'application/json', // Set content type as JSON
                  dataType: 'json', // Expect JSON response
                  success: function(response) {
                      // If successful, show SweetAlert success
                      if (response.status === 'success') {
                          $('#ReleaseOvernightModal').modal('hide');
                          Swal.fire({
                              title: 'Success!',
                              text: response.message,
                              icon: 'success',
                              confirmButtonText: 'OK'
                          }).then(() => {
                              // Call the function to refresh the parking data
                              location.reload();
                          });
                      } else {
                          // Handle failure response
                          Swal.fire({
                              title: 'Error!',
                              text: response.message,
                              icon: 'error',
                              confirmButtonText: 'OK'
                          });
                      }
                  },
                  error: function(xhr, status, error) {
                      console.error('AJAX Error:', error);
                      Swal.fire({
                          title: 'Error!',
                          text: 'An error occurred while processing your request.',
                          icon: 'error',
                          confirmButtonText: 'OK'
                      });
                  }
              });
          });
      });
  </script>

  <script>
    $(document).ready(function() {
        $('#ReleaseActiveModal').on('show.bs.modal', function() {
            // Fetch vehicle type to get the corresponding amount
            const vehicleType = $('#release-vehicle_type').val();

            $.ajax({
                url: 'mysql/get_amount.php', // PHP script path to get the amount
                type: 'POST',
                data: { vehicle_type: vehicleType },
                dataType: 'json',
                success: function(response) {
                    // Set the amount field in the modal (but don't calculate yet)
                    $('#release-active-amount').val(''); // Clear the field first
                    // console.log("Amount for vehicle type", vehicleType, ":", response.amount);
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching data:', error);
                }
            });
        });

        // Add event listener to calculate the amount when Date Out or Time Out fields change
        $('#release-date_out, #release-time_out').on('change', function() {
            calculateAmount();
        });

        function calculateAmount() {
            const dateIn = $('#release-date_in').val();
            const timeIn = $('#release-time_in').val();
            const dateOut = $('#release-date_out').val();
            const timeOut = $('#release-time_out').val();
            const vehicleType = $('#release-vehicle_type').val();

            // Ensure all necessary fields are filled before calculating
            if (dateIn && timeIn && dateOut && timeOut) {
                const dateTimeIn = new Date(`${dateIn} ${timeIn}`);
                const dateTimeOut = new Date(`${dateOut} ${timeOut}`);

                // Calculate the difference in milliseconds
                const timeDifference = dateTimeOut - dateTimeIn;

                // Convert milliseconds to hours
                let hours = Math.ceil(timeDifference / (1000 * 60 * 60)); // rounding up to the nearest hour

                // Fetch amount from the PHP script for the given vehicle type
                $.ajax({
                    url: 'mysql/get_amount.php',
                    type: 'POST',
                    data: { vehicle_type: vehicleType },
                    dataType: 'json',
                    success: function(response) {
                        const amount = response.amount;
                        const totalAmount = hours * amount;

                        // Set the calculated amount in the input field
                        $('#release-active-amount').val(totalAmount);
                        // console.log("Calculated amount:", totalAmount);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching data:', error);
                        $('#release-active-amount').val('Error');
                    }
                });
            }
        }
    });
  </script>

  <script>
    $(document).ready(function() {
    $('#release-active-parking').on('submit', function(event) {
        event.preventDefault(); // Prevent the default form submission

        // Collect the form data
        const dateIn = $('#release-date_in').val();
        const timeIn = $('#release-time_in').val();
        const dateOut = $('#release-date_out').val();
        const timeOut = $('#release-time_out').val();

        // Calculate parking duration in hours
        const dateTimeIn = new Date(`${dateIn}T${timeIn}`);
        const dateTimeOut = new Date(`${dateOut}T${timeOut}`);
        
        let duration = (dateTimeOut - dateTimeIn) / (1000 * 60 * 60); // Convert milliseconds to hours
        duration = Math.ceil(duration); // Round up to the nearest hour

        const formData = {
            id: $('#release-id').val(),
            make_model: $('#release-make_model').val(),
            color: $('#release-color').val(),
            vehicle_type: $('#release-vehicle_type').val(),
            date_in: dateIn,
            time_in: timeIn,
            date_out: dateOut,
            time_out: timeOut,
            parking_duration: duration, // Include the calculated parking duration
            amount: $('#release-active-amount').val(),
            payment_method: $('#release-payment-method').val(),
            parking_validation: $('#release-parking-validation').prop('checked'),
        };

        // console.log(formData);

        // Send formData to PHP file using AJAX
        $.ajax({
            url: 'mysql/update_active_parking.php', // Path to your PHP script
            type: 'POST',
            data: JSON.stringify(formData), // Send data as JSON
            contentType: 'application/json', // Set content type as JSON
            dataType: 'json', // Expect JSON response
            success: function(response) {
                // If successful, show SweetAlert success
                if (response.status === 'success') {
                    $('#ReleaseActiveModal').modal('hide');
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Call the function to refresh the parking data
                        location.reload();
                    });
                } else {
                    // Handle failure response
                    Swal.fire({
                        title: 'Error!',
                        text: response.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while processing your request.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    });
});


    document.addEventListener('DOMContentLoaded', function() {
      fetchActiveParkingData();
      fetchOvertimeParkingData();
      fetchParkingHistory();

      function formatDateTime(dateTime) {
          const date = new Date(dateTime);
          const options = { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true };
          const formattedDate = date.toLocaleString('en-US', options);
          
          return formattedDate.replace(',', ''); // Remove the comma between date and time
      }

      function fetchActiveParkingData() {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'mysql/get_parking_only.php', true);
        xhr.onload = function() {
            if (this.status === 200) {
                const data = JSON.parse(this.responseText);
                let tbody = document.querySelector('#active-parking-data-table tbody');
                tbody.innerHTML = '';

                data.forEach(row => {
                    let tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${row.vehicle_type} Wheeled</td>
                        <td>${row.make_model}</td>
                        <td>${row.license_num}</td>
                        <td>${row.color}</td>
                        <td>${formatDateTime(row.date_in + ' ' + row.Time_in)}</td>
                        <td>${row.payment_type}</td>
                        <td>
                            <button type="button" class="btn btn-secondary btn-sm release-button" data-id="${row.id}" data-vehicle_type="${row.vehicle_type}" data-make_model="${row.make_model}" data-color="${row.color}" data-date_in="${row.date_in}" data-time_in="${row.Time_in}" data-bs-toggle="modal" data-bs-target="#ReleaseActiveModal">Release Vehicle</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });

                // Add event listeners to the release buttons
                const releaseButtons = document.querySelectorAll('.release-button');
                releaseButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        // Set the values in the modal
                        document.getElementById('release-id').value = this.getAttribute('data-id');
                        document.getElementById('release-vehicle_type').value = this.getAttribute('data-vehicle_type');
                        document.getElementById('release-make_model').value = this.getAttribute('data-make_model');
                        document.getElementById('release-color').value = this.getAttribute('data-color');
                        document.getElementById('release-date_in').value = this.getAttribute('data-date_in');
                        document.getElementById('release-time_in').value = this.getAttribute('data-time_in');

                        // Fetch amount after setting vehicle_type
                        const vehicleType = this.getAttribute('data-vehicle_type');
                        $.ajax({
                            url: 'mysql/get_amount.php',
                            type: 'POST',
                            data: { vehicle_type: vehicleType },
                            dataType: 'json',
                            success: function(response) {
                                $('#release-active-amount').val(response.amount || '0');
                                // console.log("Amount for vehicle type", vehicleType, ":", response.amount);
                            },
                            error: function(xhr, status, error) {
                                console.error('Error fetching data:', error);
                                $('#release-active-amount').val('Error');
                            }
                        });
                    });
                });
            }
        };
        xhr.send();
      }

      function fetchOvertimeParkingData() {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'mysql/get_overnight_parking.php', true);
        xhr.onload = function() {
            if (this.status === 200) {
                const data = JSON.parse(this.responseText);
                let tbody = document.querySelector('#overtime-parking-data-table tbody');
                tbody.innerHTML = '';

                data.forEach(row => {
                    let tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${row.vehicle_type} Wheeled</td>
                        <td>${row.make_model}</td>
                        <td>${row.license_num}</td>
                        <td>${row.color}</td>
                        <td>${formatDateTime(row.date_in + ' ' + row.Time_in)}</td>
                        <td>${row.payment_type}</td>
                        <td>
                            <button type="button" class="btn btn-secondary btn-sm release-button" data-overnight-id="${row.id}" data-overnight-vehicle_type="${row.vehicle_type}" data-overnight-make_model="${row.make_model}" data-overnight-color="${row.color}" data-overnight-date_in="${row.date_in}" data-overnight-time_in="${row.Time_in}" data-bs-toggle="modal" data-bs-target="#ReleaseOvertimeModal">Release Vehicle</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });

                // Add event listeners to the release buttons
                const releaseButtons = document.querySelectorAll('.release-button');
                releaseButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        // Set the values in the modal
                        document.getElementById('release-overnight-id').value = this.getAttribute('data-overnight-id');
                        document.getElementById('release-overnight-vehicle_type').value = this.getAttribute('data-overnight-vehicle_type');
                        document.getElementById('release-overnight-make_model').value = this.getAttribute('data-overnight-make_model');
                        document.getElementById('release-overnight-color').value = this.getAttribute('data-overnight-color');
                        document.getElementById('release-overnight-date_in').value = this.getAttribute('data-overnight-date_in');
                        document.getElementById('release-overnight-time_in').value = this.getAttribute('data-overnight-time_in');

                        // Fetch amount after setting vehicle_type
                        const vehicleType = this.getAttribute('data-vehicle_type');
                        $.ajax({
                            url: 'mysql/get_amount.php',
                            type: 'POST',
                            data: { vehicle_type: vehicleType },
                            dataType: 'json',
                            success: function(response) {
                                $('#release-active-amount').val(response.amount || '0');
                                // console.log("Amount for vehicle type", vehicleType, ":", response.amount);
                            },
                            error: function(xhr, status, error) {
                                console.error('Error fetching data:', error);
                                $('#release-active-amount').val('Error');
                            }
                        });
                    });
                });
            }
        };
        xhr.send();
      }

      function fetchParkingHistory() {
          const xhr = new XMLHttpRequest();
          xhr.open('GET', 'mysql/get_parking_history.php', true);
          xhr.onload = function() {
              if (this.status === 200) {
                  const data = JSON.parse(this.responseText);
                  let tbody = document.querySelector('#parking-history-data-table tbody');
                  tbody.innerHTML = '';

                  data.forEach(row => {
                      let tr = document.createElement('tr');
                      tr.innerHTML = `
                          <td>${row.vehicle_owner || '-'}</td>
                          <td>${row.vehicle_type} Wheeled</td>
                          <td>${row.make_model}</td>
                          <td>${row.license_num}</td>
                          <td>${row.color}</td>
                          <td>${formatDateTime(row.date_in + ' ' + row.Time_in)}</td>
                          <td>${formatDateTime(row.date_out + ' ' + row.Time_out)}</td>
                          <td>${row.parking_duration_hours + (row.parking_duration_hours > 1 ? ' hours, ' : ' hour, ') + row.parking_duration_minutes + (row.parking_duration_minutes > 1 ? ' minutes,' : ' minute, ')}</td>
                          <td>${row.payment_type}</td>
                          <td>${row.amount}</td>
                      `;
                      tbody.appendChild(tr);
                  });
              }
          };
          xhr.send();
      }

      function formatDateTime(dateTime) {
          const date = new Date(dateTime);
          const options = { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true };
          const formattedDate = date.toLocaleString('en-US', options);
          
          return formattedDate.replace(',', ''); // Remove the comma between date and time
      }
  });

  </script>

 <script>
      // Function to set the current date and time
      function setCurrentDateTime() {
          var today = new Date();
          
          // Format the date as YYYY-MM-DD
          var dateIn = today.toISOString().split('T')[0];
          var timeIn = today.toTimeString().split(' ')[0].substring(0, 5);
          
          // Set values for date and time in fields
          document.getElementById('dateIn').value = dateIn;
          document.getElementById('timeIn').value = timeIn;
          
          // Set values for release date and time out fields
          document.getElementById('release-date_out').value = dateIn;
          document.getElementById('release-time_out').value = timeIn;
      }

      // Initial call to set the current date and time when the page loads
      window.onload = setCurrentDateTime;

      // Add event listener to the form
      document.getElementById('park-only').addEventListener('submit', function(event) {
          event.preventDefault(); // Prevent the default form submission

          // Gather form data
          const vehicleType = document.getElementById('new-parking-vehicle-type').value;
          const makeModel = document.getElementById('makeModel').value;
          const parkingLicense = document.getElementById('parking-license').value;
          const color = document.getElementById('color').value;
          const dateIn = document.getElementById('dateIn').value;
          const timeIn = document.getElementById('timeIn').value;

          // Prepare data for AJAX request
          const formData = {
              vehicleType,
              makeModel,
              parkingLicense,
              color,
              dateIn,
              timeIn
          };

          // Send AJAX request
          fetch('mysql/add_parking_only.php', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json'
              },
              body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Parking details have been submitted.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Clear the input fields
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'There was a problem submitting the form.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        });
  </script>

  <script>
  // Pass PHP values to JavaScript variables
  const twoWheeledRate = <?php echo $two_wheeled_rate; ?>;
  const threeWheeledRate = <?php echo $three_wheeled_rate; ?>;
  const fourWheeledRate = <?php echo $four_wheeled_rate; ?>;

  // Function to update the amount based on selected vehicle type
  document.getElementById('new-parking-vehicle-type').addEventListener('change', function() {
      const selectedValue = this.value;  // Get selected vehicle type (2, 3, or 4)
      const amountField = document.getElementById('parking-only-amount');

      // Update amount based on vehicle type
      if (selectedValue == '2') {
          amountField.value = twoWheeledRate;
      } else if (selectedValue == '3') {
          amountField.value = threeWheeledRate;
      } else if (selectedValue == '4') {
          amountField.value = fourWheeledRate;
      } else {
          amountField.value = '';  // Clear the amount field if no valid selection
      }
  });
  </script>

  <script>
      function sortTable(columnIndex, tableId) {
          const table = document.getElementById(tableId);
          const tbody = table.querySelector("tbody");
          const rowsArray = Array.from(tbody.rows);
          const isAscending = table.dataset.sortOrder === "asc";

          rowsArray.sort((rowA, rowB) => {
              const cellA = rowA.cells[columnIndex].innerText.trim();
              const cellB = rowB.cells[columnIndex].innerText.trim();
              const a = isNaN(cellA) ? cellA : Number(cellA);
              const b = isNaN(cellB) ? cellB : Number(cellB);
              const comparison = (a > b) ? 1 : (a < b) ? -1 : 0;
              return isAscending ? comparison : -comparison;
          });

          tbody.innerHTML = "";
          tbody.append(...rowsArray);

          table.dataset.sortOrder = isAscending ? "desc" : "asc";
      }

      // Update the header click events for both tables
      document.querySelectorAll('#parking-only-data-table th').forEach((th, index) => {
          th.addEventListener('click', () => sortTable(index, 'parking-only-data-table'));
      });

      document.querySelectorAll('#overnight-data-table th').forEach((th, index) => {
          th.addEventListener('click', () => sortTable(index, 'overnight-data-table'));
      });

      // Function to filter table rows based on search input
      function filterTable(searchBoxId, tableId) {
          var input = document.getElementById(searchBoxId);
          var filter = input.value.toLowerCase();
          var table = document.getElementById(tableId);
          var rows = table.querySelectorAll('tbody tr');

          rows.forEach(function(row) {
              var cells = row.querySelectorAll('td');
              var found = Array.from(cells).some(function(cell) {
                  return cell.textContent.toLowerCase().includes(filter);
              });
              row.style.display = found ? '' : 'none';
          });
      }

      // Add event listeners for both search boxes
      document.getElementById('overnight-search-box').addEventListener('input', function() {
          filterTable('overnight-search-box', 'parking-history-data-table');
      });

      // document.getElementById('parking-only-search-box').addEventListener('input', function() {
      //     filterTable('parking-only-search-box', 'parking-only-data-table');
      // });
  </script>

  <script>
    window.onload = function() {
      // Get current date and time
      var today = new Date();
      
      // Format the date as YYYY-MM-DD
      var dateIn = today.toISOString().split('T')[0];
      var overnightdateIn = today.toISOString().split('T')[0];
      var extendeddateIn = today.toISOString().split('T')[0];
      document.getElementById('dateIn').value = dateIn;
      document.getElementById('overnightdateIn').value = overnightdateIn;
      document.getElementById('release-date_out').value = extendeddateIn;
      
      // Format the time as HH:MM (24-hour format)
      var timeIn = today.toTimeString().split(' ')[0].substring(0, 5);
      var overnighttimeIn = today.toTimeString().split(' ')[0].substring(0, 5);
      document.getElementById('timeIn').value = timeIn;
      document.getElementById('overnighttimeIn').value = overnighttimeIn;
    };
    
    // Function to handle phone number input
    function handlePhoneNumberInput(event) {
      let input = event.target;
      let value = input.value;

      // Remove non-numeric characters
      value = value.replace(/\D/g, '');

      // Update the input field with the processed value
      input.value = value;

      // Prevent adding more digits after reaching the max length
      if (value.length >= 11) {
        event.preventDefault(); // Block any further input beyond 11 digits
      }
    }

    // Add event listener for phone number input
    document.getElementById('contact').addEventListener('input', handlePhoneNumberInput);

    // Also block non-numeric keys from adding extra characters
    document.getElementById('contact').addEventListener('keydown', function(event) {
      const key = event.key;
      const input = event.target;
      
      // Allow navigation keys (e.g., backspace, delete, arrows)
      const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight'];
      
      if (input.value.length >= 11 && !allowedKeys.includes(key)) {
        event.preventDefault(); // Prevent keypress beyond 11 digits unless it's a navigation key
      }
    });
  </script>

</body>

</html>