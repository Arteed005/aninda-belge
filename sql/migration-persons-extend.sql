ALTER TABLE persons
  ADD COLUMN person_type ENUM('ev_sahibi','kiraci','alici','satici','genel') NULL AFTER full_name,
  ADD COLUMN notes TEXT NULL AFTER address;

CREATE TABLE IF NOT EXISTS person_addresses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  person_id INT UNSIGNED NOT NULL,
  label VARCHAR(100) NULL,
  address TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_person_id (person_id),
  CONSTRAINT fk_person_addresses_person FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
