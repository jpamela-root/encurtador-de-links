<?php
require 'vendor/autoload.php'; // Autoload do Composer para o MongoDB Driver

function generateShortCode(): string {
    return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6);
}

function getMongoClient() {
    return (new MongoDB\Client("mongodb://localhost:27017"))->shortener->links;
}

function storeLink(string $originalUrl): string {
    $collection = getMongoClient();

    $shortCode = generateShortCode();
    $collection->insertOne([
        'original_url' => $originalUrl,
        'short_code' => $shortCode,
        'created_at' => new MongoDB\BSON\UTCDateTime(),
    ]);

    return $shortCode;
}

function getOriginalUrl(string $shortCode): ?string {
    $collection = getMongoClient();
    $document = $collection->findOne(['short_code' => $shortCode]);

    return $document['original_url'] ?? null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $originalUrl = $data['url'] ?? null;

    if ($originalUrl) {
        echo json_encode(['shortCode' => storeLink($originalUrl)]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid URL']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['code'])) {
    $originalUrl = getOriginalUrl($_GET['code']);

    if ($originalUrl) {
        header("Location: $originalUrl");
        exit;
    } else {
        http_response_code(404);
        echo "Link not found";
    }
}
?>
