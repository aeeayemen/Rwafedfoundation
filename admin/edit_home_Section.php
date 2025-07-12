<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkAdminLogin();

$id = 1; // نبحث عن السطر رقم 1 فقط
$section = getSingleRecord($pdo, "SELECT * FROM home_sections WHERE id = ?", [$id]);

$isEdit = !empty($section); // إذا وجدنا بيانات يعني تعديل، إذا لا يعني إضافة

// إذا تم إرسال النمويب
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title_en = trim($_POST['title_en']);
    $title_ar = trim($_POST['title_ar']);
    $subtitle_en = trim($_POST['subtitle_en']);
    $subtitle_ar = trim($_POST['subtitle_ar']);
    $description_en = trim($_POST['description_en']);
    $description_ar = trim($_POST['description_ar']);
    $point1_en = trim($_POST['point1_en']);
    $point1_ar = trim($_POST['point1_ar']);
    $point2_en = trim($_POST['point2_en']);
    $point2_ar = trim($_POST['point2_ar']);
    $point3_en = trim($_POST['point3_en']);
    $point3_ar = trim($_POST['point3_ar']);

    $errors = [];
    $imageFields = ['image1', 'image2', 'image3', 'image4'];
    $uploadDir = '../uploads/home/';

    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $images = [];
    foreach ($imageFields as $field) {
        $oldImage = $section[$field] ?? '';
        $images[$field] = $oldImage;

        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
            if (!in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif'])) {
                $errors[] = "$field must be a valid image.";
                continue;
            }

            $filename = $field . '_' . time() . '.' . $ext;
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($_FILES[$field]['tmp_name'], $destination)) {
                if (!empty($oldImage) && file_exists($uploadDir . $oldImage)) unlink($uploadDir . $oldImage);
                $images[$field] = $filename;
            } else {
                $errors[] = "Failed to upload $field.";
            }
        }
    }

    if (empty($errors)) {
        if ($isEdit) {
            // تحديث السجل
            $sql = "UPDATE home_sections SET 
                title_en = ?, title_ar = ?, subtitle_en = ?, subtitle_ar = ?, 
                description_en = ?, description_ar = ?, 
                point1_en = ?, point1_ar = ?, 
                point2_en = ?, point2_ar = ?, 
                point3_en = ?, point3_ar = ?, 
                image1 = ?, image2 = ?, image3 = ?, image4 = ?, 
                updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
            $params = [
                $title_en, $title_ar, $subtitle_en, $subtitle_ar,
                $description_en, $description_ar,
                $point1_en, $point1_ar,
                $point2_en, $point2_ar,
                $point3_en, $point3_ar,
                $images['image1'], $images['image2'], $images['image3'], $images['image4'],
                $id
            ];
            $result = executeQuery($pdo, $sql, $params);
        
            $success = $result ? "Home section updated successfully!" : "Failed to save changes.";
        } else {
            // إضافة سجل جديد
            $sql = "INSERT INTO home_sections 
                (id, title_en, title_ar, subtitle_en, subtitle_ar, description_en, description_ar, 
                point1_en, point1_ar, point2_en, point2_ar, point3_en, point3_ar, 
                image1, image2, image3, image4, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $params = [
                $id,
                $title_en, $title_ar, $subtitle_en, $subtitle_ar,
                $description_en, $description_ar,
                $point1_en, $point1_ar,
                $point2_en, $point2_ar,
                $point3_en, $point3_ar,
                $images['image1'], $images['image2'], $images['image3'], $images['image4']
            ];
            $result = executeQuery($pdo, $sql, $params);
            $success = $result ? "Home section added successfully!" : "Failed to add record.";
        }

        if ($result) {
            $section = getSingleRecord($pdo, "SELECT * FROM home_sections WHERE id = ?", [$id]);
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
    <title><?= $isEdit ? 'Edit' : 'Add' ?> Home Section</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
<div class="container">
<a href="index.php" class="btn-return"><i class="fa fa-arrow-left"></i> Return to Index</a>

    <h2><?= $isEdit ? 'Edit' : 'Add' ?> Home Section</h2>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <?php
        $fields = [
            'title' => 'Title',
            'subtitle' => 'Subtitle',
            'description' => 'Description',
            'point1' => 'Point 1',
            'point2' => 'Point 2',
            'point3' => 'Point 3'
        ];
        foreach ($fields as $base => $label): ?>
            <div class="form-group">
                <label><?= $label ?> (EN)</label>
                <input type="text" name="<?= $base ?>_en" value="<?= htmlspecialchars($section[$base.'_en'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label><?= $label ?> (AR)</label>
                <input type="text" name="<?= $base ?>_ar" value="<?= htmlspecialchars($section[$base.'_ar'] ?? '') ?>">
            </div>
        <?php endforeach; ?>

        <?php for ($i = 1; $i <= 4; $i++): ?>
            <div class="form-group">
                <label>Image <?= $i ?></label>
                <input type="file" name="image<?= $i ?>">
                <?php if (!empty($section['image' . $i])): ?>
                    <img src="../uploads/home/<?= htmlspecialchars($section['image' . $i]) ?>" class="preview" alt="Image <?= $i ?>">
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
