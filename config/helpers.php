<?php

function requireAuth($roles = []) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /TinyTrack/login');
        exit;
    }
    if (!empty($roles) && !in_array($_SESSION['user_role'], $roles)) {
        header('Location: /TinyTrack/login');
        exit;
    }
}

function render($viewPath, $data = []) {
    extract($data);
    require __DIR__ . '/../View/' . $viewPath;
}

function redirect($url) {
    header('Location: /TinyTrack/' . ltrim($url, '/'));
    exit;
}
