<?php
declare(strict_types=1);

define('BASE_PATH', __DIR__);
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/stayu');

require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/config/firebase.php';
require_once BASE_PATH . '/helpers/session.php';
require_once BASE_PATH . '/helpers/rbac.php';
require_once BASE_PATH . '/helpers/validator.php';

// Autoload models and controllers
spl_autoload_register(function (string $class): void {
    $paths = [
        BASE_PATH . '/app/models/' . $class . '.php',
        BASE_PATH . '/app/controllers/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

startSession();

// Parse URL
$url = trim($_GET['url'] ?? '', '/');
$segments = array_filter(explode('/', $url));
$segments = array_values($segments);

$controller = $segments[0] ?? 'home';
$action     = $segments[1] ?? 'index';
$param      = $segments[2] ?? null;

// Route map: [controller_key => ControllerClass]
$routes = [
    'auth'          => 'AuthController',
    'student'       => 'StudentController',
    'owner'         => 'OwnerController',
    'admin'         => 'AdminController',
    'notifications' => 'NotificationController',
    'home'          => 'StudentController',
];

if (!array_key_exists($controller, $routes)) {
    http_response_code(404);
    require BASE_PATH . '/app/views/layouts/404.php';
    exit;
}

$controllerClass = $routes[$controller];
require_once BASE_PATH . '/app/controllers/' . $controllerClass . '.php';

$ctrl = new $controllerClass();

if (!method_exists($ctrl, $action)) {
    http_response_code(404);
    require BASE_PATH . '/app/views/layouts/404.php';
    exit;
}

$ctrl->$action($param);
