<?php

require_once '../vendor/autoload.php';
use \Firebase\JWT\JWT;


$host = 'localhost';
$dbname = 'ecommerce_db';
$username = 'root'; 
$password = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['error' => 'Connection failed: ' . $e->getMessage()]));
}

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
$resource = array_shift($request);
$id = array_shift($request);


function generateJWT($user_id) {
    $secret = 'your_secret_key'; 
    $payload = [
        'iat' => time(),
        'exp' => time() + 3600,
        'sub' => $user_id
    ];
    return JWT::encode($payload, $secret, 'HS256');
}

function verifyJWT($token, $allow_expired = false) {
    $secret = 'your_secret_key'; 
    try {
        $decoded = JWT::decode($token, new \Firebase\JWT\Key($secret, 'HS256'));
        return $decoded->sub;
    } catch (\Firebase\JWT\ExpiredException $e) {
        if ($allow_expired) {
            
            $parts = explode('.', $token);
            if (count($parts) === 3) {
                $payload = JWT::jsonDecode(JWT::urlsafeB64Decode($parts[1]));
                return $payload->sub;
            }
            return false;
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

function getBearerToken() {
    $headers = apache_request_headers();
    if (isset($headers['Authorization'])) {
        $matches = [];
        preg_match('/Bearer (.*)/', $headers['Authorization'], $matches);
        return $matches[1] ?? null;
    }
    return null;
}

function getUserIdFromToken() {
    $token = getBearerToken();
    if ($token) {
        return verifyJWT($token);
    }
    return null;
}
?>