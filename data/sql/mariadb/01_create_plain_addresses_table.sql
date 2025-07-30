-- Создание таблицы для сохранения результатов поиска адресов
CREATE TABLE IF NOT EXISTS plain_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_address VARCHAR(1024) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;