<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT date, status FROM vape_status WHERE user_id = ? ORDER BY date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($date, $status);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vaping History - Vape Zero</title>
    <link href="https://fonts.googleapis.com/css2?family=Amatic+SC&display=swap" rel="stylesheet">
    <style>
        /* Reset margin and padding */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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
            margin-left: 220px;
            padding: 40px;
            width: calc(100% - 220px);
            background-color: #135a31; /* Same green background for the main content */
            flex-grow: 1; /* Allow the main content to grow and fill available space */
        }

        h2 {
            text-align: center;
            font-size: 30px;
            margin-bottom: 30px;
            color: #fff; /* White color for main header */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: center;
            color: #fff; /* White text for table data */
        }

        th {
            background-color: #4CAF50; /* Light green for table header */
            color: white; /* White text for table header */
        }

        .vaped {
            color: red;
            font-weight: bold;
        }

        .not-vaped {
            color: green;
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
        <a href="quit_log.php">Vaping History Log</a>
        <a href="vape_history.php">Vaping History</a>
        <a href="logout.php" style="color: red;">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2>📅 Vaping History Log</h2>

        <table>
            <tr>
                <th>Date</th>
                <th>Status</th>
            </tr>
            <?php while ($stmt->fetch()): ?>
                <tr>
                    <td><?php echo date("F j, Y", strtotime($date)); ?></td>
                    <td class="<?php echo $status == 'not_vaping' ? 'not-vaped' : 'vaped'; ?>">
                        <?php echo $status == 'not_vaping' ? '✅ Not Vaping' : '❌ Vaping'; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

</body>
</html>

<?php $stmt->close(); ?>
