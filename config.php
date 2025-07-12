<?php
/**
 * Configuration File
 * Rawafed Yemen Website
 */

// Site Configuration
define('SITE_NAME', 'Rawafed Yemen');
define('SITE_URL', 'https://rawafedyemen.org');
define('SITE_EMAIL', 'info@rsd-yemen.org');
define('SITE_PHONE', '0778 202 221');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'rawafed_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Upload Configuration
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Upload Paths
define('UPLOAD_PATH_SECTIONS', 'uploads/sections/');
define('UPLOAD_PATH_NEWS', 'uploads/news/');
define('UPLOAD_PATH_PARTNERS', 'uploads/partners/');
define('UPLOAD_PATH_PROJECTS', 'uploads/projects/');

// Pagination
define('NEWS_PER_PAGE', 10);
define('PROJECTS_PER_PAGE', 9);
define('ADMIN_ITEMS_PER_PAGE', 20);

// Language Configuration
define('DEFAULT_LANGUAGE', 'en');
define('SUPPORTED_LANGUAGES', ['en', 'ar']);

// Security Configuration
define('SESSION_TIMEOUT', 3600); // 1 hour
define('CSRF_TOKEN_NAME', 'csrf_token');

// Admin Configuration
define('ADMIN_SESSION_NAME', 'admin_logged_in');
define('ADMIN_ID_SESSION', 'admin_id');
define('ADMIN_USERNAME_SESSION', 'admin_username');

// Email Configuration (for future use)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_ENCRYPTION', 'tls');

// Social Media Links
define('FACEBOOK_URL', '#');
define('TWITTER_URL', '#');
define('YOUTUBE_URL', '#');
define('INSTAGRAM_URL', '#');
define('LINKEDIN_URL', '#');

// Contact Information
define('CONTACT_ADDRESS_EN', 'Al-Hazm Street, Hazm al Jawf, Yemen');
define('CONTACT_ADDRESS_AR', 'شارع الحزم، حزم الجوف، اليمن');
define('SERVICE_AREA_EN', 'Service Area: Marib, Yemen');
define('SERVICE_AREA_AR', 'منطقة الخدمة: مأرب، اليمن');

// Error Reporting (set to false in production)
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('Asia/Aden');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

