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

$message = "";

if (!isset($_GET['post_id']) && !isset($_POST['post_id'])) {
    die("No post selected.");
}

$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : intval($_POST['post_id']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $claimant_name = $_POST['claimant_name'];
    $claimant_email = $_POST['claimant_email'];
    $proof_description = $_POST['proof_description'];
    $proof_file = "";

    if (!empty($_FILES['proof_file']['name'])) {
        $target_dir = __DIR__ . "/claim_uploads/";
        $proof_file = time() . "_" . basename($_FILES["proof_file"]["name"]);
        $target_file = $target_dir . $proof_file;

        if (!move_uploaded_file($_FILES["proof_file"]["tmp_name"], $target_file)) {
            $message = "Proof file upload failed.";
        }
    }

    if ($message == "") {
        $sql = "INSERT INTO claims (post_id, claimant_name, claimant_email, proof_description, proof_file)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("issss", $post_id, $claimant_name, $claimant_email, $proof_description, $proof_file);

        if ($stmt->execute()) {
            $message = "Claim submitted successfully! An admin will review your ownership proof.";
        } else {
            $message = "Error submitting claim: " . $stmt->error;
        }

        $stmt->close();
    }
}

$post_sql = "SELECT * FROM posts WHERE id=?";
$post_stmt = $conn->prepare($post_sql);

if (!$post_stmt) {
    die("Prepare failed: " . $conn->error);
}

$post_stmt->bind_param("i", $post_id);
$post_stmt->execute();
$post_result = $post_stmt->get_result();
$post = $post_result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Claim Item - MSU Lost & Found</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/style.css?v=9">
    <style>
        .claim-page-title {
            font-size: 30px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .claim-page-subtitle {
            color: rgba(255,255,255,0.92);
            font-size: 15px;
        }

        .claim-layout {
            display: grid;
            grid-template-columns: 1fr 1.1fr;
            gap: 22px;
        }

        .claim-card,
        .claim-form-shell,
        .claim-info-box {
            background: white;
            border-radius: 22px;
            padding: 22px;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
            border: 1px solid #eceff3;
        }

        .claim-item-image img {
            width: 100%;
            max-height: 280px;
            object-fit: cover;
            border-radius: 18px;
            display: block;
            margin-bottom: 16px;
        }

        .claim-item-image .placeholder {
            width: 100%;
            height: 260px;
            border-radius: 18px;
            background: #f3f4f6;
            color: #9ca3af;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            margin-bottom: 16px;
        }

        .claim-item-title {
            font-size: 26px;
            font-weight: 800;
            margin-bottom: 10px;
            color: #111827;
        }

        .claim-item-desc {
            color: #6b7280;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 14px;
        }

        .claim-item-meta {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.8;
        }

        .claim-badge-row {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .claim-badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 11px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .claim-badge.lost {
            background: #ffe5ea;
            color: #b1122b;
        }

        .claim-badge.found {
            background: #e7f9ec;
            color: #13803d;
        }

        .claim-badge.category {
            background: #f3f4f6;
            color: #4b5563;
        }

        .claim-form-shell h2 {
            font-size: 24px;
            margin-bottom: 8px;
        }

        .claim-form-shell p.form-note {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 18px;
            line-height: 1.6;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-size: 14px;
            font-weight: 700;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            border-radius: 14px;
            padding: 14px 16px;
            font-size: 15px;
            outline: none;
            font-family: Arial, sans-serif;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #c8102e;
            background: white;
        }

        .form-group textarea {
            min-height: 160px;
            resize: vertical;
        }

        .upload-box {
            border: 2px dashed #d1d5db;
            background: #fafafa;
            border-radius: 18px;
            padding: 24px;
            text-align: center;
        }

        .upload-box input[type="file"] {
            background: transparent;
            border: none;
            padding: 0;
        }

        .claim-actions {
            display: flex;
            gap: 12px;
            margin-top: 22px;
            flex-wrap: wrap;
        }

        .claim-submit-btn,
        .claim-back-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 20px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }

        .claim-submit-btn {
            background: #c8102e;
            color: white;
        }

        .claim-submit-btn:hover {
            background: #a90e27;
        }

        .claim-back-btn {
            background: #f3f4f6;
            color: #374151;
        }

        .message-box-success {
            background: #ecfdf3;
            color: #166534;
            border: 1px solid #bbf7d0;
            padding: 14px 16px;
            border-radius: 14px;
            margin-bottom: 18px;
            font-size: 14px;
            font-weight: 600;
        }

        .message-box-error {
            background: #fff4f6;
            color: #b1122b;
            border: 1px solid #ffd1da;
            padding: 14px 16px;
            border-radius: 14px;
            margin-bottom: 18px;
            font-size: 14px;
            font-weight: 600;
        }

        .claim-info-box h3 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .claim-info-box p {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.7;
        }

        @media (max-width: 1000px) {
            .claim-layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 700px) {
            .form-grid {
                grid-template-columns: 1fr;
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
                    <small>Ownership verification form</small>
                    <h1 class="claim-page-title">Claim Item</h1>
                    <p class="claim-page-subtitle">Provide proof that this item belongs to you so an admin can review your request.</p>
                </div>
                <div class="hero-badge">Claim Review</div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Step 1</h3>
                    <span>Submit your ownership proof</span>
                </div>
                <div class="stat-card">
                    <h3>Step 2</h3>
                    <span>Admin verifies claim details</span>
                </div>
                <div class="stat-card">
                    <h3>Step 3</h3>
                    <span>Approved items are picked up at the front desk</span>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="claim-layout">

                <div>
                    <?php if ($post): ?>
                        <div class="claim-card">
                            <div class="claim-item-image">
                                <?php if (!empty($post['image'])): ?>
                                    <img src="upload/<?php echo htmlspecialchars($post['image']); ?>" alt="Item image">
                                <?php else: ?>
                                    <div class="placeholder">No Image Available</div>
                                <?php endif; ?>
                            </div>

                            <div class="claim-badge-row">
                                <?php if ($post['post_type'] === 'Lost') { ?>
                                    <span class="claim-badge lost">Lost</span>
                                <?php } else { ?>
                                    <span class="claim-badge found">Found</span>
                                <?php } ?>

                                <?php if (!empty($post['category'])): ?>
                                    <span class="claim-badge category"><?php echo htmlspecialchars($post['category']); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="claim-item-title"><?php echo htmlspecialchars($post['title']); ?></div>
                            <div class="claim-item-desc"><?php echo htmlspecialchars($post['description']); ?></div>

                            <div class="claim-item-meta">
                                <strong>Location:</strong> <?php echo htmlspecialchars($post['location']); ?><br>
                                <?php if (!empty($post['status'])): ?>
                                    <strong>Status:</strong> <?php echo htmlspecialchars($post['status']); ?><br>
                                <?php endif; ?>
                                <?php if (!empty($post['created_at'])): ?>
                                    <strong>Created:</strong> <?php echo htmlspecialchars($post['created_at']); ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="claim-info-box" style="margin-top:18px;">
                            <h3>How claiming works</h3>
                            <p>
                                After you submit a claim, an administrator will review your proof of ownership.
                                If approved, the item should be picked up from the designated Lost &amp; Found front desk.
                                You may be asked to show student identification and additional proof before the item is released.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="claim-form-shell">
                    <h2>Submit Claim Request</h2>
                    <p class="form-note">
                        Be as specific as possible. Include unique details such as color, marks, case type,
                        serial number, lock screen, or contents that only the real owner would know.
                    </p>

                    <?php if ($message != ""): ?>
                        <?php if (stripos($message, 'successfully') !== false) { ?>
                            <div class="message-box-success"><?php echo htmlspecialchars($message); ?></div>
                        <?php } else { ?>
                            <div class="message-box-error"><?php echo htmlspecialchars($message); ?></div>
                        <?php } ?>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">

                        <div class="form-grid">
                            <div class="form-group">
                                <label>Your Name</label>
                                <input type="text" name="claimant_name" required>
                            </div>

                            <div class="form-group">
                                <label>Your Email</label>
                                <input type="email" name="claimant_email" required>
                            </div>

                            <div class="form-group full">
                                <label>Proof It Belongs To You</label>
                                <textarea
                                    name="proof_description"
                                    required
                                    placeholder="Describe unique details, markings, case color, contents, serial number, lock screen, or anything else that proves ownership."
                                ></textarea>
                            </div>

                            <div class="form-group full">
                                <label>Upload Proof File (optional)</label>
                                <div class="upload-box">
                                    <p style="margin-bottom:10px; font-weight:700; color:#374151;">Add supporting proof</p>
                                    <p style="margin-bottom:12px; color:#6b7280; font-size:14px;">
                                        Upload a photo, receipt, screenshot, or any supporting file that helps verify ownership.
                                    </p>
                                    <input type="file" name="proof_file">
                                </div>
                            </div>
                        </div>

                        <div class="claim-actions">
                            <button type="submit" class="claim-submit-btn">Submit Claim</button>
                            <a href="view_posts.php" class="claim-back-btn">Back to Posts</a>
                        </div>
                    </form>
                </div>

            </div>
        </section>

    </main>
</div>

</body>
</html>
</body>
</html>
