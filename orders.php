<?php
require_once '../config/db.php'; 
require_once '../utils/jwt.php'; 

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
array_shift($request); 
$id = array_shift($request);

if ($method == 'GET' && empty($id)) {
    // GET /api/v1/orders 
    $stmt = $pdo->query("SELECT * FROM orders");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($orders);
} elseif ($method == 'POST') {
    // POST /api/v1/orders
    $user_id = getUserIdFromToken();
    if (!$user_id) {
        echo json_encode(['message' => 'Authentication required']);
    }
    $data = json_decode(file_get_contents('php://input'), true);
    $total = $data['total'] ?? 0;
    $status = $data['status'] ?? 'pending'; 
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $total, $status]);
    echo json_encode(['message' => 'Order created', 'order_id' => $pdo->lastInsertId()]);
} elseif ($method == 'GET' && !empty($id)) {
    // GET /api/v1/orders/{id} : 
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($order);
} elseif ($method == 'PUT' && !empty($id)) {
    // PUT /api/v1/orders/{id} 
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("UPDATE orders SET total = ?, status = ? WHERE id = ?");
    $stmt->execute([$data['total'], $data['status'], $id]);
    echo json_encode(['message' => 'Order updated']);
} elseif ($method == 'DELETE' && !empty($id)) {
    // DELETE /api/v1/orders/{id} 
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['message' => 'Order deleted']);
}else {
    echo json_encode(['message' => 'Method not allowed']);
}

?>