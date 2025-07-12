<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkAdminLogin();

$project = null;
$isEdit = false;

// Check if editing existing project
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $project = getSingleRecord($pdo, "SELECT * FROM projects WHERE id = ?", [$id]);
    if ($project) {
        $isEdit = true;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title_en = trim($_POST['title_en']);
    $title_ar = trim($_POST['title_ar']);
    $description_en = trim($_POST['description_en']);
    $description_ar = trim($_POST['description_ar']);
    $status = $_POST['status'];
    $start_date = $_POST['start_date'] ?: null;
    $end_date = $_POST['end_date'] ?: null;
    $location_en = trim($_POST['location_en']);
    $location_ar = trim($_POST['location_ar']);
    $budget = $_POST['budget'] ? (float)$_POST['budget'] : null;
    
    $errors = [];
    
    // Validation
    if (empty($title_en)) $errors[] = "English title is required.";
    if (empty($title_ar)) $errors[] = "Arabic title is required.";
    if (empty($description_en)) $errors[] = "English description is required.";
    if (empty($description_ar)) $errors[] = "Arabic description is required.";
    
    // Handle image upload
    $imageName = $project['image'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/projects/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileInfo = pathinfo($_FILES['image']['name']);
        $extension = strtolower($fileInfo['extension']);
        
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $errors[] = "Invalid image format. Only JPG, PNG, and GIF are allowed.";
        } else {
            $imageName = 'project_' . time() . '.' . $extension;
            $uploadPath = $uploadDir . $imageName;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $errors[] = "Failed to upload image.";
                $imageName = $project['image'] ?? '';
            } else {
                // Delete old image if updating
                if ($isEdit && $project['image'] && file_exists($uploadDir . $project['image'])) {
                    unlink($uploadDir . $project['image']);
                }
            }
        }
    }
    
    if (empty($errors)) {
        if ($isEdit) {
            // Update existing project
            $sql = "UPDATE projects SET title_en = ?, title_ar = ?, description_en = ?, description_ar = ?, 
                    image = ?, status = ?, start_date = ?, end_date = ?, location_en = ?, location_ar = ?, 
                    budget = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $params = [$title_en, $title_ar, $description_en, $description_ar, $imageName, $status, 
                      $start_date, $end_date, $location_en, $location_ar, $budget, $id];
        } else {
            // Insert new project
            $sql = "INSERT INTO projects (title_en, title_ar, description_en, description_ar, image, 
                    status, start_date, end_date, location_en, location_ar, budget) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [$title_en, $title_ar, $description_en, $description_ar, $imageName, $status, 
                      $start_date, $end_date, $location_en, $location_ar, $budget];
        }
        
        if (executeQuery($pdo, $sql, $params)) {
            $success = $isEdit ? "Project updated successfully!" : "Project created successfully!";
            if (!$isEdit) {
                header("Location: projects.php");
                exit;
            }
            // Refresh project data
            $project = getSingleRecord($pdo, "SELECT * FROM projects WHERE id = ?", [$id]);
        } else {
            $errors[] = "Failed to save project.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Edit' : 'Add'; ?> Project - Rawafed Yemen Admin</title>
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
        <h1><i class="fas fa-project-diagram"></i> <?php echo $isEdit ? 'Edit' : 'Add'; ?> Project</h1>
        <nav class="admin-nav">
            <a href="projects.php"><i class="fas fa-list"></i> All Projects</a>
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
                               value="<?php echo htmlspecialchars($project['title_en'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="title_ar"><i class="fas fa-heading"></i> Title (Arabic) *</label>
                        <input type="text" id="title_ar" name="title_ar" 
                               value="<?php echo htmlspecialchars($project['title_ar'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description_en"><i class="fas fa-align-left"></i> Description (English) *</label>
                    <textarea id="description_en" name="description_en" required><?php echo htmlspecialchars($project['description_en'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="description_ar"><i class="fas fa-align-left"></i> Description (Arabic) *</label>
                    <textarea id="description_ar" name="description_ar" required><?php echo htmlspecialchars($project['description_ar'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image"><i class="fas fa-image"></i> Project Image</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <?php if ($project && $project['image']): ?>
                        <img src="../uploads/projects/<?php echo htmlspecialchars($project['image']); ?>" 
                             alt="Current image" class="current-image">
                    <?php endif; ?>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="status"><i class="fas fa-circle"></i> Status</label>
                        <select id="status" name="status">
                            <option value="planned" <?php echo ($project['status'] ?? '') === 'planned' ? 'selected' : ''; ?>>Planned</option>
                            <option value="ongoing" <?php echo ($project['status'] ?? 'ongoing') === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                            <option value="completed" <?php echo ($project['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="budget"><i class="fas fa-dollar-sign"></i> Budget (USD)</label>
                        <input type="number" id="budget" name="budget" step="0.01" 
                               value="<?php echo htmlspecialchars($project['budget'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="start_date"><i class="fas fa-calendar-alt"></i> Start Date</label>
                        <input type="date" id="start_date" name="start_date" 
                               value="<?php echo htmlspecialchars($project['start_date'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="end_date"><i class="fas fa-calendar-check"></i> End Date</label>
                        <input type="date" id="end_date" name="end_date" 
                               value="<?php echo htmlspecialchars($project['end_date'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="location_en"><i class="fas fa-map-marker-alt"></i> Location (English)</label>
                        <input type="text" id="location_en" name="location_en" 
                               value="<?php echo htmlspecialchars($project['location_en'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="location_ar"><i class="fas fa-map-marker-alt"></i> Location (Arabic)</label>
                        <input type="text" id="location_ar" name="location_ar" 
                               value="<?php echo htmlspecialchars($project['location_ar'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> <?php echo $isEdit ? 'Update' : 'Create'; ?> Project
                    </button>
                    <a href="projects.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

