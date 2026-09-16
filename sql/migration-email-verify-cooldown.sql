-- E-posta doğrulama tekrar gönderim isteklerinde 60 saniyelik veritabanı tabanlı rate-limit için
-- Canlı ortamda (cPanel phpMyAdmin) bir kez çalıştırın.
ALTER TABLE users
  ADD COLUMN email_verify_requested_at DATETIME NULL AFTER verify_token_expires_at;
