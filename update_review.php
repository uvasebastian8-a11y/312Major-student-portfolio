<?php
session_start();
require_once 'db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if(!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$review_id = $_GET['id'];

// Get review details and verify ownership
$sql = "SELECT r.*, b.Title as BookTitle 
        FROM Reviews r 
        JOIN Books b ON r.BookID = b.BookID 
        WHERE r.ReviewID = ? AND r.UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $review_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$review = $result->fetch_assoc();

if(!$review) {
    header("Location: dashboard.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    
    $update_sql = "UPDATE Reviews SET Rating = ?, Comment = ? WHERE ReviewID = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("isi", $rating, $comment, $review_id);
    
    if($update_stmt->execute()) {
        header("Location: book_details.php?id=" . $review['BookID']);
    } else {
        $error = "Error updating review.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Review - Library System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Update Review for "<?php echo htmlspecialchars($review['BookTitle']); ?>"</h2>
        <?php if(isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <select name="rating" required>
                <option value="5" <?php echo $review['Rating'] == 5 ? 'selected' : ''; ?>>★★★★★ (5)</option>
                <option value="4" <?php echo $review['Rating'] == 4 ? 'selected' : ''; ?>>★★★★☆ (4)</option>
                <option value="3" <?php echo $review['Rating'] == 3 ? 'selected' : ''; ?>>★★★☆☆ (3)</option>
                <option value="2" <?php echo $review['Rating'] == 2 ? 'selected' : ''; ?>>★★☆☆☆ (2)</option>
                <option value="1" <?php echo $review['Rating'] == 1 ? 'selected' : ''; ?>>★☆☆☆☆ (1)</option>
            </select>
            <textarea name="comment" rows="5" required><?php echo htmlspecialchars($review['Comment']); ?></textarea>
            <button type="submit">Update Review</button>
            <a href="book_details.php?id=<?php echo $review['BookID']; ?>" class="btn">Cancel</a>
        </form>
    </div>
</body>
</html>