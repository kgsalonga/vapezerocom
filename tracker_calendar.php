<?php
date_default_timezone_set('Asia/Manila');
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');

if ($month < 1) {
    $month = 12;
    $year--;
} elseif ($month > 12) {
    $month = 1;
    $year++;
}

$today = date('Y-m-d');
$current_month = str_pad($month, 2, '0', STR_PAD_LEFT);

// Handle check-in
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['status'], $_POST['date'])) {
        $status = $_POST['status'];
        $date = $_POST['date'];

        $stmt = $conn->prepare("SELECT id FROM vape_status WHERE user_id = ? AND date = ?");
        $stmt->bind_param("is", $user_id, $date);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->close();
            $update = $conn->prepare("UPDATE vape_status SET status = ? WHERE user_id = ? AND date = ?");
            $update->bind_param("sis", $status, $user_id, $date);
            $update->execute();
            $update->close();
        } else {
            $stmt->close();
            $insert = $conn->prepare("INSERT INTO vape_status (user_id, date, status) VALUES (?, ?, ?)");
            $insert->bind_param("iss", $user_id, $date, $status);
            $insert->execute();
            $insert->close();
        }
    }
}

// Fetch calendar data
$calendar_data = [];
$sql = "SELECT date, status FROM vape_status WHERE user_id = ? AND MONTH(date) = ? AND YEAR(date) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $month, $year);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    if (isset($row['date']) && isset($row['status'])) {
        $calendar_data[$row['date']] = $row['status'];
    }
}

// Streak logic
$streak = 0;
$current = date('Y-m-d');
while (isset($calendar_data[$current]) && $calendar_data[$current] === 'not_vaping') {
    $streak++;
    $current = date('Y-m-d', strtotime('-1 day', strtotime($current)));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tracker Calendar - Vape Zero</title>
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

        .main-content {
            margin-left: 220px;
            padding: 20px;
            width: calc(100% - 220px);
            position: relative;
        }

        .calendar {
            max-width: 700px;
            margin: 20px auto;
        }

        .calendar-header, .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            text-align: center;
        }

        .calendar-header div {
            font-weight: bold;
            padding: 10px 0;
        }

        .day {
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .vaping {
            background-color: #ff4d4d;
            color: white;
            
        }

        .not_vaping {
            background-color: #4CAF50;
            color: white;
        }

        .today {
            border: 2px solid gold;
        }
        .calendar-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 700px;
    margin: 0 auto 10px;
    padding: 0 10px;
}

.calendar-top .buttons form {
    display: flex;
    gap: 10px;
}

.calendar-top .buttons form button {
    padding: 8px 15px;
    font-size: 14px;
    border: none;
    cursor: pointer;
    border-radius: 5px;
}

.calendar-top .buttons form button:first-child {
    background-color: #4CAF50;
    color: white;
}

.calendar-top .buttons form button:last-child {
    background-color: #ff4d4d;
    color: white;
}

.calendar-top .streak {
    font-size: 18px;
    color: lightgreen;
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
    <a href="logout.php" style="color: red;">Logout</a>
</div>

<div class="main-content">
    <h1>📅 Tracker Calendar</h1>

    <div class="calendar-top">
    <div class="buttons">
        <form method="POST">
            <input type="hidden" name="date" value="<?php echo $today; ?>">
            <button name="status" value="not_vaping">✅ Not Vaping</button>
            <button name="status" value="vaping">🚬 Vaping</button>
        </form>
    </div>
    <div class="streak">
        Current Streak: <span><?php echo $streak; ?></span> day(s)
    </div>
</div>


    <div style="text-align:center; margin-top: 10px;">
        <a href="?month=<?php echo $month - 1; ?>&year=<?php echo $year; ?>">Previous</a>
        <strong style="margin: 0 20px;"><?php echo date("F Y", strtotime("$year-$current_month-01")); ?></strong>
        <a href="?month=<?php echo $month + 1; ?>&year=<?php echo $year; ?>">Next</a>
    </div>

    <div class="calendar">
        <div class="calendar-header">
            <div>Mon</div>
            <div>Tue</div>
            <div>Wed</div>
            <div>Thu</div>
            <div>Fri</div>
            <div>Sat</div>
            <div>Sun</div>
        </div>
        <div class="calendar-grid">
            <?php
            $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $start_day = date('N', strtotime("$year-$current_month-01"));        
            $empty_slots = $start_day - 1;

            for ($i = 0; $i < $empty_slots; $i++) {
                echo "<div class='day'></div>";
            }

            for ($day = 1; $day <= $days_in_month; $day++) {
                $date = "$year-$current_month-" . str_pad($day, 2, '0', STR_PAD_LEFT);
                $status_class = '';
                if (isset($calendar_data[$date])) {
                    $status_class = $calendar_data[$date] === 'vaping' ? 'vaping' : 'not_vaping';
                }

                $today_class = $date === $today ? 'today' : '';
                echo "<div class='day $status_class $today_class'>$day</div>";
            }
            ?>
        </div>
    </div>
</div>

</body>
</html>
