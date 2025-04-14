<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

$response = [
    "status" => "success",
    "message" => "الـ API شغالة تمام ✅"
];

echo json_encode($response);
?>
