<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;
use PDOException;
use Exception;

class Database
{
    private PDO $connection;

    public function __construct(string $dsn, string $user, string $pass)
    {
        try {
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $this->connection = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Loglanmalı ancak son kullanıcıya detay verilmemeli (Kurallar md)
            throw new Exception("Veritabanı bağlantı hatası.");
        }
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}
