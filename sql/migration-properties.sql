CREATE TABLE IF NOT EXISTS properties (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(190) NOT NULL,
  province VARCHAR(100) NULL,
  district VARCHAR(100) NULL,
  neighborhood VARCHAR(150) NULL,
  address TEXT NULL,
  unit_no VARCHAR(20) NULL,
  floor VARCHAR(20) NULL,
  room_count VARCHAR(20) NULL,
  gross_sqm VARCHAR(20) NULL,
  block_no VARCHAR(30) NULL,
  parcel_no VARCHAR(30) NULL,
  independent_section_no VARCHAR(30) NULL,
  title_deed_info VARCHAR(255) NULL,
  rent_amount VARCHAR(30) NULL,
  deposit_amount VARCHAR(30) NULL,
  description TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_user_id (user_id),
  CONSTRAINT fk_properties_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS property_owners (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  property_id INT UNSIGNED NOT NULL,
  person_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_property_person (property_id, person_id),
  CONSTRAINT fk_property_owners_property FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
  CONSTRAINT fk_property_owners_person FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
