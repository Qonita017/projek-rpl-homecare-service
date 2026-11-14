<?php
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Config/Database.php';

$db = new Database();
$pdo = $db->getConnection();
$stmt = $pdo->query("SELECT id, name, brand, category, repair_price FROM equipment ORDER BY id ASC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Total equipment: " . count($rows) . "\n";
foreach ($rows as $r) {
    echo "ID: {$r['id']} | Name: {$r['name']} | Brand: {$r['brand']} | Category: {$r['category']} | Price: Rp " . number_format($r['repair_price'], 0, ',', '.') . "\n";
}
