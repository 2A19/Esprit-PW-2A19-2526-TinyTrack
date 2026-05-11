<?php
session_start();
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/core/Model.php';
$router = require __DIR__ . '/routes.php';

// Resolve URL from .htaccess rewrite OR REQUEST_URI (PHP -S without rewrite).
$url = $_GET['url'] ?? null;
if ($url === null) {
    $url = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
}
// Strip leading slashes and the TinyTrack/ subdir prefix in any form.
$url = ltrim($url, '/');
if (str_starts_with($url, 'TinyTrack/')) $url = substr($url, strlen('TinyTrack/'));
elseif ($url === 'TinyTrack')             $url = '';
$url = '/' . ltrim($url, '/');

try {
    $router->dispatch($url, $_SERVER['REQUEST_METHOD'] ?? 'GET');
} catch (Throwable $e) {
    http_response_code(500);
    echo "<pre>" . htmlspecialchars($e->getMessage() . "\n" . $e->getTraceAsString()) . "</pre>";
}
