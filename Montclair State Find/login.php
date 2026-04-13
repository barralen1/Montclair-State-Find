<?php
session_start();

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username === "admin" && $password === "admin123") {
        $_SESSION['role'] = "admin";
        $_SESSION['username'] = "Admin";
        header("Location: admin_index.php");
        exit();
    } elseif ($username === "student" && $password === "student123") {
        $_SESSION['role'] = "student";
        $_SESSION['username'] = "Linus Mundo";
        header("Location: index.php");
        exit();
    } else {
        $error_message = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - MSU Lost & Found</title>
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

        .login-shell {
            width: 100%;
            max-width: 1100px;
            min-height: 650px;
            background: white;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 20px 45px rgba(0,0,0,0.18);
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .login-left {
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

        .login-left h1 {
            font-size: 42px;
            line-height: 1.1;
            margin-bottom: 14px;
        }

        .login-left p {
            font-size: 16px;
            line-height: 1.7;
            color: rgba(255,255,255,0.92);
            margin-bottom: 26px;
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

        .login-right {
            padding: 56px 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }

        .login-right h2 {
            font-size: 36px;
            margin-bottom: 8px;
            color: #111827;
        }

        .subtext {
            font-size: 15px;
            color: #6b7280;
            margin-bottom: 26px;
        }

        .error-box {
            padding: 12px 14px;
            border-radius: 14px;
            background: #fff4f6;
            color: #b1122b;
            border: 1px solid #ffd1da;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 18px;
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

        .login-btn {
            width: 100%;
            background: #c8102e;
            color: white;
            border: none;
            border-radius: 14px;
            padding: 14px 22px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 6px;
        }

        .login-btn:hover {
            background: #a90e27;
        }

        .demo-box {
            margin-top: 18px;
            font-size: 14px;
            color: #6b7280;
            line-height: 1.8;
        }

        .demo-box strong {
            color: #111827;
        }

        .back-link {
            margin-top: 18px;
            font-size: 14px;
            color: #6b7280;
        }

        .back-link a {
            color: #c8102e;
            font-weight: bold;
            text-decoration: none;
        }

        @media (max-width: 900px) {
            .login-shell {
                grid-template-columns: 1fr;
            }

            .login-left,
            .login-right {
                padding: 34px 24px;
            }

            .login-left h1 {
                font-size: 34px;
            }

            .login-right h2 {
                font-size: 30px;
            }
        }
    </style>
</head>
<body>

<div class="login-shell">

    <div class="login-left">
        <div class="logo-box">🔎</div>
        <h1>MSU Lost &amp; Found</h1>
        <p>
            Sign in to report lost items, browse approved listings,
            submit ownership claims, and access the Montclair State lost-and-found workflow.
        </p>

        <div class="feature-box">Student access for reporting and claiming items.</div>
        <div class="feature-box">Admin access for approving posts and reviewing claims.</div>
        <div class="feature-box">Built as a web app for future NEST integration.</div>
    </div>

    <div class="login-right">
        <h2>Sign In</h2>
        <p class="subtext">Use your account to continue into the MSU Lost &amp; Found portal.</p>

        <?php if ($error_message != ""): ?>
            <div class="error-box"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Enter your username" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="login-btn">Sign In</button>
        </form>

        <div class="demo-box">
            <strong>Demo accounts:</strong><br>
            admin / admin123<br>
            student / student123
        </div>

        <div class="back-link">
            Back to <a href="landing.php">Landing Page</a>
        </div>
    </div>

</div>

</body>
</html>