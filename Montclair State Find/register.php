<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include("db.php");

$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($full_name === "" || $username === "" || $email === "" || $password === "" || $confirm_password === "") {
        $message = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (!preg_match('/@montclair\.edu$/i', $email)) {
        $message = "Only @montclair.edu email addresses can register.";
    } elseif (!preg_match('/^[a-zA-Z0-9._-]{3,30}$/', $username)) {
        $message = "Username must be 3-30 characters and can only contain letters, numbers, dots, underscores, or hyphens.";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
    } else {
        $check_sql = "SELECT id FROM users WHERE email = ? OR username = ?";
        $check_stmt = $conn->prepare($check_sql);

        if (!$check_stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $check_stmt->bind_param("ss", $email, $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $message = "That email or username is already in use.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $insert_sql = "INSERT INTO users (full_name, username, email, password, role)
                           VALUES (?, ?, ?, ?, 'student')";
            $insert_stmt = $conn->prepare($insert_sql);

            if (!$insert_stmt) {
                die("Prepare failed: " . $conn->error);
            }

            $insert_stmt->bind_param("ssss", $full_name, $username, $email, $hashed_password);

            if ($insert_stmt->execute()) {
                $success = true;
                $message = "Registration successful! You can now sign in.";
            } else {
                $message = "Registration failed: " . $insert_stmt->error;
            }

            $insert_stmt->close();
        }

        $check_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Montclair State University Lost &amp; Found</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #b30f29 0%, #c8102e 55%, #e2334f 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .register-shell {
            width: 100%;
            max-width: 1120px;
            min-height: 700px;
            background: white;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 20px 45px rgba(0,0,0,0.18);
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .register-left {
            background: linear-gradient(180deg, #c8102e 0%, #9f1026 100%);
            color: white;
            padding: 56px 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo-box {
            width: 82px;
            height: 82px;
            border-radius: 22px;
            background: rgba(255,255,255,0.14);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 38px;
            margin-bottom: 24px;
        }

        .register-left h1 {
            font-size: 40px;
            line-height: 1.15;
            margin-bottom: 14px;
        }

        .register-left p {
            font-size: 16px;
            line-height: 1.7;
            color: rgba(255,255,255,0.92);
            margin-bottom: 26px;
            max-width: 520px;
        }

        .feature-box {
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.16);
            border-radius: 16px;
            padding: 14px 16px;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 12px;
        }

        .register-right {
            padding: 56px 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }

        .register-right h2 {
            font-size: 36px;
            margin-bottom: 8px;
            color: #111827;
        }

        .subtext {
            font-size: 15px;
            color: #6b7280;
            margin-bottom: 26px;
        }

        .message-box-error {
            padding: 12px 14px;
            border-radius: 14px;
            background: #fff4f6;
            color: #b1122b;
            border: 1px solid #ffd1da;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .message-box-success {
            padding: 12px 14px;
            border-radius: 14px;
            background: #ecfdf3;
            color: #166534;
            border: 1px solid #bbf7d0;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .form-group {
            margin-bottom: 18px;
            display: flex;
            flex-direction: column;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            border-radius: 14px;
            padding: 14px 16px;
            font-size: 15px;
            outline: none;
        }

        .form-group input:focus {
            border-color: #c8102e;
            background: white;
        }

        .register-btn {
            width: 100%;
            background: #c8102e;
            color: white;
            border: none;
            border-radius: 14px;
            padding: 14px 22px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 8px;
        }

        .register-btn:hover {
            background: #a90e27;
        }

        .helper-text {
            margin-top: 18px;
            font-size: 14px;
            color: #6b7280;
            line-height: 1.7;
        }

        .helper-text a {
            color: #c8102e;
            font-weight: bold;
            text-decoration: none;
        }

        @media (max-width: 900px) {
            .register-shell {
                grid-template-columns: 1fr;
            }

            .register-left,
            .register-right {
                padding: 34px 24px;
            }

            .register-left h1 {
                font-size: 32px;
            }

            .register-right h2 {
                font-size: 30px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="register-shell">

    <div class="register-left">
        <div class="logo-box">🎓</div>

        <h1>Create Your Account</h1>

        <p>
            Register with your Montclair State email to report lost items,
            browse approved posts, and submit ownership claims.
        </p>

        <div class="feature-box">Only @montclair.edu email addresses can register.</div>
        <div class="feature-box">You will sign in later using your username and password.</div>
        <div class="feature-box">All submitted reports and claims are reviewed through the portal.</div>
    </div>

    <div class="register-right">
        <h2>Register</h2>
        <p class="subtext">Create your student account to access the portal.</p>

        <?php if ($message != ""): ?>
            <?php if ($success) { ?>
                <div class="message-box-success"><?php echo htmlspecialchars($message); ?></div>
            <?php } else { ?>
                <div class="message-box-error"><?php echo htmlspecialchars($message); ?></div>
            <?php } ?>
        <?php endif; ?>

        <form method="POST">
            <div class="form-grid">

                <div class="form-group full">
                    <label>Full Name</label>
                    <input type="text" name="full_name" required>
                </div>

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="Choose a username" required>
                </div>

                <div class="form-group">
                    <label>Montclair Email</label>
                    <input type="email" name="email" placeholder="yourname@montclair.edu" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required>
                </div>

            </div>

            <button type="submit" class="register-btn">Create Account</button>
        </form>

        <div class="helper-text">
            Already have an account? <a href="login.php">Sign In</a>
        </div>
    </div>

</div>

</body>
</html>
