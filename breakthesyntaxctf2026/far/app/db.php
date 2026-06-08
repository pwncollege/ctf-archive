<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Create tables from schema file
    $schema_file = __DIR__ . '/schema.sql';
    if (file_exists($schema_file)) {
        $schema = file_get_contents($schema_file);
        $pdo->exec($schema);
    }

    // Auto-populate data
    $flag_file = __DIR__ . '/.data_populated';
    if (!file_exists($flag_file)) {
        $populate_script = __DIR__ . '/populate_test_data.php';
        if (file_exists($populate_script)) {
            $process = new Process(['php', $populate_script]);
            $process->start();
        }
    }

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>