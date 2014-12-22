<?php

$connection = new SQLite3('bamidb');

try {
    $connection->query('DROP TABLE IF EXISTS USERS;');
    $connection->query('DROP TABLE IF EXISTS ORDERS;');
    $connection->query('DROP TABLE IF EXISTS TAGS;');

    $connection->query(file_get_contents('sql/users.sql'));
    $connection->query(file_get_contents('sql/orders.sql'));
    $connection->query(file_get_contents('sql/tags.sql'));
} catch (Exception $e) {
    echo 'Failed: ' . $e;
}

echo 'Success';
