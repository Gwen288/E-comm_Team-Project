<?php
/*
 * Shows a product photo kept in the database (see core/image_store.php).
 * Used only when the server does not allow saving photos into images/products.
 */
require_once __DIR__ . '/core/core.php';
require_once __DIR__ . '/core/image_store.php';

$name = basename((string) ($_GET['f'] ?? ''));
$image = $name !== '' ? (new ImageStore())->get($name) : null;

if (!$image || !preg_match('#^image/(jpeg|png|gif|webp)$#', $image['mime_type'])) {
    http_response_code(404);
    exit;
}

$etag = '"' . md5($name . $image['created_at']) . '"';
header('Content-Type: ' . $image['mime_type']);
header('Cache-Control: public, max-age=604800');
header('ETag: ' . $etag);
header('X-Content-Type-Options: nosniff');

if (trim($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
    http_response_code(304);
    exit;
}

header('Content-Length: ' . strlen($image['image_data']));
echo $image['image_data'];
