<?php
$host = 'localhost';
$dbname = 'searchimg';
$user = 'root';
$password = '';

$query = isset($_GET['query']) ? $_GET['query'] : '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM images WHERE name LIKE :query");
    $stmt->bindValue(':query', '%' . $query . '%');
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($results);
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
