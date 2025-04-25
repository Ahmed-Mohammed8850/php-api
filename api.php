<?php
header('Content-Type: application/json');

$host = 'localhost';
$db   = 'project';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
    exit;
}


$tables = [

    "admins" => "CREATE TABLE IF NOT EXISTS admins (
        id INT(11) NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY email (email)
    )",

    "customers" => "CREATE TABLE IF NOT EXISTS customers (
        id INT(11) NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        address VARCHAR(255),
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY email (email)
    )",

    "sellers" => "CREATE TABLE IF NOT EXISTS sellers (
        id INT(11) NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY email (email)
    )",

    "products" => "CREATE TABLE IF NOT EXISTS products (
        id INT(11) NOT NULL AUTO_INCREMENT,
        seller_id INT(11) NOT NULL,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        image VARCHAR(255),
        price DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (seller_id) REFERENCES sellers(id)
    )",

    "orders" => "CREATE TABLE IF NOT EXISTS orders (
        id INT(11) NOT NULL AUTO_INCREMENT,
        customer_id INT(11) NOT NULL,
        order_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        total DECIMAL(10,2) NOT NULL,
        PRIMARY KEY (id),
        FOREIGN KEY (customer_id) REFERENCES customers(id)
    )",

    "order_details" => "CREATE TABLE IF NOT EXISTS order_details (
        order_id INT(11) NOT NULL,
        product_id INT(11) NOT NULL,
        quantity INT(11) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        PRIMARY KEY (order_id, product_id),
        FOREIGN KEY (order_id) REFERENCES orders(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    )"
];


foreach ($tables as $name => $sql) {
    try {
        $pdo->exec($sql);
    } catch (PDOException $e) {
        echo json_encode(['error' => "Error creating table $name: " . $e->getMessage()]);
        exit;
    }
}

//  API Logic 

$tableList = ['admins', 'customers', 'sellers', 'products', 'orders', 'order_details'];

if (isset($_GET['table']) && in_array($_GET['table'], $tableList)) {
    $table = $_GET['table'];

    try {
        $stmt = $pdo->query("SELECT * FROM `$table`");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($data);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch data: ' . $e->getMessage()]);
    }
} else {
    echo json_encode([
        'message' => 'API Ready ✅',
        'usage' => 'Use ?table=admins OR customers OR sellers OR products OR orders OR order_details',
        'example' => 'http://localhost/your-folder/api.php?table=products'
    ]);
}
?>