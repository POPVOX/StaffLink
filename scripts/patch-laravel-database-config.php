<?php

$path = __DIR__.'/../vendor/laravel/framework/config/database.php';

if (! file_exists($path)) {
    exit(0);
}

$contents = file_get_contents($path);

if ($contents === false) {
    fwrite(STDERR, "Unable to read {$path}\n");
    exit(1);
}

$search = "PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),";
$replace = "(defined('Pdo\\\\Mysql::ATTR_SSL_CA') ? \\Pdo\\Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA) => env('MYSQL_ATTR_SSL_CA'),";

if (! str_contains($contents, $search)) {
    exit(0);
}

$updated = str_replace($search, $replace, $contents, $count);

if ($count !== 2) {
    fwrite(STDERR, "Unexpected patch count for {$path}: {$count}\n");
    exit(1);
}

if (file_put_contents($path, $updated) === false) {
    fwrite(STDERR, "Unable to write {$path}\n");
    exit(1);
}
