<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gauntlet Journal</title>
    <link rel="stylesheet" href="./assets/css/tables.css">
</head>
<body>
    <div class="back"></div>

    <!-- Horizontal control bar at top -->
    <div class="control-panel">
        <!-- Title sits inside the bar on the left -->
        <div style="display:flex; flex-direction:column; justify-content:flex-end; margin-right:8px;">
            <span style="font-size:22px; font-weight:900; font-family:Rajdhani; color:#fed02c; letter-spacing:1px; white-space:nowrap;">
                Gauntlet<br>Database
            </span>
        </div>

        <div class="ctrl_item">
            <label>Location</label>
            <select id="loc_drop"></select>
        </div>
        <div class="ctrl_item">
            <label>Track</label>
            <select id="track_drop"></select>
        </div>
        <div class="ctrl_item">
            <label>Car</label>
            <select id="car_drop"></select>
        </div>
        <div class="ctrl_item">
            <label>Time</label>
            <input type="text" placeholder="00.000" id="input">
        </div>
           <div class="ctrl_item">
            <label>View</label>
            <select name="view" id="view_drop" >
                <option value="table">Table View</option>
                <option value="blocks">Block View</option>
            </select>
        </div>
        <button id="add">Add</button>
        <button id="reset">Reset</button>
    </div>

    <!-- Table takes the full page -->
    <div class="mystuff">
        <table id="table" border="1">
            <tr id="head">
                <td id="start">Car →<br>──────────<br>Location ↓</td>
            </tr>
        </table>
        <div class="car-list" id="card_view" style="display:none;"></div>
    </div>

    <!-- Toast -->
    <div class="toast" id="toast"></div>

    <script src="./scripts/tables.js"></script>
</body>
</html>
