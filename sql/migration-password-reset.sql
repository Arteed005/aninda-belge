-- Mevcut kurulumlarda bir kez çalıştırın.
ALTER TABLE users
  ADD COLUMN password_reset_token_hash CHAR(64) NULL AFTER verify_token_expires_at,
  ADD COLUMN password_reset_expires_at DATETIME NULL AFTER password_reset_token_hash;
