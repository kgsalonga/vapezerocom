<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

include 'includes/db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($id, $hashed_password);
    $stmt->fetch();

    if ($id && password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $id;
        header("Location: vape_questions.php");
        exit();
    } else {
        $message = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Vape Zero</title>
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
        .login-container {
            background: #2d4c00; /* Updated container color */
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0px 4px 20px rgba(0,0,0,0.3);
            text-align: center;
            width: 90%;
            max-width: 400px;
            color: white;
        }
        .login-container h2 {
            margin-bottom: 20px;
            font-size: 28px;
        }
        .login-container input[type="email"],
        .login-container input[type="password"] {
            width: 90%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 25px;
            background: #E8F5E9;
            color: #2E7D32;
            font-size: 16px;
        }
        .login-container input::placeholder {
            color: #81C784;
        }
        .login-container button {
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
        .login-container button:hover {
            background-color: #43A047;
        }
        .login-container p {
            margin-top: 15px;
            font-size: 14px;
        }
        .login-container a {
            color: #A5D6A7;
            text-decoration: none;
        }
        .login-container a:hover {
            text-decoration: underline;
        }

        /* Toast Message */
        #toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #f44336;
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

        /* Logo Styling */
        .logo {
            width: 160px; /* Adjust the width as needed */
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <img src="vapezerologo.JPG" alt="Vape Zero Logo" class="logo"> <!-- Add the logo here -->
    <h2>Log-in to your account</h2>
    <form method="POST">
        <input type="email" name="email" placeholder="Enter your email" required><br>
        <input type="password" name="password" placeholder="Enter your password" required><br>
        <button type="submit">Login</button>
    </form>
    <p>Don't have an account yet? <a href="signup.php">Sign up here</a></p>
</div>

<?php if (!empty($message)): ?>
    <div id="toast"><?php echo $message; ?></div>
<?php endif; ?>

</body>
</html>
