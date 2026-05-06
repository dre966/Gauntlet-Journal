<?php
session_start();
$funct_name = $_GET['funct'] ?? null;
$args_string = $_GET['args'] ?? null;
$uid = $_SESSION["uid"] ?? null;
function connect(){
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db_name = "web_prog";
    $conn = new mysqli($host, $user, $pass, $db_name);
    if($conn->error){
        die(json_encode(["status"=>"error","msg"=>"Connection Failed"]));
    }
    return $conn;
}

function check_user($uid){
    $conn = connect();
    if(!isset($_SESSION["uid"])){
        return ["status"=>"error", "msg"=>"Not Logged In... Redirecting...Please Log In", "body"=>null];
    }else{
        return ["status"=>"success", "msg"=>"Logged In... Loadng...","body"=>null];
    }
}
function get_cars($u){
    $car_conn = connect();
    $car_query = "SELECT cars from selection where id = ?";
    $car_stmt = $car_conn->prepare($car_query);
    $car_stmt->bind_param("s", $u);
    $car_stmt->execute();
    $car_result = $car_stmt->get_result();
    while($row = $car_result->fetch_assoc()){
        $cars[]=$row["cars"];
    };
    $cars = explode(",", $cars[0]);
    $res_array = [];
    if(count($cars) <= 1){
        $e =new ErrorException("User Has No Cars");
        throw $e;
    }else{
        foreach($cars as $car){
            $conn = connect();
            $query = "SELECT id, brand, model FROM cars WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $car);
            $stmt->execute();
            $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $res_array[] = $row;
        }
       }
       return $res_array;
    }
}
function get_locs(){
    $conn = connect();
    $query = "SELECT * from locations";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()){
        $res_array[] = $row;
    }
    return $res_array;
}
function get_tracks($loc){
    $conn = connect();
    $query = "SELECT * from tracks where loc = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $loc);
    $stmt->execute();
    $result = $stmt->get_result();
    $res_array = [];
    while($row = $result->fetch_assoc()){
        $res_array[] = $row;
    }
    return $res_array;
}

function add_time($t_id, $c_id, $time) {
    global $uid;
    $conn = connect();
    $stmt = $conn->prepare("INSERT INTO times (user_id, track_id, car_id, lap_time) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE lap_time=LEAST(lap_time, VALUES(lap_time))");
    $actual_time = calc_time($time);
    // "iiid" means 3 integers and 1 double/decimal
    $stmt->bind_param("iiid", $uid, $t_id, $c_id, $actual_time);
    try{
    if($stmt->execute()) {
        return ["status" => "set"];
    }else{
        $res = $stmt->get_result();
        return ["status"=>"error","msg"=>$stmt->error];
    }
    } catch(Exception $e){
        throw new Exception($e->getMessage());
    }
}

function calc_time($time){
    $parts = explode(".", $time);
    if (count($parts) == 2){
    $sec = (int)$parts[0];
    $millisecs = (int)$parts[1];

    $total_secs = $sec+($millisecs/1000);
    return $total_secs;

    }
}

function get_times($uid){
    $conn = connect();
    $query = 'SELECT 
    users.uname AS Pilot,
    users.id AS "Pilot ID",
    locations.name AS City,
    tracks.name AS Track,
    cars.brand AS Brand,
    cars.model AS Vehicle,
    cars.id AS "Vehicle ID",
    times.lap_time AS Time,
    times.date_Set as "Date Set"
    FROM times
    JOIN users ON times.user_id = users.id
    JOIN tracks ON times.track_id = tracks.id
    JOIN locations ON tracks.loc = locations.id
    JOIN cars ON times.car_id = cars.id
    where users.id = ?
    ORDER BY times.date_Set DESC;';
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i",$uid);
    $stmt->execute();
    $res = $stmt->get_result();
    $res_arr  =[];
    while($row = $res->fetch_assoc()){
        $res_arr[] = $row;
    }
    return $res_arr;

}
function reset_table($uid){
    $conn = connect();
    $query = 'DELETE from times where user_id=?';
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i",$uid);
    $stmt->execute();
    if($stmt->affected_rows > 0){
        return ["Something Happened"];
    }else{

        return ["Nope"];
    }
}

try{
if ($funct_name === "get_locs") {
    echo json_encode(["status"=>"success","msg"=>"Locations Set","body"=>get_locs()]);
}elseif ($funct_name === "get_tracks") {
    echo json_encode(["status"=>"success","msg"=>"Tracks Set","body"=>get_tracks($args_string)]);
}elseif($funct_name == "get_cars"){
    echo json_encode(["status"=>"success","msg"=>"Cars Loaded","body"=>get_cars($uid)]);
}elseif($funct_name == "add_time"){
    echo json_encode(["status"=>"success","msg"=>"Time Added","body"=>add_time(...explode(",", $args_string))]);
}elseif($funct_name==="reset"){
    echo json_encode(["status"=>"success", "msg"=>"Deleted", "body"=>reset_table($uid)]);
}
elseif($funct_name === "get_times"){
    echo json_encode(["status"=>"success", "msg"=>"Updated Table", "body"=>get_times($uid)]);
}elseif($funct_name === "check_user"){
    echo json_encode(check_user($uid));
}
else {
    echo json_encode(["status" => "error", "msg" => "Function ". $funct_name." not found OR is not set"]);
}
} catch(Exception $e){
    echo json_encode(["status"=>"error","msg"=>$e->getMessage(), "body"=>null]);
}
?>
