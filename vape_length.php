<?php
session_start();
include 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get vape info from vape_info table
$sql_info = "SELECT vape_start_date, puffs_per_day, juice_per_day FROM vape_info WHERE user_id = ?";
$stmt_info = $conn->prepare($sql_info);
$stmt_info->bind_param("i", $user_id);
$stmt_info->execute();
$stmt_info->bind_result($start_date, $puffs_per_day, $juice_per_day);
$stmt_info->fetch();
$stmt_info->close();

// If no vape info found
if (!$start_date) {
    $error = "No vape information found. Please update your profile.";
}

// Get earliest 'not vaping' check-in from vape_status table
$sql_quit = "SELECT date FROM vape_status WHERE user_id = ? AND status = 'not_vaping' ORDER BY date ASC LIMIT 1";
$stmt_quit = $conn->prepare($sql_quit);
$stmt_quit->bind_param("i", $user_id);
$stmt_quit->execute();
$stmt_quit->bind_result($quit_date);
$stmt_quit->fetch();
$stmt_quit->close();

// If no quit date found
if (!$quit_date) {
    $quit_date = null; // para maiwasan error sa DateTime
}

// Calculate durations
$now = new DateTime();

if ($start_date && $quit_date) {
    $start = new DateTime($start_date);
    $quit = new DateTime($quit_date);
    $vape_duration = $start->diff($quit)->format('%y years, %m months, %d days');
} else {
    $vape_duration = "N/A";
}

// --- NEW CODE for Vape-Free Streak Calculation ---

$streak_days = 0;
$today = date('Y-m-d');

// Fetch all vape status from today going backwards
$stmt = $conn->prepare("SELECT date, status FROM vape_status WHERE user_id = ? ORDER BY date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $date = $row['date'];
    $status = $row['status'];

    if ($status == 'not_vaping') {
        if ($date == $today || date('Y-m-d', strtotime($date)) == date('Y-m-d', strtotime($today . " -" . $streak_days . " days"))) {
            $streak_days++;
        } else {
            break; // Streak broken if gap in dates
        }
    } else {
        break; // Vaped today or in between
    }
}
$stmt->close();

// Pag walang streak entries
if ($streak_days == 0) {
    $quit_duration = "No vape-free streak yet.";
} else {
    $quit_duration = "$streak_days day" . ($streak_days > 1 ? "s" : "");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vape Length Tracker</title>
    <link href="https://fonts.googleapis.com/css2?family=Amatic+SC:wght@700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Amatic SC', cursive;
            background-color: #135a31; /* light green background */
            display: flex;
        }

        .sidebar {
            width: 220px;
            height: 100vh;
            background: #2d4c00; /* Changed to a new green color */
            color: white;
            position: fixed;
            padding-top: 20px;
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
            font-family: 'Funtastic Slab', sans-serif; /* Apply Funtastic font to sidebar header */
        }
        .sidebar a {
            display: block;
            padding: 15px 20px;
            text-decoration: none;
            color: white;
        }
        .sidebar a:hover {
            background-color: #4CAF50; /* New hover color */
        }

        .main-content {
            margin-left: 220px;
            padding: 40px;
            width: calc(100% - 220px);
        }

        .tracker-card {
            background-color: #e6ffe6;
            padding: 40px;
            border-radius: 18px;
            max-width: 650px;
            margin: auto;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        h1 {
            font-size: 42px;
            margin-bottom: 28px;
            color: #2e8b57;
        }

        .info {
            font-size: 28px;
            margin-bottom: 16px;
        }

        .highlight {
            font-weight: bold;
            color: #1e5631;
        }

        .error {
            color: red;
            font-size: 28px;
            margin-bottom: 20px;
        }

        .edit-link {
            margin-top: 30px;
            font-size: 28px;
        }

        .edit-link a {
            color: #1e90ff;
            text-decoration: none;
        }

        .edit-link a:hover {
            text-decoration: underline;
        }
        h3 {
            text-align: center;
        }

        a {
            text-decoration: none;
            color: #333;
        }

        a:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body>

    <div class="sidebar">
    <div style="text-align: center; margin-bottom: 20px;">
        <img src="vapezerologo.JPG" alt="Vape Zero Logo" style="width: 100px; height: auto;">
    </div>
        <a href="dashboard.php">Dashboard</a>
        <a href="tracker_calendar.php">Tracker Calendar</a>
        <a href="vape_length.php">Vape Length Tracker</a>
        <a href="quit_log.php">Vaping History Log</a>
        <a href="vape_history.php">Vaping History</a>
        <a href="logout.php" style="color: red;">Logout</a>
    </div>

    <div class="main-content">
        <div class="tracker-card">
            <h1>Vape Length Tracker</h1>

            <?php if (!empty($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php else: ?>
                <div class="info">🕐 Started Vaping: <span class="highlight"><?php echo $start_date; ?></span></div>
                <div class="info">🚭 Vape-Free Since: <span class="highlight"><?php echo $quit_date ? $quit_date : 'Still Vaping'; ?></span></div>
                <div class="info">📆 Total Vaping Duration: <span class="highlight"><?php echo $vape_duration; ?></span></div>
                <div class="info">🔥 Current Vape-Free Streak: <span class="highlight"><?php echo $quit_duration; ?></span></div>
                <div class="info">📊 Average Puffs/Day: <span class="highlight"><?php echo $puffs_per_day; ?></span></div>
                <div class="info">💧 Juice Consumption/Day: <span class="highlight"><?php echo $juice_per_day; ?> mL</span></div>
            <?php endif; ?>

        </div>
    </div>

</body>
</html>
