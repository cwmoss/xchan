<?php

use xchan\sqlite;

require __DIR__ . '/vendor/autoload.php';

$factory = new Clue\React\SQLite\Factory();
$db = $factory->openLazy(__DIR__ . '/var/app.db');

$store = new sqlite($db);

$store->insert("posts", ['title' => 'nr. 1', 'body' => 'hello']);

foreach ($store->select('SELECT * from posts') as $row) {
    print_r($row);
}
$db->quit();
