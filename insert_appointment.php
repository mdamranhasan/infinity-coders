<?php
$conn = mysqli_connect("localhost", "root", "", "hospital_management");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$success = false;
$new_id = 0;

if (isset($_POST['submit'])) {
    $patient_full_name = $_POST['patient_full_name'];
    $doctor_name       = $_POST['doctor_name'];
    $appointment_time  = $_POST['appointment_time'];

    $stmt = $conn->prepare(
        "INSERT INTO appointment_management 
        (patient_full_name, doctor_name, appointment_time) 
        VALUES (?, ?, ?)"
    );

    $stmt->bind_param("sss", $patient_full_name, $doctor_name, $appointment_time);

    if ($stmt->execute()) {
        $success = true;
        $new_id = $stmt->insert_id; // Capture the ID for the PDF
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Appointment Status</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    body { margin: 0; font-family: 'Segoe UI', sans-serif; }
    .success-wrapper {
        min-height: 100vh;
        background: linear-gradient(135deg, #4facfe, #00f2fe);
        display: flex; align-items: center; justify-content: center;
    }
    .success-box {
        background: #fff; padding: 40px 35px; border-radius: 16px;
        width: 400px; text-align: center; box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    }
    .checkmark { font-size: 60px; color: #28a745; margin-bottom: 10px; }
    
    /* Buttons */
    .btn-group { display: flex; flex-direction: column; gap: 10px; margin-top: 20px; }
    .home-btn {
        text-decoration: none; background: #4facfe; color: #fff;
        padding: 12px; border-radius: 30px; font-weight: 600; transition: 0.3s;
    }
    .download-btn {
        text-decoration: none; background: #27ae60; color: #fff;
        padding: 12px; border-radius: 30px; font-weight: 600; cursor: pointer; border: none;
    }
    .download-btn:hover { background: #219150; }

    /* PDF SLIP STYLING (Same as Admin) */
    #pdf-template { display: none; }

    @media print {
        body * { visibility: hidden; }
        #pdf-template, #pdf-template * { visibility: visible; }
        #pdf-template { 
            display: block !important; 
            position: absolute; left: 0; top: 0; width: 100%; padding: 40px; 
        }
        .slip-card { border: 2px solid #333; padding: 30px; border-radius: 10px; max-width: 600px; margin: 0 auto; }
        .slip-header { text-align: center; border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 20px; }
        .slip-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px dashed #ccc; }
    }
</style>
</head>
<body>

<?php if ($success) { ?>
    <div class="success-wrapper">
        <div class="success-box">
            <div class="checkmark">✔</div>
            <h2>Appointment Booked!</h2>
            <p>Your appointment has been successfully scheduled.</p>
            
            <div class="btn-group">
                <button onclick="window.print()" class="download-btn">
                    <i class="fas fa-file-pdf"></i> Download Appointment Slip
                </button>
                
                <a href="index.php" class="home-btn">🏠 Go to Home</a>
            </div>
        </div>
    </div>

    <div id="pdf-template">
        <div class="slip-card">
            <div class="slip-header">
                <h1 style="margin:0; color: #2c3e50;">Northern General Hospital</h1>
                <p>Kawla, Dakhinkhan, Airport Dhaka | Tel: +8801632692739</p>
                <h3 style="background: #eee; padding: 10px; margin-top: 15px;">OFFICIAL APPOINTMENT SLIP</h3>
            </div>
            
            <div class="slip-row"><strong>Slip ID:</strong> <span>#<?php echo $new_id; ?></span></div>
            <div class="slip-row"><strong>Patient Name:</strong> <span><?php echo htmlspecialchars($patient_full_name); ?></span></div>
            <div class="slip-row"><strong>Consulting Doctor:</strong> <span><?php echo htmlspecialchars($doctor_name); ?></span></div>
            <div class="slip-row"><strong>Date & Time:</strong> <span><?php echo date("d M Y, h:i A", strtotime($appointment_time)); ?></span></div>
            <div class="slip-row"><strong>Status:</strong> <span style="color:green; font-weight:bold;">CONFIRMED</span></div>

            <div style="text-align: center; margin-top: 40px; font-size: 12px; color: #777;">
                <p>Please present this slip at the reception upon arrival.</p>
                <?php date_default_timezone_set("Asia/Dhaka"); ?>
<p>Generated on: <?php echo date("d M Y, h:i A"); ?></p>
            </div>
        </div>
    </div>

<?php } else { ?>
    <div class="success-wrapper">
        <div class="success-box">
            <div class="checkmark" style="color:red;">✖</div>
            <h2>Something Went Wrong</h2>
            <p>We couldn't process your request. Please try again.</p>
            <a href="index.php" class="home-btn">⬅ Back to Home</a>
        </div>
    </div>
<?php } ?>

</body>
</html>