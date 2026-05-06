<?php
header('Content-Type: application/json');


$host     = "localhost";
$user     = "root";
$password = "";
$db_name  = "web_prog";

$uname  = isset($_POST["uname"])  ? trim($_POST["uname"])  : "";
$pass   = isset($_POST["pass"])   ? trim($_POST["pass"])   : "";
$action = isset($_POST["action"]) ? $_POST["action"]       : "";

if (empty($uname) || empty($pass)) {
    die(json_encode(["error" => "Username and password are required"]));
}

if ($action !== "login" && $action !== "register") {
    die(json_encode(["status" => "error", "msg"=>"Invalid action"]));
}

$conn = new mysqli($host, $user, $password, $db_name);

if ($conn->connect_error) {
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