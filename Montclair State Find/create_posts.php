<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include("db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    die("Access Denied");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $post_type   = trim($_POST['post_type']);
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category    = trim($_POST['category']);
    $location    = trim($_POST['location']);
    $status      = "pending";
    $image_name  = "";

    if (!empty($_FILES['image']['name'])) {
        $target_dir = __DIR__ . "/upload/";

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;

        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $message = "Image upload failed.";
        }
    }

    if ($message == "") {
        $sql = "INSERT INTO posts (post_type, title, description, category, location, status, image)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param(
            "sssssss",
            $post_type,
            $title,
            $description,
            $category,
            $location,
            $status,
            $image_name
        );

        if ($stmt->execute()) {
            $message = "Post submitted successfully and is awaiting admin approval.";
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Post - Montclair State Find</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:Arial, Helvetica, sans-serif;
        }

        body{
            background:#f3f4f6;
            color:#111827;
        }

        .layout{
            display:flex;
            min-height:100vh;
        }

        .sidebar{
            width:230px;
            background:linear-gradient(180deg,#b80f2f 0%, #d81b42 100%);
            color:white;
            padding:24px 16px;
        }

        .sidebar-logo{
            font-size:42px;
            font-weight:800;
            line-height:1;
            margin-bottom:12px;
        }

        .sidebar-sub{
            font-size:14px;
            color:rgba(255,255,255,0.88);
            margin-bottom:24px;
        }

        .sidebar a{
            display:block;
            color:white;
            text-decoration:none;
            padding:14px 16px;
            border-radius:16px;
            margin-bottom:10px;
            font-size:16px;
            font-weight:700;
        }

        .sidebar a:hover{
            background:rgba(255,255,255,0.10);
        }

        .sidebar a.active{
            background:rgba(255,255,255,0.16);
        }

        .main{
            flex:1;
            padding:24px;
        }

        .hero{
            background:linear-gradient(135deg,#c8102e 0%, #df2348 100%);
            color:white;
            border-radius:24px;
            padding:22px;
            margin-bottom:20px;
        }

        .hero-top{
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            gap:16px;
            margin-bottom:16px;
        }

        .hero-kicker{
            font-size:13px;
            font-weight:700;
            opacity:0.9;
            margin-bottom:6px;
        }

        .hero h1{
            font-size:28px;
            margin-bottom:6px;
        }

        .hero p{
            font-size:15px;
            opacity:0.95;
        }

        .hero-pill{
            background:rgba(255,255,255,0.14);
            padding:8px 14px;
            border-radius:999px;
            font-size:13px;
            font-weight:700;
            white-space:nowrap;
        }

        .hero-cards{
            display:grid;
            grid-template-columns:repeat(3,1fr);
            gap:14px;
        }

        .hero-card{
            background:rgba(255,255,255,0.10);
            border:1px solid rgba(255,255,255,0.12);
            border-radius:18px;
            padding:18px;
        }

        .hero-card h3{
            font-size:18px;
            margin-bottom:4px;
        }

        .hero-card p{
            font-size:13px;
            opacity:0.95;
        }

        .card{
            background:white;
            border-radius:22px;
            padding:22px;
            box-shadow:0 10px 28px rgba(0,0,0,0.06);
        }

        .card-head{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:16px;
            margin-bottom:18px;
        }

        .card-head h2{
            font-size:18px;
        }

        .card-head a{
            color:#b80f2f;
            text-decoration:none;
            font-weight:700;
            font-size:14px;
        }

        .message{
            padding:12px 14px;
            border-radius:12px;
            margin-bottom:16px;
            background:#ecfdf3;
            border:1px solid #bbf7d0;
            color:#166534;
            font-weight:700;
            font-size:14px;
        }

        .form-grid{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:16px;
        }

        .form-group{
            margin-bottom:16px;
        }

        .form-group.full{
            grid-column:1 / -1;
        }

        label{
            display:block;
            margin-bottom:7px;
            font-size:14px;
            font-weight:700;
            color:#374151;
        }

        input, select, textarea{
            width:100%;
            border:1px solid #e5e7eb;
            border-radius:14px;
            background:#f9fafb;
            padding:13px 14px;
            font-size:14px;
            outline:none;
        }

        input:focus, select:focus, textarea:focus{
            border-color:#c8102e;
            background:#fff;
        }

        textarea{
            min-height:130px;
            resize:vertical;
        }

        .hint{
            margin-top:7px;
            font-size:12px;
            color:#6b7280;
        }

        .submit-btn{
            border:none;
            background:linear-gradient(90deg,#c8102e,#e11d48);
            color:white;
            padding:14px 20px;
            border-radius:14px;
            font-size:15px;
            font-weight:700;
            cursor:pointer;
        }

        .submit-btn:hover{
            opacity:0.95;
        }

        @media(max-width:950px){
            .layout{
                flex-direction:column;
            }

            .sidebar{
                width:100%;
            }

            .hero-cards{
                grid-template-columns:1fr;
            }

            .form-grid{
                grid-template-columns:1fr;
            }

            .hero-top{
                flex-direction:column;
            }
        }
    </style>
</head>
<body>

<div class="layout">

    <aside class="sidebar">
        <div class="sidebar-logo">MSU</div>
        <div class="sidebar-sub">Student Portal</div>

        <a href="index.php">🏠 Home</a>
        <a href="view_posts.php">🔎 Browse</a>
        <a href="create_posts.php" class="active">➕ Report</a>
        <a href="logout.php">🚪 Logout</a>
    </aside>

    <main class="main">
        <section class="hero">
            <div class="hero-top">
                <div>
                    <div class="hero-kicker">Submit a new listing</div>
                    <h1>Create Post</h1>
                    <p>Report a lost or found item to help reconnect it with its owner.</p>
                </div>
                <div class="hero-pill">Report Form</div>
            </div>

            <div class="hero-cards">
                <div class="hero-card">
                    <h3>Lost</h3>
                    <p>Report missing items</p>
                </div>
                <div class="hero-card">
                    <h3>Found</h3>
                    <p>Post discovered items</p>
                </div>
                <div class="hero-card">
                    <h3>Review</h3>
                    <p>Pending admin approval</p>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-head">
                <h2>Item Details</h2>
                <a href="view_posts.php">Browse Existing Posts</a>
            </div>

            <?php if ($message != ""): ?>
                <div class="message"><?php echo htmlspecialchars($message); ?></div>
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
                        <select name="category" required>
                            <option value="">Select a category</option>
                            <option value="Electronics">Electronics</option>
                            <option value="Wallet / Cards">Wallet / Cards</option>
                            <option value="Keys">Keys</option>
                            <option value="Clothing">Clothing</option>
                            <option value="Academic Items">Academic Items</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group full">
                        <label>Title</label>
                        <input type="text" name="title" placeholder="Ex: AirPods Pro, Black Wallet, Car Keys" required>
                    </div>

                    <div class="form-group full">
                        <label>Description</label>
                        <textarea name="description" placeholder="Describe the item with details like color, brand, size, case, markings, or anything that helps identify it." required></textarea>
                    </div>

                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" name="location" placeholder="Ex: Student Center, Library, University Hall" required>
                    </div>

                    <div class="form-group">
                        <label>Upload Image</label>
                        <input type="file" name="image" accept="image/*">
                        <div class="hint">Optional, but recommended for faster identification.</div>
                    </div>

                    <div class="form-group full">
                        <button type="submit" class="submit-btn">Submit Post</button>
                    </div>

                </div>
            </form>
        </section>
    </main>

</div>

</body>
</html>

    </main>
</div>

</body>
</html>
