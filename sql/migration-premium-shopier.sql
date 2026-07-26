-- Mevcut kurulumlarda çalıştırılacak tek seferlik migration.
-- Yeni kurulumlarda schema.sql zaten bu kolon/tabloyu içerir, bu dosyaya gerek yoktur.

ALTER TABLE users ADD COLUMN premium_expires_at DATETIME NULL AFTER is_premium;

CREATE TABLE IF NOT EXISTS shopier_processed_orders (
  order_id VARCHAR(64) NOT NULL PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
