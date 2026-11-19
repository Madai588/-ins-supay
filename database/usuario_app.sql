-- Ejecutar con root de MariaDB una sola vez.
CREATE USER IF NOT EXISTS 'supay_app'@'localhost' IDENTIFIED BY 'SupayApp2026!';
ALTER USER 'supay_app'@'localhost' IDENTIFIED BY 'SupayApp2026!';
GRANT SELECT, INSERT, UPDATE, DELETE ON `supay`.* TO 'supay_app'@'localhost';
CREATE USER IF NOT EXISTS 'supay_app'@'127.0.0.1' IDENTIFIED BY 'SupayApp2026!';
ALTER USER 'supay_app'@'127.0.0.1' IDENTIFIED BY 'SupayApp2026!';
GRANT SELECT, INSERT, UPDATE, DELETE ON `supay`.* TO 'supay_app'@'127.0.0.1';
FLUSH PRIVILEGES;
