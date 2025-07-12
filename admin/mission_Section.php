<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkAdminLogin();

$id = 1;
$section = getSingleRecord($pdo, "SELECT * FROM mission_sections WHERE id = ?", [$id]);
$isEdit = !empty($section);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title_en = trim($_POST['title_en']);
    $title_ar = trim($_POST['title_ar']);
    $description_en = trim($_POST['description_en']);
    $description_ar = trim($_POST['description_ar']);

    $errors = [];
    $uploadDir = '../uploads/mission/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $image_path = $section['image_path'] ?? '';

    if (isset($_FILES['image_path']) && $_FILES['image_path']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image_path']['name'], PATHINFO_EXTENSION);
        if (!in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif'])) {
            $errors[] = "Image must be a valid format (jpg, png, gif).";
        } else {
            $filename = 'mission_' . time() . '.' . $ext;
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['image_path']['tmp_name'], $destination)) {
                if (!empty($image_path) && file_exists($uploadDir . $image_path)) {
                    unlink($uploadDir . $image_path);
                }
                $image_path = $filename;
            } else {
                $errors[] = "Failed to upload image.";
            }
        }
    }

    if (empty($errors)) {
        if ($isEdit) {
            $sql = "UPDATE mission_sections SET 
                title_en = ?, title_ar = ?, 
                description_en = ?, description_ar = ?, 
                image_path = ?, updated_at = NOW() 
                WHERE id = ?";
            $params = [$title_en, $title_ar, $description_en, $description_ar, $image_path, $id];
        } else {
            $sql = "INSERT INTO mission_sections 
                (id, title_en, title_ar, description_en, description_ar, image_path, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $params = [$id, $title_en, $title_ar, $description_en, $description_ar, $image_path];
        }

        $result = executeQuery($pdo, $sql, $params);
        $success = $result ? "Mission section saved successfully!" : "Failed to save mission section.";

        if ($result) {
            $section = getSingleRecord($pdo, "SELECT * FROM mission_sections WHERE id = ?", [$id]);
            $isEdit = true;
        } else {
            $errors[] = $success;
            $success = '';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $isEdit ? 'Edit' : 'Add' ?> Mission Section</title>
    <style>
        body { font-family: Arial; background: #f8f9fa; padding: 2rem; }
        .container { max-width: 800px; margin: auto; background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 1rem; }
        .form-group label { font-weight: bold; display: block; }
        .form-group input, .form-group textarea { width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 5px; }
        .form-actions { margin-top: 2rem; }
        .btn { background-color: #28a745; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 5px; cursor: pointer; }
        img.preview { width: 200px; margin-top: 0.5rem; border-radius: 5px; border: 1px solid #ccc; }
        .alert { padding: 1rem; border-radius: 5px; margin-bottom: 1rem; }
        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<a href="index.php" class="btn-return"><i class="fa fa-arrow-left"></i> Return to Index</a>

<div class="container">
    <h2><?= $isEdit ? 'Edit' : 'Add' ?> Mission Section</h2>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Title (EN)</label>
            <input type="text" name="title_en" value="<?= htmlspecialchars($section['title_en'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Title (AR)</label>
            <input type="text" name="title_ar" value="<?= htmlspecialchars($section['title_ar'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Description (EN)</label>
            <textarea name="description_en" rows="5"><?= htmlspecialchars($section['description_en'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label>Description (AR)</label>
            <textarea name="description_ar" rows="5"><?= htmlspecialchars($section['description_ar'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label>Image</label>
            <input type="file" name="image_path">
            <?php if (!empty($section['image_path'])): ?>
                <img src="../uploads/mission/<?= htmlspecialchars($section['image_path']) ?>" class="preview" alt="Mission Image">
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn"><?= $isEdit ? 'Save Changes' : 'Add Mission' ?></button>
        </div>
    </form>
</div>
</body>
</html>
