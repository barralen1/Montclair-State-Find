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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $post_type = $_POST['post_type'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $location = $_POST['location'];
    $image_name = "";

    if (!empty($_FILES['image']['name'])) {
        $target_dir = __DIR__ . "/upload/";
        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;

        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $message = "Image upload failed.";
        }
    }

    if ($message == "") {
        $sql = "INSERT INTO posts (post_type, title, description, category, location, image)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ssssss", $post_type, $title, $description, $category, $location, $image_name);

        if ($stmt->execute()) {
            $message = "Post submitted successfully! It is now pending admin approval.";
        } else {
            $message = "Execute failed: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Report Item - MSU Lost & Found</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/style.css?v=8">
    <style>
        .report-page-title {
            font-size: 30px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .report-page-subtitle {
            color: rgba(255,255,255,0.92);
            font-size: 15px;
        }

        .form-shell {
            background: white;
            border-radius: 22px;
            padding: 24px;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
            border: 1px solid #eceff3;
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
        .form-group select,
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
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #c8102e;
            background: white;
        }

        .form-group textarea {
            min-height: 140px;
            resize: vertical;
        }

        .upload-box {
            border: 2px dashed #d1d5db;
            background: #fafafa;
            border-radius: 18px;
            padding: 26px;
            text-align: center;
        }

        .upload-box input[type="file"] {
            background: transparent;
            border: none;
            padding: 0;
        }

        .submit-row {
            display: flex;
            gap: 12px;
            margin-top: 22px;
            flex-wrap: wrap;
        }

        .submit-btn,
        .secondary-link-btn {
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

        .submit-btn {
            background: #c8102e;
            color: white;
        }

        .submit-btn:hover {
            background: #a90e27;
        }

        .secondary-link-btn {
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

        @media (max-width: 900px) {
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
                    <a href="view_posts.php">🔎 Browse</a>
                    <a href="create_posts.php" class="active">➕ Report</a>
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
                    <small>Submit a new listing</small>
                    <h1 class="report-page-title">Create Post</h1>
                    <p class="report-page-subtitle">Report a lost or found item to help reconnect it with its owner.</p>
                </div>
                <div class="hero-badge">Report Form</div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Lost</h3>
                    <span>Report missing items</span>
                </div>
                <div class="stat-card">
                    <h3>Found</h3>
                    <span>Post discovered items</span>
                </div>
                <div class="stat-card">
                    <h3>Review</h3>
                    <span>Pending admin approval</span>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-header">
                <h2>Item Details</h2>
                <a href="view_posts.php">Browse Existing Posts</a>
            </div>

            <div class="form-shell">

                <?php if ($message != ""): ?>
                    <?php if (stripos($message, 'successfully') !== false || stripos($message, 'pending admin approval') !== false) { ?>
                        <div class="message-box-success"><?php echo htmlspecialchars($message); ?></div>
                    <?php } else { ?>
                        <div class="message-box-error"><?php echo htmlspecialchars($message); ?></div>
                    <?php } ?>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-grid">

                        <div class="form-group">
                            <label>Post Type</label>
                            <select name="post_type" required>
                                <option value="Lost">Lost</option>
                                <option value="Found">Found</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Category</label>
                            <input type="text" name="category" placeholder="Ex: Electronics, Wallet, Keys">
                        </div>

                        <div class="form-group full">
                            <label>Title</label>
                            <input type="text" name="title" placeholder="Ex: AirPods Pro, Black Wallet, Car Keys" required>
                        </div>

                        <div class="form-group full">
                            <label>Description</label>
                            <textarea name="description" placeholder="Describe the item with details like color, brand, size, case, markings, or anything that helps identify it." required></textarea>
                        </div>

                        <div class="form-group full">
                            <label>Location</label>
                            <input type="text" name="location" placeholder="Ex: Student Center, Sprague Library, University Hall">
                        </div>

                        <div class="form-group full">
                            <label>Upload Image</label>
                            <div class="upload-box">
                                <p style="margin-bottom:10px; font-weight:700; color:#374151;">Add a photo of the item</p>
                                <p style="margin-bottom:12px; color:#6b7280; font-size:14px;">Images help students and admins identify the item more easily.</p>
                                <input type="file" name="image">
                            </div>
                        </div>

                    </div>

                    <div class="submit-row">
                        <button type="submit" class="submit-btn">Submit Post</button>
                        <a href="index.php" class="secondary-link-btn">Back to Dashboard</a>
                    </div>
                </form>

            </div>
        </section>

    </main>
</div>

</body>
</html>