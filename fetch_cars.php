<?php
header('Content-Type: application/json');
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "web_prog";
$searchterm = isset($_GET['search']) ? $_GET['search'] : '';
$class = isset($_GET["class"]) ? $_GET["class"] : null;
$conn = new mysqli($host, $user, $pass, $db_name);
$queryterm = "%".$searchterm."%";

if($conn->connect_error){
    die(json_encode((["error"=>"Connection Failed"])));
}
if(isset($class)){
    $stmt = $conn->prepare("SELECT id,brand,model FROM cars WHERE brand LIKE ? OR model LIKE ? AND class LIKE ?");
    $stmt->bind_param("sss", $queryterm, $queryterm, $class);
    }else{
    $stmt = $conn->prepare("SELECT id,brand,model FROM cars WHERE brand LIKE ? OR model LIKE ?");
    $stmt->bind_param("ss", $queryterm, $queryterm);
}


$stmt->execute();
$res = $stmt->get_result();

$cars = [];
while($row = $res->fetch_assoc()){
    $cars[] = $row;
}

echo json_encode(array_values($cars));
$stmt->close();
$conn->close();
?>