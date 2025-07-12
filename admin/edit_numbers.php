<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkAdminLogin();

$item = null;
$isEdit = false;

// Check if editing existing item
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $item = getSingleRecord($pdo, "SELECT * FROM items WHERE id = ?", [$id]);
    if ($item) {
        $isEdit = true;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $number = (int)$_POST['number'];
    $text_ar = trim($_POST['text_ar']);
    $text_en = trim($_POST['text_en']);
    
    $errors = [];

    if (empty($number)) $errors[] = "Item number is required.";
    if (empty($text_ar)) $errors[] = "Arabic text is required.";
    if (empty($text_en)) $errors[] = "English text is required.";

    // Handle image upload
    $imageName = $item['image_url'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/items/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileInfo = pathinfo($_FILES['image']['name']);
        $extension = strtolower($fileInfo['extension']);

        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $errors[] = "Invalid image format. Only JPG, PNG, and GIF are allowed.";
        } else {
            $imageName = 'item_' . time() . '.' . $extension;
            $uploadPath = $uploadDir . $imageName;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $errors[] = "Failed to upload image.";
                $imageName = $item['image_url'] ?? '';
            } else {
                if ($isEdit && $item['image_url'] && file_exists($uploadDir . $item['image_url'])) {
                    unlink($uploadDir . $item['image_url']);
                }
            }
        }
    }

    if (empty($errors)) {
        if ($isEdit) {
            $sql = "UPDATE items SET number = ?, text_ar = ?, text_en = ?, image_url = ? WHERE id = ?";
            $params = [$number, $text_ar, $text_en, $imageName, $id];
        } else {
            $sql = "INSERT INTO items (number, text_ar, text_en, image_url) VALUES (?, ?, ?, ?)";
            $params = [$number, $text_ar, $text_en, $imageName];
        }

        if (executeQuery($pdo, $sql, $params)) {
            $success = $isEdit ? "Item updated successfully!" : "Item created successfully!";
            if (!$isEdit) {
                header("Location: numbers.php");
                exit;
            }
            $item = getSingleRecord($pdo, "SELECT * FROM items WHERE id = ?", [$id]);
        } else {
            $errors[] = "Failed to save item.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Edit' : 'Add'; ?> items - Rawafed Yemen Admin</title>
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
        <h1><i class="fas fa-itemspaper"></i> <?php echo $isEdit ? 'Edit' : 'Add'; ?> items</h1>
        <nav class="admin-nav">
            <a href="numbers.php"><i class="fas fa-list"></i> All Numbers</a>
            <a href="index.php"><i class="fas fa-dashboard"></i> Dashboard</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </header>
    
    <div class="container">
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <ul style="margin: 0.5rem 0 0 1rem;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
        <form method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <label for="number">Item Number *</label>
        <input type="number" id="number" name="number" value="<?php echo htmlspecialchars($item['number'] ?? ''); ?>" required>
    </div>

    <div class="form-group">
        <label for="text_en">Text (English) *</label>
        <input type="text" id="text_en" name="text_en" value="<?php echo htmlspecialchars($item['text_en'] ?? ''); ?>" required>
    </div>

    <div class="form-group">
        <label for="text_ar">Text (Arabic) *</label>
        <input type="text" id="text_ar" name="text_ar" value="<?php echo htmlspecialchars($item['text_ar'] ?? ''); ?>" required>
    </div>

    <div class="form-group">
        <label for="image">Image</label>
        <input type="file" id="image" name="image" accept="image/*">
        <?php if ($item && $item['image_url']): ?>
            <img src="../uploads/items/<?php echo htmlspecialchars($item['image_url']); ?>" alt="Current image" class="current-image">
        <?php endif; ?>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn">
            <i class="fas fa-save"></i> <?php echo $isEdit ? 'Update' : 'Create'; ?> Item
        </button>
        <a href="numbers.php" class="btn btn-secondary">
            <i class="fas fa-times"></i> Cancel
        </a>
    </div>
</form>

        </div>
    </div>
</body>
</html>