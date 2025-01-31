<?php
header("Content-Type: application/json");
require "db.php";

// الحصول على البيانات من الـ POST
$data = json_decode(file_get_contents("php://input"));

// التحقق من وجود الـ API Key في البيانات المدخلة
if (empty($data->api_key)) {
    echo json_encode(["status" => "error", "message" => "API Key is required"]);
    exit;
}

// التحقق من صحة الـ API Key في قاعدة البيانات
$apiKey = $data->api_key;
try {
    $stmt = $conn->prepare("SELECT id FROM users WHERE api_key = ?");
    $stmt->execute([$apiKey]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // إذا لم يتم العثور على الـ API Key في قاعدة البيانات
    if (!$user) {
        echo json_encode(["status" => "error", "message" => "Invalid API Key"]);
        exit;
    }

    // إذا كانت الـ API Key صحيحة
    echo json_encode(["status" => "success", "message" => "API Key is valid"]);
} catch (PDOException $e) {
    // في حال حدوث خطأ في قاعدة البيانات
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    exit;
}
