<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkAdminLogin();

// Get section data (we assume only one row with id=1)
$stmt = $pdo->prepare("SELECT * FROM home_sections WHERE id = 1 LIMIT 1");
$stmt->execute();
$section = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8">
  <title>Home Section - Rawafed Yemen Admin</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f8f9fa;
      padding: 2rem;
    }

    .container {
      max-width: 1000px;
      margin: auto;
      background: white;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    h2 {
      margin-bottom: 1rem;
      color: #333;
    }

    .section-content p {
      margin-bottom: 0.5rem;
      color: #555;
    }

    .section-images {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
      margin-top: 1rem;
    }

    .section-images img {
      width: 200px;
      height: 130px;
      object-fit: cover;
      border-radius: 5px;
      border: 1px solid #ddd;
    }

    .btn {
      display: inline-block;
      padding: 0.5rem 1rem;
      background-color: #fa9505;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      margin-top: 1rem;
    }

    .btn:hover {
      background-color: #556cd6;
    }

    ul {
      margin-top: 1rem;
    }

    ul li {
      color: #333;
      padding: 0.2rem 0;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Homepage Section Preview</h2>

    <?php if ($section): ?>
      <div class="section-content">
        <h3><?php echo htmlspecialchars($section['title']); ?></h3>
        <h4><?php echo htmlspecialchars($section['subtitle']); ?></h4>
        <p><?php echo nl2br(htmlspecialchars($section['description'])); ?></p>

        <ul>
          <li>✔ <?php echo htmlspecialchars($section['point1']); ?></li>
          <li>✔ <?php echo htmlspecialchars($section['point2']); ?></li>
          <li>✔ <?php echo htmlspecialchars($section['point3']); ?></li>
        </ul>

        <div class="section-images">
          <?php for ($i = 1; $i <= 4; $i++): ?>
            <?php if (!empty($section["image$i"])): ?>
              <img src="../uploads/home/<?php echo htmlspecialchars($section["image$i"]); ?>" alt="Image <?php echo $i; ?>">
            <?php endif; ?>
          <?php endfor; ?>
        </div>

        <a href="edit_home_section.php" class="btn">Edit Section</a>
      </div>
    <?php else: ?>
      <p>No section content found.</p>
    <?php endif; ?>
  </div>
</body>
</html>
