<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include("db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "student") {
    header("Location: login.php");
    exit();
}

$username = isset($_SESSION['username']) ? $_SESSION['username'] : "Student";

$total_posts = 0;
$claimed_posts = 0;
$new_today = 0;

$total_result = $conn->query("SELECT COUNT(*) AS total FROM posts WHERE status IN ('approved', 'claimed')");
if ($total_result) {
    $total_posts = $total_result->fetch_assoc()['total'];
}

$claimed_result = $conn->query("SELECT COUNT(*) AS total FROM posts WHERE status='claimed'");
if ($claimed_result) {
    $claimed_posts = $claimed_result->fetch_assoc()['total'];
}

$today_result = $conn->query("SELECT COUNT(*) AS total FROM posts WHERE DATE(created_at)=CURDATE()");
if ($today_result) {
    $new_today = $today_result->fetch_assoc()['total'];
}

$recent_posts = $conn->query("SELECT * FROM posts WHERE status='approved' ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard - MSU Lost & Found</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/style.css?v=4">
</head>
<body>

<div class="app">

    <aside class="sidebar">
        <div class="sidebar-top">
            <h2>MSU</h2>
            <div class="sidebar-subtitle">Student Portal</div>

            <nav>
                <a href="index.php" class="active">🏠 Home</a>
                <a href="view_posts.php">🔎 Browse</a>
                <a href="create_posts.php">➕ Report</a>
                <a href="logout.php">🚪 Logout</a>
            </nav>
        </div>

        <div class="sidebar-footer">
            Montclair State University<br>
            Welcome, <?php echo htmlspecialchars($username); ?>
        </div>
    </aside>

    <main class="main">

        <section class="hero">
            <div class="hero-top">
                <div class="hero-text">
                    <small>Welcome back, Red Hawk</small>
                    <h1>Student Dashboard</h1>
                    <p>Browse, report, and claim lost items across campus.</p>
                </div>
                <div class="hero-badge">Student</div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo $total_posts; ?></h3>
                    <span>Approved Items</span>
                </div>
                <div class="stat-card">
                    <h3><?php echo $claimed_posts; ?></h3>
                    <span>Claimed / Returned</span>
                </div>
                <div class="stat-card">
                    <h3><?php echo $new_today; ?></h3>
                    <span>New Today</span>
                </div>
            </div>
        </section>

        <section class="search-card">
            <form action="view_posts.php" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Search by item name or location...">
                <button type="submit">Search</button>
            </form>
        </section>

        <section class="section">
            <div class="section-header">
                <h2>Quick Actions</h2>
            </div>

            <div class="quick-grid">
                <a href="create_posts.php" class="quick-card red">
                    <div class="quick-icon">🔍</div>
                    <h3>Lost Something?</h3>
                    <p>Report a lost item and let the campus help you recover it.</p>
                    <div class="action-link">Report Lost Item →</div>
                </a>

                <a href="create_posts.php" class="quick-card green">
                    <div class="quick-icon">➕</div>
                    <h3>Found Something?</h3>
                    <p>Post a found item and help return it to its owner.</p>
                    <div class="action-link">Report Found Item →</div>
                </a>
            </div>
        </section>

        <section class="section">
            <div class="section-header">
                <h2>Recent Activity</h2>
                <a href="view_posts.php">View All</a>
            </div>

            <div class="activity-list">
                <?php
                if ($recent_posts && $recent_posts->num_rows > 0) {
                    while ($row = $recent_posts->fetch_assoc()) {
                        echo "<div class='activity-card'>";
                        echo "<div class='activity-image'>";
                        if (!empty($row['image'])) {
                            echo "<img src='upload/" . htmlspecialchars($row['image']) . "' alt='Item image'>";
                        } else {
                            echo "<div class='placeholder'>No Image</div>";
                        }
                        echo "</div>";

                        echo "<div class='activity-content'>";
                        echo "<div class='badge-row'>";
                        if ($row['post_type'] === 'Lost') {
                            echo "<span class='badge lost'>Lost</span>";
                        } else {
                            echo "<span class='badge found'>Found</span>";
                        }
                        if (!empty($row['category'])) {
                            echo "<span class='badge category'>" . htmlspecialchars($row['category']) . "</span>";
                        }
                        echo "</div>";

                        echo "<h4>" . htmlspecialchars($row['title']) . "</h4>";
                        echo "<p>" . htmlspecialchars($row['description']) . "</p>";
                        echo "<div class='activity-meta'>📍 " . htmlspecialchars($row['location']) . " | 🗓 " . htmlspecialchars($row['created_at']) . "</div>";

                        echo "<a class='claim-link' href='claim_item.php?post_id=" . $row['id'] . "'>Claim Item</a>";
                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<div class='activity-card'><div class='activity-content'><h4>No approved posts yet</h4><p>Approved items will show here.</p></div></div>";
                }
                ?>
            </div>
        </section>

    </main>
</div>

</body>
</html>