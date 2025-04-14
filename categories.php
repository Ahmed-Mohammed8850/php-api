<?php
require_once '../config/db.php'; 
require_once '../utils/jwt.php'; 

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
array_shift($request); 
$id = array_shift($request);
if ($method == 'GET' && empty($id)) {
    // GET /api/v1/categories
    $stmt = $pdo->query("SELECT * FROM categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($categories);
} elseif ($method == 'POST') {
    // POST /api/v1/categories
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
    $stmt->execute([$data['name'], $data['slug']]);
    echo json_encode(['message' => 'Category created']);
} elseif ($method == 'GET' && !empty($id)) {
    // GET /api/v1/categories/{id}
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($category);
} elseif ($method == 'PUT' && !empty($id)) {
    // PUT /api/v1/categories/{id}
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?");
    $stmt->execute([$data['name'], $data['slug'], $id]);
    echo json_encode(['message' => 'Category updated']);
} elseif ($method == 'DELETE' && !empty($id)) {
    // DELETE /api/v1/categories/{id}
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['message' => 'Category deleted']);
} elseif ($method == 'GET' && $id == 'slug') {
    // GET /api/v1/categories/slug/{slug}
    $slug = $request[0];
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
    $stmt->execute([$slug]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($category);
} elseif ($method == 'GET' && isset($request[0]) && $request[0] == 'products') {
    // GET /api/v1/categories/{id}/products
    $category_id = $id;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($products);
}
?>