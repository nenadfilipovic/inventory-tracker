<?php

// Load server code
include('server.php');

// Process Login and Register request
if ((isset($_POST['login']) or isset($_POST['register']))) {
    if (isset($_POST['login'])) {
        login();
    } else {
        register();
    }
}
?>
<!-- HTML -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="author" content="Nenad Filipovic">
    <meta name="description" content="Website for maintaining inventory.">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico"/>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <meta name="robots" content="noindex,nofollow">
</head>
<body>
<div class="container">
    <!-- Logo -->
    <h1>INVENTORY</h1>
    <!-- Alert message -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="message">
            <span>
                <?php
                echo $_SESSION['message'];
                unset($_SESSION['message']);
                ?>
            </span>
        </div>
    <?php endif ?>
    <?php if (isset($_GET['register'])): ?>
        <!-- Header -->
        <p>Register to access inventory</p>
        <!-- Register form -->
        <div class="register-form">
            <form autocomplete="off" action="login.php?register" method="post">
                <label for="username">Username</label>
                <input id="username" required type="text" name="username" placeholder="Username">
                <label for="email">Email</label>
                <input id="email" required type="text" name="email" placeholder="Email">
                <label for="password">Password</label>
                <input id="password" required type="password" name="password" minlength=7 placeholder="Password">
                <label for="confirm-password">Confirm password</label>
                <input id="confirm-password" required type="password" name="confirm-password" minlength=7
                       placeholder="Confirm password">
                <div class="buttons">
                    <button type="submit" name="register"><span>Register</span></button>
                    <button onclick="window.location.href='login.php'" type="button"><span>Back</span></button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <!-- Header -->
        <p>Login to access inventory</p>
        <!-- Login form -->
        <div class="login-form">
            <form autocomplete="off" action="login.php" method="post">
                <label for="username">Username</label>
                <input id="username" required type="text" name="username" placeholder="Username">
                <label for="password">Password</label>
                <input id="password" required type="password" name="password" minlength=7 placeholder="Password">
                <div class="buttons">
                    <button type="submit" name="login"><span>Login</span></button>
                    <button onclick="window.location.href='?register'" type="button"><span>Register</span></button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>
<script src="js/script.js"></script>
</body>
</html>