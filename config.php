<?php
session_start(["cookie_httponly"=>true,"cookie_secure"=>true,"cookie_samesite"=>"Strict","cookie_lifetime"=>3600]);

$recv = json_decode(file_get_contents("php://input"),true);
header("Content-Type: application/json");
$funct_name = $recv['to_do'] ?? null;
$args_string = $recv['params'] ?? null;
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

function ret(string $stat,string $msg, $body){
    return ["status"=>$stat, "msg"=>$msg, "body"=>$body];
}

function check_user($uid){
    $conn = connect();
    if(!isset($_SESSION["uid"])){
        return ret("error","Not Logged In... Redirecting...Please Log In",null);
        }else{
        return ret("success","Logged In... Loadng...",null);
    }
}
function get_all_cars(string $searchterm){
    try{
        $conn =  connect();
        $queryterm = "%".$searchterm."%";
        $stmt = $conn->prepare("SELECT id,brand,model FROM cars WHERE brand LIKE ? OR model LIKE ?");
        $stmt->bind_param("ss", $queryterm, $queryterm);
        $stmt->execute();
        $res = $stmt->get_result();

        $cars = [];
        while($row = $res->fetch_assoc()){
            $cars[] = $row;
            }
        return ret("success", "Cars Loaded", $cars);
    }catch(Exception $e){
        http_response_code(400);
        return ret("error", $e->getMessage(), null);
    }

}

function save_cars(string $uid, string $cars){
    $checkconn = connect();
    $query = "INSERT INTO selection (id, cars) VALUES(?,?)";
    try{
        $checkquery = "SELECT * FROM users where id = ?";
        $chckstmt = $checkconn->prepare($checkquery);
        $chckstmt->bind_param("i", $uid);
        $chckstmt->execute();
        if($chckstmt->fetch() == null){
            die(json_encode(["status"=>"error", "msg"=>"User Does Not Exist", "body"=>null]));
        }
        $checkconn->close();
        $conn = connect();
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $uid, $cars);
        if($stmt->execute()){
            return ret("success","Cars added succesfully",$cars);
        }else{
            return ret("success","Something went wrong",$cars);
        }
    
    }catch(mysqli_sql_exception $e){
        if($e->getCode() == 1062){
            $query = $query . "ON DUPLICATE KEY UPDATE cars = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iss", $uid, $cars, $cars);
            if($stmt->execute()){
                return ret("success","Cars modified",$cars);
                }else{
                    return ret("success","Something went wrong",$cars);
            }
        }else{
            http_response_code(400);
            return $e->getMessage();
        }
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
       return ret("success","Cars Loaded",$res_array);
    }
}
function get_uname(){
    return ret("success","Logged in", $_SESSION["uname"]);
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
    return ret("success","Locations Loaded",$res_array);
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
    return ret("success","Tracks Loaded",$res_array);;
}

function add_time($t_id, $c_id, $time) {
    global $uid;
    $conn = connect();
    $stmt = $conn->prepare("INSERT INTO times (user_id, track_id, car_id, lap_time) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE lap_time= VALUES(lap_time)");
    $actual_time = calc_time($time);
    // "iiid" means 3 integers and 1 double/decimal
    $stmt->bind_param("iiid", $uid, $t_id, $c_id, $actual_time);
    try{
    if($stmt->execute()) {
        return ret("success","Time Added",null);
    }else{
        $res = $stmt->get_result();
        return ret("success","Time Add Failed", $actual_time);
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
    
    $total_secs =$sec.".".str_pad($millisecs,3,"0");
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
    return ret("success","Location Loaded",$res_arr);;

}
function reset_table($uid){
    $conn = connect();
    $query = 'DELETE from times where user_id=?';
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i",$uid);
    $stmt->execute();
    if($stmt->affected_rows > 0){
        return ret("success","Table Deleted",null);
    }else{

        return ret("error","Table Delete Fail", null);
    }
}

try{
if ($funct_name === "get_locs") {
    echo json_encode(get_locs());
}elseif ($funct_name === "get_tracks") {
    echo json_encode(get_tracks($args_string));
}elseif($funct_name == "user"){
    echo json_encode(get_uname());
}
elseif($funct_name == "get_cars"){
    echo json_encode(get_cars($uid));
}elseif($funct_name == "add_time"){
    echo json_encode(add_time(...explode(",", $args_string)));
}elseif($funct_name==="reset"){
    echo json_encode(reset_table($uid));
}elseif($funct_name == "save"){
    echo json_encode(save_cars($uid,$args_string));
}elseif($funct_name === "get_times"){
    echo json_encode(get_times($uid));
}elseif($funct_name === "check_user"){
    echo json_encode(check_user($uid));
}elseif($funct_name == "load_cars"){
    echo json_encode(get_all_cars($args_string));
}
else {
    echo json_encode(["status" => "error", "msg" => "Function ". $funct_name." not found OR is not set"]);
}
} catch(Exception $e){
    echo json_encode(["status"=>"error","msg"=>$e->getMessage(), "body"=>null]);
}
?>
