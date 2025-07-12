<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkAdminLogin();
// requireAdminRole('admin'); // Only super admin can manage users



// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Prevent deleting own account
    if ($id == $_SESSION['admin_id']) {
        $error = "You cannot delete your own account.";
    } else {
        if (executeQuery($pdo, "DELETE FROM users WHERE id = ?", [$id])) {
            logAdminActivity('Delete User', "Deleted user ID: $id");
            $success = "User deleted successfully!";
        } else {
            $error = "Failed to delete user.";
        }
    }
}

// Handle status toggle
if (isset($_GET['toggle_status']) && is_numeric($_GET['toggle_status'])) {
    $id = (int)$_GET['toggle_status'];
    
    $user = getSingleRecord($pdo, "SELECT status FROM users WHERE id = ?", [$id]);
    if ($user) {
        $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
        if (executeQuery($pdo, "UPDATE users SET status = ? WHERE id = ?", [$newStatus, $id])) {
            logAdminActivity('Toggle User Status', "Changed user ID $id status to $newStatus");
            $success = "User status updated successfully!";
        } else {
            $error = "Failed to update user status.";
        }
    }
}

// Pagination
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total count
$totalCount = getSingleRecord($pdo, "SELECT COUNT(*) as count FROM users")['count'];
$totalPages = ceil($totalCount / $limit);


// Get users with pagination

$stmt = $pdo->prepare("SELECT * FROM users");

$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - Rawafed Yemen Admin</title>
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
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .users-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .table tr:hover {
            background-color: #f8f9fa;
        }
        
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            font-size: 0.7rem;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .role-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            font-size: 0.7rem;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .role-admin {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .role-editor {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .actions {
            display: flex;
            gap: 0.5rem;
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
            .table {
                font-size: 0.8rem;
            }
            
            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header class="admin-header clearfix">
        <h1><i class="fas fa-users"></i> Users Management</h1>
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
            <h2><i class="fas fa-list"></i> All Users</h2>
            <a href="edit_user.php" class="btn"><i class="fas fa-plus"></i> Add New User</a>
        </div>
        
        <?php if ($users): ?>
            <div class="users-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                    <?php if ($user['id'] == $_SESSION['admin_id']): ?>
                                        <small style="color: #fa9505;">(You)</small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['email'] ?: 'N/A'); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $user['role']; ?>">
                                        <i class="fas fa-<?php echo $user['role'] === 'admin' ? 'crown' : 'edit'; ?>"></i>
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $user['status']; ?>">
                                        <i class="fas fa-circle"></i> <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $user['last_login'] ? formatDate($user['last_login'], 'M d, Y H:i') : 'Never'; ?>
                                </td>
                                <td><?php echo formatDate($user['created_at']); ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" 
                                           class="btn btn-small btn-edit" title="Edit User">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                                            <a href="?toggle_status=<?php echo $user['id']; ?>" 
                                               class="btn btn-small btn-warning" 
                                               title="Toggle Status"
                                               onclick="return confirm('Are you sure you want to change this user\'s status?')">
                                                <i class="fas fa-<?php echo $user['status'] === 'active' ? 'pause' : 'play'; ?>"></i>
                                            </a>
                                            
                                            <a href="?delete=<?php echo $user['id']; ?>" 
                                               class="btn btn-small btn-delete" 
                                               title="Delete User"
                                               onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
                <i class="fas fa-users fa-3x" style="margin-bottom: 1rem; color: #ddd;"></i>
                <h3>No users found</h3>
                <p>Start by <a href="edit_user.php">adding your first user</a></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

