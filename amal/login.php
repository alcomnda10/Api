<?php
header('Access-Control-Allow-Origin:*');
header('Content-Type: Application/json');
header('Access-Control-Allow-Method: POST');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Request-with');
require "db.php";

$requestMethod = $_SERVER['REQUEST_METHOD'];



if ($requestMethod == "POST") {
    $datainput = json_decode(file_get_contents("php://input"));
    if (empty($datainput->email) || empty($datainput->password)) {
        echo json_encode(["status" => "error", "message" => "Email and password are required"]);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, password, api_key FROM users WHERE email = ?");
    $stmt->execute([$datainput->email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($datainput->password, $user["password"])) {
        echo json_encode(["status" => "error", "message" => "Invalid credentials"]);
        exit;
    }

    // إعادة API Key الحالي للمستخدم
    echo json_encode(["status" => "success", "message" => "Login successful", "api_key" => $user["api_key"]]);
} else {
    $data = [
        "statue" => "405",
        "message" => $requestMethod . " Method Not Allowed"
    ];
    header('HTTP/1.0 405 Method Not Allowed');
    echo json_encode($data);
}
