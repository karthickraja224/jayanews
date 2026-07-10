-- extra_tables.sql
-- Run this once against your `jayanews` database (see README.md, step 3).
-- Safe to re-run: uses IF NOT EXISTS / INSERT IGNORE, so it will never
-- duplicate data or overwrite content you've already written in tbl_pages.
--
-- mysql --default-character-set=utf8mb4 -u youruser -p jayanews < extra_tables.sql

CREATE TABLE IF NOT EXISTS `tbl_contact_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_created` (`created_at`),
  KEY `idx_is_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Safe placeholder static pages — only inserted if a row with that page_key
-- doesn't already exist, so your real About/Contact/Privacy/Terms content
-- (if you've already written it in the admin) is never touched.
INSERT IGNORE INTO `tbl_pages` (`page_key`, `title`, `content`) VALUES
('about',   'About Us',           '<p>Tell your readers who Jaya Plus is and what you cover.</p>'),
('contact', 'Contact Us',         '<p>Reach us using the form below.</p>'),
('privacy', 'Privacy Policy',     '<p>Add your privacy policy here.</p>'),
('terms',   'Terms & Conditions', '<p>Add your terms and conditions here.</p>');
