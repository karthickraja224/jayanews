-- ============================================================
-- Jaya Plus Admin — setup for the missing-pages feature
-- Run this ONCE in phpMyAdmin / MySQL.
-- Every statement uses IF NOT EXISTS, so it's safe to re-run
-- and will NOT touch tables that already exist in your DB.
-- ============================================================

-- Used by pages/about.php, contact.php, privacy.php, terms.php
CREATE TABLE IF NOT EXISTS tbl_pages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(50) NOT NULL,
  title VARCHAR(255) NOT NULL DEFAULT '',
  content LONGTEXT,
  meta_title VARCHAR(255) DEFAULT '',
  meta_description TEXT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Already used by getSetting()/saveSetting() in functions.php —
-- included here only in case it does not exist yet on your server.
CREATE TABLE IF NOT EXISTS tbl_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) NOT NULL,
  setting_value LONGTEXT,
  UNIQUE KEY uniq_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Already used by logActivity() in auth.php — included only
-- in case it does not exist yet on your server.
CREATE TABLE IF NOT EXISTS tbl_activity_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  action VARCHAR(50) NOT NULL,
  module VARCHAR(50) NOT NULL,
  description TEXT,
  ip_address VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Already used by comments/comments.php and the sidebar pending-count
-- badge — included only in case it does not exist yet on your server.
CREATE TABLE IF NOT EXISTS tbl_comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  news_id INT NOT NULL,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150),
  comment TEXT NOT NULL,
  status ENUM('pending','approved','spam') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_news (news_id),
  KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
