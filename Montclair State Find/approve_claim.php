<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include("db.php");

if (isset($_GET['id']) && isset($_GET['post_id'])) {
    $claim_id = intval($_GET['id']);
    $post_id = intval($_GET['post_id']);

    $claim_sql = "UPDATE claims SET status='approved' WHERE id=?";
    $claim_stmt = $conn->prepare($claim_sql);

    if (!$claim_stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $claim_stmt->bind_param("i", $claim_id);

    if (!$claim_stmt->execute()) {
        die("Execute failed: " . $claim_stmt->error);
    }

    $claim_stmt->close();

    $post_sql = "UPDATE posts SET status='claimed' WHERE id=?";
    $post_stmt = $conn->prepare($post_sql);

    if (!$post_stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $post_stmt->bind_param("i", $post_id);

    if (!$post_stmt->execute()) {
        die("Execute failed: " . $post_stmt->error);
    }

    $post_stmt->close();
}

header("Location: review_claims.php");
exit();
?>