<?php
declare(strict_types=1);

// Initialize Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../controllers/PostController.php';
require_once __DIR__ . '/../controllers/AdminController.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/ProfileController.php';

$page = (string) ($_GET['page'] ?? 'home');

try {
    switch ($page) {
        case 'login':
            $authController = new AuthController();
            $authController->login();
            break;

        case 'register':
            $authController = new AuthController();
            $authController->register();
            break;

        case 'logout':
            $authController = new AuthController();
            $authController->logout();
            break;

        case 'post':
            $controller = new PostController();
            $controller->show();
            break;

        case 'admin':
            $controller = new AdminController();
            $controller->index();
            break;

        case 'profile':
            $profileController = new ProfileController();
            $profileController->index();
            break;

        case 'home':
        default:
            $controller = new PostController();
            $controller->index();
            break;
    }
} catch (Throwable $e) {
    error_log("Uncaught Exception: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    http_response_code(500);
    require_once __DIR__ . '/../views/errors/500.php';
}