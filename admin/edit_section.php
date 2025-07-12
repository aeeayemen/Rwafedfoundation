<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkAdminLogin();

$id = $_GET['id'] ?? null;
$page = $_GET['page'] ?? '';
$section = null;
$error = '';
$success = '';

// Get section data if editing
if ($id) {
    $section = getSingleRecord($pdo, "SELECT * FROM sections WHERE id = ?", [$id]);
    if (!$section) {
        header('Location: sections.php');
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $page_name = sanitizeInput($_POST['page']);
        $section_name = sanitizeInput($_POST['section_name']);
        $content_en = $_POST['content_en'];
        $content_ar = $_POST['content_ar'];
        $image_filename = null;
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $upload_result = uploadFile($_FILES['image'], '../uploads/sections');
            if ($upload_result['success']) {
                $image_filename = $upload_result['filename'];
                
                // Delete old image if updating
                if ($section && $section['image']) {
                    deleteFile('../uploads/sections/' . $section['image']);
                }
            } else {
                $error = $upload_result['message'];
            }
        } elseif ($section) {
            $image_filename = $section['image'];
        }
        
        if (!$error) {
            if ($id) {
                // Update existing section
                $sql = "UPDATE sections SET page = ?, section_name = ?, content_en = ?, content_ar = ?, image = ? WHERE id = ?";
                $params = [$page_name, $section_name, $content_en, $content_ar, $image_filename, $id];
            } else {
                // Insert new section
                $sql = "INSERT INTO sections (page, section_name, content_en, content_ar, image) VALUES (?, ?, ?, ?, ?)";
                $params = [$page_name, $section_name, $content_en, $content_ar, $image_filename];
            }
            
            if (executeQuery($pdo, $sql, $params)) {
                $success = $id ? 'Section updated successfully!' : 'Section created successfully!';
                if (!$id) {
                    // Redirect to edit the newly created section
                    $new_id = $pdo->lastInsertId();
                    header("Location: edit_section.php?id=$new_id&success=1");
                    exit;
                }
            } else {
                $error = 'Failed to save section. Please try again.';
            }
        }
    }
}

if (isset($_GET['success'])) {
    $success = 'Section created successfully!';
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $id ? 'Edit' : 'Add'; ?> Section - Rawafed Yemen Admin</title>
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
            color: #333;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
            font-size: 1rem;
            font-family: inherit;
        }
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #fa9505;
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
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .alert {
            padding: 0.75rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .current-image {
            margin-top: 0.5rem;
        }
        
        .current-image img {
            max-width: 200px;
            height: auto;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body>
    <header class="admin-header clearfix">
        <h1><?php echo $id ? 'Edit' : 'Add'; ?> Section</h1>
        <nav class="admin-nav">
            <a href="sections.php">Back to Sections</a>
            <a href="index.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    
    <div class="container">
        <div class="form-container">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="page">Page</label>
                        <select id="page" name="page" required>
                            <option value="">Select Page</option>
                            <option value="home" <?php echo ($section && $section['page'] === 'home') || $page === 'home' ? 'selected' : ''; ?>>Home</option>
                            <option value="about" <?php echo ($section && $section['page'] === 'about') || $page === 'about' ? 'selected' : ''; ?>>About</option>
                            <option value="mission" <?php echo ($section && $section['page'] === 'mission') || $page === 'mission' ? 'selected' : ''; ?>>Mission</option>
                            <option value="projects" <?php echo ($section && $section['page'] === 'projects') || $page === 'projects' ? 'selected' : ''; ?>>Projects</option>
                            <option value="news" <?php echo ($section && $section['page'] === 'news') || $page === 'news' ? 'selected' : ''; ?>>News</option>
                            <option value="partners" <?php echo ($section && $section['page'] === 'partners') || $page === 'partners' ? 'selected' : ''; ?>>Partners</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="section_name">Section Name</label>
                        <input type="text" id="section_name" name="section_name" required
                               value="<?php echo $section ? htmlspecialchars($section['section_name']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="content_en">English Content</label>
                    <textarea id="content_en" name="content_en" required><?php echo $section ? htmlspecialchars($section['content_en']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="content_ar">Arabic Content</label>
                    <textarea id="content_ar" name="content_ar" required><?php echo $section ? htmlspecialchars($section['content_ar']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image">Image (optional)</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <?php if ($section && $section['image']): ?>
                        <div class="current-image">
                            <p>Current image:</p>
                            <img src="../uploads/sections/<?php echo htmlspecialchars($section['image']); ?>" alt="Current image">
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn"><?php echo $id ? 'Update' : 'Create'; ?> Section</button>
                    <a href="sections.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

