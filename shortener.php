<?php
declare(strict_types=1);

function generateShortCode(): string {
    return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6);
}

function storeLink(string $originalUrl): string {
    $pdo = new PDO('mysql:host=localhost;dbname=shortener', 'root', 'password');
    $shortCode = generateShortCode();

    $stmt = $pdo->prepare("INSERT INTO links (original_url, short_code) VALUES (:original_url, :short_code)");
    $stmt->execute([
        ':original_url' => $originalUrl,
        ':short_code' => $shortCode,
    ]);

    return $shortCode;
}

function getOriginalUrl(string $shortCode): ?string {
    $pdo = new PDO('mysql:host=localhost;dbname=shortener', 'root', 'password');

    $stmt = $pdo->prepare("SELECT original_url FROM links WHERE short_code = :short_code");
    $stmt->execute([':short_code' => $shortCode]);

    return $stmt->fetchColumn() ?: null;
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


