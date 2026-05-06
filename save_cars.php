<?php
header('Content-Type: application/json');
$uid = $_GET["id"];
$cars = $_GET['cars'];
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "web_prog";
$conn = new mysqli($host, $user, $pass, $db_name);
$checkconn = new mysqli($host, $user, $pass, $db_name);

if($conn->connect_error){
    die(json_encode((["error"=>"Connection Failed"])));
}
$checkquery = "SELECT * FROM users where id = ?";
$chckstmt = $checkconn->prepare($checkquery);
$chckstmt->bind_param("i", $uid);
$chckstmt->execute();
if($chckstmt->fetch() == null){
    die(json_encode(["status"=>"error", "msg"=>"User Does Not Exist"]));
}
$checkconn->close();
$query = "INSERT INTO selection (id, cars) VALUES(?,?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $uid, $cars);
try{
    if($stmt->execute()){
        echo json_encode(["status"=>"success", "msg"=>"Cars added succesfully"]);
    }else{
        echo json_encode(["status"=>"error", "msg"=>"Something went wrong"]);
    }

}catch(mysqli_sql_exception $e){
if($e->getCode() == 1062){
    $query = $query . "ON DUPLICATE KEY UPDATE cars = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $uid, $cars, $cars);
     if($stmt->execute()){
        echo json_encode(["status"=>"success", "msg"=>"Cars modified"]);
    }else{
        echo json_encode(["status"=>"error", "msg"=>"Something went wrong"]);
    }
    
}else{
    echo json_encode(["status"=>"error","msg"=>"Databse Error"]);
}
}
$stmt->close();
$conn->close();
?>