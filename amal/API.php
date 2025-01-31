<?php

header('Access-Control-Allow-Origin:*');
header('Content-Type: Application/json');
header('Access-Control-Allow-Method: POST GET DELETE PUT');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Request-with');

require('db.php');

$requestMethod = $_SERVER['REQUEST_METHOD'];

switch ($requestMethod) {
    case 'GET':
        if ($requestMethod == 'GET') {
            if (isset($_GET['id'])) {
                $customer = getuser($_GET);
                echo $customer;
            } else {
                $userList = getuserList();
                echo $userList;
            }
        } else {
            $data = [
                "statues" => "error",
                "message" => $requestMethod . "Method Not Allowed"
            ];
            header('HTTP/1.0 error Method Not Allowed');
            echo json_encode($data);
        }
        break;
    case 'POST':
        error_reporting(0);


        if ($requestMethod == 'POST') {
            $inputdata = json_decode(file_get_contents("php://input"));
            $hashedPassword = password_hash($inputdata->password, PASSWORD_DEFAULT);

            if (empty($inputdata)) {
                $createuser = createuser($_POST);
            } else {
                $createuser = createuser($inputdata);
            }
            echo $createuser;
        } else {
            $data = [
                'status' => 405,

                'message' => $requestMethod . "Method Not Allowed",

            ];
            header('HTTP/1.0 405 Method Not Allowed');
            echo json_encode($data);
        }
        break;

    default:
        echo json_encode(['message' => $requestMethod . ' Method Not Allowed']);

        break;
}



function error422($message)
{

    $data = [
        'status' => 422,

        'message' => $message,

    ];
    header('HTTP/1.0 422 Unprocessable Entity');
    echo json_encode($data);
    exit();
}

// ----------------------------------------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------------------------

function createuser($userInput)
{
    global $conn;

    $name = $userInput->full_name;
    $email = $userInput->email;
    $password = password_hash($userInput->password, PASSWORD_DEFAULT);
    $mobile = $userInput->mobile;
    $address = $userInput->address;
    $apiKey =  bin2hex(random_bytes(32)); // توليد API Key عشوائي


    // التحقق من البريد الإلكتروني إذا كان موجودًا في قاعدة البيانات
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    if ($stmt->fetch()) {
        echo json_encode(["status" => "error", "message" => "Email already exists"]);
        exit;
    }


    if (empty(trim($name))) {
        return error422("Enter your name");
    } elseif (empty(trim($email))) {
        return error422("Enter your email");
    } elseif (empty(trim($password))) {
        return error422("Enter your passwoed");
    } elseif (empty(trim($mobile))) {
        return error422("Enter your phone");
    } elseif (empty(trim($address))) {
        return error422("Enter your address");
    } else {

        $quary = "INSERT INTO users (full_name,email,password,mobile,address,api_key) VALUES (:full_name,:email,:password,:mobile,:address,:api_key)";
        $stmt = $conn->prepare($quary);

        $stmt->bindParam(':full_name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':mobile', $mobile);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':api_key', $apiKey);


        if ($stmt->execute()) {
            $data = [
                'status' => 201,
                'message' => 'user Created Successfully',
                "api_key" => $apiKey
            ];
            header('HTTP/1.0 201 Created');
            return json_encode($data);
        } else {
            $data = [
                'status' => 500,
                'message' => 'Internal Server Error',
            ];
            header('HTTP/1.0 500 Internal Server Error');
            return json_encode($data);
        }
    }
}


// ----------------------------------------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------------------------




function getuser($userParams)
{

    global $conn;

    if ($userParams['id'] == null) {
        return error422("Enter Your User Id");
    }

    $userId = $userParams['id'];

    $quary = "SELECT * FROM users WHERE id=:id LIMIT 1";
    $stmt = $conn->prepare($quary);
    $stmt->bindParam('id', $userId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($res) {
            $data = [
                "statue" => "200",
                "message" => "user Fetched Successfully",
                "data" => $res
            ];
            header("HTTP/1.0 200 OK");
            return json_encode($data);
        } else {
            $data = [
                "statue" => "404",
                "message" => "User Not Found"
            ];
            header("HTTP/1.0 404 User Not Found");
            return json_encode($data);
        }
    } else {
        $data = [
            "statue" => "error",
            'message' => 'Internal Server Error',
        ];
        header("HTTP/1.0 error Internal Server Error");
        return json_encode($data);
    }
}


// ----------------------------------------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------------------------


function getuserList()
{

    global $conn;

    $quary = "SELECT * FROM users";
    $stmt = $conn->prepare($quary);

    if ($stmt->execute()) {
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($res) > 0) {
            $data =
                [
                    "statue" => 200,
                    "message" => "user List Fetched Successfully",
                    "data" => $res
                ];
            header("HTTP/1.0 200 OK");
            return json_encode($data);
        } else {
            $data = [
                "statue" => "404",
                "message" => "user Not Found"
            ];

            header("HTTP/1.0 404 user Not Found");
            return json_encode($data);
        }
    } else {
        $data = [
            "statue" => "error",
            "message" => "Internal Server Error"
        ];
        header("HTTP/1.0 error Internal Server Error");
        return json_encode($data);
    }
}
