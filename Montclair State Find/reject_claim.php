<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include("db.php");

if (isset($_GET['id'])) {
    $claim_id = intval($_GET['id']);

    $sql = "UPDATE claims SET status='rejected' WHERE id=?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $claim_id);

    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    $stmt->close();
}

header("Location: review_claims.php");
exit();
?>