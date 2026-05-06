<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gauntlet Databse - Login</title>
    <link rel="stylesheet" href="./assets/css/login.css">
</head>
<body>
    <div class="nav">
        <h2>
            Gauntlet Database
        </h2>
    </div>
    <div class="screen">
        <div class="login-box">
            <h2>LOGIN</h2>
            <hr>
            <form>
                <label>
                    Username
                </label>
                <input type="text" name="uname" id="uname" required>
                <label>
                    Password
                </label>
                <input type="text" name="password" id="pass" required>
                <div class="buttons">
                    <button type="button" id="Login">Login</button>
                    <button type="button" id="Register">Register</button>
                </div>
            </form>
        </div>
    </div>
    <div id="toast"></div>
</body>
<script src="/web_prog/scripts/login.js"></script>
</html>