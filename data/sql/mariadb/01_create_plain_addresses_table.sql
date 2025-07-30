-- Создание таблицы для сохранения результатов поиска адресов
CREATE TABLE IF NOT EXISTS plain_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    region VARCHAR(256) NOT NULL,
    city VARCHAR(256) NOT NULL,
    street VARCHAR(256) NOT NULL,
    house VARCHAR(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;