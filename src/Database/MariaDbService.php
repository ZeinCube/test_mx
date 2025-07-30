<?php

namespace App\Database;

use PDO;
use PDOException;

class MariaDbService
{
    private PDO $pdo;
    private string $tableName = 'plain_addresses';

    public function __construct()
    {
        $this->connect();
    }

    private function connect(): void
    {
        $host = $_ENV['MARIADB_HOST'] ?? 'mariadb';
        $port = $_ENV['MARIADB_PORT'] ?? '3306';
        $dbname = $_ENV['MARIADB_DB_NAME'] ?? 'mx_test';
        $username = $_ENV['MARIADB_DB_USER'] ?? 'mx_test';
        $password = $_ENV['MARIADB_DB_PASS'] ?? 'mx_test';

        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        
        try {
            $this->pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new \RuntimeException("Ошибка подключения к MariaDB: " . $e->getMessage());
        }
    }

    public function saveAddresses(array $addresses): int
    {
        if (empty($addresses)) {
            return 0;
        }

        $sql = "
            INSERT INTO {$this->tableName} 
            (full_address) 
            VALUES (?)
        ";

        $stmt = $this->pdo->prepare($sql);
        $savedCount = 0;

        foreach ($addresses as $address) {
            try {
                $fullAddress = $this->buildFullAddress($address);

                $stmt->execute([$fullAddress]);

                $savedCount++;
            } catch (PDOException $e) {
                error_log("Ошибка сохранения адреса: " . $e->getMessage());
            }
        }

        return $savedCount;
    }

    private function buildFullAddress($address): string
    {
        $parts = [];

        if (!empty($address->region_name)) {
            $parts[] = ($address->region_shortname ?? '') . ' ' . $address->region_name;
        }

        if (!empty($address->city_name)) {
            $parts[] = ($address->city_shortname ?? '') . ' ' . $address->city_name;
        }

        if (!empty($address->street_name)) {
            $parts[] = ($address->street_shortname ?? '') . ' ' . $address->street_name;
        }

        if (!empty($address->house_name)) {
            $parts[] = ($address->house_shortname ?? '') . ' ' . $address->house_name;
        }

        return implode(', ', $parts);
    }

    public function testConnection(): bool
    {
        try {
            $this->pdo->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}