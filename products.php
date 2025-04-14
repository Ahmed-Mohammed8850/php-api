<?php
require_once '../config/db.php'; 
require_once '../utils/jwt.php'; 

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
array_shift($request); 
$id = array_shift($request);

if ($method == 'GET' && empty($id)) {
    $stmt = $pdo->query("SELECT * FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($products);
} elseif ($method == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("INSERT INTO products (name, slug, description, price, category_id, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$data['name'], $data['slug'], $data['description'], $data['price'], $data['category_id'], $data['image']]);
    echo json_encode(['message' => 'Product created']);
} elseif ($method == 'GET' && !empty($id)) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($product);
}elseif ($method == 'PUT' && !empty($id)) {
    // PUT /api/v1/products/{id}
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("UPDATE products SET name = ?, slug = ?, description = ?, price = ?, category_id = ?, image = ? WHERE id = ?");
    $stmt->execute([$data['name'], $data['slug'], $data['description'], $data['price'], $data['category_id'], $data['image'], $id]);
    echo json_encode(['message' => 'Product updated']);
} elseif ($method == 'DELETE' && !empty($id)) {
    // DELETE /api/v1/products/{id}
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['message' => 'Product deleted']);
} elseif ($method == 'GET' && $id == 'related') {
    // GET /api/v1/products/{id}/related
    $product_id = $request[0];
    $stmt = $pdo->prepare("SELECT category_id FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $category_id = $stmt->fetchColumn();
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ?");
    $stmt->execute([$category_id, $product_id]);
    $related = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($related);
} elseif ($method == 'GET' && $id == 'slug') {
    // GET /api/v1/products/slug/{slug}
    $slug = $request[0];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE slug = ?");
    $stmt->execute([$slug]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($product);
} elseif ($method == 'GET' && $id == 'slug' && isset($request[1]) && $request[1] == 'related') {
    // GET /api/v1/products/slug/{slug}/related
    $slug = $request[0];
    $stmt = $pdo->prepare("SELECT category_id FROM products WHERE slug = ?");
    $stmt->execute([$slug]);
    $category_id = $stmt->fetchColumn();
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND slug != ?");
    $stmt->execute([$category_id, $slug]);
    $related = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($related);
}
?>