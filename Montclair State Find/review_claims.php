<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    die("Access Denied");
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

include("db.php");

$username = isset($_SESSION['username']) ? $_SESSION['username'] : "Admin";

$pending_claims_count = 0;
$approved_claims_count = 0;
$rejected_claims_count = 0;

$pending_result = $conn->query("SELECT COUNT(*) AS total FROM claims WHERE status='pending'");
if ($pending_result) {
    $pending_claims_count = $pending_result->fetch_assoc()['total'];
}

$approved_result = $conn->query("SELECT COUNT(*) AS total FROM claims WHERE status='approved'");
if ($approved_result) {
    $approved_claims_count = $approved_result->fetch_assoc()['total'];
}

$rejected_result = $conn->query("SELECT COUNT(*) AS total FROM claims WHERE status='rejected'");
if ($rejected_result) {
    $rejected_claims_count = $rejected_result->fetch_assoc()['total'];
}

$result = $conn->query("
    SELECT claims.*, posts.title AS post_title, posts.category AS post_category, posts.location AS post_location, posts.image AS post_image
    FROM claims
    JOIN posts ON claims.post_id = posts.id
    WHERE claims.status = 'pending'
    ORDER BY claims.created_at DESC
");

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Review Claims - MSU Lost & Found</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/style.css?v=5">
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

        .claims-grid {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .claim-card {
            background: white;
            border-radius: 22px;
            border: 1px solid #eceff3;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
            padding: 22px;
            display: grid;
            grid-template-columns: 130px 1fr;
            gap: 22px;
        }

        .claim-image img {
            width: 130px;
            height: 130px;
            object-fit: cover;
            border-radius: 16px;
            display: block;
        }

        .claim-image .placeholder {
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

        .claim-title {
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 8px;
            color: #111827;
        }

        .claim-meta {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .claim-section-label {
            font-size: 13px;
            font-weight: 800;
            color: #c8102e;
            margin-top: 10px;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .claim-proof-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 14px;
            font-size: 14px;
            color: #374151;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .proof-link-btn {
            display: inline-block;
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            margin-top: 6px;
        }

        .claim-actions {
            display: flex;
            gap: 12px;
            margin-top: 16px;
            flex-wrap: wrap;
        }

        .claim-actions a {
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

        .btn-reject {
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
            .claim-card {
                grid-template-columns: 1fr;
            }

            .claim-image img,
            .claim-image .placeholder {
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
                <a href="admin.php">📝 Review Posts</a>
                <a href="review_claims.php" class="active">📄 Review Claims</a>
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
                    <small>Admin claim verification</small>
                    <h1 class="admin-page-title">Review Claims</h1>
                    <p class="admin-page-subtitle">Verify ownership proof and decide whether a claimant should receive the item.</p>
                </div>
                <div class="hero-badge">Admin Review</div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo $pending_claims_count; ?></h3>
                    <span>Pending Claims</span>
                </div>
                <div class="stat-card">
                    <h3><?php echo $approved_claims_count; ?></h3>
                    <span>Approved Claims</span>
                </div>
                <div class="stat-card">
                    <h3><?php echo $rejected_claims_count; ?></h3>
                    <span>Rejected Claims</span>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-header">
                <h2>Pending Claim Requests</h2>
                <a href="admin_index.php">Back to Admin Dashboard</a>
            </div>

            <div class="claims-grid">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='claim-card'>";

                        echo "<div class='claim-image'>";
                        if (!empty($row['post_image'])) {
                            echo "<img src='upload/" . htmlspecialchars($row['post_image']) . "' alt='Item image'>";
                        } else {
                            echo "<div class='placeholder'>No Item Image</div>";
                        }
                        echo "</div>";

                        echo "<div class='claim-content'>";

                        echo "<div class='badge-row'>";
                        echo "<span class='badge category'>" . htmlspecialchars($row['post_category']) . "</span>";
                        echo "<span class='badge found'>Pending Claim</span>";
                        echo "</div>";

                        echo "<div class='claim-title'>" . htmlspecialchars($row['post_title']) . "</div>";

                        echo "<div class='claim-meta'>";
                        echo "<strong>Claimant:</strong> " . htmlspecialchars($row['claimant_name']) . "<br>";
                        echo "<strong>Email:</strong> " . htmlspecialchars($row['claimant_email']) . "<br>";
                        echo "<strong>Location:</strong> " . htmlspecialchars($row['post_location']) . "<br>";
                        echo "<strong>Submitted:</strong> " . htmlspecialchars($row['created_at']);
                        echo "</div>";

                        echo "<div class='claim-section-label'>Ownership Proof</div>";
                        echo "<div class='claim-proof-box'>" . nl2br(htmlspecialchars($row['proof_description'])) . "</div>";

                        if (!empty($row['proof_file'])) {
                            echo "<a class='proof-link-btn' href='claim_uploads/" . htmlspecialchars($row['proof_file']) . "' target='_blank'>View Proof File</a>";
                        }

                        echo "<div class='claim-actions'>";
                        echo "<a class='btn-approve' href='approve_claim.php?id=" . $row['id'] . "&post_id=" . $row['post_id'] . "'>Approve Claim</a>";
                        echo "<a class='btn-reject' href='reject_claim.php?id=" . $row['id'] . "'>Reject Claim</a>";
                        echo "</div>";

                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<div class='empty-state'>";
                    echo "<h3 style='margin-bottom:8px;'>No pending claims</h3>";
                    echo "<p>All ownership requests have been reviewed for now.</p>";
                    echo "</div>";
                }
                ?>
            </div>
        </section>

    </main>
</div>

</body>
</html>