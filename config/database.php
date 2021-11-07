<?php

declare(strict_types=1);

use Illuminate\Database\DBAL\TimestampType;

return [

    "default" => \env("DB_CONNECTION", "mysql"),

    "connections" => [
        "mysql" => [
            "driver" => "mysql",
            "host" => \env("DB_HOST", "127.0.0.1"),
            "port" => \env("DB_PORT", 3306),
            "database" => \env("DB_DATABASE"),
            "username" => \env("DB_USERNAME"),
            "password" => \env("DB_PASSWORD", ""),
            "unix_socket" => \env("DB_SOCKET", ""),
            "charset" => "utf8mb4",
            "collation" => "utf8mb4_bin",
            "prefix" => "",
            "strict" => true,
            "engine" => "InnoDB",
            "timezone" => "+00:00",
        ],
    ],

    "migrations" => "migrations",

    "dbal" => [
        "types" => [
           "timestamp" => TimestampType::class,
        ],
    ],

];
