<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkAdminLogin();

$news = null;
$isEdit = false;

// Check if editing existing news
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $news = getSingleRecord($pdo, "SELECT * FROM news WHERE id = ?", [$id]);
    if ($news) {
        $isEdit = true;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title_en = trim($_POST['title_en']);
    $title_ar = trim($_POST['title_ar']);
    $content_en = trim($_POST['content_en']);
    $content_ar = trim($_POST['content_ar']);
    $excerpt_en = trim($_POST['excerpt_en']);
    $excerpt_ar = trim($_POST['excerpt_ar']);
    $date = $_POST['date'];
    $status = $_POST['status'];
    
    $errors = [];
    
    // Validation
    if (empty($title_en)) $errors[] = "English title is required.";
    if (empty($title_ar)) $errors[] = "Arabic title is required.";
    if (empty($content_en)) $errors[] = "English content is required.";
    if (empty($content_ar)) $errors[] = "Arabic content is required.";
    if (empty($date)) $errors[] = "Date is required.";
    
    // Handle image upload
    $imageName = $news['image'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/news/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileInfo = pathinfo($_FILES['image']['name']);
        $extension = strtolower($fileInfo['extension']);
        
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $errors[] = "Invalid image format. Only JPG, PNG, and GIF are allowed.";
        } else {
            $imageName = 'news_' . time() . '.' . $extension;
            $uploadPath = $uploadDir . $imageName;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $errors[] = "Failed to upload image.";
                $imageName = $news['image'] ?? '';
            } else {
                // Delete old image if updating
                if ($isEdit && $news['image'] && file_exists($uploadDir . $news['image'])) {
                    unlink($uploadDir . $news['image']);
                }
            }
        }
    }
    
    if (empty($errors)) {
        if ($isEdit) {
            // Update existing news
            $sql = "UPDATE news SET title_en = ?, title_ar = ?, content_en = ?, content_ar = ?, 
                    excerpt_en = ?, excerpt_ar = ?, image = ?, date = ?, status = ?, 
                    updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $params = [$title_en, $title_ar, $content_en, $content_ar, $excerpt_en, $excerpt_ar, 
                      $imageName, $date, $status, $id];
        } else {
            // Insert new news
            $sql = "INSERT INTO news (title_en, title_ar, content_en, content_ar, excerpt_en, 
                    excerpt_ar, image, date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [$title_en, $title_ar, $content_en, $content_ar, $excerpt_en, $excerpt_ar, 
                      $imageName, $date, $status];
        }
        
        if (executeQuery($pdo, $sql, $params)) {
            $success = $isEdit ? "News updated successfully!" : "News created successfully!";
            if (!$isEdit) {
                header("Location: news.php");
                exit;
            }
            // Refresh news data
            $news = getSingleRecord($pdo, "SELECT * FROM news WHERE id = ?", [$id]);
        } else {
            $errors[] = "Failed to save news.";
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
            <a href="news.php"><i class="fas fa-list"></i> All News</a>
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
                <div class="form-row">
                    <div class="form-group">
                        <label for="title_en"><i class="fas fa-heading"></i> Title (English) *</label>
                        <input type="text" id="title_en" name="title_en" 
                               value="<?php echo htmlspecialchars($news['title_en'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="title_ar"><i class="fas fa-heading"></i> Title (Arabic) *</label>
                        <input type="text" id="title_ar" name="title_ar" 
                               value="<?php echo htmlspecialchars($news['title_ar'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="excerpt_en"><i class="fas fa-quote-left"></i> Excerpt (English)</label>
                        <textarea id="excerpt_en" name="excerpt_en"><?php echo htmlspecialchars($news['excerpt_en'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="excerpt_ar"><i class="fas fa-quote-left"></i> Excerpt (Arabic)</label>
                        <textarea id="excerpt_ar" name="excerpt_ar"><?php echo htmlspecialchars($news['excerpt_ar'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="content_en"><i class="fas fa-align-left"></i> Content (English) *</label>
                    <textarea id="content_en" name="content_en" class="content" required><?php echo htmlspecialchars($news['content_en'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="content_ar"><i class="fas fa-align-left"></i> Content (Arabic) *</label>
                    <textarea id="content_ar" name="content_ar" class="content" required><?php echo htmlspecialchars($news['content_ar'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image"><i class="fas fa-image"></i> News Image</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <?php if ($news && $news['image']): ?>
                        <img src="../uploads/news/<?php echo htmlspecialchars($news['image']); ?>" 
                             alt="Current image" class="current-image">
                    <?php endif; ?>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="date"><i class="fas fa-calendar-alt"></i> Publication Date *</label>
                        <input type="date" id="date" name="date" 
                               value="<?php echo htmlspecialchars($news['date'] ?? date('Y-m-d')); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="status"><i class="fas fa-circle"></i> Status</label>
                        <select id="status" name="status">
                            <option value="draft" <?php echo ($news['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo ($news['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> <?php echo $isEdit ? 'Update' : 'Create'; ?> News
                    </button>
                    <a href="news.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

