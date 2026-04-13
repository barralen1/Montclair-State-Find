<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    die("Access Denied");
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

include("db.php");

$username = isset($_SESSION['username']) ? $_SESSION['username'] : "Admin";

$pending_posts_count = 0;
$approved_posts_count = 0;
$claimed_posts_count = 0;

$pending_result = $conn->query("SELECT COUNT(*) AS total FROM posts WHERE status='pending'");
if ($pending_result) {
    $pending_posts_count = $pending_result->fetch_assoc()['total'];
}

$approved_result = $conn->query("SELECT COUNT(*) AS total FROM posts WHERE status='approved'");
if ($approved_result) {
    $approved_posts_count = $approved_result->fetch_assoc()['total'];
}

$claimed_result = $conn->query("SELECT COUNT(*) AS total FROM posts WHERE status='claimed'");
if ($claimed_result) {
    $claimed_posts_count = $claimed_result->fetch_assoc()['total'];
}

$result = $conn->query("SELECT * FROM posts WHERE status='pending' ORDER BY created_at DESC");

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Review Posts - MSU Lost & Found</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/style.css?v=6">
    <style>
        .admin-page-title {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .admin-page-subtitle {
            color: rgba(255,255,255,0.92);
            font-size: 15px;
        }

        .posts-grid {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .post-review-card {
            background: white;
            border-radius: 22px;
            border: 1px solid #eceff3;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
            padding: 22px;
            display: grid;
            grid-template-columns: 130px 1fr;
            gap: 22px;
        }

        .post-image img {
            width: 130px;
            height: 130px;
            object-fit: cover;
            border-radius: 16px;
            display: block;
        }

        .post-image .placeholder {
            width: 130px;
            height: 130px;
            border-radius: 16px;
            background: #f3f4f6;
            color: #9ca3af;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            text-align: center;
        }

        .post-title {
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 8px;
            color: #111827;
        }

        .post-meta {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 10px;
            line-height: 1.7;
        }

        .post-description-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 14px;
            font-size: 14px;
            color: #374151;
            line-height: 1.6;
            margin-top: 8px;
        }

        .review-actions {
            display: flex;
            gap: 12px;
            margin-top: 18px;
            flex-wrap: wrap;
        }

        .review-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 18px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
        }

        .btn-approve {
            background: #16a34a;
            color: white;
        }

        .btn-delete {
            background: #dc2626;
            color: white;
        }

        .empty-state {
            background: white;
            border: 1px solid #eceff3;
            border-radius: 20px;
            padding: 28px;
            text-align: center;
            color: #6b7280;
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.04);
        }

        @media (max-width: 900px) {
            .post-review-card {
                grid-template-columns: 1fr;
            }

            .post-image img,
            .post-image .placeholder {
                width: 100%;
                height: 220px;
            }
        }
    </style>
</head>
<body>

<div class="app">

    <aside class="sidebar">
        <div class="sidebar-top">
            <h2>MSU</h2>
            <div class="sidebar-subtitle">Admin Portal</div>

            <nav>
                <a href="admin_index.php">🏠 Dashboard</a>
                <a href="admin.php" class="active">📝 Review Posts</a>
                <a href="review_claims.php">📄 Review Claims</a>
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
                    <small>Admin post moderation</small>
                    <h1 class="admin-page-title">Review Posts</h1>
                    <p class="admin-page-subtitle">Approve or reject pending lost and found reports before they become visible to students.</p>
                </div>
                <div class="hero-badge">Admin Review</div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo $pending_posts_count; ?></h3>
                    <span>Pending Posts</span>
                </div>
                <div class="stat-card">
                    <h3><?php echo $approved_posts_count; ?></h3>
                    <span>Approved Posts</span>
                </div>
                <div class="stat-card">
                    <h3><?php echo $claimed_posts_count; ?></h3>
                    <span>Claimed / Returned</span>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-header">
                <h2>Pending Post Requests</h2>
                <a href="admin_index.php">Back to Admin Dashboard</a>
            </div>

            <div class="posts-grid">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='post-review-card'>";

                        echo "<div class='post-image'>";
                        if (!empty($row['image'])) {
                            echo "<img src='upload/" . htmlspecialchars($row['image']) . "' alt='Post image'>";
                        } else {
                            echo "<div class='placeholder'>No Image</div>";
                        }
                        echo "</div>";

                        echo "<div class='post-content'>";

                        echo "<div class='badge-row'>";
                        if ($row['post_type'] === 'Lost') {
                            echo "<span class='badge lost'>Lost</span>";
                        } else {
                            echo "<span class='badge found'>Found</span>";
                        }

                        if (!empty($row['category'])) {
                            echo "<span class='badge category'>" . htmlspecialchars($row['category']) . "</span>";
                        }

                        echo "<span class='badge category'>Pending</span>";
                        echo "</div>";

                        echo "<div class='post-title'>" . htmlspecialchars($row['title']) . "</div>";

                        echo "<div class='post-meta'>";
                        echo "<strong>Location:</strong> " . htmlspecialchars($row['location']) . "<br>";
                        echo "<strong>Created:</strong> " . htmlspecialchars($row['created_at']) . "<br>";
                        echo "<strong>Status:</strong> " . htmlspecialchars($row['status']);
                        echo "</div>";

                        echo "<div class='post-description-box'>" . nl2br(htmlspecialchars($row['description'])) . "</div>";

                        echo "<div class='review-actions'>";
                        echo "<a class='btn-approve' href='approve.php?id=" . $row['id'] . "'>Approve Post</a>";
                        echo "<a class='btn-delete' href='delete.php?id=" . $row['id'] . "'>Reject / Delete</a>";
                        echo "</div>";

                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<div class='empty-state'>";
                    echo "<h3 style='margin-bottom:8px;'>No pending posts</h3>";
                    echo "<p>All submitted posts have been reviewed for now.</p>";
                    echo "</div>";
                }
                ?>
            </div>
        </section>

    </main>
</div>

</body>
</html>