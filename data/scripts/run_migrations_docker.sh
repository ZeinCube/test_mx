#!/bin/bash

# Скрипт для выполнения миграций PostgreSQL через Docker
# Использование: ./run_migrations_docker.sh [container_name]

set -e

CONTAINER_NAME=${1:-"test_mx-postgres-1"}
DB_NAME=${2:-"mx_test"}
DB_USER=${3:-"mx_test"}
DB_PASS=${4:-"mx_test"}

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

error() {
    echo -e "${RED}[ERROR] $1${NC}"
}

warning() {
    echo -e "${YELLOW}[WARNING] $1${NC}"
}

info() {
    echo -e "${BLUE}[INFO] $1${NC}"
}

check_container() {
    log "Проверка контейнера PostgreSQL..."
    
    if docker ps --format "table {{.Names}}" | grep -q "$CONTAINER_NAME"; then
        log "Контейнер $CONTAINER_NAME найден и запущен"
    else
        error "Контейнер $CONTAINER_NAME не найден или не запущен"
        error "Убедитесь, что Docker контейнеры запущены: docker compose up -d"
        exit 1
    fi
}

create_database() {
    log "Проверка существования базы данных $DB_NAME..."
    
    if docker exec "$CONTAINER_NAME" psql -U "$DB_USER" -d "postgres" -c "SELECT 1 FROM pg_database WHERE datname='$DB_NAME';" | grep -q 1; then
        log "База данных $DB_NAME уже существует"
    else
        log "Создание базы данных $DB_NAME..."
        docker exec "$CONTAINER_NAME" psql -U "$DB_USER" -d "postgres" -c "CREATE DATABASE $DB_NAME;"
        log "База данных $DB_NAME создана"
    fi
}

execute_sql_file() {
    local file="$1"
    local description="$2"
    
    if [ -f "$file" ]; then
        log "Выполнение: $description"
        log "Файл: $file"
        
        local container_file="/tmp/$(basename "$file")"
        docker cp "$file" "$CONTAINER_NAME:$container_file"
        
        if docker exec "$CONTAINER_NAME" psql -U "$DB_USER" -d "$DB_NAME" -f "$container_file"; then
            log "✓ $description выполнено успешно"
        else
            error "✗ Ошибка при выполнении $description"
            exit 1
        fi
        
        docker exec "$CONTAINER_NAME" rm -f "$container_file"
    else
        warning "Файл $file не найден, пропускаем"
    fi
}

main() {
    log "Запуск миграций PostgreSQL через Docker"
    log "Контейнер: $CONTAINER_NAME"
    log "База данных: $DB_NAME"
    log "Пользователь: $DB_USER"
    
    check_container
    create_database
    
    SQL_DIR="$(dirname "$0")/../sql"
    
    log "Начинаем выполнение миграций..."
    
    execute_sql_file "$SQL_DIR/01_create_fias_table.sql" "Создание таблицы d_fias_addrobj"
    
    execute_sql_file "$SQL_DIR/02_insert_sample_data.sql" "Вставка тестовых данных"
    
    execute_sql_file "$SQL_DIR/04_create_extensions.sql" "Создание расширений PostgreSQL"
    
    execute_sql_file "$SQL_DIR/03_create_indexes.sql" "Создание индексов для оптимизации"
    
    log "Все миграции выполнены успешно!"
    
    info "Статистика базы данных:"
    docker exec "$CONTAINER_NAME" psql -U "$DB_USER" -d "$DB_NAME" -c "
        SELECT 
            schemaname,
            tablename,
            attname,
            n_distinct,
            correlation
        FROM pg_stats 
        WHERE tablename = 'd_fias_addrobj' 
        ORDER BY schemaname, tablename, attname;
    "
    
    info "Количество записей в таблице d_fias_addrobj:"
    docker exec "$CONTAINER_NAME" psql -U "$DB_USER" -d "$DB_NAME" -c "
        SELECT COUNT(*) as total_records FROM d_fias_addrobj;
    "
}

show_help() {
    echo "Использование: $0 [container_name] [database_name] [username] [password]"
    echo ""
    echo "Параметры:"
    echo "  container_name - Имя Docker контейнера PostgreSQL (по умолчанию: test_mx-postgres-1)"
    echo "  database_name  - Имя базы данных (по умолчанию: test_mx)"
    echo "  username      - Имя пользователя (по умолчанию: postgres)"
    echo "  password      - Пароль (по умолчанию: postgres)"
    echo ""
    echo "Примеры:"
    echo "  $0"
    echo "  $0 my-postgres-container"
    echo "  $0 my-postgres-container my_database postgres mypass"
    echo ""
    echo "Примечание: Убедитесь, что Docker контейнеры запущены: docker compose up -d"
}

if [ "$1" = "-h" ] || [ "$1" = "--help" ]; then
    show_help
    exit 0
fi

main "$@"