<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/index.css">
    <title>Gauntlet</title>
</head>
<?php
session_start();
if(!isset($_SESSION["uid"])){
    header("Location: login.php");
    exit();
}
$uid = $_SESSION["uid"];
$acc = $_SESSION["uname"]
?>
<body>
    <div class="nav">
        <h1>Gauntlet Database</h1>
    </div>
    <div class="screen">
        <!-- sticky wrapper: searchbar + counter + next button -->
        <div class="sticky-bar">
            <div class="searchbar">
                <input type="text" class="search" placeholder="Search...">
            </div>
            <div class="controls-row">
                <div class="selection-counter"><span id="sel-count">0</span> selected</div>
                <div class="selection-counter">Logged in as <span id="acc"><?php echo $acc;?></span></div>
                <button class="btn-next" id="btn-next">Next →</button>
            </div>
        </div>

        <div class="car-list">
            <div class="car-card" style="background-image: url('/web_prog/assets/car_imgs/1.jpg')">
            </div>
        </div>
    </div>

    <!-- Toast notification -->
    <div class="toast" id="toast"></div>
</body>
    <script src="./scripts/index.js"></script>
    <script>const sessionData  = {"uid":<?php echo $uid ?>};</script>
</html>
