<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
$resource = array_shift($request);

switch ($resource) {
    case 'products':
        require_once 'products/products.php';
        break;
    case 'users':
        require_once 'users/users.php';
        break;
    case 'auth':
        require_once 'auth/auth.php';
        break;
    case 'categories':
        require_once 'categories/categories.php';
        break;
    case 'files':
        require_once 'files/files.php';
        break;
    case 'cart':
        require_once 'cart/cart.php';
        break;
    case 'orders':
        require_once 'orders/orders.php';
        break;
    default:
        echo json_encode(['message' => 'Resource not found']);
        break;
}
?>