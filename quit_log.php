<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reason = $_POST['reason_to_reduce'];
    $mood = $_POST['mood'];
    $challenge = $_POST['challenge_level'];
    $reflection = $_POST['reflection'];

    $stmt = $conn->prepare("INSERT INTO quit_tracker_log (user_id, reason_to_quit, mood, challenge_level, reflection) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $reason, $mood, $challenge, $reflection);

    if ($stmt->execute()) {
        $msg = "✅ Reflection saved! Keep moving forward 💪";
    } else {
        $msg = "❌ Error saving reflection.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vape Reduction Log - Vape Zero</title>
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
            padding: 40px;
            width: calc(100% - 220px);
        }

        .log-form {
            max-width: 650px;
            background: #2d4c00;
            padding: 30px;
            border-radius: 20px;
            margin: auto;
            box-shadow: 0 10px 18px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            font-size: 36px;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            font-size: 24px;
        }

        textarea, input, select {
            width: 100%;
            padding: 10px;
            margin: 5px 0 20px 0;
            border-radius: 10px;
            border: 1px solid #ccc;
            font-family: Arial, sans-serif;
            font-size: 18px;
        }

        button {
            padding: 12px 20px;
            background-color: #2e8b57;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 22px;
        }

        button:hover {
            background-color: #256d45;
        }

        .msg {
            text-align: center;
            color: green;
            font-weight: bold;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .link-view {
            text-align: center;
            margin-top: 20px;
        }

        .link-view a {
            text-decoration: none;
            color: #2e8b57;
            font-weight: bold;
            font-size: 22px;
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

    <!-- Sidebar -->
    <div class="sidebar">
    <div style="text-align: center; margin-bottom: 20px;">
        <img src="vapezerologo.JPG" alt="Vape Zero Logo" style="width: 100px; height: auto;">
    </div>
        <a href="dashboard.php">Dashboard</a>
        <a href="tracker_calendar.php">Tracker Calendar</a>
        <a href="vape_length.php">Vape Length Tracker</a>

        <div style="padding-left: 10px;">
            <strong style="color: #ccc;">🧘 Reduction Log</strong>
            <a href="quit_log.php" style="padding-left: 20px;">➕ New Entry</a>
            <a href="quit_log_view.php" style="padding-left: 20px;">📖 View Reflections</a>
        </div>

        <a href="vape_history.php">Vaping History</a>
        <a href="logout.php" style="color: red;">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="log-form">
            <h2>🧘 Vape Reduction Log</h2>
            <?php if ($msg): ?>
                <p class="msg"><?php echo $msg; ?></p>
            <?php endif; ?>

            <form method="POST">
                <label>Why are you trying to reduce vaping?</label>
                <textarea name="reason_to_reduce" required rows="3" placeholder="Your main motivation to reduce..."></textarea>

                <label>Your Mood Today:</label>
                <input type="text" name="mood" placeholder="e.g. hopeful, anxious" required>

                <label>Challenge Level:</label>
                <select name="challenge_level">
                    <option value="Easy">Easy</option>
                    <option value="Moderate">Moderate</option>
                    <option value="Hard">Hard</option>
                </select>

                <label>Reflection:</label>
                <textarea name="reflection" rows="5" placeholder="What's helping you stay strong today?"></textarea>

                <button type="submit">Save Entry</button>
            </form>

            <div class="link-view">
                <a href="quit_log_view.php">📖 View My Past Reflections</a>
            </div>
        </div>
    </div>

</body>
</html>
