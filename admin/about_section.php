<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkAdminLogin();

$id = 1;
$section = getSingleRecord($pdo, "SELECT * FROM about_us_sections WHERE id = ?", [$id]);
$isEdit = !empty($section);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title_en = trim($_POST['title_en']);
    $title_ar = trim($_POST['title_ar']);
    $description_en = trim($_POST['description_en']);
    $description_ar = trim($_POST['description_ar']);

    $errors = [];
    $uploadDir = '../uploads/about/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $images = [
        'image1_path' => $section['image1_path'] ?? '',
        'image2_path' => $section['image2_path'] ?? '',
        'image3_path' => $section['image3_path'] ?? ''
    ];

    foreach ($images as $field => $oldImage) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
            if (!in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif'])) {
                $errors[] = "$field must be a valid image format (jpg, png, gif).";
                continue;
            }

            $filename = $field . '_' . time() . '.' . $ext;
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($_FILES[$field]['tmp_name'], $destination)) {
                if (!empty($oldImage) && file_exists($uploadDir . $oldImage)) {
                    unlink($uploadDir . $oldImage);
                }
                $images[$field] = $filename;
            } else {
                $errors[] = "Failed to upload $field.";
            }
        }
    }

    if (empty($errors)) {
        if ($isEdit) {
            $sql = "UPDATE about_us_sections SET 
                title_en = ?, title_ar = ?, 
                description_en = ?, description_ar = ?, 
                image1_path = ?, image2_path = ?, image3_path = ? 
                WHERE id = ?";
            $params = [
                $title_en, $title_ar, $description_en, $description_ar,
                $images['image1_path'], $images['image2_path'], $images['image3_path'],
                $id
            ];
        } else {
            $sql = "INSERT INTO about_us_sections 
                (id, title_en, title_ar, description_en, description_ar, image1_path, image2_path, image3_path) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [
                $id, $title_en, $title_ar, $description_en, $description_ar,
                $images['image1_path'], $images['image2_path'], $images['image3_path']
            ];
        }

        $result = executeQuery($pdo, $sql, $params);

        $success = $result ? "Section saved successfully!" : "Failed to save section.";

        if ($result) {
            $section = getSingleRecord($pdo, "SELECT * FROM about_us_sections WHERE id = ?", [$id]);
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
    <title><?= $isEdit ? 'Edit' : 'Add' ?> About Us Section</title>
    <style>
        body { font-family: Arial; background: #f8f9fa; padding: 2rem; }
        .container { max-width: 900px; margin: auto; background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 1rem; }
        .form-group label { font-weight: bold; display: block; }
        .form-group input, .form-group textarea { width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 5px; }
        .form-actions { margin-top: 2rem; }
        .btn { background-color: #fa9505; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 5px; cursor: pointer; }
        img.preview { width: 200px; margin-top: 0.5rem; border-radius: 5px; border: 1px solid #ccc; }
        .alert { padding: 1rem; border-radius: 5px; margin-bottom: 1rem; }
        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<a href="index.php" class="btn-return"><i class="fa fa-arrow-left"></i> Return to Index</a>

<div class="container">
    <h2><?= $isEdit ? 'Edit' : 'Add' ?> About Us Section</h2>

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

        <?php for ($i = 1; $i <= 3; $i++): ?>
            <div class="form-group">
                <label>Image <?= $i ?></label>
                <input type="file" name="image<?= $i ?>_path">
                <?php if (!empty($section["image{$i}_path"])): ?>
                    <img src="../uploads/about/<?= htmlspecialchars($section["image{$i}_path"]) ?>" class="preview" alt="Image <?= $i ?>">
                <?php endif; ?>
            </div>
        <?php endfor; ?>

        <div class="form-actions">
            <button type="submit" class="btn"><?= $isEdit ? 'Save Changes' : 'Add Section' ?></button>
        </div>
    </form>
</div>
</body>
</html>
