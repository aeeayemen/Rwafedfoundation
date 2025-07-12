<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkAdminLogin();

// Pagination
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM news");
$totalCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
$totalPages = ceil($totalCount / $limit);

// Get news with pagination
$stmt = $pdo->prepare("SELECT * FROM news ORDER BY date DESC, created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$news = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Management - Rawafed Yemen Admin</title>
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
        
        .news-grid {
            display: grid;
            gap: 1.5rem;
        }
        
        .news-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            display: grid;
            grid-template-columns: 200px 1fr auto;
            gap: 1rem;
        }
        
        .news-image {
            width: 200px;
            height: 150px;
            object-fit: cover;
        }
        
        .news-content {
            padding: 1rem;
            flex: 1;
        }
        
        .news-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .news-excerpt {
            color: #666;
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }
        
        .news-meta {
            font-size: 0.8rem;
            color: #999;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .news-actions {
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            justify-content: center;
        }
        
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            font-size: 0.7rem;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .status-published {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-draft {
            background-color: #fff3cd;
            color: #856404;
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
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        
        @media (max-width: 768px) {
            .news-card {
                grid-template-columns: 1fr;
            }
            
            .news-image {
                width: 100%;
                height: 200px;
            }
            
            .news-actions {
                flex-direction: row;
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>
    <header class="admin-header clearfix">
        <h1>News Management</h1>
        <nav class="admin-nav">
            <a href="index.php">Dashboard</a>
            <a href="../index.php" target="_blank">View Site</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    
    <div class="container">
        <div class="page-header">
            <h2>All News Articles</h2>
            <a href="edit_news.php" class="btn">Add New Article</a>
        </div>
        
        <?php if ($news && count($news) > 0): ?>
            <div class="news-grid">
                <?php foreach ($news as $article): ?>
                    <div class="news-card">
                        <?php if (!empty($article['image'])): ?>
                            <img src="../uploads/news/<?php echo htmlspecialchars($article['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($article['title_en']); ?>" 
                                 class="news-image">
                        <?php else: ?>
                            <div class="news-image" style="background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; color: #999;">
                                No Image
                            </div>
                        <?php endif; ?>
                        
                        <div class="news-content">
                            <h3 class="news-title"><?php echo htmlspecialchars($article['title_en']); ?></h3>
                            <p class="news-excerpt">
                                <?php 
                                $excerpt = !empty($article['excerpt_en']) ? $article['excerpt_en'] : $article['content_en'];
                                echo htmlspecialchars(truncateText($excerpt, 150)); 
                                ?>
                            </p>
                            <div class="news-meta">
                                <span class="status-badge status-<?php echo $article['status']; ?>">
                                    <?php echo ucfirst($article['status']); ?>
                                </span>
                                <span>Published: <?php echo formatDate($article['date']); ?></span>
                            </div>
                        </div>
                        
                        <div class="news-actions">
                            <a href="edit_news.php?id=<?php echo $article['id']; ?>" class="btn btn-small btn-edit">Edit</a>
                            <a href="delete_news.php?id=<?php echo $article['id']; ?>" 
                               class="btn btn-small btn-delete" 
                               onclick="return confirm('Are you sure you want to delete this article?')">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>">&laquo; Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-data">
                <h3>No news articles found</h3>
                <p>Start by <a href="edit_news.php">adding your first article</a></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>