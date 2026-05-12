<?php
header('Content-Type: application/json');
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$host     = $_ENV['MYSQLHOST'];
$user     = $_ENV['MYSQLUSER'];
$password = $_ENV['MYSQLPASSWORD'];
$db_name  = $_ENV['MYSQLDATABASE'];

$res = json_decode(file_get_contents("php://input"),true);

$uname  = isset($res["uname"])  ? trim($res["uname"])  : "";
$pass   = isset($res["pass"])   ? trim($res["pass"])   : "";
$action = isset($res["action"]) ? $res["action"]       : "";

if (empty($uname) || empty($pass)) {
    http_response_code(400);
    die(json_encode(["status"=>"error", "msg" => "Username and password are required"]));
}

if ($action !== "login" && $action !== "register") {
    http_response_code(400);
    die(json_encode(["status" => "error", "msg"=>"Invalid action"]));
}

$conn = new mysqli($host, $user, $password, $db_name);

if ($conn->connect_error) {
    http_response_code(400);
    die(json_encode(["error" => "Connection Failed: " . $conn->connect_error]));
}

if ($action === "register") {
    // Check if user already exists
    $check = $conn->prepare("SELECT id FROM users WHERE uname = ?");
    $check->bind_param("s", $uname);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $check->bind_result($uid);
        $check->fetch();
        $check->close();
        echo json_encode(["status" => "info", "id" => $uid, "msg" => "Username already exists."]);
    } else {
        $check->close();
        $stmt = $conn->prepare("INSERT INTO users (uname, pass) VALUES (?, ?)");
        $pass = password_hash($pass, PASSWORD_DEFAULT);
        $stmt->bind_param("ss", $uname, $pass);
        $stmt->execute();

        if ($stmt->affected_rows == 1) {
            $uid = $conn->insert_id;
            echo json_encode(["status" => "success", "id" => $uid, "msg" => "Account created. Please login."]);
        } else {
            echo json_encode(["status" => "error", "msg" => "Registration failed: " . $stmt->error]);
        }
        $stmt->close();
    }

} elseif ($action === "login") {
    $stmt = $conn->prepare("SELECT id, uname, pass FROM users WHERE uname = ?");
    $stmt->bind_param("s", $uname);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if($result){
        if (password_verify($pass, $result["pass"])) {
            session_start();
            $_SESSION["uid"] = $result["id"];
            $_SESSION["uname"] = $result["uname"];
            echo json_encode(["status" => "success", "id" => $result["id"], "uname" => $result["uname"], "msg" => "Logging In...."]);
        } else {
            echo json_encode(["status" => "error", "msg" => "Invalid username or password."]);
        }
    }else{
        echo json_encode(["status" => "error", "msg" => "User Doesn't Exist. Please Registers"]);
    }
    $stmt->close();
}

$conn->close();
?>