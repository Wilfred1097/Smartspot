<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>SmartSpot - Parking Availability Status</title>
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
</head>

<body>
<style>
  .card {
    background: url(https://i.ibb.co/M9LB3Kq/Glassmorphism-Background.png);
    background-size:cover;
  }
  .card-footer-fixed {
    position: sticky;
    bottom: 0;
    z-index: 10;
    background-color: white;
    box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
  }

  .status-badge {
    font-size: 0.9rem;
    font-weight: bold;
    border-radius: 15px;
    padding: 0.3rem 0.7rem;
  }

  .rounded-number {
    width: 90px;
    height: 90px;
    font-size: 3.5rem;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.4);
    background-color: white;
  }

  .card-body-available {
    height: 200px;
    background: rgba(217, 253, 211, 0.3); /* Semi-transparent green */
    backdrop-filter: blur(10px); /* Creates the glass effect */
    border-radius: 0 0 15px 15px;
    border: 1px solid rgba(217, 253, 211, 0.5); /* Subtle border for glass edge */
    box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.1); /* Inner shadow for depth */
  }

  .card-body-occupied {
    height: 200px;
    background: rgba(255, 214, 214, 0.3); /* Semi-transparent red */
    backdrop-filter: blur(10px); /* Creates the glass effect */
    border-radius: 0 0 15px 15px;
    border: 1px solid rgba(255, 214, 214, 0.5); /* Subtle border for glass edge */
    box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.1); /* Inner shadow for depth */
  }
</style>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<div class="card text-center shadow-lg" style="height: 100vh; display: flex; flex-direction: column;">
  <div class="card-header bg-primary text-white py-4">
    <div class="d-flex justify-content-center align-items-center mb-3">
      <img src="assets/img/camila-logo.jpg" alt="Hotel Image" class="rounded-circle" style="width: 50px; height: 50px;">
      <h3 class="ms-3 mb-0">Hotel Camila Pay Parking</h3>
    </div>
    <h1 class="card-title mb-3">Availability Status</h1>
    <button type="button" class="btn btn-outline-light shadow">
      Available Slots <span class="badge bg-white text-primary" id="vacant-space">0</span>
    </button>
  </div>

  <div class="card-body bg-light flex-grow-1">
    <div class="container">
      <div class="row g-4" id="parking-slots">
        <!-- Dynamic Parking Slots will be appended here -->
      </div>
    </div>
  </div>

  <div class="card-footer sticky-bottom bg-white border-top py-3 card-footer-fixed">
    <span>Hotel Camila Pay Parking </span>
    <a href="https://www.google.com/maps/place/Hotel+Camila+Pagadian/@7.8241487,123.4387934,3a,75y,258.14h,92.81t/data=!3m7!1e1!3m5!1s5xNAJDl5oezqeKUZsylKsA!2e0!6shttps:%2F%2Fstreetviewpixels-pa.googleapis.com%2Fv1%2Fthumbnail%3Fcb_client%3Dmaps_sv.tactile%26w%3D900%26h%3D600%26pitch%3D-2.8128304814619014%26panoid%3D5xNAJDl5oezqeKUZsylKsA%26yaw%3D258.1427426170666!7i16384!8i8192!4m20!1m10!3m9!1s0x325422a75043b68b:0x4404a0362399e5c0!2sHotel+Camila+Pagadian!5m2!4m1!1i2!8m2!3d7.8244792!4d123.438936!16s%2Fg%2F1tfd9_4y!3m8!1s0x325422a75043b68b:0x4404a0362399e5c0!5m2!4m1!1i2!8m2!3d7.8244792!4d123.438936!16s%2Fg%2F1tfd9_4y?entry=ttu&g_ep=EgoyMDI0MTExOS4yIKXMDSoASAFQAw%3D%3D" target="_blank">Location</a>
  </div>
</div>
<!-- ======================= -->

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

  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
  $(document).ready(function () {
    function fetchParkingStatus() {
      $.ajax({
        url: 'mysql/get_parking_status.php',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
          const parkingContainer = $('#parking-slots');
          parkingContainer.empty(); // Clear existing slots
          
          let availableCount = 0;

          response.forEach((slot) => {
            const slotNumber = slot.slots_number;
            const status = slot.status;
            
            // Determine slot card content based on status
            const slotCard = `
              <div class="col-6 col-md-4 col-lg-3">
                <div class="card shadow-sm h-100" style="border-radius: 15px;">
                  <div class="card-header ${
                    status == 0
                      ? 'bg-success-subtle text-success'
                      : 'bg-danger-subtle text-danger'
                  } d-flex flex-column flex-md-row justify-content-center justify-content-md-between align-items-center">
                    <div class="d-flex align-items-center text-center text-md-start">
                      <i class="fas fa-parking me-2"></i> Parking Slot
                    </div>
                    <span class="badge ${
                      status == 0 ? 'bg-success text-white' : 'bg-danger text-white'
                    } mt-2 mt-md-0 text-center">${status == 0 ? 'Available' : 'Occupied'}</span>
                  </div>
                  <div class="card-body d-flex flex-column align-items-center justify-content-center ${
                    status == 0 ? 'card-body-available' : 'card-body-occupied'
                  }">
                    <div class="rounded-number ${
                      status == 0 ? 'text-success' : 'text-danger'
                    }">
                      ${slotNumber}
                    </div>
                  </div>
                </div>
              </div>
            `;
            // Append slot card to the container
            parkingContainer.append(slotCard);

            // Count available slots
            if (status == 0) availableCount++;
          });

          // Update the available slots count
          $('#vacant-space').text(availableCount);
        },
        error: function (xhr, status, error) {
          console.error('Error fetching parking status:', error);
        },
      });
    }

    // Initial fetch
    fetchParkingStatus();

    // Refresh data every 10 seconds
    setInterval(fetchParkingStatus, 5000);
  });
</script>
</body>

</html>