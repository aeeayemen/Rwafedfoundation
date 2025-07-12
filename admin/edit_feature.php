<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkAdminLogin();

$feature = null;
$isEdit = false;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $feature = getSingleRecord($pdo, "SELECT * FROM features WHERE id = ?", [$id]);
    if ($feature) {
        $isEdit = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title_en = trim($_POST['title_en']);
    $title_ar = trim($_POST['title_ar']);
    $description_en = trim($_POST['description_en']);
    $description_ar = trim($_POST['description_ar']);
    
    $errors = [];

    if (empty($title_en)) $errors[] = "English title is required.";
    if (empty($title_ar)) $errors[] = "Arabic title is required.";
    if (empty($description_en)) $errors[] = "English description is required.";
    if (empty($description_ar)) $errors[] = "Arabic description is required.";

    // Handle image upload
    $imageName = $feature['image_url'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/features/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileInfo = pathinfo($_FILES['image']['name']);
        $extension = strtolower($fileInfo['extension']);

        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $errors[] = "Invalid image format. Only JPG, PNG, and GIF are allowed.";
        } else {
            $imageName = 'feature_' . time() . '.' . $extension;
            $uploadPath = $uploadDir . $imageName;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $errors[] = "Failed to upload image.";
                $imageName = $feature['image_url'] ?? '';
            } else {
                if ($isEdit && $feature['image_url'] && file_exists($uploadDir . $feature['image_url'])) {
                    unlink($uploadDir . $feature['image_url']);
                }
            }
        }
    }

    if (empty($errors)) {
        if ($isEdit) {
            $sql = "UPDATE features SET title_en = ?, title_ar = ?, description_en = ?, description_ar = ?, image_url = ? WHERE id = ?";
            $params = [$title_en, $title_ar, $description_en, $description_ar, $imageName, $id];
        } else {
            $sql = "INSERT INTO features (title_en, title_ar, description_en, description_ar, image_url, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
            $params = [$title_en, $title_ar, $description_en, $description_ar, $imageName];
        }

        if (executeQuery($pdo, $sql, $params)) {
            $success = $isEdit ? "Feature updated successfully!" : "Feature created successfully!";
            if (!$isEdit) {
                header("Location: features.php");
                exit;
            }
            $feature = getSingleRecord($pdo, "SELECT * FROM features WHERE id = ?", [$id]);
        } else {
            $errors[] = "Failed to save feature.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Edit' : 'Add'; ?> News - Rawafed Yemen Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            line-height: 1.6;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #fa9505 0%, #12b5f0 100%);
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .admin-header h1 {
            font-size: 1.5rem;
            display: inline-block;
        }
        
        .admin-nav {
            float: right;
        }
        
        .admin-nav a {
            color: white;
            text-decoration: none;
            margin-left: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        .admin-nav a:hover {
            background-color: rgba(255,255,255,0.2);
        }
        
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #fa9505;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-group textarea.content {
            min-height: 200px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #fa9505 0%, #12b5f0 100%);
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            transition: transform 0.2s;
            font-size: 1rem;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-start;
            margin-top: 2rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .current-image {
            max-width: 200px;
            height: auto;
            border-radius: 5px;
            margin-top: 0.5rem;
        }
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header class="admin-header clearfix">
        <h1><i class="fas fa-newspaper"></i> <?php echo $isEdit ? 'Edit' : 'Add'; ?> News</h1>
        <nav class="admin-nav">
            <a href="features.php"><i class="fas fa-list"></i> All Features</a>
            <a href="index.php"><i class="fas fa-dashboard"></i> Dashboard</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </header>
    
    <div class="container">
    <?php if (isset($success)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <ul style="margin: 0.5rem 0 0 1rem;">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="title_en"><i class="fas fa-heading"></i> Feature Title (English) *</label>
                    <input type="text" id="title_en" name="title_en" 
                        value="<?= htmlspecialchars($feature['title_en'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="title_ar"><i class="fas fa-heading"></i> Feature Title (Arabic) *</label>
                    <input type="text" id="title_ar" name="title_ar" 
                        value="<?= htmlspecialchars($feature['title_ar'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="description_en"><i class="fas fa-align-left"></i> Description (English) *</label>
                <textarea id="description_en" name="description_en" required><?= htmlspecialchars($feature['description_en'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="description_ar"><i class="fas fa-align-left"></i> Description (Arabic) *</label>
                <textarea id="description_ar" name="description_ar" required><?= htmlspecialchars($feature['description_ar'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="image"><i class="fas fa-image"></i> Feature Image</label>
                <input type="file" id="image" name="image" accept="image/*">
                <?php if (!empty($feature['image_url'])): ?>
                    <img src="../uploads/features/<?= htmlspecialchars($feature['image_url']) ?>" 
                         alt="Current Image" class="current-image">
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn">
                    <i class="fas fa-save"></i> <?= $isEdit ? 'Update' : 'Create' ?> Feature
                </button>
                <a href="features.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html>

