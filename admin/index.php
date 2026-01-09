<?php
session_start();
date_default_timezone_set("Asia/Dhaka");

// --- CONFIGURATION ---
$ADMIN_PASS = "admin123"; // Change your password here
$conn = mysqli_connect("localhost", "root", "", "hospital_management");

// --- LOGOUT LOGIC ---
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// --- LOGIN LOGIC ---
if (isset($_POST['login'])) {
    if ($_POST['password'] === $ADMIN_PASS) {
        $_SESSION['loggedin'] = true;
    } else {
        $error = "Invalid Password!";
    }
}

// --- ACCESS CONTROL ---
if (!isset($_SESSION['loggedin'])): ?>
<!DOCTYPE html>
<html>
<head>
    <title>Login | Admin</title>
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .login-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 300px; text-align: center; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #2c3e50; color: white; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>Admin Login</h2>
        <?php if(isset($error)) echo "<p style='color:red; font-size:13px;'>$error</p>"; ?>
        <form method="POST">
            <input type="password" name="password" placeholder="Enter Password" required autofocus>
            <button type="submit" name="login">Access Dashboard</button>
        </form>
    </div>
</body>
</html>
<?php exit; endif; 

// --- DATABASE FETCH ---
$sql = "SELECT * FROM appointment_management ORDER BY appointment_time ASC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --sidebar-w: 200px; --primary: #2c3e50; --bg: #f0f2f5; }
        body { font-family: 'Inter', 'Segoe UI', sans-serif; margin: 0; display: flex; background: var(--bg); font-size: 13px; }
        
        /* Compact Sidebar */
        .sidebar { width: var(--sidebar-w); height: 100vh; background: var(--primary); color: white; position: fixed; }
        .sidebar h2 { padding: 15px; font-size: 16px; text-align: center; background: rgba(0,0,0,0.2); margin: 0; }
        .nav-item { padding: 10px 20px; color: #adb5bd; cursor: pointer; display: block; text-decoration: none; }
        .nav-item:hover, .nav-item.active { background: #34495e; color: white; }
        
        /* Main Content */
        .main-content { margin-left: var(--sidebar-w); width: 100%; }
        header { background: #fff; padding: 10px 25px; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center; }
        
        .container { padding: 20px; }
        .card { background: #fff; border-radius: 4px; border: 1px solid #dee2e6; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .card-header { padding: 10px 15px; border-bottom: 1px solid #eee; font-weight: bold; font-size: 14px; }

        /* Compact Table */
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 15px; border-bottom: 1px solid #f0f0f0; text-align: left; }
        th { background: #fafafa; color: #666; font-size: 12px; text-transform: uppercase; }
        tr:hover { background: #fcfcfc; }

        .btn-pdf { 
            background: #dc3545; color: white; border: none; padding: 4px 10px; 
            border-radius: 3px; cursor: pointer; font-size: 11px;
        }

        /* PDF SLIP STYLING */
        #pdf-template { display: none; }
        @media print {
            body * { visibility: hidden; }
            #pdf-template, #pdf-template * { visibility: visible; }
            #pdf-template { display: block !important; position: absolute; left: 0; top: 0; width: 100%; padding: 20px; }
            .slip-card { border: 1px solid #000; padding: 20px; width: 500px; margin: auto; }
            .slip-header { text-align: center; border-bottom: 1px solid #000; margin-bottom: 15px; }
            .slip-row { display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px dotted #ccc; }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>NGH ADMIN</h2>
    <a href="#" class="nav-item active"><i class="fas fa-calendar-alt"></i> Appointments</a>
    <a href="index.php" target="_blank" class="nav-item"><i class="fas fa-eye"></i> View Site</a>
    <a href="?logout=1" class="nav-item"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main-content">
    <header>
        <span style="color: #666;">Dashboard / <strong style="color: #333;">Appointments</strong></span>
        <div style="font-size: 12px;">Welcome, Admin | <?php echo date("d M, Y"); ?></div>
    </header>

    <div class="container">
        <div class="card">
            <div class="card-header">Upcoming Appointments</div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient Name</th>
                        <th>Doctor</th>
                        <th>Appointment Time</th>
                        <th style="text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>#<?= $row['id']; ?></td>
                        <td><strong><?= htmlspecialchars($row['patient_full_name']); ?></strong></td>
                        <td><?= htmlspecialchars($row['doctor_name']); ?></td>
                        <td><?= date("d M, h:i A", strtotime($row['appointment_time'])); ?></td>
                        <td style="text-align:right;">
                            <button class="btn-pdf" onclick="generatePDF('<?= $row['id'] ?>', '<?= addslashes($row['patient_full_name']) ?>', '<?= addslashes($row['doctor_name']) ?>', '<?= date('d M Y, h:i A', strtotime($row['appointment_time'])) ?>')">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="pdf-template">
    <div class="slip-card">
        <div class="slip-header">
            <h2 style="margin:0;">Northern General Hospital</h2>
            <p style="font-size:12px;">Appointment Confirmation Slip</p>
        </div>
        <div class="slip-row"><strong>ID:</strong> <span id="pdf-id"></span></div>
        <div class="slip-row"><strong>Patient:</strong> <span id="pdf-patient"></span></div>
        <div class="slip-row"><strong>Doctor:</strong> <span id="pdf-doctor"></span></div>
        <div class="slip-row"><strong>Schedule:</strong> <span id="pdf-date"></span></div>
        <div style="margin-top:20px; font-size:11px; text-align:center; color:#555;">
            Printed on: <?php echo date("d M Y, h:i A"); ?> BD Time
        </div>
    </div>
</div>

<script>
    function generatePDF(id, patient, doctor, date) {
        document.getElementById('pdf-id').innerText = "#" + id;
        document.getElementById('pdf-patient').innerText = patient;
        document.getElementById('pdf-doctor').innerText = doctor;
        document.getElementById('pdf-date').innerText = date;
        window.print();
    }
</script>

</body>
</html>