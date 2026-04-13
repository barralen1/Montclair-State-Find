<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include("db.php");

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$username = isset($_SESSION['username']) ? $_SESSION['username'] : "User";
$role = isset($_SESSION['role']) ? $_SESSION['role'] : "student";

$search = "";

if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

$total_items = 0;
$lost_count = 0;
$found_count = 0;

$total_result = $conn->query("SELECT COUNT(*) AS total FROM posts WHERE status='approved'");
if ($total_result) {
    $total_items = $total_result->fetch_assoc()['total'];
}

$lost_result = $conn->query("SELECT COUNT(*) AS total FROM posts WHERE status='approved' AND post_type='Lost'");
if ($lost_result) {
    $lost_count = $lost_result->fetch_assoc()['total'];
}

$found_result = $conn->query("SELECT COUNT(*) AS total FROM posts WHERE status='approved' AND post_type='Found'");
if ($found_result) {
    $found_count = $found_result->fetch_assoc()['total'];
}

if ($search != "") {
    $sql = "SELECT * FROM posts
            WHERE status='approved'
            AND (
                title LIKE ?
                OR category LIKE ?
                OR location LIKE ?
                OR description LIKE ?
            )
            ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM posts WHERE status='approved' ORDER BY created_at DESC");

    if (!$result) {
        die("Query failed: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Browse Items - MSU Lost & Found</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/style.css?v=7">
    <style>
        .browse-page-title {
            font-size: 30px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .browse-page-subtitle {
            color: rgba(255,255,255,0.92);
            font-size: 15px;
        }

        .browse-search-card {
            background: white;
            border-radius: 20px;
            padding: 18px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
            margin-bottom: 22px;
        }

        .browse-search-form {
            display: flex;
            gap: 12px;
        }

        .browse-search-form input {
            flex: 1;
            border: 1px solid #e4e7ec;
            background: #f9fafb;
            border-radius: 14px;
            padding: 14px 16px;
            font-size: 15px;
            outline: none;
        }

        .browse-search-form input:focus {
            border-color: #c8102e;
            background: white;
        }

        .browse-search-form button {
            background: #c8102e;
            color: white;
            border: none;
            border-radius: 14px;
            padding: 14px 20px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
        }

        .browse-grid {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .browse-card {
            background: white;
            border-radius: 22px;
            border: 1px solid #eceff3;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
            padding: 22px;
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 22px;
        }

        .browse-image img {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 16px;
            display: block;
        }

        .browse-image .placeholder {
            width: 140px;
            height: 140px;
            border-radius: 16px;
            background: #f3f4f6;
            color: #9ca3af;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            text-align: center;
        }

        .browse-title {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 8px;
            color: #111827;
        }

        .browse-description {
            color: #6b7280;
            font-size: 15px;
            line-height: 1.55;
            margin-bottom: 12px;
        }

        .browse-meta {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.7;
            margin-bottom: 12px;
        }

        .browse-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .browse-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
        }

        .browse-claim-btn {
            background: #c8102e;
            color: white;
        }

        .browse-claim-btn:hover {
            background: #a90e27;
        }

        .browse-empty {
            background: white;
            border: 1px solid #eceff3;
            border-radius: 20px;
            padding: 28px;
            text-align: center;
            color: #6b7280;
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.04);
        }

        @media (max-width: 900px) {
            .browse-card {
                grid-template-columns: 1fr;
            }

            .browse-image img,
            .browse-image .placeholder {
                width: 100%;
                height: 240px;
            }

            .browse-search-form {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<div class="app">

    <aside class="sidebar">
        <div class="sidebar-top">
            <h2>MSU</h2>
            <div class="sidebar-subtitle">
                <?php echo ($role === 'admin') ? 'Admin Portal' : 'Student Portal'; ?>
            </div>

            <nav>
                <?php if ($role === 'admin') { ?>
                    <a href="admin_index.php">🏠 Dashboard</a>
                    <a href="admin.php">📝 Review Posts</a>
                    <a href="review_claims.php">📄 Review Claims</a>
                <?php } else { ?>
                    <a href="index.php">🏠 Home</a>
                    <a href="view_posts.php" class="active">🔎 Browse</a>
                    <a href="create_posts.php">➕ Report</a>
                <?php } ?>

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
                    <small>Approved campus listings</small>
                    <h1 class="browse-page-title">Browse Items</h1>
                    <p class="browse-page-subtitle">Search approved lost and found items across Montclair State University.</p>
                </div>
                <div class="hero-badge">Live Listings</div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo $total_items; ?></h3>
                    <span>Total Approved Items</span>
                </div>
                <div class="stat-card">
                    <h3><?php echo $lost_count; ?></h3>
                    <span>Lost Items</span>
                </div>
                <div class="stat-card">
                    <h3><?php echo $found_count; ?></h3>
                    <span>Found Items</span>
                </div>
            </div>
        </section>

        <section class="browse-search-card">
            <form method="GET" action="view_posts.php" class="browse-search-form">
                <input
                    type="text"
                    name="search"
                    value="<?php echo htmlspecialchars($search); ?>"
                    placeholder="Search by title, category, location, or description..."
                >
                <button type="submit">Search</button>
            </form>
        </section>

        <section class="section">
            <div class="section-header">
                <h2>Approved Posts</h2>
                <?php if ($search != "") { ?>
                    <a href="view_posts.php">Clear Search</a>
                <?php } ?>
            </div>

            <div class="browse-grid">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='browse-card'>";

                        echo "<div class='browse-image'>";
                        if (!empty($row['image'])) {
                            echo "<img src='upload/" . htmlspecialchars($row['image']) . "' alt='Item image'>";
                        } else {
                            echo "<div class='placeholder'>No Image</div>";
                        }
                        echo "</div>";

                        echo "<div class='browse-content'>";

                        echo "<div class='badge-row'>";
                        if ($row['post_type'] === 'Lost') {
                            echo "<span class='badge lost'>Lost</span>";
                        } else {
                            echo "<span class='badge found'>Found</span>";
                        }

                        if (!empty($row['category'])) {
                            echo "<span class='badge category'>" . htmlspecialchars($row['category']) . "</span>";
                        }

                        echo "<span class='badge category'>Approved</span>";
                        echo "</div>";

                        echo "<div class='browse-title'>" . htmlspecialchars($row['title']) . "</div>";
                        echo "<div class='browse-description'>" . htmlspecialchars($row['description']) . "</div>";

                        echo "<div class='browse-meta'>";
                        echo "<strong>Location:</strong> " . htmlspecialchars($row['location']) . "<br>";
                        echo "<strong>Status:</strong> " . htmlspecialchars($row['status']) . "<br>";
                        echo "<strong>Created:</strong> " . htmlspecialchars($row['created_at']);
                        echo "</div>";

                        echo "<div class='browse-actions'>";
                        if ($role !== 'admin' && $row['status'] === 'approved') {
                            echo "<a class='browse-claim-btn' href='claim_item.php?post_id=" . $row['id'] . "'>Claim Item</a>";
                        }
                        echo "</div>";

                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<div class='browse-empty'>";
                    echo "<h3 style='margin-bottom:8px;'>No approved posts found</h3>";
                    echo "<p>Try a different search term or check back after more items are approved.</p>";
                    echo "</div>";
                }
                ?>
            </div>
        </section>

    </main>
</div>

</body>
</html>