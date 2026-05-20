<?php
session_start();
require_once 'db.php';

// Helper function to get book image path (place this at the top)
function getBookImagePath(array $book) {
    // Define title to image mapping
    $title_image_map = [
        'gatsby' => ['file' => 'gatsby.jpg', 'keywords' => ['gatsby', 'great gatsby']],
        'mockingbird' => ['file' => 'mockingbird.jpg', 'keywords' => ['mockingbird', 'to kill a mockingbird']],
        '1984' => ['file' => '1984.jpg', 'keywords' => ['1984', 'nineteen eighty-four']],
        'alchemist' => ['file' => 'alchemist.jpg', 'keywords' => ['alchemist', 'the alchemist']],
        'atomic' => ['file' => 'atomic.jpg', 'keywords' => ['atomic', 'atomic habits']]
    ];
    
    // Check database image first
    if(!empty($book['Image'])) {
        if(file_exists("uploads/" . $book['Image'])) {
            return "uploads/" . $book['Image'];
        }
        elseif(file_exists("images/" . $book['Image'])) {
            return "images/" . $book['Image'];
        }
    }
    
    // Try to find by title
    $book_title = strtolower($book['Title']);
    foreach($title_image_map as $data) {
        foreach($data['keywords'] as $keyword) {
            if(strpos($book_title, $keyword) !== false) {
                $image_path = "images/" . $data['file'];
                if(file_exists($image_path)) {
                    return $image_path;
                }
            }
        }
    }
    
    return false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <div class="nav-container">
            <h1>📚 Library System</h1>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="index.php#books">Books</a></li>
                <li><a href="reviews.php">Reviews</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php">My Account</a></li>
                    <li><a href="logout.php">Logout (<?php echo $_SESSION['user_name']; ?>)</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="hero">
            <h2>Welcome to Library System</h2>
            <p>Discover amazing books and share your reviews</p>
        </div>

        <div class="books-section" id="books">
            <h2>📖 All Books</h2>
            <div class="books-grid">
                <?php
                $sql = "SELECT b.*, 
                        (SELECT AVG(Rating) FROM Reviews WHERE BookID = b.BookID) as avg_rating,
                        (SELECT COUNT(*) FROM Reviews WHERE BookID = b.BookID) as review_count
                        FROM Books b";
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    while($book = $result->fetch_assoc()) {
                        $image_path = getBookImagePath($book);
                        ?>
                        <div class="book-card">
                            <div class="book-cover">
                                <?php if($image_path): ?>
                                    <img src="<?php echo $image_path; ?>" 
                                         alt="<?php echo htmlspecialchars($book['Title']); ?>"
                                         loading="lazy"
                                         onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'no-image\'>📖</div>'">
                                <?php else: ?>
                                    <div class="no-image">📖</div>
                                <?php endif; ?>
                            </div>
                            <h3><?php echo htmlspecialchars($book['Title']); ?></h3>
                            <p class="author">by <?php echo htmlspecialchars($book['Author']); ?></p>
                            <p class="category"><?php echo htmlspecialchars($book['Category']); ?></p>
                            <p class="description"><?php echo substr(htmlspecialchars($book['Description']), 0, 100); ?>...</p>
                            
                            <?php if($book['review_count'] > 0): ?>
                                <div class="book-rating">
                                    <span class="stars">
                                        <?php 
                                        $avg = round($book['avg_rating'], 1);
                                        $full_stars = floor($avg);
                                        echo str_repeat('★', $full_stars);
                                        echo str_repeat('☆', 5 - $full_stars);
                                        ?>
                                    </span>
                                    <small>(<?php echo $book['review_count']; ?> reviews)</small>
                                </div>
                            <?php endif; ?>
                            
                            <a href="book_details.php?id=<?php echo $book['BookID']; ?>" class="btn">View Details & Reviews</a>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p>No books found in the database.</p>";
                }
                ?>
            </div>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>