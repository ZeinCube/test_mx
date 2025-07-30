<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use PDO;
use PDOException;

class DatabaseMigration
{
    private PDO $pgsqlPdo;
    private PDO $mariadbPdo;
    
    public function __construct()
    {
        $this->connectDatabases();
    }
    
    private function connectDatabases(): void
    {
        $pgHost = $_ENV['POSTGRES_HOST'] ?? 'postgres';
        $pgPort = $_ENV['POSTGRES_PORT'] ?? '5432';
        $pgDbname = $_ENV['POSTGRES_DB_NAME'] ?? 'mx_test';
        $pgUsername = $_ENV['POSTGRES_DB_USER'] ?? 'mx_test';
        $pgPassword = $_ENV['POSTGRES_DB_PASS'] ?? 'mx_test';
        
        $pgDsn = "pgsql:host=$pgHost;port=$pgPort;dbname=$pgDbname";
        
        try {
            $this->pgsqlPdo = new PDO($pgDsn, $pgUsername, $pgPassword, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            echo "✅ Подключение к PostgreSQL успешно\n";
        } catch (PDOException $e) {
            echo "❌ Ошибка подключения к PostgreSQL: " . $e->getMessage() . "\n";
            exit(1);
        }
        
        $mariaHost = $_ENV['MARIADB_HOST'] ?? 'mariadb';
        $mariaPort = $_ENV['MARIADB_PORT'] ?? '3306';
        $mariaDbname = $_ENV['MARIADB_DB_NAME'] ?? 'mx_test';
        $mariaUsername = $_ENV['MARIADB_DB_USER'] ?? 'mx_test';
        $mariaPassword = $_ENV['MARIADB_DB_PASS'] ?? 'mx_test';
        
        $mariaDsn = "mysql:host=$mariaHost;port=$mariaPort;dbname=$mariaDbname;charset=utf8mb4";
        
        try {
            $this->mariadbPdo = new PDO($mariaDsn, $mariaUsername, $mariaPassword, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            echo "✅ Подключение к MariaDB успешно\n";
        } catch (PDOException $e) {
            echo "❌ Ошибка подключения к MariaDB: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
    
    public function runMigrations(): void
    {
        echo "\n🚀 Запуск миграций...\n";
        
        $this->runPostgresMigrations();
        
        $this->runMariaDbMigrations();
        
        echo "\n✅ Все миграции выполнены успешно!\n";
    }
    
    private function runPostgresMigrations(): void
    {
        echo "\n📊 Выполнение миграций PostgreSQL...\n";
        
        $sqlDir = __DIR__ . '/../sql/postgres';
        $sqlFiles = $this->getSqlFiles($sqlDir);
        
        foreach ($sqlFiles as $file) {
            $this->executeSqlFile($this->pgsqlPdo, $file, 'PostgreSQL');
        }
    }
    
    private function runMariaDbMigrations(): void
    {
        echo "\n🗄️  Выполнение миграций MariaDB...\n";
        
        $sqlDir = __DIR__ . '/../sql/mariadb';
        $sqlFiles = $this->getSqlFiles($sqlDir);
        
        foreach ($sqlFiles as $file) {
            $this->executeSqlFile($this->mariadbPdo, $file, 'MariaDB');
        }
    }
    
    private function getSqlFiles(string $directory): array
    {
        if (!is_dir($directory)) {
            echo "⚠️  Директория $directory не найдена\n";
            return [];
        }
        
        $files = glob($directory . '/*.sql');
        sort($files);
        
        return $files;
    }
    
    private function executeSqlFile(PDO $pdo, string $filePath, string $dbType): void
    {
        $fileName = basename($filePath);
        
        try {
            $sql = file_get_contents($filePath);
            if ($sql === false) {
                echo "❌ Ошибка чтения файла: $fileName\n";
                return;
            }
            
            // Разбиваем SQL на отдельные запросы
            $queries = $this->splitSqlQueries($sql);
            
            foreach ($queries as $query) {
                $query = trim($query);
                if (!empty($query)) {
                    $pdo->exec($query);
                }
            }
            
            echo "✅ $dbType: Файл $fileName выполнен успешно\n";
            
        } catch (PDOException $e) {
            echo "❌ $dbType: Ошибка выполнения $fileName: " . $e->getMessage() . "\n";
        }
    }
    
    private function splitSqlQueries(string $sql): array
    {
        $sql = preg_replace('/--.*$/m', '', $sql);
        
        $queries = [];
        $currentQuery = '';
        $inString = false;
        $stringChar = '';
        
        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];
            
            if (!$inString && ($char === "'" || $char === '"')) {
                $inString = true;
                $stringChar = $char;
                $currentQuery .= $char;
            } elseif ($inString && $char === $stringChar) {
                if ($i > 0 && $sql[$i - 1] !== '\\') {
                    $inString = false;
                    $stringChar = '';
                }
                $currentQuery .= $char;
            } elseif (!$inString && $char === ';') {
                $currentQuery .= $char;
                $queries[] = trim($currentQuery);
                $currentQuery = '';
            } else {
                $currentQuery .= $char;
            }
        }
        
        if (!empty(trim($currentQuery))) {
            $queries[] = trim($currentQuery);
        }
        
        return $queries;
    }
    
    public function showDatabaseStats(): void
    {
        echo "\n📈 Статистика баз данных: \n";
        
        try {
            $result = $this->pgsqlPdo->query("SELECT COUNT(*) as total FROM d_fias_addrobj WHERE actstatus = 1");
            $total = $result->fetch()['total'];
            echo "PostgreSQL: $total активных записей в d_fias_addrobj\n";
        } catch (PDOException $e) {
            echo "Ошибка получения статистики PostgreSQL: " . $e->getMessage() . "\n";
        }
        
        try {
            $result = $this->mariadbPdo->query("SELECT COUNT(*) as total FROM plain_addresses");
            $total = $result->fetch()['total'];
            echo "MariaDB: $total сохраненных адресов в plain_addresses\n";
        } catch (PDOException $e) {
            echo "Ошибка получения статистики MariaDB: " . $e->getMessage() . "\n";
        }
    }
}

$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

$migration = new DatabaseMigration();
$migration->runMigrations();
$migration->showDatabaseStats(); 