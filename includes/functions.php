<?php
/**
 * Helper Functions
 * Rawafed Yemen Website
 */

/**
 * Handle file upload
 */


//  function logAdminActivity($action, $details) {
//     // مثال لتسجيل النشاط في جدول activity_log
//     global $pdo;
//     $stmt = $pdo->prepare("INSERT INTO activity_log (action, details, timestamp) VALUES (?, ?, NOW())");
//     $stmt->execute([$action, $details]);
// }

function checkPasswordStrength($password) {
    $strength = 0;

    if (strlen($password) >= 8) $strength++;
    if (preg_match('/[A-Z]/', $password)) $strength++;
    if (preg_match('/[a-z]/', $password)) $strength++;
    if (preg_match('/[0-9]/', $password)) $strength++;
    if (preg_match('/[\W_]/', $password)) $strength++;

    if ($strength <= 2) {
        return ["كلمة المرور ضعيفة جدًا. حاول أن تجعلها أطول وتحتوي على أحرف كبيرة وصغيرة وأرقام ورموز."];
    } elseif ($strength <= 4) {
        return ["كلمة المرور متوسطة. أضف مزيدًا من التنوع لزيادة الأمان."];
    } else {
        return []; // قوية، لا يوجد خطأ
    }
}



 function isValidEmail($email) {
    $pattern = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
    return preg_match($pattern, $email) === 1;
}

function uploadFile($file, $uploadDir, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif']) {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'message' => 'Invalid file parameters'];
    }

    // Check for upload errors
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return ['success' => false, 'message' => 'No file sent'];
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['success' => false, 'message' => 'File size exceeded'];
        default:
            return ['success' => false, 'message' => 'Unknown upload error'];
    }

    // Check file size (5MB max)
    if ($file['size'] > 5000000) {
        return ['success' => false, 'message' => 'File size too large'];
    }

    // Check file type
    $fileInfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $fileInfo->file($file['tmp_name']);
    
    $allowedMimes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif'
    ];

    $ext = array_search($mimeType, $allowedMimes, true);
    if ($ext === false) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }

    // Generate unique filename
    $filename = sprintf('%s.%s', sha1_file($file['tmp_name']), $ext);
    $filepath = $uploadDir . '/' . $filename;

    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'message' => 'Failed to move uploaded file'];
    }

    return ['success' => true, 'filename' => $filename, 'filepath' => $filepath];
}

/**
 * Delete file
 */
function deleteFile($filepath) {
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return true;
}

/**
 * Get language content
 */
function getContent($pdo, $page, $section, $lang = 'en') {
    $contentColumn = "content_" . $lang;
    $sql = "SELECT $contentColumn AS content, image FROM sections WHERE page = ? AND section_name = ?";
    $result = getSingleRecord($pdo, $sql, [$page, $section]);
    return $result ? $result : ['content' => '', 'image' => ''];
}

/**
 * Update or insert content
 */
function updateContent($pdo, $page, $section, $contentEn, $contentAr, $image = null) {
    // Check if record exists
    $sql = "SELECT id FROM sections WHERE page = ? AND section_name = ?";
    $existing = getSingleRecord($pdo, $sql, [$page, $section]);
    
    if ($existing) {
        // Update existing record
        if ($image) {
            $sql = "UPDATE sections SET content_en = ?, content_ar = ?, image = ? WHERE page = ? AND section_name = ?";
            $params = [$contentEn, $contentAr, $image, $page, $section];
        } else {
            $sql = "UPDATE sections SET content_en = ?, content_ar = ? WHERE page = ? AND section_name = ?";
            $params = [$contentEn, $contentAr, $page, $section];
        }
    } else {
        // Insert new record
        $sql = "INSERT INTO sections (page, section_name, content_en, content_ar, image) VALUES (?, ?, ?, ?, ?)";
        $params = [$page, $section, $contentEn, $contentAr, $image];
    }
    
    return executeQuery($pdo, $sql, $params);
}

/**
 * Format date for display
 */
function formatDate($date, $lang = 'en') {
    $timestamp = strtotime($date);
    if ($lang === 'ar') {
        return date('d/m/Y', $timestamp);
    }
    return date('M d, Y', $timestamp);
}

/**
 * Truncate text
 */
function truncateText($text, $length = 150) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

/**
 * Get current language
 */
function getCurrentLanguage() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['lang'])) {
        $_SESSION['lang'] = 'en';
    }
    
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ar'])) {
        $_SESSION['lang'] = $_GET['lang'];
    }
    
    return $_SESSION['lang'];
}

/**
 * Set page direction based on language
 */
function getPageDirection($lang = null) {
    if (!$lang) {
        $lang = getCurrentLanguage();
    }
    return $lang === 'ar' ? 'rtl' : 'ltr';
}

/**
 * Generate pagination
 */
function generatePagination($currentPage, $totalPages, $baseUrl) {
    $pagination = '';
    
    if ($totalPages > 1) {
        $pagination .= '<div class="pagination">';
        
        // Previous button
        if ($currentPage > 1) {
            $pagination .= '<a href="' . $baseUrl . '?page=' . ($currentPage - 1) . '">&laquo; Previous</a>';
        }
        
        // Page numbers
        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i == $currentPage) {
                $pagination .= '<span class="current">' . $i . '</span>';
            } else {
                $pagination .= '<a href="' . $baseUrl . '?page=' . $i . '">' . $i . '</a>';
            }
        }
        
        // Next button
        if ($currentPage < $totalPages) {
            $pagination .= '<a href="' . $baseUrl . '?page=' . ($currentPage + 1) . '">Next &raquo;</a>';
        }
        
        $pagination .= '</div>';
    }
    
    return $pagination;
}
?>

