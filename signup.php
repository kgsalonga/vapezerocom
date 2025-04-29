<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

include 'includes/db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $age = $_POST['age'];
    $birthday = $_POST['birthday'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($password !== $confirm) {
        $message = "Passwords don't match!";
    } else {
        // Check if email exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "Email is already registered!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, age, birthday, email, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sisss", $name, $age, $birthday, $email, $hashed_password);
            $stmt->execute();

            $message = "Account created successfully!";
            header("refresh:2;url=login.php");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - Vape Zero</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #33691E, #558B2F);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .signup-container {
            background: #2d4c00;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0px 4px 20px rgba(0,0,0,0.3);
            text-align: center;
            width: 90%;
            max-width: 400px;
            color: white;
        }
        .signup-container h2 {
            margin-bottom: 20px;
            font-size: 28px;
        }
        .signup-container input[type="text"],
        .signup-container input[type="number"],
        .signup-container input[type="date"],
        .signup-container input[type="email"],
        .signup-container input[type="password"] {
            width: 90%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 25px;
            background: #E8F5E9;
            color: #2E7D32;
            font-size: 16px;
        }
        .signup-container input::placeholder {
            color: #81C784;
        }
        .signup-container button {
            width: 90%;
            padding: 12px;
            border: none;
            border-radius: 25px;
            background-color: #66BB6A;
            color: white;
            font-size: 18px;
            margin-top: 20px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .signup-container button:hover {
            background-color: #43A047;
        }
        .signup-container p {
            margin-top: 15px;
            font-size: 14px;
        }
        .signup-container a {
            color: #A5D6A7;
            text-decoration: none;
        }
        .signup-container a:hover {
            text-decoration: underline;
        }

        /* Toast Message */
        #toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: <?php echo ($message == "Account created successfully!") ? '#4CAF50' : '#f44336'; ?>;
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0,0,0,0.2);
            z-index: 9999;
            font-size: 16px;
            animation: fadeout 3s forwards;
        }
        @keyframes fadeout {
            0% { opacity: 1; }
            70% { opacity: 1; }
            100% { opacity: 0; display: none; }
        }
    </style>
</head>
<body>

<div class="signup-container">
    <h2>Create an Account</h2>
    <form method="POST">
        <input type="text" name="name" placeholder="Full Name" required><br>
        <input type="number" name="age" placeholder="Age" required><br>
        <input type="date" name="birthday" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <input type="password" name="confirm" placeholder="Re-enter Password" required><br>
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
</div>

<?php if (!empty($message)): ?>
    <div id="toast"><?php echo $message; ?></div>
<?php endif; ?>

</body>
</html>
