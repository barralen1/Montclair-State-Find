<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include("db.php");
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
            $message = "Claim submitted successfully!";
        } else {
            $message = "Error submitting claim: " . $stmt->error;
        }
        $stmt->close();
    }
}
$post_sql = "SELECT * FROM posts WHERE id=?";
$post_stmt = $conn->prepare($post_sql);
$post_stmt->bind_param("i", $post_id);
$post_stmt->execute();
$post_result = $post_stmt->get_result();
$post = $post_result->fetch_assoc();
?>

<html>
<head>
    <title>Claim Item</title>
</head>
<body>
    <h2>Claim Item</h2>
    <?php if ($post): ?>
        <div style="border:1px solid black; padding:10px; margin-bottom:20px;">
            <strong>Title:</strong> <?php echo htmlspecialchars($post['title']); ?><br>
            <strong>Description:</strong> <?php echo htmlspecialchars($post['description']); ?><br>
            <strong>Category:</strong> <?php echo htmlspecialchars($post['category']); ?><br>
            <strong>Location:</strong> <?php echo htmlspecialchars($post['location']); ?><br>
            <?php if (!empty($post['image'])): ?>
                <br><img src="upload/<?php echo htmlspecialchars($post['image']); ?>" width="200"><br>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php if ($message != "") echo "<p>$message</p>"; ?>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
        <label>Your Name:</label><br>
        <input type="text" name="claimant_name" required><br><br>
        <label>Your Email:</label><br>
        <input type="email" name="claimant_email" required><br><br>
        <label>Proof It Belongs To You:</label><br>
        <textarea name="proof_description" required placeholder="Describe unique details, markings, contents, lock screen, serial number, or anything that proves ownership"></textarea><br><br>        <label>Upload Proof File (optional):</label><br>
        <input type="file" name="proof_file"><br><br>
        <button type="submit">Submit Claim</button>
    </form>
    <br>
    <a href="view_posts.php">Back to Posts</a>
</body>
</html>