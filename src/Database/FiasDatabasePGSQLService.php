<?php

namespace App\Database;

use App\Model\FiasRecord;
use PDO;
use PDOException;

class FiasDatabasePGSQLService
{
    private PDO $pdo;
    private string $tableName = 'd_fias_addrobj';

    public function __construct()
    {
        $this->connect();
    }

    private function connect(): void
    {
        $host = $_ENV['POSTGRES_HOST'] ?? 'postgres';
        $port = $_ENV['POSTGRES_PORT'] ?? '5432';
        $dbname = $_ENV['POSTGRES_DB_NAME'] ?? 'mx_test';
        $username = $_ENV['POSTGRES_DB_USER'] ?? 'mx_test';
        $password = $_ENV['POSTGRES_DB_PASS'] ?? 'mx_test';

        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        
        try {
            $this->pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new \RuntimeException("Ошибка подключения к базе данных: " . $e->getMessage());
        }
    }

    public function search(string $regionName = '', string $cityName = '', string $streetName = '', string $houseNumber = ''): array
    {
        $conditions = [];
        $params = [];
        
        // Определяем основной уровень поиска
        $mainLevel = 0;
        if (!empty($houseNumber)) {
            $mainLevel = 8; // Дом
        } elseif (!empty($streetName)) {
            $mainLevel = 7; // Улица
        } elseif (!empty($cityName)) {
            $mainLevel = 7; // При поиске по городу показываем улицы этого города
        } elseif (!empty($regionName)) {
            $mainLevel = 0; // При поиске по региону показываем города и улицы
        }
        
        $sql = "
            WITH main_data AS (
                SELECT 
                    aoid, formalname, regioncode, offname, postalcode,
                    aolevel, parentguid, aoguid, shortname, actstatus,
                    startdate, enddate
                FROM {$this->tableName}
                WHERE " . ($mainLevel > 0 ? "aolevel = :main_level" : "aolevel IN (4, 7)") . "
                AND actstatus = 1
            ),
            region_data AS (
                SELECT DISTINCT regioncode, formalname as region_name, shortname as region_shortname
                FROM {$this->tableName}
                WHERE aolevel = 1 AND actstatus = 1
            ),
            city_data AS (
                SELECT DISTINCT aoguid, formalname as city_name, shortname as city_shortname
                FROM {$this->tableName}
                WHERE aolevel = 4 AND actstatus = 1
            ),
            street_data AS (
                SELECT DISTINCT aoguid, formalname as street_name, shortname as street_shortname
                FROM {$this->tableName}
                WHERE aolevel = 7 AND actstatus = 1
            ),
            house_data AS (
                SELECT DISTINCT aoguid, formalname as house_name, shortname as house_shortname
                FROM {$this->tableName}
                WHERE aolevel = 8 AND actstatus = 1
            )
            SELECT 
                main.aoid, main.formalname, main.regioncode, main.offname, main.postalcode,
                main.aolevel, main.parentguid, main.aoguid, main.shortname, main.actstatus,
                main.startdate, main.enddate,
                region.region_name,
                region.region_shortname,
                city.city_name,
                city.city_shortname,
                street.street_name,
                street.street_shortname,
                house.house_name,
                house.house_shortname
            FROM main_data main
            LEFT JOIN region_data region ON region.regioncode = main.regioncode
            LEFT JOIN city_data city ON (
                CASE 
                    WHEN main.aolevel = 4 THEN city.aoguid = main.aoguid
                    ELSE city.aoguid = main.parentguid
                END
            )
            LEFT JOIN street_data street ON (
                CASE 
                    WHEN main.aolevel = 7 THEN street.aoguid = main.aoguid
                    ELSE street.aoguid = main.parentguid
                END
            )
            LEFT JOIN house_data house ON house.aoguid = main.aoguid
        ";
        
        if ($mainLevel > 0) {
            $params['main_level'] = $mainLevel;
        }
        
        // Добавляем условия поиска по порядку: регион -> город -> улица -> дом
        if (!empty($regionName)) {
            $conditions[] = "region.region_name ILIKE :region_name";
            $params['region_name'] = "%$regionName%";
        }
        
        if (!empty($cityName)) {
            $conditions[] = "city.city_name ILIKE :city_name";
            $params['city_name'] = "%$cityName%";
        }
        
        if (!empty($streetName)) {
            $conditions[] = "main.formalname ILIKE :street_name";
            $params['street_name'] = "%$streetName%";
        }
        
        if (!empty($houseNumber)) {
            $conditions[] = "main.formalname ILIKE :house_number";
            $params['house_number'] = "%$houseNumber%";
        }
        
        // Если нет условий поиска, возвращаем пустой массив
        if (empty($conditions)) {
            return [];
        }
        
        if (!empty($conditions)) {
            $whereClause = implode(' AND ', $conditions);
            $sql .= " WHERE {$whereClause}";
        }
        
        $sql .= " ORDER BY main.formalname";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $this->mapToFiasRecordsWithHierarchy($stmt->fetchAll());
    }

    private function mapToFiasRecordsWithHierarchy(array $data): array
    {
        return array_map(function($row) {
            // Создаем базовую запись
            $record = FiasRecord::fromArray($row);
            
            // Добавляем информацию об иерархии из JOIN'ов
            $record->region_name = $row['region_name'] ?? '';
            $record->region_shortname = $row['region_shortname'] ?? '';
            $record->city_name = $row['city_name'] ?? '';
            $record->city_shortname = $row['city_shortname'] ?? '';
            $record->street_name = $row['street_name'] ?? '';
            $record->street_shortname = $row['street_shortname'] ?? '';
            $record->house_name = $row['house_name'] ?? '';
            $record->house_shortname = $row['house_shortname'] ?? '';
            
            return $record;
        }, $data);
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