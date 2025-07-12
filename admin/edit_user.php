<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkAdminLogin();
// requireAdminRole('admin'); // Only super admin can manage users

$user = null;
$isEdit = false;

// Check if editing existing user
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $user = getSingleRecord($pdo, "SELECT * FROM users WHERE id = ?", [$id]);
    if ($user) {
        $isEdit = true;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    $status = $_POST['status'];
    
    $errors = [];
    
    // Validation
    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters long.";
    } else {
        // Check if username already exists (exclude current user if editing)
        $existingUser = getSingleRecord($pdo, 
            $isEdit ? "SELECT id FROM users WHERE username = ? AND id != ?" : "SELECT id FROM users WHERE username = ?", 
            $isEdit ? [$username, $id] : [$username]
        );
        if ($existingUser) {
            $errors[] = "Username already exists.";
        }
    }
    
    if ($email && !isValidEmail($email)) {
        $errors[] = "Please enter a valid email address.";
    }
    
    // Password validation (required for new users, optional for editing)
    if (!$isEdit || !empty($password)) {
        if (empty($password)) {
            $errors[] = "Password is required.";
        } elseif ($password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        } else {
            $passwordErrors = checkPasswordStrength($password);
            $errors = array_merge($errors, $passwordErrors);
        }
    }
    
    if (empty($errors)) {
        if ($isEdit) {
            // Update existing user
            if (!empty($password)) {
                $hashedPassword = hashPassword($password);
                $sql = "UPDATE users SET username = ?, email = ?, password = ?, role = ?, status = ?, 
                        updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                $params = [$username, $email, $hashedPassword, $role, $status, $id];
            } else {
                $sql = "UPDATE users SET username = ?, email = ?, role = ?, status = ?, 
                        updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                $params = [$username, $email, $role, $status, $id];
            }
        } else {
            // Insert new user
            $hashedPassword = hashPassword($password);
            $sql = "INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, ?, ?)";
            $params = [$username, $email, $hashedPassword, $role, $status];
        }
        
        if (executeQuery($pdo, $sql, $params)) {
            $action = $isEdit ? 'Update User' : 'Create User';
            $details = $isEdit ? "Updated user ID: $id" : "Created new user: $username";
            // logAdminActivity($action, $details);
            
            $success = $isEdit ? "User updated successfully!" : "User created successfully!";
            if (!$isEdit) {
                header("Location: users.php");
                exit;
            }
            // Refresh user data
            $user = getSingleRecord($pdo, "SELECT * FROM users WHERE id = ?", [$id]);
        } else {
            $errors[] = "Failed to save user.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Edit' : 'Add'; ?> User - Rawafed Yemen Admin</title>
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
            max-width: 600px;
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
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus {
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
        
        .password-help {
            font-size: 0.8rem;
            color: #666;
            margin-top: 0.25rem;
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
        <h1><i class="fas fa-user-<?php echo $isEdit ? 'edit' : 'plus'; ?>"></i> <?php echo $isEdit ? 'Edit' : 'Add'; ?> User</h1>
        <nav class="admin-nav">
            <a href="users.php"><i class="fas fa-users"></i> All Users</a>
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
            <form method="POST">
                <div class="form-group">
                    <label for="username"><i class="fas fa-user"></i> Username *</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i> Password <?php echo $isEdit ? '' : '*'; ?></label>
                        <input type="password" id="password" name="password" <?php echo $isEdit ? '' : 'required'; ?>>
                        <div class="password-help">
                            <?php if ($isEdit): ?>
                                Leave blank to keep current password
                            <?php else: ?>
                                Must be at least 8 characters with uppercase, lowercase, and number
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password"><i class="fas fa-lock"></i> Confirm Password <?php echo $isEdit ? '' : '*'; ?></label>
                        <input type="password" id="confirm_password" name="confirm_password" <?php echo $isEdit ? '' : 'required'; ?>>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="role"><i class="fas fa-crown"></i> Role</label>
                        <select id="role" name="role">
                            <option value="editor" <?php echo ($user['role'] ?? 'editor') === 'editor' ? 'selected' : ''; ?>>Editor</option>
                            <option value="admin" <?php echo ($user['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="status"><i class="fas fa-circle"></i> Status</label>
                        <select id="status" name="status">
                            <option value="active" <?php echo ($user['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($user['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> <?php echo $isEdit ? 'Update' : 'Create'; ?> User
                    </button>
                    <a href="users.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

