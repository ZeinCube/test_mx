# Test MX - Система поиска адресов ФИАС

Система для поиска адресов по базе данных ФИАС с возможностью сохранения результатов в MariaDB.

## Возможности

- **Поиск адресов** по регионам, городам, улицам и домам
- **Пагинация результатов** с навигацией по страницам
- **Сохранение результатов** в MariaDB с полной информацией об адресе
- **Веб-интерфейс** для удобного поиска и управления результатами

## Технологии

- **Backend**: PHP 8.4, Symfony HttpFoundation
- **Базы данных**: PostgreSQL (ФИАС), MariaDB (сохранение результатов)
- **Frontend**: HTML5, CSS3, JavaScript (Fetch API)
- **Контейнеризация**: Docker, Docker Compose
- **Веб-сервер**: Nginx + PHP-FPM

## Установка и запуск

### 1. Клонирование репозитория
```bash
git clone <repository-url>
cd test_mx
```

### 2. Запуск контейнеров
```bash
make up
```

### 3. Выполнение миграций
```bash
make migrate
```

### 4. Открытие в браузере
```
http://localhost:8078
```

## Структура проекта

```
test_mx/
├── data/
│   ├── scripts/
│   │   └── run_migrations.php          # PHP скрипт миграций
│   └── sql/
│       ├── postgres/                   # SQL файлы для PostgreSQL
│       │   ├── 01_create_fias_table.sql
│       │   ├── 02_insert_data.sql
│       │   ├── 03_create_indexes.sql
│       │   └── 04_create_extensions.sql
│       └── mariadb/                    # SQL файлы для MariaDB
│           └── 01_create_plain_addresses_table.sql
├── src/
│   ├── Controller/
│   │   ├── BaseController.php
│   │   └── MainController.php
│   ├── Database/
│   │   ├── FiasDatabasePGSQLService.php
│   │   └── MariaDbService.php
│   ├── Model/
│   │   ├── FiasRecord.php
│   │   └── Pagination.php
│   └── Router/
│       └── Router.php
├── docker/
├── docker-compose.yaml
└── README.md
```

## Команды Makefile

```bash
make help          # Показать справку по командам
make build         # Собрать Docker образы
make up            # Запустить контейнеры
make down          # Остановить контейнеры
make restart       # Перезапустить контейнеры
make logs          # Показать логи
make migrate       # Запустить миграции БД
```

## API Endpoints

### Поиск адресов
```
GET /api/search?region=Ростовская&city=Москва&street=Ленина&house=1&page=1&limit=100
```

**Параметры:**
- `region` - название региона
- `city` - название города
- `street` - название улицы
- `house` - номер дома
- `page` - номер страницы (по умолчанию: 1)
- `limit` - количество записей на странице (по умолчанию: 100)

**Ответ:**
```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "current_page": 1,
    "total_pages": 10,
    "total_count": 1000,
    "limit": 100,
    "offset": 0,
    "has_next": true,
    "has_prev": false
  }
}
```

### Сохранение текущей страницы
```
POST /api/save-current-page
Content-Type: application/json

{
  "addresses": [
    {
      "aoid": "...",
      "formalname": "...",
      "region_name": "...",
      "city_name": "...",
      "street_name": "...",
      "house_name": "...",
      ...
    }
  ]
}
```

**Ответ:**
```json
{
  "success": true,
  "message": "Сохранено 5 адресов с текущей страницы",
  "saved_count": 5
}
```

### Сохранение всех результатов поиска
```
POST /api/save-all-results
Content-Type: application/json

{
  "searchParams": {
    "region": "Ростовская",
    "city": "Москва",
    "street": "Ленина",
    "house": "1"
  }
}
```

**Ответ:**
```json
{
  "success": true,
  "message": "Сохранено 1000 адресов из всех результатов поиска",
  "saved_count": 1000,
  "total_found": 1000
}
```

## Структура базы данных

### PostgreSQL (ФИАС)
- **Таблица**: `d_fias_addrobj`
- **Индексы**: Оптимизированные индексы для быстрого поиска
- **Данные**: Полная база адресов ФИАС

### MariaDB (Результаты поиска)
- **Таблица**: `plain_addresses`
- **Поля**:
  - `id` - автоинкрементный ID
  - `full_address` - полный адрес (VARCHAR 1024)
