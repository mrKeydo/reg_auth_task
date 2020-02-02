<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login/Register forms task</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script src="script.js"></script>
</head>

<body>
    <div class="main">
        <div id="tabs">
            <!-- Кнопки -->
            <ul class="tabs-nav">
                <li><a href="#tab-1">Войти</a></li>
                <li><a href="#tab-2">Зарегистрироваться</a></li>
            </ul>

            <!-- Контент -->
            <div class="tabs-items">
                <div class="tabs-item" id="tab-1">
                    <form name="login" method="post" id="login">
                        <label>Login *</label><br>
                        <input type="text" name="login_auth"><br>

                        <label>Password *</label><br>
                        <input type="password" name="password_auth"><br>

                        <input type="submit" name="login_button" value="Войти">
                    </form>
                    <p class="not_found"></p>
                </div>
                <div class="tabs-item" id="tab-2">
                    <form name="registration" action="process.php" method="post" id="registration">
                        <label>Login *</label><br>
                        <input type="text" name="login"><br>

                        <label>Password *</label><br>
                        <input type="password" name="password"><br>

                        <label>Confirm password *</label><br>
                        <input type="password" name="confirm_password"><br>

                        <label>Email *</label><br>
                        <input type="text" name="email"><br>

                        <label>Name *</label><br>
                        <input type="text" name="name"><br>

                        <input type="submit" name="registration_button" value="Зарегистрироваться">
                    </form>
                    <p class="output"></p>
                </div>
            </div>
        </div>
    </div>

</body>

</html>