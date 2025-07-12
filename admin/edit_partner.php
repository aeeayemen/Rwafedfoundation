<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkAdminLogin();

$partner = null;
$isEdit = false;

// Check if editing existing partner
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $partner = getSingleRecord($pdo, "SELECT * FROM partners WHERE id = ?", [$id]);
    if ($partner) {
        $isEdit = true;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $website = trim($_POST['website']);
    $description_en = trim($_POST['description_en']);
    $description_ar = trim($_POST['description_ar']);
    $status = $_POST['status'];
    
    $errors = [];
    
    // Validation
    if (empty($name)) $errors[] = "Partner name is required.";
    if ($website && !filter_var($website, FILTER_VALIDATE_URL)) {
        $errors[] = "Please enter a valid website URL.";
    }
    
    // Handle logo upload
    $logoName = $partner['logo'] ?? '';
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/partners/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileInfo = pathinfo($_FILES['logo']['name']);
        $extension = strtolower($fileInfo['extension']);
        
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $errors[] = "Invalid logo format. Only JPG, PNG, and GIF are allowed.";
        } else {
            $logoName = 'partner_' . time() . '.' . $extension;
            $uploadPath = $uploadDir . $logoName;
            
            if (!move_uploaded_file($_FILES['logo']['tmp_name'], $uploadPath)) {
                $errors[] = "Failed to upload logo.";
                $logoName = $partner['logo'] ?? '';
            } else {
                // Delete old logo if updating
                if ($isEdit && $partner['logo'] && file_exists($uploadDir . $partner['logo'])) {
                    unlink($uploadDir . $partner['logo']);
                }
            }
        }
    }
    
    if (empty($errors)) {
        if ($isEdit) {
            // Update existing partner
            $sql = "UPDATE partners SET name = ?, logo = ?, website = ?, description_en = ?, 
                    description_ar = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $params = [$name, $logoName, $website, $description_en, $description_ar, $status, $id];
        } else {
            // Insert new partner
            $sql = "INSERT INTO partners (name, logo, website, description_en, description_ar, status) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $params = [$name, $logoName, $website, $description_en, $description_ar, $status];
        }
        
        if (executeQuery($pdo, $sql, $params)) {
            $success = $isEdit ? "Partner updated successfully!" : "Partner created successfully!";
            if (!$isEdit) {
                header("Location: partners.php");
                exit;
            }
            // Refresh partner data
            $partner = getSingleRecord($pdo, "SELECT * FROM partners WHERE id = ?", [$id]);
        } else {
            $errors[] = "Failed to save partner.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Edit' : 'Add'; ?> Partner - Rawafed Yemen Admin</title>
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
        
        .current-logo {
            max-width: 200px;
            height: auto;
            border-radius: 5px;
            margin-top: 0.5rem;
            border: 1px solid #ddd;
            padding: 0.5rem;
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
        <h1><i class="fas fa-handshake"></i> <?php echo $isEdit ? 'Edit' : 'Add'; ?> Partner</h1>
        <nav class="admin-nav">
            <a href="partners.php"><i class="fas fa-list"></i> All Partners</a>
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
                    <label for="name"><i class="fas fa-building"></i> Partner Name *</label>
                    <input type="text" id="name" name="name" 
                           value="<?php echo htmlspecialchars($partner['name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="website"><i class="fas fa-globe"></i> Website URL</label>
                    <input type="url" id="website" name="website" 
                           value="<?php echo htmlspecialchars($partner['website'] ?? ''); ?>" 
                           placeholder="https://example.com">
                </div>
                
                <div class="form-group">
                    <label for="logo"><i class="fas fa-image"></i> Partner Logo</label>
                    <input type="file" id="logo" name="logo" accept="image/*">
                    <?php if ($partner && $partner['logo']): ?>
                        <img src="../uploads/partners/<?php echo htmlspecialchars($partner['logo']); ?>" 
                             alt="Current logo" class="current-logo">
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="description_en"><i class="fas fa-align-left"></i> Description (English)</label>
                    <textarea id="description_en" name="description_en"><?php echo htmlspecialchars($partner['description_en'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="description_ar"><i class="fas fa-align-left"></i> Description (Arabic)</label>
                    <textarea id="description_ar" name="description_ar"><?php echo htmlspecialchars($partner['description_ar'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="status"><i class="fas fa-circle"></i> Status</label>
                    <select id="status" name="status">
                        <option value="active" <?php echo ($partner['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($partner['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> <?php echo $isEdit ? 'Update' : 'Create'; ?> Partner
                    </button>
                    <a href="partners.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

