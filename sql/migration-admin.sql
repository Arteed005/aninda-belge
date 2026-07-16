-- Mevcut kurulumlarda çalıştırılacak tek seferlik migration.
-- Yeni kurulumlarda schema.sql zaten is_admin kolonunu içerir, bu dosyaya gerek yoktur.

ALTER TABLE users ADD COLUMN is_admin TINYINT(1) NOT NULL DEFAULT 0 AFTER is_premium;

-- Kendi hesabınızı admin yapmak için (email adresini değiştirin):
-- UPDATE users SET is_admin = 1 WHERE email = 'ornek@eposta.com';
