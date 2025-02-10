<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PATCH,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require 'connect.php';
require 'functions.php';
echo 'API для управления задачами.';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$uriParts = explode('/', $uri);
array_shift($uriParts);
$taskType = $uriParts[0] ?? null;
$taskId = $uriParts[1] ?? null;

try {
    switch ($method) {
        case 'GET':
            if ($taskType === 'tasks') {
                if (isset($taskId)) {
                    getTask($pdo, $taskId);
                } else {
                    getTasks($pdo);
                }
            }
            break;

        case 'POST':
            if ($taskType === 'tasks') {
                addTask($pdo, $_POST);
            }
            break;

        case 'PUT':
            if ($taskType === 'tasks') {
                if (isset($taskId)) {
                    $data = file_get_contents('php://input');
                    $data = json_decode($data, true);
                    updateTask($pdo, $taskId, $data);
                }
            }
            break;

        case 'DELETE':
            if ($taskType === 'tasks') {
                if (isset($taskId)) {
                    deleteTask($pdo, $taskId);
                }
            }
            break;

        default:
            throw new Exception('Method not supported', 405);
    }
} catch (Exception $e) {
    http_response_code($e->getCode());
    echo json_encode(['error' => $e->getMessage()]);
}
