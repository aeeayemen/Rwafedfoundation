    <!-- Footer Area Start -->
    <footer class="footer-area">
        <div class="footer-top"></div>
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-sm-6">
                    <div class="footer-widget footer-about">
                        <div class="f-logo">
                            <a href="index.php">
                                <img src="images/components/logo.png" alt="Rawafed Yemen Logo" width="50%" height="50%" style="background-color: white;">
                            </a>
                        </div>
                        <p>
                            <?php 
                            $aboutContent = getContent($pdo, 'footer', 'about', $lang);
                            echo $aboutContent['content'] ?: ($lang === 'ar' ? 
                                'تأسست روافد للتنمية المستدامة في عام 2016 برؤية لرفع مستوى المجتمعات واستعادة الأمل في جميع أنحاء اليمن.' : 
                                'Founded in 2016, Rawafed for Sustainable Development began with a vision to uplift communities and restore hope across Yemen.');
                            ?>
                        </p>
                        <div class="footer-social">
                            <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
                            <a href="#"><i class="fa-brands fa-twitter"></i></a>
                            <a href="#"><i class="fa-brands fa-youtube"></i></a>
                            <a href="#"><i class="fa-brands fa-pinterest"></i></a>
                            <a href="#"><i class="fa-brands fa-instagram"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-1 offset-lg-1 col-sm-5 offset-sm-1"></div>
                <div class="col-lg-2 col-sm-6">
                    <div class="footer-widget">
                        <h3 class="widget-title">
                            <?php echo $lang === 'ar' ? 'روابط مفيدة' : 'Useful Links'; ?>
                        </h3>
                        <ul>
                            <li><a class="nav-link" href="index.php"><?php echo $lang === 'ar' ? 'الرئيسية' : 'Home'; ?></a></li>
                            <li><a href="#about" class="nav-link"><?php echo $lang === 'ar' ? 'من نحن' : 'About'; ?></a></li>
                            <li><a href="#mission" class="nav-link"><?php echo $lang === 'ar' ? 'مهمتنا' : 'Mission'; ?></a></li>
                            <li><a href="#projects" class="nav-link"><?php echo $lang === 'ar' ? 'المشاريع' : 'Projects'; ?></a></li>
                            <li><a href="#news" class="nav-link"><?php echo $lang === 'ar' ? 'الأخبار' : 'News'; ?></a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-5 offset-sm-1 offset-lg-0">
                    <div class="footer-widget">
                        <h3 class="widget-title">
                            <?php echo $lang === 'ar' ? 'اتصل بنا' : 'Contact Us'; ?>
                        </h3>
                        <ul class="footer-contact">
                            <li>
                                <i class="fa fa-home"></i> 
                                <span><?php echo $lang === 'ar' ? 'شارع الحزم، حزم الجوف، اليمن' : 'Al-Hazm Street, Hazm al Jawf, Yemen'; ?></span>
                            </li>
                            <li>
                                <i class="fa fa-map-marker"></i> 
                                <span><?php echo $lang === 'ar' ? 'منطقة الخدمة: مأرب، اليمن' : 'Service Area: Marib, Yemen'; ?></span>
                            </li>
                            <li>
                                <i class="fa fa-phone"></i>
                                <span><a href="tel:+967778202221">0778 202 221</a></span>
                            </li>
                            <li>
                                <i class="fa fa-envelope"></i> 
                                <span><a href="mailto:info@rsd-yemen.org">info@rsd-yemen.org</a></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row copy-right g-0">
                <div class="col-xl-6 col-md-7 order-2 order-md-1">
                    <p>
                        <?php echo $lang === 'ar' ? 'حقوق الطبع والنشر © 2025. تطوير بواسطة' : 'Copyright © 2025. Developed by'; ?> 
                        <a href="#"><?php echo $lang === 'ar' ? 'أنس إسماعيل' : 'Anas Ismail'; ?></a>
                    </p>
                </div>
            </div>
        </div>
    </footer>
    <!-- Footer Area End -->

    <!-- JavaScript Files -->
    <script src="js/jquery.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery.stellar.js"></script>
    <script src="js/jquery.scrollUp.min.js"></script>
    <script src="js/jquery.easing.1.3.js"></script>
    <script src="js/jquery.magnific-popup.min.js"></script>
    <script src="js/jquery.syotimer.min.js"></script>
    <script src="js/wow.js"></script>
    <script src="js/jquery.counterup.min.js"></script>
    <script src="js/jquery.waypoints.min.js"></script>
    <script src="js/isotope.pkgd.min.js"></script>
    <script src="js/jquery.ajaxchimp.min.js"></script>
    <script src="js/form.js"></script>
    <script src="js/jquery-ui.min.js"></script>
    <script src="js/jquery.nice-select.min.js"></script>
    <script src="js/custom.js"></script>
    <script>
$(document).ready(function(){
    // تهيئة الكاروسيل للأخبار
    var newsCarousel = $(".events-wrap");
    
    // إظهار الكاروسيل قبل التهيئة
    newsCarousel.css('opacity', 1);
    
    newsCarousel.owlCarousel({
        loop: true,
        margin: 30,
        nav: true,
        dots: false,
        autoplay: true,
        responsive: {
            0: { 
                items: 1,
                nav: false
            },
            600: { 
                items: 2,
                nav: false
            },
            1000: { 
                items: 3,
                nav: true
            }
        },
        onInitialized: function(event) {
            // تأكيد إظهار الكاروسيل بعد التهيئة
            newsCarousel.css('display', 'block');
        }
    });
});
</script>
</body>
</html>

