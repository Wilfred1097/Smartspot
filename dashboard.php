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

$parking_only_profit_today = $conn->query("SELECT SUM(amount) FROM `parking_only` WHERE date_out = CURDATE()")->fetch_row()[0];
$overnight_parking_profit_today = $conn->query("SELECT SUM(amount) FROM `overnight_parking` WHERE DATE(date_added) = CURDATE()")->fetch_row()[0];
$parking_only = $conn->query("SELECT COUNT(*) FROM parking_only WHERE TIMESTAMP(date_in, time_in) >= NOW() - INTERVAL 12 HOUR AND date_out = ''")->fetch_row()[0];
$overnight_parking = $conn->query("SELECT COUNT(*) FROM parking_only WHERE TIMESTAMP(date_in, time_in) <= NOW() - INTERVAL 12 HOUR AND date_out = ''")->fetch_row()[0];
$vacant_space = $conn->query("SELECT COUNT(id) FROM `parking_status` WHERE status = 0")->fetch_row()[0];
$occupied_space = $conn->query("SELECT COUNT(id) FROM `parking_status` WHERE status = 1")->fetch_row()[0];

$total_profit = $parking_only_profit_today + $overnight_parking_profit_today;
$total_space = $vacant_space + $occupied_space;

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>SmartSpot - Dashboard</title>
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

        <!-- <li class="nav-item d-block d-lg-none">
          <a class="nav-link nav-icon search-bar-toggle " href="#">
            <i class="bi bi-search"></i>
          </a>
        </li> --><!-- End Search Icon-->


        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <img src="assets/img/profile/<?php echo $customer['profile']?>" alt="Profile" class="rounded-circle">
            <span class="d-none d-md-block dropdown-toggle ps-2"><?php echo $first_letter_fname . '. ' . htmlspecialchars($customer_lname); ?></span>
          </a><!-- End Profile Iamge Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6><?php echo htmlspecialchars($customer_fname) . ' ' . htmlspecialchars($customer_mname) . ' ' . htmlspecialchars($customer_lname); ?></h6>
              <!-- <h6>Kevin Anderson</h6> -->
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
        <a class="nav-link " href="dashboard.php">
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

    <section class="section dashboard">
      <div class="row">

        <!-- Left side columns -->
        <div class="col-lg-12">
          <div class="row">

            <!-- Sales Card -->
            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
              <div class="card info-card sales-card">

                <div class="card-body">
                  <h5 class="card-title">Profit <span>| Today</span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <s class="bi">₱</s>
                    </div>
                    <div class="ps-3">
                      <h6><?php echo $total_profit;?></h6>
                      <!-- <span class="text-success small pt-1 fw-bold">12%</span> <span class="text-muted small pt-2 ps-1">increase</span> -->

                    </div>
                  </div>
                </div>

              </div>
            </div><!-- End Sales Card -->

            <!-- Customers Card -->
            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">

              <div class="card info-card customers-card">
                <div class="card-body">
                  <h5 class="card-title">Active Hourly Parking</h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?php echo $parking_only . ' ' . ($parking_only == 2 ? 'vehicles' : 'vehicle'); ?></h6>
                      <!-- <span class="text-danger small pt-1 fw-bold">12%</span> <span class="text-muted small pt-2 ps-1">decrease</span> -->

                    </div>
                  </div>

                </div>
              </div>

            </div><!-- End Customers Card -->

            <!-- Customers Card -->
            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">

              <div class="card info-card customers-card">

                <div class="card-body">
                  <h5 class="card-title">Active Overtime Parking</h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-clock"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?php echo $overnight_parking . ' ' . ($overnight_parking == 2 ? 'vehicles' : 'vehicle'); ?></h6>
                      <!-- <span class="text-danger small pt-1 fw-bold">12%</span> <span class="text-muted small pt-2 ps-1">decrease</span> -->

                    </div>
                  </div>

                </div>
              </div>

            </div><!-- End Customers Card -->

            <!-- Revenue Card -->
            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
              <div class="card info-card revenue-card">

                <div class="card-body">
                  <h5 class="card-title">Vacant Space<span></span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-p-circle"></i>
                    </div>
                    <div class="ps-3">
                      <!-- <h6 id="total_space">0</h6> -->
                      <h6><?php echo $vacant_space . '/' . $total_space . ' ' . ($vacant_space == 1 ? 'spot' : 'spots'); ?></h6>

                    </div>
                  </div>
                </div>

              </div>
            </div><!-- End Revenue Card -->

            <!-- Parking Analytics Chart -->
            <div class="col-lg-6">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center">
                      <h5 class="card-title">Parking Analytics <span>| Daily</span></h5>
                      <button class="btn btn-sm btn-success" id="export-chart1">Export</button>
                  </div>

                  <!-- Line Chart -->
                  <canvas id="parkingChart" style="max-height: 400px;"></canvas>
                  

                </div>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title">Parking Analytics <span>| Monthly</span></h5>
                        <button class="btn btn-sm btn-success" id="export-chart2">Export</button>
                    </div>
                    <!-- Bar Chart -->
                    <canvas id="monthlyParkingChart" style="max-height: 400px;"></canvas>
                </div>
              </div>
            </div>

            <!-- Parking Only -->
            <div class="col-12">
              <div class="card recent-sales overflow-auto">

                <div class="card-body">
                  <h5 class="card-title">Recent Parking</h5>

                  <table id="parking-only-recent" class="table table-hover table-borderless table-striped">
                    <thead>
                      <tr>
                        <th scope="col">Owner</th>
                        <th scope="col">Vehicle Type</th>
                        <th scope="col">Make/Model</th>
                        <th scope="col">License Number</th>
                        <th scope="col">Color</th>
                        <th scope="col">Start Parking</th>
                        <th scope="col">End Parking</th>
                        <th scope="col">Duration</th>
                        <th scope="col">Payment Type</th>
                        <th scope="col">Amount</th>
                      </tr>
                    </thead>
                    <tbody>
                      
                    </tbody>
                  </table>

                </div>

              </div>
            </div><!-- End Parking Only -->

          </div>
        </div><!-- End Left side columns -->

      </div>
    </section>

  </main><!-- End #main -->

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

  <script src="assets/plugins/jquery/jquery-3.7.1.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

<!--   <script>
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
      // Function to format date from YYYY-MM-DD to MMM. DD, YYYY
      function formatDate(dateString) {
          const options = { year: 'numeric', month: 'short', day: '2-digit' };
          const date = new Date(dateString);
          return date.toLocaleDateString('en-US', options).replace(/,/, '.'); // Replace comma with a period
      }

      // Generate dates for the last 7 days
      const today = new Date();
      const labels = [];
      for (let i = 6; i >= 0; i--) {
          const date = new Date(today);
          date.setDate(today.getDate() - i);
          const formattedDate = formatDate(date.toISOString().split('T')[0]);
          labels.push(formattedDate); // Use the formatted date
      }

      // AJAX request to fetch parking data
      fetch('mysql/parking_analytics_1.php') // Update with the correct path to your PHP file
          .then(response => response.json())
          .then(data => {
              const parkingOnlyData = new Array(7).fill(0);
              const overnightParkingData = new Array(7).fill(0);

              // Populate the data arrays based on the fetched data
              data.forEach(item => {
                  const formattedDate = formatDate(item.parking_date); // Format the fetched date
                  const index = labels.indexOf(formattedDate);
                  if (index !== -1) {
                      parkingOnlyData[index] = item.parking_only_count;
                      overnightParkingData[index] = item.overnight_parking_count;
                  }
              });

              // Create the chart with the fetched data
              const ctx1 = document.getElementById('parkingChart').getContext('2d');
              const parkingChart = new Chart(ctx1, {
                  type: 'bar',
                  data: {
                      labels: labels,
                      datasets: [
                          {
                              label: 'Vehicle Count',
                              data: parkingOnlyData,
                              backgroundColor: 'rgba(54, 162, 235, 0.6)',
                          }
                      ]
                  },
                  options: {
                      responsive: true,
                      scales: {
                          x: {
                              title: {
                                  display: true,
                                  text: 'Date'
                              }
                          },
                          y: {
                              title: {
                                  display: true,
                                  text: 'Vehicle Traffic'
                              },
                              beginAtZero: true
                          }
                      }
                  }
              });

              document.getElementById('export-chart1').addEventListener('click', function() {
                  var imageUrl = parkingChart.toBase64Image();  // Get the chart as base64 image
                  var link = document.createElement('a');      // Create a temporary link element
                  link.href = imageUrl;                        // Set the link's href to the image URL
                  link.download = 'Daily Analytics.png';         // Specify the download file name
                  link.click();                                // Simulate a click to trigger download
              });
          })
          .catch(error => console.error('Error fetching data:', error));
  </script>

  <script>
    // Function to format the month from YYYY-MM to MMM. YYYY
    function formatMonthYear(monthYear) {
        const [year, month] = monthYear.split('-');
        const monthNames = ["Jan.", "Feb.", "Mar.", "Apr.", "May", "Jun.", "Jul.", "Aug.", "Sep.", "Oct.", "Nov.", "Dec."];
        return `${monthNames[parseInt(month) - 1]} ${year}`; // Convert month to number and format
    }

    // Fetch monthly parking data using AJAX
    fetch('mysql/parking_analytics_2.php') // Update with the correct path to your PHP script
        .then(response => response.json())
        .then(data => {
            // Prepare labels and data arrays
            const labels = [];
            const totalParking = [];

            // Process the fetched data
            data.forEach(item => {
                const formattedMonth = formatMonthYear(item.parking_month); // Format month
                labels.push(formattedMonth); // Use formatted month
                totalParking.push(item.total_count); // total_count
            });

            // Create the chart with the fetched data
            const ctx2 = document.getElementById('monthlyParkingChart').getContext('2d');
            const monthlyParkingChart = new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Parking',
                        data: totalParking,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Month'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Total Parking'
                            },
                            beginAtZero: true
                        }
                    }
                }
            });

            document.getElementById('export-chart2').addEventListener('click', function() {
                var imageUrl = monthlyParkingChart.toBase64Image();  // Get the chart as base64 image
                var link = document.createElement('a');      // Create a temporary link element
                link.href = imageUrl;                        // Set the link's href to the image URL
                link.download = 'Monthy Analytics.png';         // Specify the download file name
                link.click();                                // Simulate a click to trigger download
            });
        })
        .catch(error => {
            console.error('Error fetching data:', error);
        });
</script>

  <script>
        document.addEventListener('DOMContentLoaded', function() {
      fetchParkingData();

      function fetchParkingData() {
          const xhr = new XMLHttpRequest();
          xhr.open('GET', 'mysql/get_parking_history.php', true);
          xhr.onload = function() {
              if (this.status === 200) {
                  const data = JSON.parse(this.responseText);
                  let tbody = document.querySelector('#parking-only-recent tbody');
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

</body>

</html>