<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if already answered
$check = $conn->prepare("SELECT id FROM vape_info WHERE user_id = ?");
$check->bind_param("i", $user_id);
$check->execute();
$check->store_result();

// Redirect if form is already filled out
if ($check->num_rows > 0) {
    header("Location: dashboard.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vape_start_date = $_POST['vape_start_date'];
    $puffs_per_day = $_POST['puffs_per_day'];
    $juice_per_day = $_POST['juice_per_day'];
    $years_vaping = $_POST['years_vaping'];

    $stmt = $conn->prepare("INSERT INTO vape_info (user_id, vape_start_date, puffs_per_day, juice_per_day, years_vaping) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isidd", $user_id, $vape_start_date, $puffs_per_day, $juice_per_day, $years_vaping);

    if ($stmt->execute()) {
        // Optional: Update user's vape_started to true
        $update = $conn->prepare("UPDATE users SET vape_started = 1 WHERE id = ?");
        $update->bind_param("i", $user_id);
        $update->execute();

        header("Location: dashboard.php");
        exit();
    } else {
        $message = "Something went wrong!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vape Info - Vape Zero</title>
    <link href="https://fonts.googleapis.com/css2?family=Amatic+SC&display=swap" rel="stylesheet">
    <style>
        /* Reset styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Amatic SC', cursive;
            background: linear-gradient(135deg, #33691E, #558B2F);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
        }

        .container {
            background-color: #2d4c00; /* Light green background for the form */
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            color: #2e8b57;
        }

        label {
            font-size: 18px;
            color: #333;
            display: block;
            margin-bottom: 8px;
        }

        input[type="date"],
        input[type="number"],
        button {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #45a049;
        }

        p {
            text-align: center;
            color: red;
        }

        /* Additional styling for error messages */
        .error-message {
            color: #f44336;
            text-align: center;
            margin-top: 20px;
        }

        .footer {
            position: absolute;
            bottom: 20px;
            width: 100%;
            text-align: center;
            color: #fff;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Tell Us About Your Vaping Habits</h2>

        <!-- Form to collect vaping information -->
        <form method="POST">
            <label for="vape_start_date">When did you start vaping?</label>
            <input type="date" id="vape_start_date" name="vape_start_date" required><br>

            <label for="puffs_per_day">Average puffs per day?</label>
            <input type="number" id="puffs_per_day" name="puffs_per_day" required><br>

            <label for="juice_per_day">How much juice do you consume daily? (ml)</label>
            <input type="number" id="juice_per_day" name="juice_per_day" step="0.1" required><br>

            <label for="years_vaping">How many years have you been vaping?</label>
            <input type="number" id="years_vaping" name="years_vaping" step="0.1" required><br>

            <button type="submit">Save & Go to Dashboard</button>
        </form>

        <!-- Display error message if there's an issue -->
        <?php if (!empty($message)): ?>
            <p class="error-message"><?php echo $message; ?></p>
        <?php endif; ?>
    </div>

    <div class="footer">
        <p>&copy; 2025 Vape Zero | All Rights Reserved</p>
    </div>

</body>
</html>
