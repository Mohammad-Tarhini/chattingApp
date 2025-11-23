<?php 
require_once(__DIR__ . "/services/ResponseService.php");
require_once(__DIR__ . "/controllers/AuthoController.php");
require_once(__DIR__ . "/controllers/ChatController.php");
require_once(__DIR__ . "/controllers/UserController.php");

// Get the request URI without query string
$request = strtok($_SERVER['REQUEST_URI'], '?');

// Remove the base directory (/ChattingApp/server)
// This assumes your script is at /ChattingApp/server/index.php (or similar)
$base_path = '/ChattingApp/server';

if (strpos($request, $base_path) === 0) {
    $request = substr($request, strlen($base_path));
}

// Ensure it starts with /
if (empty($request) || $request[0] !== '/') {
    $request = '/' . $request;
}

$apis = [
    // AUTH ENDPOINTS
    '/auth/signup'          => ['controller' => 'AuthoController', 'method' => 'signUp'],
    '/auth/signin'          => ['controller' => 'AuthoController', 'method' => 'signIn'],

    // USER ENDPOINTS
    '/user/getUserInfo'     => ['controller' => 'UserController', 'method' => 'getUserInfo'],
    '/user/update'          => ['controller' => 'UserController', 'method' => 'updateUserInfo'],
    '/user/getAllUsers'     => ['controller' => 'UserController', 'method' => 'getAllUsers'],

    // CHAT ENDPOINTS
    '/chat/send'                   => ['controller' => 'ChatController', 'method' => 'sendMessage'],
    '/chat/receiveNew'             => ['controller' => 'ChatController', 'method' => 'receivingNewMessages'],
    '/chat/readMessage'            => ['controller' => 'ChatController', 'method' => 'readMessage'],
    '/chat/getAllWithUser'         => ['controller' => 'ChatController', 'method' => 'getAllMessageWithOtherUser'],
    '/chat/getAllForUser'          => ['controller' => 'ChatController', 'method' => 'getAllMessageForUser'],
    '/chat/catchUpAI'              => ['controller' => 'ChatController', 'method' => 'CatchUpWithAI'],
];

if (isset($apis[$request])) {
    $controller_name = $apis[$request]['controller']; 
    $method = $apis[$request]['method'];
    
    $controller = new $controller_name();
    if (method_exists($controller, $method)) {
        $controller->$method();
    } else {
        echo ResponseService::error("Error: Method {$method} not found in {$controller_name}");
    }
} else {
    // Show available routes in error for debugging
    echo ResponseService::error("Route Not Found: {$request}. Available routes: " . implode(', ', array_keys($apis)));
}
?>