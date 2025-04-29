<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT entry_date, reason_to_quit, mood, challenge_level, reflection FROM quit_tracker_log WHERE user_id = ? ORDER BY entry_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($date, $reason, $mood, $challenge, $reflection);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Reduction Reflections - Vape Zero</title>
    <link href="https://fonts.googleapis.com/css2?family=Amatic+SC&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Amatic SC', cursive; /* Apply Funtastic font */
            background-color: #135a31; /* Background color for the page */
            color: white; /* Main text color */
            min-height: 100vh; /* Ensure the body takes at least the full height */
            display: flex;
            flex-direction: column;
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
            margin-left: 400px;
            padding: 40px;
            width: 750px;
        }

        .entry {
    background: #ffffff; /* White background for entry */
    border-left: 5px solid #2e8b57;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    color: #333; /* dark text for good readability */
    font-family: Arial, sans-serif; /* readable font for entries */
    line-height: 1.6; /* better line spacing */
}

.entry strong {
    display: block;
    margin-bottom: 5px;
    color: #2d4c00; /* dark green for strong tags */
}

.entry em {
    color: #4CAF50;
}

        h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            text-decoration: none;
            color: #2e8b57;
            font-weight: bold;
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
            <strong style="color: #ccc;">🧘 Reduction Tracker</strong>
            <a href="quit_log.php" style="padding-left: 20px;">➕ New Entry</a>
            <a href="quit_log_view.php" style="padding-left: 20px;">📖 View Reflections</a>
        </div>

        <a href="vape_history.php">Vaping History</a>
        <a href="logout.php" style="color: red;">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2>🗓️ Your Reflections</h2>

        <?php while ($stmt->fetch()): ?>
            <div class="entry">
                <strong>Date:</strong> <?php echo $date; ?><br>
                <strong>Mood:</strong> <?php echo htmlspecialchars($mood); ?><br>
                <strong>Challenge Level:</strong> <?php echo htmlspecialchars($challenge); ?><br>
                <strong>Reason to Reduce:</strong> <em><?php echo htmlspecialchars($reason); ?></em><br><br>
                <strong>Reflection:</strong>
                <p><?php echo nl2br(htmlspecialchars($reflection)); ?></p>
            </div>
        <?php endwhile; ?>

        <div class="back-link">
            <a href="quit_log.php">← Back to New Entry</a>
        </div>
    </div>

</body>
</html>

<?php $stmt->close(); ?>
