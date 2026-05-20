<?php
session_start();
require_once 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Reviews - Library System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .reviews-container {
            max-width: 1200px;
            margin: 30px auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            backdrop-filter: blur(5px);
        }
        
        .reviews-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .reviews-header h2 {
            font-size: 2.5rem;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .review-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .review-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .review-book-title {
            font-size: 1.3rem;
            color: #3498db;
            margin-bottom: 10px;
        }
        
        .review-book-title a {
            color: #3498db;
            text-decoration: none;
        }
        
        .review-book-title a:hover {
            text-decoration: underline;
        }
        
        .review-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .reviewer-name {
            font-weight: bold;
            color: #2c3e50;
        }
        
        .review-rating {
            color: #f39c12;
            font-size: 1.1rem;
        }
        
        .review-date {
            color: #7f8c8d;
            font-size: 0.85rem;
        }
        
        .review-comment {
            color: #555;
            line-height: 1.6;
            margin-top: 10px;
        }
        
        .no-reviews {
            text-align: center;
            padding: 50px;
            color: #7f8c8d;
            font-size: 1.2rem;
        }
        
        .filter-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 10px;
        }
        
        .filter-section select {
            padding: 8px 15px;
            margin-left: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    </style>
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

    <div class="reviews-container">
        <div class="reviews-header">
            <h2>📝 All Book Reviews</h2>
            <p>Read what other readers are saying about their favorite books</p>
        </div>

        <div class="filter-section">
            <label for="book_filter">Filter by Book:</label>
            <select id="book_filter" onchange="window.location.href='reviews.php?book_id='+this.value">
                <option value="">All Books</option>
                <?php
                $books_sql = "SELECT BookID, Title FROM Books ORDER BY Title";
                $books_result = $conn->query($books_sql);
                $selected_book = isset($_GET['book_id']) ? $_GET['book_id'] : '';
                while($book = $books_result->fetch_assoc()) {
                    $selected = ($selected_book == $book['BookID']) ? 'selected' : '';
                    echo "<option value='{$book['BookID']}' $selected>" . htmlspecialchars($book['Title']) . "</option>";
                }
                ?>
            </select>
        </div>

        <div class="reviews-list">
            <?php
            // Build query based on filter
            $sql = "SELECT r.*, u.FirstName, u.LastName, b.Title as BookTitle, b.BookID 
                    FROM Reviews r 
                    JOIN Users u ON r.UserID = u.UserID 
                    JOIN Books b ON r.BookID = b.BookID";
            
            if(isset($_GET['book_id']) && !empty($_GET['book_id'])) {
                $book_id = intval($_GET['book_id']);
                $sql .= " WHERE r.BookID = $book_id";
            }
            
            $sql .= " ORDER BY r.CreatedAt DESC";
            
            $result = $conn->query($sql);
            
            if($result->num_rows > 0) {
                while($review = $result->fetch_assoc()) {
                    ?>
                    <div class="review-item">
                        <div class="review-book-title">
                            <a href="book_details.php?id=<?php echo $review['BookID']; ?>">
                                📖 <?php echo htmlspecialchars($review['BookTitle']); ?>
                            </a>
                        </div>
                        <div class="review-meta">
                            <span class="reviewer-name">By: <?php echo htmlspecialchars($review['FirstName'] . ' ' . $review['LastName']); ?></span>
                            <span class="review-rating">
                                <?php 
                                $rating = $review['Rating'];
                                echo str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
                                ?>
                            </span>
                            <span class="review-date"><?php echo date('F j, Y', strtotime($review['CreatedAt'])); ?></span>
                        </div>
                        <div class="review-comment">
                            "<?php echo nl2br(htmlspecialchars($review['Comment'])); ?>"
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<div class="no-reviews">';
                echo '<p>😕 No reviews found.</p>';
                echo '<p><a href="index.php#books">Browse books</a> and be the first to write a review!</p>';
                echo '</div>';
            }
            ?>
        </div>
    </div>

    <script>
        // Optional: Add smooth filtering without page reload
        document.getElementById('book_filter')?.addEventListener('change', function() {
            window.location.href = 'reviews.php?book_id=' + this.value;
        });
    </script>
</body>
</html>