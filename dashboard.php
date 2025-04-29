<?php
session_start();
include 'includes/db.php'; // Adjust path if needed

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


$check = $conn->prepare("SELECT id FROM vape_info WHERE user_id = ?");
$check->bind_param("i", $user_id);
$check->execute();
$check->store_result();

// If no record found, force redirect to vape_questions.php
if ($check->num_rows === 0) {
    $check->close(); // Close first before redirect
    header("Location: vape_questions.php");
    exit();
}
$check->close(); // Important: Close after checking

// Fetch user name
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_name);
$stmt->fetch();
$stmt->close();

// Fetch vape info
$stmt = $conn->prepare("SELECT vape_start_date, puffs_per_day, juice_per_day, years_vaping, first_not_vaping_date FROM vape_info WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($vape_start_date, $puffs_per_day, $juice_per_day, $years_vaping, $first_not_vaping_date);
$stmt->fetch();
$stmt->close();

// Fetch today's status from vape_status table
$today = date('Y-m-d');
$stmt = $conn->prepare("SELECT status FROM vape_status WHERE user_id = ? AND date = ?");
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$stmt->bind_result($daily_status);
$stmt->fetch();
$stmt->close();

if (!$daily_status) {
    $daily_status = "No Check-in"; // default pag walang entry
}

// Calculate vape-free streak
$streak_days = 0;
$last_date = null;

$stmt = $conn->prepare("SELECT date, status FROM vape_status WHERE user_id = ? ORDER BY date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    if ($row['status'] == 'not_vaping') {
        if (is_null($last_date)) {
            $last_date = $row['date'];
            $streak_days = 1;
        } else {
            $expected_date = date('Y-m-d', strtotime($last_date . ' -1 day'));
            if ($row['date'] == $expected_date) {
                $streak_days++;
                $last_date = $row['date'];
            } else {
                break; // streak broken
            }
        }
    } else {
        break; // user vaped this day, end streak
    }
}
$stmt->close();

// Random motivational quotes
$quotes = [
    "Stay strong, one day at a time!",
    "You are stronger than your cravings.",
    "Every day vape-free is a win!",
    "Small steps lead to big changes.",
    "Your future self will thank you.",
    "Keep pushing forward!",
    "Believe in yourself!",
];
$random_quote = $quotes[array_rand($quotes)];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Vape Zero</title>
    <!-- Include Google Fonts link -->
    <link href="https://fonts.googleapis.com/css2?family=Amatic+SC&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Amatic SC', cursive; /* Apply Funtastic font */
            background-color: #135a31; /* Background color */
            color: white; /* Main text color */
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

        .main {
            margin-left: 240px;
            padding: 30px;
        }
        .card {
            background: #034a21; /* Card background color */
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
        .card-title {
            font-size: 22px;
            margin-bottom: 10px;
        }
        .stats {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .stat {
            flex: 1;
            min-width: 150px;
            background: #184923; /* Stat background color */
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            margin-top: 15px;
        }
        .calendar-day {
            background: #fff; /* White background for the days */
            color: #333; /* Dark text for readability */
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            font-size: 12px;
            font-weight: bold; /* Bold for better readability */
        }
        .calendar-day-name {
            font-weight: bold;
            text-align: center;
            font-size: 12px;
            padding: 10px;
            background-color: #1B5E20; /* Dark background for the weekdays */
            color: white; /* White text color */
        }

        .not-vaping {
            background-color: #c8f7c5; /* Light green for not vaping */
            color: #2e7d32; /* Dark green text */
        }
        .vaping {
            background-color: #f7c5c5; /* Light red for vaping */
            color: #d32f2f; /* Dark red text */
        }
        .empty {
            background-color: #f0f0f0; /* Light gray for empty cells */
            color: #ccc; /* Lighter text for empty cells */
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
    <!-- Logo -->
    <div style="text-align: center; margin-bottom: 20px;">
        <img src="vapezerologo.JPG" alt="Vape Zero Logo" style="width: 100px; height: auto;">
    </div>
    <a href="dashboard.php">Dashboard</a>
    <a href="tracker_calendar.php">Tracker Calendar</a>
    <a href="vape_length.php">Vape Length Tracker</a>
    <a href="quit_log.php">Vaping History Log</a>
    <a href="vape_history.php">Vaping History</a>
    <a href="logout.php" style="color:red;">Logout</a>
</div>


<div class="main">

    <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>! 🎉</h1>

    <div class="card">
        <div class="card-title">🔥 Vape-Free Streak</div>
        <h2><?php echo $streak_days; ?> Days</h2>
        <p>Today's Status: 
    <?php 
    $daily_status_lower = strtolower($daily_status);
    if ($daily_status_lower == "not_vaping"): ?>
        <span style="color: green; background-color: #ffffff; padding: 5px 10px; border-radius: 5px; font-weight: bold;">✅ Not Vaping</span>
    <?php elseif ($daily_status_lower == "vaping"): ?>
        <span style="color: red; background-color: #ffffff; padding: 5px 10px; border-radius: 5px; font-weight: bold;">❌ Vaping</span>
    <?php else: ?>
        <span style="color: gray; background-color: #ffffff; padding: 5px 10px; border-radius: 5px; font-weight: bold;">➖ No Check-in</span>
    <?php endif; ?>
</p>

    </div>

    <div class="card">
        <div class="card-title">📋 Vape Profile Summary</div>
        <div class="stats">
            <div class="stat">
                Start Vaping Date<br><strong><?php echo $vape_start_date ?: "N/A"; ?></strong>
            </div>
            <div class="stat">
                Puffs Per Day<br><strong><?php echo $puffs_per_day ?: "N/A"; ?></strong>
            </div>
            <div class="stat">
                Juice Per Day<br><strong><?php echo $juice_per_day ? $juice_per_day." mL" : "N/A"; ?></strong>
            </div>
            <div class="stat">
                Years Vaping<br><strong><?php echo $years_vaping ?: "N/A"; ?></strong>
            </div>
            <div class="stat">
                First Quit Date<br><strong><?php echo $first_not_vaping_date ?: "N/A"; ?></strong>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-title">💬 Daily Motivation</div>
        <p><?php echo htmlspecialchars($random_quote); ?></p>
    </div>

    <div class="card">
        <div class="card-title">📅 Small Calendar Preview</div>
        <div class="calendar">
    <?php
    // Calendar Data
    $current_month = date('m');
    $current_year = date('Y');

    $stmt = $conn->prepare("SELECT DAY(date) as day, status FROM vape_status WHERE user_id = ? AND MONTH(date) = ? AND YEAR(date) = ?");
    $stmt->bind_param("iii", $user_id, $current_month, $current_year);
    $stmt->execute();
    $result = $stmt->get_result();

    $days_status = [];
    while ($row = $result->fetch_assoc()) {
        $days_status[$row['day']] = $row['status'];
    }
    $stmt->close();

    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);
    $first_day_of_month = date('N', strtotime("$current_year-$current_month-01")); // Monday = 1

    // Display weekdays header (Mon-Sun)
    $weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    foreach ($weekdays as $day_name) {
        echo "<div class='calendar-day-name'>$day_name</div>";
    }

    // Empty slots before first day
    for ($i = 1; $i < $first_day_of_month; $i++) {
        echo "<div class='calendar-day empty'></div>";
    }

    // Days of the month
    for ($day = 1; $day <= $days_in_month; $day++) {
        $class = "";
        if (isset($days_status[$day])) {
            $class = ($days_status[$day] == 'not_vaping') ? "not-vaping" : "vaping";
        }
        echo "<div class='calendar-day $class'>$day</div>";
    }
    ?>
</div>

    </div>

</div>

</body>
</html>
