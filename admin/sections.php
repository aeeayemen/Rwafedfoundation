<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkAdminLogin();

$page = $_GET['page'] ?? '';
$sections = [];

if ($page) {
    $sections = getMultipleRecords($pdo, "SELECT * FROM sections WHERE page = ? ORDER BY section_name", [$page]);
} else {
    $sections = getMultipleRecords($pdo, "SELECT * FROM sections ORDER BY page, section_name");
}

$pages = getMultipleRecords($pdo, "SELECT DISTINCT page FROM sections ORDER BY page");
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Sections - Rawafed Yemen Admin</title>
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
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .page-header {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .page-filter {
            margin-bottom: 1rem;
        }
        
        .page-filter select {
            padding: 0.5rem;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, #fa9505 0%, #12b5f0 100%);
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-small {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }
        
        .btn-edit {
            background: #28a745;
        }
        
        .btn-delete {
            background: #dc3545;
        }
        
        .sections-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .content-preview {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        
        .no-data {
            text-align: center;
            padding: 2rem;
            color: #666;
        }
    </style>
</head>
<body>
    <header class="admin-header clearfix">
        <h1>Content Sections</h1>
        <nav class="admin-nav">
            <a href="index.php">Dashboard</a>
            <a href="../index.php" target="_blank">View Site</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    
    <div class="container">
        <div class="page-header">
            <h2>Content Sections Management</h2>
            <div class="page-filter">
                <form method="GET" style="display: inline;">
                    <select name="page" onchange="this.form.submit()">
                        <option value="">All Pages</option>
                        <?php foreach ($pages as $p): ?>
                            <option value="<?php echo htmlspecialchars($p['page']); ?>" 
                                    <?php echo $page === $p['page'] ? 'selected' : ''; ?>>
                                <?php echo ucfirst(htmlspecialchars($p['page'])); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <a href="edit_section.php<?php echo $page ? '?page=' . urlencode($page) : ''; ?>" class="btn">Add New Section</a>
            </div>
        </div>
        
        <div class="sections-table">
            <?php if ($sections): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Page</th>
                            <th>Section Name</th>
                            <th>English Content</th>
                            <th>Arabic Content</th>
                            <th>Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sections as $section): ?>
                            <tr>
                                <td><?php echo ucfirst(htmlspecialchars($section['page'])); ?></td>
                                <td><?php echo htmlspecialchars($section['section_name']); ?></td>
                                <td class="content-preview"><?php echo htmlspecialchars(truncateText($section['content_en'], 100)); ?></td>
                                <td class="content-preview"><?php echo htmlspecialchars(truncateText($section['content_ar'], 100)); ?></td>
                                <td><?php echo $section['image'] ? 'Yes' : 'No'; ?></td>
                                <td>
                                    <a href="edit_section.php?id=<?php echo $section['id']; ?>" class="btn btn-small btn-edit">Edit</a>
                                    <a href="delete_section.php?id=<?php echo $section['id']; ?>" 
                                       class="btn btn-small btn-delete" 
                                       onclick="return confirm('Are you sure you want to delete this section?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <p>No sections found. <a href="edit_section.php">Add the first section</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

