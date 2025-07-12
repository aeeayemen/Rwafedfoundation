-- Database: rawafed_db
-- Rawafed Yemen Website Database Structure

CREATE DATABASE IF NOT EXISTS `rawafed_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `rawafed_db`;

-- Table structure for table `sections`
CREATE TABLE `sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page` varchar(50) NOT NULL,
  `section_name` varchar(50) NOT NULL,
  `content_en` text,
  `content_ar` text,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_section` (`page`, `section_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `news`
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title_en` varchar(255) NOT NULL,
  `title_ar` varchar(255) NOT NULL,
  `content_en` text NOT NULL,
  `content_ar` text NOT NULL,
  `excerpt_en` text,
  `excerpt_ar` text,
  `image` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `status` enum('published','draft') DEFAULT 'published',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `partners`
CREATE TABLE `partners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `description_en` text,
  `description_ar` text,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','editor') DEFAULT 'admin',
  `status` enum('active','inactive') DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `projects`
CREATE TABLE `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title_en` varchar(255) NOT NULL,
  `title_ar` varchar(255) NOT NULL,
  `description_en` text NOT NULL,
  `description_ar` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `status` enum('ongoing','completed','planned') DEFAULT 'ongoing',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `location_en` varchar(255) DEFAULT NULL,
  `location_ar` varchar(255) DEFAULT NULL,
  `budget` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (username: admin, password: admin123)
INSERT INTO `users` (`username`, `password`, `email`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@rawafedyemen.org', 'admin');

-- Insert sample sections for home page
INSERT INTO `sections` (`page`, `section_name`, `content_en`, `content_ar`) VALUES
('home', 'hero_title', 'Welcome to Rawafed Yemen', 'مرحباً بكم في روافد اليمن'),
('home', 'hero_subtitle', 'Building a Better Future for Yemen', 'نبني مستقبلاً أفضل لليمن'),
('home', 'about_title', 'About Us', 'من نحن'),
('home', 'about_content', 'Rawafed Yemen is dedicated to sustainable development and humanitarian aid in Yemen.', 'روافد اليمن مؤسسة مكرسة للتنمية المستدامة والمساعدات الإنسانية في اليمن.'),
('about', 'page_title', 'About Rawafed Yemen', 'حول روافد اليمن'),
('about', 'mission_title', 'Our Mission', 'مهمتنا'),
('about', 'vision_title', 'Our Vision', 'رؤيتنا'),
('mission', 'page_title', 'Our Mission', 'مهمتنا'),
('mission', 'content', 'To provide sustainable solutions for Yemen''s development challenges.', 'تقديم حلول مستدامة لتحديات التنمية في اليمن.');

-- Insert sample news
INSERT INTO `news` (`title_en`, `title_ar`, `content_en`, `content_ar`, `excerpt_en`, `excerpt_ar`, `image`, `date`) VALUES
('New Water Project Launched', 'إطلاق مشروع مياه جديد', 'We are excited to announce the launch of our new water project...', 'نحن متحمسون للإعلان عن إطلاق مشروع المياه الجديد...', 'New water project to serve 1000 families', 'مشروع مياه جديد لخدمة 1000 عائلة', 'water-project.jpg', '2024-01-15'),
('Education Initiative Success', 'نجاح مبادرة التعليم', 'Our education initiative has reached 500 students...', 'وصلت مبادرة التعليم الخاصة بنا إلى 500 طالب...', 'Education program reaches 500 students', 'برنامج التعليم يصل إلى 500 طالب', 'education.jpg', '2024-01-10');

-- Insert sample partners
INSERT INTO `partners` (`name`, `logo`, `website`, `description_en`, `description_ar`) VALUES
('UN Yemen', 'un-yemen.png', 'https://yemen.un.org', 'United Nations Yemen Office', 'مكتب الأمم المتحدة في اليمن'),
('USAID', 'usaid.png', 'https://usaid.gov', 'United States Agency for International Development', 'وكالة الولايات المتحدة للتنمية الدولية');

-- Insert sample projects
INSERT INTO `projects` (`title_en`, `title_ar`, `description_en`, `description_ar`, `image`, `status`, `start_date`, `location_en`, `location_ar`) VALUES
('Clean Water Initiative', 'مبادرة المياه النظيفة', 'Providing clean water access to rural communities', 'توفير الوصول للمياه النظيفة للمجتمعات الريفية', 'water-project.jpg', 'ongoing', '2024-01-01', 'Sana''a Governorate', 'محافظة صنعاء'),
('Education Support Program', 'برنامج دعم التعليم', 'Supporting education in underserved areas', 'دعم التعليم في المناطق المحرومة', 'education.jpg', 'ongoing', '2023-09-01', 'Hodeidah Governorate', 'محافظة الحديدة');

