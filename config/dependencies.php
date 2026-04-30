<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use App\Repository\Database;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        Database::class => function (ContainerInterface $c) {
            $host = $_ENV['DB_HOST'];
            $port = $_ENV['DB_PORT'];
            $dbName = $_ENV['DB_NAME'];
            $user = $_ENV['DB_USER'];
            $pass = $_ENV['DB_PASS'];

            $dsn = "mysql:host=$host;port=$port;dbname=$dbName;charset=utf8mb4";
            
            // Return PDO instance directly instead of wrapping? No, let's return our wrapper for strict rules
            return new Database($dsn, $user, $pass);
        },
        PDO::class => function (ContainerInterface $c) {
            // Also register pure PDO for direct injection if needed
            $db = $c->get(Database::class);
            return $db->getConnection();
        }
    ]);
};
