<?php
session_start();

// Автозагрузка классов
spl_autoload_register(function ($class) {
    require_once __DIR__ . '/app/' . str_replace('\\', '/', $class) . '.php';
});

// Получаем запрошенный путь
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = array_values(array_filter(explode('/', $path)));

// Обработка API-маршрутов
if (strpos($path, '/api/') === 0) {
    $apiController = new \controllers\ApiController();

    if ($path === '/api/random-poll') {
        $apiController->actionRandomPoll();
    } else {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'API endpoint not found']);
    }
    exit;
}

// Определение контроллера и действия
$controllerName = !empty($pathParts[0]) ? $pathParts[0] : 'main';
$actionName = !empty($pathParts[1]) ? $pathParts[1] : 'index';

// Обработка маршрутов для опросов
if ($controllerName === 'poll') {
    $controller = new \controllers\PollController();

    // Специальные маршруты, требующие авторизации
    if (in_array($actionName, ['my', 'create', 'edit', 'delete', 'publish', 'delete-question', 'delete-answer'])) {
        if (!isset($_SESSION['user'])) {
            header('Location: /auth/login');
            exit;
        }
    }

    // Обработка конкретных маршрутов
    switch (true) {
        case ($path === '/poll/my'):
            $controller->actionMyPolls();
            break;

        case ($path === '/poll/create'):
            $controller->actionCreate();
            break;

        case ($actionName === 'edit' && !empty($pathParts[2])):
            $controller->actionEdit($pathParts[2]);
            break;

        case ($actionName === 'delete' && !empty($pathParts[2])):
            $controller->actionDelete($pathParts[2]);
            break;

        case ($actionName === 'view' && !empty($pathParts[2])):
            $controller->actionView($pathParts[2]);
            break;

        case ($actionName === 'publish' && !empty($pathParts[2])):
            $controller->actionPublish($pathParts[2]);
            break;

        case ($actionName === 'delete-question' && !empty($pathParts[2])):
            $controller->actionDeleteQuestion($pathParts[2]);
            break;

        case ($actionName === 'delete-answer' && !empty($pathParts[2])):
            $controller->actionDeleteAnswer($pathParts[2]);
            break;

        case ($actionName === 'vote'):
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->actionVote();
            } else {
                header('HTTP/1.1 405 Method Not Allowed');
                echo 'Only POST method is allowed';
            }
            break;

        case ($actionName === 'index'):
            // Страница всех опросов - доступна без авторизации
            $controller->actionIndex();
            break;

        default:
            // Если маршрут не распознан
            header('HTTP/1.1 404 Not Found');
            echo 'Page not found';
            break;
    }
    exit;
}

// Общая маршрутизация для других контроллеров
$controllerClass = 'controllers\\' . ucfirst($controllerName) . 'Controller';

try {
    if (class_exists($controllerClass)) {
        $controller = new $controllerClass();
        $actionMethod = 'action' . ucfirst($actionName);

        if (method_exists($controller, $actionMethod)) {
            $controller->$actionMethod();
        } else {
            throw new Exception("Action not found");
        }
    } else {
        throw new Exception("Controller not found");
    }
} catch (Exception $e) {
    header('HTTP/1.1 404 Not Found');
    echo "Error: " . $e->getMessage();
}