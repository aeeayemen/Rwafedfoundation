<?php
require_once 'db.php';
require_once 'functions.php';

$lang = getCurrentLanguage();
$dir = getPageDirection($lang);
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Rawafed Yemen - Sustainable Development Foundation">
    <meta name="author" content="Rawafed Yemen">
    
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Rawafed Yemen</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="images/components/favicon.ico">
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/fontawesome/all.min.css">
    <link rel="stylesheet" href="css/font/flaticon.css">
    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/magnific-popup.css">
    <link rel="stylesheet" href="css/nice-select.css">
    <link rel="stylesheet" href="css/style.css">
    <!-- أحدث نسخة موثوقة من Font Awesome 6 -->
<!-- Font Awesome 6 مع دعم v4 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/fontawesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/solid.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/v4-shims.min.css">

    
    <?php if ($lang === 'ar'): ?>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            direction: rtl;
        }
        .navbar-nav {
            direction: rtl;
        }
        .banner-info {
            text-align: right;
        }
        .section-intro {
            text-align: right;
        }
        .footer-widget {
            text-align: right;
        }

       /* في ملف style.css أو في <style> في الهدر */
.owl-carousel {
    display: block !important; /* تجاوز خاصية display: none */
}

.owl-carousel.owl-loaded {
    display: block !important;
}

    </style>
    <?php endif; ?>
</head>
<body>
    <!-- Preloader -->
    <div id="preloader">
        <div class="preloader">
            <span></span>
            <span></span>
        </div>
    </div>
    
    <!-- Header Start -->
    <header class="hearer">
        <div class="header-top">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 col-sm-6">
                        <ul class="top-info d-none d-md-block">
                            <li><a title="Email"><i class="fa fa-paper-plane"></i>info@rsd-yemen.org</a></li>
                            <li><a href="tel:0778202221" title="Phone"><i class="fa fa-message"></i>0778 202 221</a></li>
                        </ul>
                        <div class="dropdown d-md-none">
                            <a class="dropdown-toggle" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo $lang === 'ar' ? 'روابط سريعة' : 'Quick link'; ?>
                            </a>
                            <ul class="dropdown-menu shadow-sm" aria-labelledby="dropdownMenuButton1">
                                <li><a title="Email"><i class="fa fa-paper-plane"></i>info@rsd-yemen.org</a></li>
                                <li><a href="tel:0778202221" title="Phone"><i class="fa fa-message"></i>0778 202 221</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-6" style="margin:1% 0% 1% 0%">
                        <div class="account-info d-flex align-items-center">
                            <div class="lang-dropdown d-flex align-items-center">
                                <span><i class="fa fa-flag"></i></span>
                                
                                <select name="lang" id="lang" onchange="changeLanguage(this.value)" class="mx-2">
                                    <option value="en" <?php echo $lang === 'en' ? 'selected' : ''; ?>>English</option>
                                    <option value="ar" <?php echo $lang === 'ar' ? 'selected' : ''; ?>>العربية</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Search Offcanvas -->
        <div class="offcanvas offcanvas-top bg-info" id="offcanvas-search" data-bs-scroll="true">
            <div class="container d-flex flex-row py-5">
                <form class="search-form w-100">
                    <input id="search-form" type="text" class="form-control" 
                           placeholder="<?php echo $lang === 'ar' ? 'اكتب كلمة البحث واضغط إنتر' : 'Type keyword and hit enter'; ?>">
                </form>
                <button type="button" class="btn-close icon-xs bg-light rounded-5" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg">
            <div class="container px-lg-0">
                <a class="navbar-brand" href="index.php">
                    <img src="images/components/logo.png" alt="Rawafed Yemen Logo">
                </a>
                <button class="navbar-toggler offcanvas-nav-btn" type="button">
                    <i class="fa fa-bars"></i>
                </button>
                <div class="offcanvas offcanvas-start offcanvas-nav">
                    <div class="offcanvas-header">
                        <a href="index.php" class="text-inverse">
                            <img src="images/components/logo.png" alt="Rawafed Yemen Logo">
                        </a>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body pt-0 align-items-center">
                        <ul class="navbar-nav mx-auto align-items-lg-center">
                            <li class="nav-item">
                                <a class="nav-link" href="index.php">
                                    <?php echo $lang === 'ar' ? 'الرئيسية' : 'Home'; ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#about" class="nav-link">
                                    <?php echo $lang === 'ar' ? 'من نحن' : 'About'; ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#mission" class="nav-link">
                                    <?php echo $lang === 'ar' ? 'مهمتنا' : 'Mission'; ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#projects" class="nav-link">
                                    <?php echo $lang === 'ar' ? 'المشاريع' : 'Projects'; ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#news" class="nav-link">
                                    <?php echo $lang === 'ar' ? 'الأخبار' : 'News'; ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#partners" class="nav-link">
                                    <?php echo $lang === 'ar' ? 'الشركاء' : 'Partners'; ?>
                                </a>
                            </li>
                        </ul>
                  
                    </div>
                </div>
            </div>
        </nav>
    </header>
    <!-- Header End -->


    <script>
$(document).ready(function(){
  $(".owl-carousel").owlCarousel({
    items: 3, // عدد العناصر المرئية
    loop: true,
    margin: 20,
    autoplay: true,
    responsive:{
      0:{ items:1 },
      600:{ items:2 },
      1000:{ items:3 }
    }
  });
});
</script>

    <script>
        function changeLanguage(lang) {
            const url = new URL(window.location);
            url.searchParams.set('lang', lang);
            window.location.href = url.toString();
        }
    </script>

