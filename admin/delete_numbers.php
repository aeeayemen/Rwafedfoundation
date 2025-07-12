<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkAdminLogin();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Get feature to delete associated image
    $feature = getSingleRecord($pdo, "SELECT image_url FROM items WHERE id = ?", [$id]);
    
    if ($feature) {
        // Delete the feature record
        if (executeQuery($pdo, "DELETE FROM items WHERE id = ?", [$id])) {
            // Delete associated image file if exists
            if (!empty($feature['image_url']) && file_exists("../uploads/items/" . $feature['image_url'])) {
                unlink("../uploads/items/" . $feature['image_url']);
            }
            
            // logAdminActivity('Delete Feature', "Deleted feature ID: $id");
            header("Location: numbers.php?success=Feature deleted successfully");
        } else {
            header("Location: numbers.php?error=Failed to delete feature");
        }
    } else {
        header("Location: numbers.php?error=Feature not found");
    }
} else {
    header("Location: numbers.php?error=Invalid feature ID");
}

exit;
?>
