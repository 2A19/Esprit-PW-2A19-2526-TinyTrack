<?php

abstract class Controller {

    protected function render(string $view, array $data = [], ?string $layout = null): void {
        extract($data, EXTR_SKIP);

        $viewFile = __DIR__ . '/../View/' . $view . '.php';
        if (!file_exists($viewFile)) {
            throw new RuntimeException("View not found: $viewFile");
        }

        if ($layout !== null) {
            ob_start();
            require $viewFile;
            $content = ob_get_clean();

            $layoutFile = __DIR__ . '/../View/' . $layout . '.php';
            if (!file_exists($layoutFile)) {
                throw new RuntimeException("Layout not found: $layoutFile");
            }
            require $layoutFile;
        } else {
            require $viewFile;
        }
    }

    protected function redirect(string $path): void {
        $base = '/TinyTrack';
        if (strpos($path, 'http') === 0) {
            header("Location: $path");
        } else {
            $path = '/' . ltrim($path, '/');
            header("Location: $base$path");
        }
        exit;
    }

    protected function isPost(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isGet(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    protected function input(string $key, $default = null) {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function requireAuth(?string $role = null): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
        if ($role !== null && ($_SESSION['role'] ?? null) !== $role) {
            http_response_code(403);
            echo "<h1>403 - Accès refusé</h1>";
            exit;
        }
    }

    protected function jsonResponse(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
