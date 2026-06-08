<?php
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Access denied. This script can only be run from command line.");
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/vendor/autoload.php';

$faker = Faker\Factory::create();

$flag_file = __DIR__ . '/.data_populated';
if (file_exists($flag_file)) {
    echo "Test data already populated (flag file exists). Skipping.\n";
    exit;
}

$stmt = $pdo->query("SELECT COUNT(*) as count FROM clients");
$row = $stmt->fetch();
if ($row['count'] > 0) {
    echo "Test data already exists. Skipping population.\n";
    touch($flag_file);
    exit;
}

// Insert test clients
$clients = [];
for ($i = 0; $i < 5; $i++) {
    $suffixes = ['Inc', 'Co', 'Corp'];
    $companyName = ucfirst($faker->word()) . ' ' . $faker->randomElement($suffixes);
    $clients[] = [
        $companyName,
        $faker->randomFloat(2, 0.10, 0.25),
        $faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d')
    ];
}

$stmt = $pdo->prepare("INSERT INTO clients (company_name, cost_per_archive_gb, registration_date) VALUES (?, ?, ?)");
foreach ($clients as $client) {
    $stmt->execute($client);
}

$client_map = [];
$stmt = $pdo->query("SELECT id, company_name FROM clients");
while ($row = $stmt->fetch()) {
    $client_map[$row['company_name']] = $row['id'];
}

// Insert test archives
$archives = [];
foreach ($client_map as $company_name => $client_id) {
    $num_archives = $faker->numberBetween(2, 5);
    for ($i = 0; $i < $num_archives; $i++) {
        $archives[] = [
            $company_name,
            $faker->randomFloat(1, 20.0, 200.0),
            $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d')
        ];
    }
}

$stmt = $pdo->prepare("INSERT INTO archives (client_id, size, creation_date) VALUES (?, ?, ?)");
foreach ($archives as $archive) {
    $company_name = $archive[0];
    $client_id = $client_map[$company_name];
    $stmt->execute([$client_id, $archive[1], $archive[2]]);
}

echo "Test data populated successfully!\n";
echo "Clients inserted: " . count($clients) . "\n";
echo "Archives inserted: " . count($archives) . "\n";

touch($flag_file);
?>
