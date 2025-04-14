<?php
require_once '../config/db.php'; 
require_once '../utils/jwt.php'; 

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
array_shift($request); 
$id = array_shift($request);



if ($method == 'GET' && empty($id)) {
    // GET /api/v1/cart
    $user_id = getUserIdFromToken();
    if ($user_id) {
        $stmt = $pdo->prepare("SELECT c.id, c.quantity, p.* FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
        $stmt->execute([$user_id]);
        $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($cart_items);
    } else {
        echo json_encode(['message' => 'Authentication required']);
    }
} elseif ($method == 'POST') {
    // POST /api/v1/cart
    $user_id = getUserIdFromToken();
    if ($user_id) {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $data['product_id']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            $new_quantity = $existing['quantity'] + $data['quantity'];
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt->execute([$new_quantity, $existing['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $data['product_id'], $data['quantity']]);
        }
        echo json_encode(['message' => 'Product added to cart']);
    } else {
        echo json_encode(['message' => 'Authentication required']);
    }
} elseif ($method == 'PUT' && !empty($id)) {
    // PUT /api/v1/cart/{id}
    $user_id = getUserIdFromToken();
    if ($user_id) {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$data['quantity'], $id, $user_id]);
        echo json_encode(['message' => 'Cart item updated']);
    } else {
        echo json_encode(['message' => 'Authentication required']);
    }
} elseif ($method == 'DELETE' && !empty($id)) {
    // DELETE /api/v1/cart/{id}
    $user_id = getUserIdFromToken();
    if ($user_id) {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        echo json_encode(['message' => 'Cart item deleted']);
    } else {
        echo json_encode(['message' => 'Authentication required']);
    }
}
?>