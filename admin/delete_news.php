<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkAdminLogin();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Get news item to delete associated image
    $news = getSingleRecord($pdo, "SELECT image FROM news WHERE id = ?", [$id]);
    
    if ($news) {
        // Delete the news record
        if (executeQuery($pdo, "DELETE FROM news WHERE id = ?", [$id])) {
            // Delete associated image file if exists
            if ($news['image'] && file_exists("../uploads/news/" . $news['image'])) {
                unlink("../uploads/news/" . $news['image']);
            }
            
            logAdminActivity('Delete News', "Deleted news ID: $id");
            header("Location: news.php?success=News deleted successfully");
        } else {
            header("Location: news.php?error=Failed to delete news");
        }
    } else {
        header("Location: news.php?error=News not found");
    }
} else {
    header("Location: news.php?error=Invalid news ID");
}

exit;
?>

