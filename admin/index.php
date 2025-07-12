<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkAdminLogin();

// Get statistics
$newsCount = getSingleRecord($pdo, "SELECT COUNT(*) as count FROM news")['count'];
$partnersCount = getSingleRecord($pdo, "SELECT COUNT(*) as count FROM partners")['count'];
$projectsCount = getSingleRecord($pdo, "SELECT COUNT(*) as count FROM projects")['count'];
$sectionsCount = getSingleRecord($pdo, "SELECT COUNT(*) as count FROM sections")['count'];
$usersCount = getSingleRecord($pdo, "SELECT COUNT(*) as count FROM users")['count'];

// Get recent news
$recentNews = getMultipleRecords($pdo, "SELECT * FROM news ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Rawafed Yemen Admin</title>
    <link rel="stylesheet" href="assets/admin.css">
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            font-size: 2rem;
            color: #fa9505;
            margin-bottom: 0.5rem;
        }
        
        .stat-card p {
            color: #666;
            font-weight: 500;
        }
        
        .admin-menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .menu-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .menu-card h3 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }
        
        .menu-card ul {
            list-style: none;
        }
        
        .menu-card li {
            margin-bottom: 0.5rem;
        }
        
        .menu-card a {
            color: #fa9505;
            text-decoration: none;
            padding: 0.5rem;
            display: block;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        .menu-card a:hover {
            background-color: #f8f9fa;
        }
        
        .recent-activity {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .recent-activity h3 {
            color: #333;
            margin-bottom: 1rem;
        }
        
        .activity-item {
            padding: 0.75rem;
            border-bottom: 1px solid #eee;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-item h4 {
            color: #333;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
        
        .activity-item p {
            color: #666;
            font-size: 0.8rem;
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
        <h1>Rawafed Yemen Admin</h1>
        <nav class="admin-nav">
            <a href="../index.php" target="_blank">View Site</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    
    <div class="container">
        <h2>Dashboard</h2>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $newsCount; ?></h3>
                <p>News Articles</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $partnersCount; ?></h3>
                <p>Partners</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $projectsCount; ?></h3>
                <p>Projects</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $sectionsCount; ?></h3>
                <p>Content Sections</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $usersCount; ?></h3>
                <p>Content users</p>
            </div>

            
        </div>
        
        <!-- Admin Menu -->
        <div class="admin-menu">
            <div class="menu-card">
                <h3>Content Management</h3>
                <ul>
                    <li><a href="sections.php?page=home">Edit Home Page</a></li>
                    <li><a href="sections.php?page=about">Edit About Page</a></li>
                    <li><a href="sections.php?page=mission">Edit Mission Page</a></li>
                    <li><a href="sections.php">All Sections</a></li>
                </ul>
            </div>
            
            <div class="menu-card">
                <h3>News Management</h3>
                <ul>
                    <li><a href="news.php">All News</a></li>
                    <li><a href="edit_news.php">Add New Article</a></li>
                </ul>
            </div>
            
            <div class="menu-card">
                <h3>Projects Management</h3>
                <ul>
                    <li><a href="projects.php">All Projects</a></li>
                    <li><a href="edit_project.php">Add New Project</a></li>
                </ul>
            </div>

            <div class="menu-card">
                <h3>users Management</h3>
                <ul>
                    <li><a href="users.php">All users</a></li>
                    <li><a href="edit_user.php">Add New user</a></li>
                </ul>
            </div>
            
            <div class="menu-card">
                <h3>Partners Management</h3>
                <ul>
                    <li><a href="partners.php">All Partners</a></li>
                    <li><a href="edit_partner.php">Add New Partner</a></li>
                </ul>
            </div>
            
            <div class="menu-card">
                <h3>Home Sections Management</h3>
                <ul>
                    <li><a href="edit_home_Section.php">home section data</a></li>

                </ul>
            </div>
            
            <div class="menu-card">
                <h3>Partners features</h3>
                <ul>
                    <li><a href="features.php">All features</a></li>
                    <li><a href="edit_features.php">Add New feature</a></li>
                </ul>
            </div>

            <div class="menu-card">
                <h3>About section </h3>
                <ul>
                    <li><a href="about_section.php">About section </a></li>
                </ul>
            </div>

            <div class="menu-card">
                <h3>Mission section </h3>
                <ul>
                    <li><a href="mission_Section.php">Mission section </a></li>
                </ul>
            </div>

            <div class="menu-card">
                <h3>numbers projects</h3>
                <ul>
                    <li><a href="numbers.php">All numbers</a></li>
                    <li><a href="edit_numbers.php">Add New number</a></li>
                </ul>
            </div>


            
        </div>
        
        
        <!-- Recent Activity -->
        <div class="recent-activity">
            <h3>Recent News</h3>
            <?php if ($recentNews): ?>
                <?php foreach ($recentNews as $news): ?>
                    <div class="activity-item">
                        <h4><?php echo htmlspecialchars($news['title_en']); ?></h4>
                        <p>Published on <?php echo formatDate($news['date']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No recent news articles.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

