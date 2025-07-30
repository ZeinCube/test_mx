-- Создание расширения для триграмм (необходимо для GIN индексов с ILIKE)
CREATE EXTENSION IF NOT EXISTS pg_trgm;

COMMENT ON EXTENSION pg_trgm IS 'Расширение для триграммного поиска и индексации';