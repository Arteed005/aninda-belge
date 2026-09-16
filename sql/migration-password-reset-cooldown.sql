-- Şifre sıfırlama isteklerinde 60 saniyelik e-posta / veritabanı tabanlı rate-limit için
-- Canlı ortamda (cPanel phpMyAdmin) bir kez çalıştırın.
ALTER TABLE users
  ADD COLUMN password_reset_requested_at DATETIME NULL AFTER password_reset_expires_at;
