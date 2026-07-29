CREATE TABLE IF NOT EXISTS user_packages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  package VARCHAR(50) NOT NULL,
  expires_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_user_package (user_id, package),
  CONSTRAINT fk_user_packages_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE shopier_processed_orders
  ADD COLUMN package VARCHAR(20) NOT NULL DEFAULT 'premium' AFTER user_id;
