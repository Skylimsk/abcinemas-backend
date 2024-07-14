<?php
require 'db.php';

$db = new db();
$conn = $db->connect();

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

$sql = "SELECT id, title FROM movies";
$stmt = $conn->prepare($sql);
$stmt->execute();
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($movies);
?>
