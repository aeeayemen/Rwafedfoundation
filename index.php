<?php
// $page_title = $lang === 'ar' ? 'الرئيسية' : 'Home';
include 'includes/header.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Get home page content
$heroTitle = getContent($pdo, 'home', 'hero_title', $lang);
$heroSubtitle = getContent($pdo, 'home', 'hero_subtitle', $lang);
$aboutTitle = getContent($pdo, 'home', 'about_title', $lang);
$aboutContent = getContent($pdo, 'home', 'about_content', $lang);


// استبدال هذا
// استبدال هذا الجزء
$recentNew = $pdo->prepare("SELECT * FROM news");
$recentNew->execute();
$recentNews = $recentNew->fetchAll(PDO::FETCH_ASSOC);

// بهذا
$recentNewsQuery = $pdo->prepare("
    SELECT 
        id,
        image,
        date,
        title_$lang AS title,
        content_$lang AS content,
        excerpt_$lang AS excerpt
    FROM news 
    ORDER BY date DESC 
    LIMIT 6
");
$recentNewsQuery->execute();
$recentNews = $recentNewsQuery->fetchAll(PDO::FETCH_ASSOC);


// Get featured projects
$featuredProjects = getMultipleRecords($pdo, "SELECT * FROM projects WHERE status = 'ongoing' ORDER BY created_at DESC LIMIT 3");


$section = getSingleRecord($pdo, "SELECT * FROM home_sections WHERE id = ?", [1]);
$about = getSingleRecord($pdo, "SELECT * FROM about_us_sections WHERE id = ?", [1]);
$mission = getSingleRecord($pdo, "SELECT * FROM mission_sections WHERE id = ?", [1]);


$item = $pdo->prepare("SELECT * FROM items ORDER BY id DESC LIMIT 3");
$item->execute();
$items = $item->fetchAll(PDO::FETCH_ASSOC);



$stmt = $pdo->prepare("SELECT * FROM features ORDER BY id DESC LIMIT 3");
$stmt->execute();
$features = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Get partners
$partners = getMultipleRecords($pdo, "SELECT * FROM partners WHERE status = 'active' ORDER BY name");
?>

<!-- Banner Area Start -->
<section class="banner-area banner-1">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-6 order-md-1 order-2">
                <div class="banner-info pe-lg-5 mt-3 mt-sm-0">
                <h1 class="banner-title">
                <?php 
                echo $section ? ($lang === 'ar' ? $section['title_ar'] : $section['title_en']) : 
                    ($lang === 'ar' ? 'إحداث <span>التأثير</span>... زراعة الأمل' : 'Creating <span>Impact</span>... Planting Hope');
                ?>
            </h1>

            <p>
                    <?php 
                    echo $section ? ($lang === 'ar' ? $section['subtitle_ar'] : $section['subtitle_en']) :
                        ($lang === 'ar' 
                            ? 'معاً، نعيد الحياة لمن هم في حاجة ونمكن الأفراد والمجتمعات من خلال المشاريع التنموية والصحية والتعليمية...' 
                            : 'Together, we restore life to those in need and empower individuals and communities...');
                    ?>
                </p>

                <div class="banner-meta mt-5">
                    <span><?php echo $section ? ($lang === 'ar' ? $section['point1_ar'] : $section['point1_en']) : ($lang === 'ar' ? 'الدعم الصحي المستدام' : 'Sustainable Health Support'); ?></span>
                    <span><?php echo $section ? ($lang === 'ar' ? $section['point2_ar'] : $section['point2_en']) : ($lang === 'ar' ? 'تمكين المجتمع' : 'Community Empowerment'); ?></span>
                    <span><?php echo $section ? ($lang === 'ar' ? $section['point3_ar'] : $section['point3_en']) : ($lang === 'ar' ? 'التعليم للجميع' : 'Education for All'); ?></span>
                </div>

                </div>
            </div>
            <div class="col-lg-6 col-md-6 order-md-2 order-1">
                <div class="banner-gallery position-relative row g-2 g-lg-3">
                    <div class="col">
                        <div class="bg-item">
                            <img class="img-fluid rounded-4" src="images/banner/banner-s1.jpg" alt="Banner">
                        </div>
                        <div class="bg-item">
                            <img class="img-fluid rounded-4" src="images/banner/banner-s3.jpg" alt="">
                        </div>
                    </div>
                    <div class="col">
                        <div class="bg-item">
                            <img class="img-fluid rounded-4" src="images/banner/banner-s4.jpg" alt="">
                        </div>
                        <div class="bg-item">
                            <img class="img-fluid rounded-4" src="images/banner/banner-s2.jpg" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Banner Area End -->

<!-- Feature Area Start -->
<section class="feature-area2" id="Values">
    <div class="container">
        <div class="row g-lg-5 g-2">
            <?php foreach ($features as $feature): ?>
                <div class="col-xl-4 col-md-4">
                    <div class="d-flex feature-item">
                        <span class="icon shadow">
                            <img src="uploads/features/<?php echo htmlspecialchars($feature['image_url']); ?>" alt="Feature Icon">
                        </span>
                        <div class="feat-txt ms-4">
                            <h3>
                                <?php echo $lang === 'ar' 
                                    ? htmlspecialchars($feature['title_ar']) 
                                    : htmlspecialchars($feature['title_en']); ?>
                            </h3>
                            <p>
                                <?php echo $lang === 'ar' 
                                    ? htmlspecialchars($feature['description_ar']) 
                                    : htmlspecialchars($feature['description_en']); ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Feature Area End -->

<!-- About Area Start -->
<section class="history-area section-padding" id="about">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 order-lg-1 order-2">
                <div class="history-txt">
                    <h2 class="section-title">

                    
                    <?php 
                        if (is_array($about) && isset($about['title_en'], $about['title_ar'])) {
                            echo $lang === 'ar' ? $about['title_ar'] : $about['title_en'];
                        } else {
                            echo $lang === 'ar' ? 'دعونا نتعرف على <span>قصتنا</span>' : 'Let\'s Know <span>Our Story</span>';
                        }
                        ?>
                    </h2>
                    <p>
                        <?php 
                        if (is_array($about) && isset($about['description_en'], $about['description_ar'])) {
                            echo $lang === 'ar' ? $about['description_ar'] : $about['description_en'];
                        } else {
                            echo $lang === 'ar' 
                                ? 'منذ ذلك الحين، أطلقنا مشاريع مؤثرة في الرعاية الصحية والتعليم وتمكين المجتمع...' 
                                : 'Since then, we\'ve launched impactful projects in healthcare, education...';
                        }
                        ?>
                    </p>

                </div>
            </div>

            <div class="col-lg-6 order-lg-2 order-1">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="raise-img height-2">
                            <img src="uploads/about/<?php echo $about['image1_path'] ?? 'rs-1.jpg'; ?>" 
                                 alt="Community empowerment project" 
                                 class="img-fluid rounded-3">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="raise-img">
                            <img src="uploads/about/<?php echo $about['image2_path'] ?? 'rs-2.jpg'; ?>" 
                                 alt="Healthcare initiative" 
                                 class="img-fluid rounded-3">
                        </div>
                        <div class="raise-img">
                            <img src="uploads/about/<?php echo $about['image3_path'] ?? 'rs-3.jpg'; ?>" 
                                 alt="Education program" 
                                 class="img-fluid rounded-3">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Area End -->

<!-- Mission Area Start -->
<section class="misson-area section-padding" id="mission">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <div>
                    <img 
                        src="uploads/mission/<?php echo isset($mission['image_path']) ? htmlspecialchars($mission['image_path']) : '7.jpg'; ?>" 
                        alt="Rawafed team in action"
                        class="img-fluid rounded-3"
                    >
                </div>
            </div>
            <div class="col-lg-6">
                <div class="misson-txt">
                    <h2 class="section-title">
                        <?php
                        echo (is_array($mission) && isset($mission['title_ar'], $mission['title_en']))
                            ? ($lang === 'ar' ? $mission['title_ar'] : $mission['title_en'])
                            : ($lang === 'ar' ? 'نحن نهتم <span>بمهمتنا</span>' : 'We Care About<span> Our Mission</span>');
                        ?>
                    </h2>
                    <p>
                        <?php
                        echo (is_array($mission) && isset($mission['description_ar'], $mission['description_en']))
                            ? ($lang === 'ar' ? $mission['description_ar'] : $mission['description_en'])
                            : ($lang === 'ar'
                                ? 'في روافد للتنمية المستدامة، مهمتنا هي تمكين المجتمعات الضعيفة...'
                                : 'At Rawafed for Sustainable Development, our mission is to empower vulnerable communities...');
                        ?>
                    </p>
                    <p>
                        <?php
                        echo $lang === 'ar'
                            ? 'نؤمن بخلق تأثير طويل الأمد من خلال الرحمة والتعاون وبناء القدرات...'
                            : 'We believe in creating long-term impact through compassion, collaboration, and capacity-building...';
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission Area End -->

<!-- Counter Area Start -->
<section class="counter-area" data-stellar-background-ratio="0.5">
    <div class="container">
        <div class="row">
            <?php foreach ($items as $item): ?>
                <div class="col-md-4 col-sm-6">
                    <div class="single-counter text-center">
                        <img src="uploads/items/<?php echo htmlspecialchars($item['image_url'] ?? 'default.png'); ?>" alt="">
                        <span class="counter"><?php echo (int)$item['number']; ?></span>
                        <h3>
                            <?php echo $lang === 'ar' ? $item['text_ar'] : $item['text_en']; ?>
                        </h3>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Counter Area End -->

<!-- Projects Area Start -->
<?php if ($featuredProjects): ?>
<section class="causes-area section-padding" id="projects">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-intro">
                    <h2 class="section-title">
                        <?php echo $lang === 'ar' ? 'المشاريع <span>المميزة</span>' : 'Featured <span>Projects</span>'; ?>
                    </h2>
                    <p>
                        <?php echo $lang === 'ar' ? 
                            'مبادرات حقيقية من روافد للتنمية المستدامة لتمكين الناس ودعم الابتكار وبناء القدرات في جميع أنحاء اليمن.' : 
                            'Real initiatives by Rawafed for Sustainable Development to empower people, support innovation, and build capacity across Yemen.'; 
                        ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="row">
            <?php foreach ($featuredProjects as $project): ?>
                <div class="col-md-4 col-sm-6">
                    <div class="single-cause">
                        <figure class="cause-thumb">
                            <?php if ($project['image']): ?>
                                <img src="uploads/projects/<?php echo htmlspecialchars($project['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($project['title_' . $lang]); ?>">
                            <?php else: ?>
                                <img src="images/causes/default-project.jpg" 
                                     alt="<?php echo htmlspecialchars($project['title_' . $lang]); ?>">
                            <?php endif; ?>
                        </figure>
                        <div class="cause-details mt-5">
                            <h3><a >
                                <?php echo htmlspecialchars($project['title_' . $lang]); ?>
                            </a></h3>
                            <p><?php echo htmlspecialchars(truncateText($project['description_' . $lang], 120)); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>
<?php endif; ?>
<!-- Projects Area End -->

<!-- News Area Start -->


<?php if (count($recentNews) > 0): ?>
<section class="upcoming-events section-padding" id="news">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-intro">
                    <h2 class="section-title">
                        <?php echo $lang === 'ar' ? 'آخر <span>الأخبار</span>' : 'Latest <span>News</span>'; ?>
                    </h2>
                    <p>
                        <?php echo $lang === 'ar' ? 
                            'ابق على اطلاع بأنشطتنا الأخيرة والتطورات في دعم المجتمع المستدام.' : 
                            'Stay updated with our recent activities and developments in sustainable community support.'; 
                        ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="row" style="
    direction: ltr;
">
            <div class="col-lg-12">
                <div class="events-wrap owl-carousel owl-theme">
                    <?php foreach ($recentNews as $news): ?>
                        <div class="single-event">
                            <figure class="event-thumb">
                                <?php if ($news['image']): ?>
                                    <img src="uploads/news/<?php echo $news['image']; ?>" 
                                         alt="<?php echo htmlspecialchars($news['title']); ?>"
                                         class="img-fluid">
                                <?php else: ?>
                                    <img src="images/news/default.jpg" 
                                         alt="<?php echo htmlspecialchars($news['title']); ?>"
                                         class="img-fluid">
                                <?php endif; ?>
                                <figcaption>
                                    <strong><?php echo date('d', strtotime($news['date'])); ?></strong>
                                    <?php echo date('M', strtotime($news['date'])); ?>
                                </figcaption>
                            </figure>
                            <div class="event-details">
                                <h3><a href="news-details.php?id=<?php echo $news['id']; ?>">
                                    <?php echo htmlspecialchars($news['title']); ?>
                                </a></h3>
                                <p>
                                    <?php 
                                    $excerpt = $news['excerpt'] ?: $news['content'];
                                    echo htmlspecialchars(truncateText($excerpt, 150)); 
                                    ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
<!-- News Area End -->

<!-- Partners Area Start -->
<?php if ($partners): ?>
<section id="partners" class="partner-area bg-cover section-padding" data-stellar-background-ratio="0.5" style="background-image: url('images/partner/partner-bg.jpg');">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-intro">
                    <h2 class="section-title">
                        <?php echo $lang === 'ar' ? '<span class="color">الشركاء</span> العالميون' : 'Global <span class="color">Partners</span>'; ?>
                    </h2>
                    <p>
                        <?php echo $lang === 'ar' ? 
                            'نعمل مع شركاء محليين وعالميين لتحقيق أهدافنا في التنمية المستدامة.' : 
                            'We work with local and global partners to achieve our sustainable development goals.'; 
                        ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="row" style="
    direction: ltr;
">
            <div class="col-lg-12">
                <div class="partner-wrap owl-carousel">
                    <?php foreach ($partners as $partner): ?>
                        <div class="partner-name">
                            <?php if ($partner['website']): ?>
                                <a href="<?php echo htmlspecialchars($partner['website']); ?>" target="_blank">
                                    <img src="uploads/partners/<?php echo htmlspecialchars($partner['logo']); ?>" 
                                         alt="<?php echo htmlspecialchars($partner['name']); ?>">
                                </a>
                            <?php else: ?>
                                <img src="uploads/partners/<?php echo htmlspecialchars($partner['logo']); ?>" 
                                     alt="<?php echo htmlspecialchars($partner['name']); ?>">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
<!-- Partners Area End -->

<?php include 'includes/footer.php'; ?>

