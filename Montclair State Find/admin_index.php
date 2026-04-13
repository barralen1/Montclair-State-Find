<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    die("Access Denied");
}

include("db.php");

// Count pending posts
$post_count = $conn->query("SELECT COUNT(*) as total FROM posts WHERE status='pending'")
                    ->fetch_assoc()['total'];

// Count pending claims
$claim_count = $conn->query("SELECT COUNT(*) as total FROM claims WHERE status='pending'")
                     ->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - MSU Lost & Found</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f6f7fb;
            display: flex;
        }

        /* Sidebar */
        .sidebar {
            width: 240px;
            background: linear-gradient(180deg, #c8102e, #9b0f24);
            color: white;
            height: 100vh;
            padding: 20px;
        }

        .sidebar h2 {
            margin-bottom: 20px;
        }

        .sidebar a {
            display: block;
            padding: 12px;
            border-radius: 8px;
            color: white;
            text-decoration: none;
            margin-bottom: 8px;
        }

        .sidebar a:hover {
            background: rgba(255,255,255,0.2);
        }

        /* Main */
        .main {
            flex: 1;
            padding: 30px;
        }

        .header {
            background: linear-gradient(135deg, #c8102e, #ef4444);
            color: white;
            padding: 25px;
            border-radius: 16px;
            margin-bottom: 25px;
        }

        .header h1 {
            margin: 0;
        }

        .cards {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            flex: 1;
            background: white;
            padding: 20px;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .card h2 {
            margin: 0;
            font-size: 28px;
        }

        .card p {
            color: gray;
        }

        .actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .action-box {
            background: white;
            padding: 20px;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .action-box a {
            display: inline-block;
            margin-top: 10px;
            color: #c8102e;
            font-weight: bold;
            text-decoration: none;
        }
    </style>
</head>

<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>MSU Admin</h2>

    <a href="admin_index.php">🏠 Dashboard</a>
    <a href="admin.php">📦 Review Posts</a>
    <a href="review_claims.php">📄 Review Claims</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<!-- Main -->
<div class="main">

    <div class="header">
        <h1>Welcome, Admin</h1>
        <p>Manage posts and verify ownership claims</p>
    </div>

    <!-- Stats -->
    <div class="cards">
        <div class="card">
            <h2><?php echo $post_count; ?></h2>
            <p>Pending Posts</p>
        </div>

        <div class="card">
            <h2><?php echo $claim_count; ?></h2>
            <p>Pending Claims</p>
        </div>
    </div>

    <!-- Actions -->
    <div class="actions">

        <div class="action-box">
            <h3>Review Posts</h3>
            <p>Approve or reject submitted lost/found items.</p>
            <a href="admin.php">Go to Posts →</a>
        </div>

        <div class="action-box">
            <h3>Review Claims</h3>
            <p>Verify ownership and approve item claims.</p>
            <a href="review_claims.php">Go to Claims →</a>
        </div>

    </div>

</div>

</body>
</html>