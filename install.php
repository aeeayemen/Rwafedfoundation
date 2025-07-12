<?php
/**
 * Installation Script
 * Rawafed Yemen Website
 */

// Check if already installed
if (file_exists('installed.lock')) {
    die('Website is already installed. Delete installed.lock file to reinstall.');
}

$error = '';
$success = '';
$step = $_GET['step'] ?? 1;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($step == 2) {
        // Database configuration step
        $host = $_POST['host'];
        $dbname = $_POST['dbname'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        try {
            $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create database if it doesn't exist
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$dbname`");
            
            // Read and execute SQL file
            $sql = file_get_contents('database.sql');
            $statements = explode(';', $sql);
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    $pdo->exec($statement);
                }
            }
            
            // Update database configuration in db.php
            $dbConfig = "<?php
/**
 * Database Connection File
 * Rawafed Yemen Website
 */

// Database configuration
\$host = '$host';
\$dbname = '$dbname';
\$username = '$username';
\$password = '$password';

try {
    // Create PDO connection with UTF-8 charset for Arabic support
    \$pdo = new PDO(\"mysql:host=\$host;dbname=\$dbname;charset=utf8mb4\", \$username, \$password);
    
    // Set error mode to exception
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to associative array
    \$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException \$e) {
    die(\"Database connection failed: \" . \$e->getMessage());
}

/**
 * Function to execute prepared statements safely
 */
function executeQuery(\$pdo, \$sql, \$params = []) {
    try {
        \$stmt = \$pdo->prepare(\$sql);
        \$stmt->execute(\$params);
        return \$stmt;
    } catch (PDOException \$e) {
        error_log(\"Database query error: \" . \$e->getMessage());
        return false;
    }
}

/**
 * Function to get single record
 */
function getSingleRecord(\$pdo, \$sql, \$params = []) {
    \$stmt = executeQuery(\$pdo, \$sql, \$params);
    return \$stmt ? \$stmt->fetch() : false;
}

/**
 * Function to get multiple records
 */
function getMultipleRecords(\$pdo, \$sql, \$params = []) {
    \$stmt = executeQuery(\$pdo, \$sql, \$params);
    return \$stmt ? \$stmt->fetchAll() : false;
}
?>";
            
            file_put_contents('includes/db.php', $dbConfig);
            
            $success = 'Database installed successfully!';
            $step = 3;
            
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    } elseif ($step == 3) {
        // Admin user creation
        $admin_username = $_POST['admin_username'];
        $admin_password = $_POST['admin_password'];
        $admin_email = $_POST['admin_email'];
        
        if (strlen($admin_password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } else {
            try {
                include 'includes/db.php';
                
                $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET username = ?, password = ?, email = ? WHERE id = 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$admin_username, $hashed_password, $admin_email]);
                
                // Create installation lock file
                file_put_contents('installed.lock', date('Y-m-d H:i:s'));
                
                $success = 'Installation completed successfully!';
                $step = 4;
                
            } catch (PDOException $e) {
                $error = 'Error creating admin user: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rawafed Yemen - Installation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #fa9505 0%, #12b5f0  100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .install-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo h1 {
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .logo p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        
        .step {
            flex: 1;
            text-align: center;
            padding: 0.5rem;
            background: #f8f9fa;
            margin: 0 0.25rem;
            border-radius: 5px;
            font-size: 0.8rem;
        }
        
        .step.active {
            background: #fa9505;
            color: white;
        }
        
        .step.completed {
            background: #28a745;
            color: white;
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
        
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #fa9505;
        }
        
        .btn {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #fa9505 0%, #12b5f0 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 0.75rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
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
        
        .requirements {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .requirements h4 {
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .requirements ul {
            margin-left: 1rem;
        }
        
        .requirements li {
            margin-bottom: 0.25rem;
            color: #666;
        }
        
        .success-message {
            text-align: center;
            padding: 2rem;
        }
        
        .success-message h3 {
            color: #28a745;
            margin-bottom: 1rem;
        }
        
        .success-message a {
            color: #fa9505;
            text-decoration: none;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="logo">
            <h1>Rawafed Yemen</h1>
            <p>Website Installation</p>
        </div>
        
        <div class="step-indicator">
            <div class="step <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>">1. Welcome</div>
            <div class="step <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : ''; ?>">2. Database</div>
            <div class="step <?php echo $step >= 3 ? ($step > 3 ? 'completed' : 'active') : ''; ?>">3. Admin</div>
            <div class="step <?php echo $step >= 4 ? 'active' : ''; ?>">4. Complete</div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if ($step == 1): ?>
            <!-- Welcome Step -->
            <h3>Welcome to Rawafed Yemen Installation</h3>
            <p>This installer will help you set up your website. Please ensure your server meets the following requirements:</p>
            
            <div class="requirements">
                <h4>System Requirements:</h4>
                <ul>
                    <li>PHP 7.4 or higher</li>
                    <li>MySQL 5.7 or higher</li>
                    <li>Write permissions for uploads directory</li>
                    <li>mod_rewrite enabled (optional)</li>
                </ul>
            </div>
            
            <a href="?step=2" class="btn">Start Installation</a>
            
        <?php elseif ($step == 2): ?>
            <!-- Database Configuration Step -->
            <h3>Database Configuration</h3>
            <p>Please enter your database connection details:</p>
            
            <form method="POST">
                <div class="form-group">
                    <label for="host">Database Host</label>
                    <input type="text" id="host" name="host" value="localhost" required>
                </div>
                
                <div class="form-group">
                    <label for="dbname">Database Name</label>
                    <input type="text" id="dbname" name="dbname" value="rawafed_db" required>
                </div>
                
                <div class="form-group">
                    <label for="username">Database Username</label>
                    <input type="text" id="username" name="username" value="root" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Database Password</label>
                    <input type="password" id="password" name="password">
                </div>
                
                <button type="submit" class="btn">Install Database</button>
            </form>
            
        <?php elseif ($step == 3): ?>
            <!-- Admin User Creation Step -->
            <h3>Create Admin User</h3>
            <p>Create your administrator account:</p>
            
            <form method="POST">
                <div class="form-group">
                    <label for="admin_username">Admin Username</label>
                    <input type="text" id="admin_username" name="admin_username" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_password">Admin Password</label>
                    <input type="password" id="admin_password" name="admin_password" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_email">Admin Email</label>
                    <input type="email" id="admin_email" name="admin_email" required>
                </div>
                
                <button type="submit" class="btn">Create Admin User</button>
            </form>
            
        <?php elseif ($step == 4): ?>
            <!-- Installation Complete -->
            <div class="success-message">
                <h3>Installation Complete!</h3>
                <p>Your Rawafed Yemen website has been successfully installed.</p>
                <p>You can now:</p>
                <ul style="text-align: left; margin: 1rem 0;">
                    <li><a href="index.php">Visit your website</a></li>
                    <li><a href="admin/login.php">Access admin panel</a></li>
                </ul>
                <p><strong>Important:</strong> Please delete the install.php file for security reasons.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

