<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkAdminLogin();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Get feature to delete associated image
    $feature = getSingleRecord($pdo, "SELECT image_url FROM features WHERE id = ?", [$id]);
    
    if ($feature) {
        // Delete the feature record
        if (executeQuery($pdo, "DELETE FROM features WHERE id = ?", [$id])) {
            // Delete associated image file if exists
            if (!empty($feature['image_url']) && file_exists("../uploads/features/" . $feature['image_url'])) {
                unlink("../uploads/features/" . $feature['image_url']);
            }
            
            // logAdminActivity('Delete Feature', "Deleted feature ID: $id");
            header("Location: features.php?success=Feature deleted successfully");
        } else {
            header("Location: features.php?error=Failed to delete feature");
        }
    } else {
        header("Location: features.php?error=Feature not found");
    }
} else {
    header("Location: features.php?error=Invalid feature ID");
}

exit;
?>
