<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkAdminLogin();

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Get partner logo to delete file
    $partner = getSingleRecord($pdo, "SELECT logo FROM partners WHERE id = ?", [$id]);
    
    if ($partner && executeQuery($pdo, "DELETE FROM partners WHERE id = ?", [$id])) {
        // Delete logo file if exists
        if ($partner['logo'] && file_exists("../uploads/partners/" . $partner['logo'])) {
            unlink("../uploads/partners/" . $partner['logo']);
        }
        $success = "Partner deleted successfully!";
    } else {
        $error = "Failed to delete partner.";
    }
}

// Pagination
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total count
$totalCount = getSingleRecord($pdo, "SELECT COUNT(*) as count FROM partners")['count'];
$totalPages = ceil($totalCount / $limit);


// Get partners with pagination
$stmt = $pdo->prepare("SELECT * FROM partners");

$stmt->execute();
$partners = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partners Management - Rawafed Yemen Admin</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        
        .partners-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .partner-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.2s;
        }
        
        .partner-card:hover {
            transform: translateY(-5px);
        }
        
        .partner-logo {
            width: 100%;
            height: 150px;
            object-fit: contain;
            background-color: #f8f9fa;
            padding: 1rem;
        }
        
        .partner-content {
            padding: 1rem;
        }
        
        .partner-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .partner-description {
            color: #666;
            margin-bottom: 1rem;
            line-height: 1.4;
            font-size: 0.9rem;
        }
        
        .partner-meta {
            font-size: 0.8rem;
            color: #999;
            margin-bottom: 1rem;
        }
        
        .partner-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-start;
        }
        
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            font-size: 0.7rem;
            font-weight: 500;
            text-transform: uppercase;
            display: inline-block;
        }
        
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }
        
        .pagination a,
        .pagination span {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
        }
        
        .pagination a:hover {
            background-color: #f8f9fa;
        }
        
        .pagination .current {
            background-color: #fa9505;
            color: white;
            border-color: #fa9505;
        }
        
        .no-data {
            text-align: center;
            padding: 3rem;
            color: #666;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        
        @media (max-width: 768px) {
            .partners-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="admin-header clearfix">
        <h1><i class="fas fa-handshake"></i> Partners Management</h1>
        <nav class="admin-nav">
            <a href="index.php"><i class="fas fa-dashboard"></i> Dashboard</a>
            <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </header>
    
    <div class="container">
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="page-header">
            <h2><i class="fas fa-list"></i> All Partners</h2>
            <a href="edit_partner.php" class="btn"><i class="fas fa-plus"></i> Add New Partner</a>
        </div>
        
        <?php if ($partners): ?>
            <div class="partners-grid">
                <?php foreach ($partners as $partner): ?>
                    <div class="partner-card">
                        <?php if ($partner['logo']): ?>
                            <img src="../uploads/partners/<?php echo htmlspecialchars($partner['logo']); ?>" 
                                 alt="<?php echo htmlspecialchars($partner['name']); ?>" 
                                 class="partner-logo">
                        <?php else: ?>
                            <div class="partner-logo" style="display: flex; align-items: center; justify-content: center; color: #999;">
                                <i class="fas fa-building fa-3x"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="partner-content">
                            <h3 class="partner-name"><?php echo htmlspecialchars($partner['name']); ?></h3>
                            
                            <?php if ($partner['description_en']): ?>
                                <p class="partner-description"><?php echo htmlspecialchars(truncateText($partner['description_en'], 100)); ?></p>
                            <?php endif; ?>
                            
                            <div class="partner-meta">
                                <span class="status-badge status-<?php echo $partner['status']; ?>">
                                    <i class="fas fa-circle"></i> <?php echo ucfirst($partner['status']); ?>
                                </span>
                                <?php if ($partner['website']): ?>
                                    <br><i class="fas fa-globe"></i> 
                                    <a href="<?php echo htmlspecialchars($partner['website']); ?>" target="_blank">
                                        <?php echo htmlspecialchars($partner['website']); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <div class="partner-actions">
                                <a href="edit_partner.php?id=<?php echo $partner['id']; ?>" class="btn btn-small btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="?delete=<?php echo $partner['id']; ?>" 
                                   class="btn btn-small btn-delete" 
                                   onclick="return confirm('Are you sure you want to delete this partner?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>"><i class="fas fa-chevron-left"></i> Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>">Next <i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-data">
                <i class="fas fa-handshake fa-3x" style="margin-bottom: 1rem; color: #ddd;"></i>
                <h3>No partners found</h3>
                <p>Start by <a href="edit_partner.php">adding your first partner</a></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

