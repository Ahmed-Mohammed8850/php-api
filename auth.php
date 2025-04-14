<?php
require_once '../config/db.php'; 
require_once '../utils/jwt.php'; 

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
array_shift($request); 
$id = array_shift($request);

if ($method == 'POST' && $id == 'login') {
    // POST /api/v1/auth/login
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$data['username']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($data['password'], $user['password'])) {
        $token = generateJWT($user['id']);
        echo json_encode(['token' => $token]);
    } else {
        echo json_encode(['message' => 'Invalid credentials']);
    }
} elseif ($method == 'GET' && $id == 'profile') {
    // GET /api/v1/auth/profile
    $user_id = getUserIdFromToken();
    if ($user_id) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($user);
    } else {
        echo json_encode(['message' => 'Invalid or missing token']);
    }
} elseif ($method == 'POST' && $id == 'refresh-token') {
    // POST /api/v1/auth/refresh-token
    $token = getBearerToken();
    if ($token) {
        $user_id = verifyJWT($token, true);
        if ($user_id) {
            $new_token = generateJWT($user_id);
            echo json_encode(['token' => $new_token]);
        } else {
            echo json_encode(['message' => 'Invalid token']);
        }
    } else {
        echo json_encode(['message' => 'Token required']);
    }
}
?>