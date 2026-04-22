<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include("db.php");

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: admin_index.php");
                exit();
            } else {
                header("Location: index.php");
                exit();
            }

        } else {
            $error_message = "Incorrect password.";
        }

    } else {
        $error_message = "Username not found.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Montclair State Find</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial, Helvetica, sans-serif;
}

body{
    min-height:100vh;
    background:linear-gradient(135deg,#b4002f,#e31837);
    display:flex;
    justify-content:center;
    align-items:center;
    padding:25px;
}

.wrapper{
    width:1100px;
    max-width:95%;
    min-height:650px;
    background:white;
    border-radius:24px;
    overflow:hidden;
    display:grid;
    grid-template-columns:1fr 1fr;
    box-shadow:0 30px 60px rgba(0,0,0,0.18);
}

.left{
    background:linear-gradient(145deg,#c10034,#e31b3f);
    color:white;
    padding:70px 55px;
    display:flex;
    flex-direction:column;
    justify-content:center;
}

.logo-box{
    width:82px;
    height:82px;
    border-radius:22px;
    background:rgba(255,255,255,0.16);
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:42px;
    box-shadow:0 10px 25px rgba(0,0,0,0.15);
    margin-bottom:28px;
}

.left h1{
    font-size:54px;
    line-height:1.05;
    margin-bottom:22px;
    font-weight:800;
}

.left p{
    font-size:18px;
    line-height:1.7;
    opacity:0.95;
    max-width:500px;
}

.right{
    padding:70px 60px;
    display:flex;
    flex-direction:column;
    justify-content:center;
}

.right h2{
    font-size:54px;
    color:#222;
    margin-bottom:12px;
}

.sub{
    color:#777;
    margin-bottom:35px;
    font-size:17px;
}

label{
    display:block;
    margin-bottom:8px;
    font-weight:700;
    color:#333;
}

input{
    width:100%;
    padding:16px 18px;
    border:1px solid #ddd;
    border-radius:14px;
    margin-bottom:20px;
    font-size:16px;
}

input:focus{
    outline:none;
    border-color:#d40036;
}

button{
    width:100%;
    padding:16px;
    background:linear-gradient(90deg,#c10034,#e31b3f);
    color:white;
    border:none;
    border-radius:14px;
    font-size:18px;
    font-weight:700;
    cursor:pointer;
    margin-top:10px;
}

button:hover{
    opacity:0.95;
}

.error{
    background:#ffe5e8;
    color:#b00020;
    padding:14px;
    border-radius:12px;
    margin-bottom:18px;
    font-size:15px;
}

.bottom{
    margin-top:25px;
    color:#777;
    font-size:15px;
}

.bottom a{
    color:#c10034;
    text-decoration:none;
    font-weight:700;
}

@media(max-width:900px){
    .wrapper{
        grid-template-columns:1fr;
    }

    .left{
        padding:45px 30px;
    }

    .left h1{
        font-size:40px;
    }

    .right{
        padding:45px 30px;
    }

    .right h2{
        font-size:42px;
    }
}
</style>
</head>

<body>

<div class="wrapper">

    <div class="left">
        <div class="logo-box">🔍</div>

        <h1>Montclair State Find</h1>

        <p>
            Sign in to access the lost-and-found portal, browse approved items,
            report missing property, and submit ownership claims securely.
        </p>
    </div>

    <div class="right">

        <h2>Sign In</h2>
        <div class="sub">Use your username and password to continue.</div>

        <?php if($error_message != ""){ ?>
            <div class="error"><?php echo $error_message; ?></div>
        <?php } ?>

        <form method="POST">

            <label>Username</label>
            <input type="text" name="username" placeholder="Enter your username" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Enter your password" required>

            <button type="submit">Sign In</button>

        </form>

        <div class="bottom">
            Don’t have an account?
            <a href="register.php">Register</a>
        </div>

    </div>

</div>

</body>
</html>
