<?php

// Include database link
include('config.php');

// Check if user is logged in
function isLoggedIn()
{
    if (isset($_SESSION['user'])) {
        return true;
    } else {
        return false;
    }
}

// MySQL escape string
function escape($val)
{
    global $db;
    return mysqli_real_escape_string($db, trim($val));
}

// Check if user is admin
function isAdmin()
{
    if (isset($_SESSION['user']) && $_SESSION['user']['type'] == 'admin') {
        return true;
    } else {
        return false;
    }
}

// Check if single result
function isSingle()
{
    if (isset($_GET['id'])) {
        return true;
    } else {
        return false;
    }
}

// Check if search is active
function isSearch()
{
    if (isset($_GET['q'])) {
        return true;
    } else {
        return false;
    }
}

// Log out user
if (isset($_GET['logout'])) {
    session_destroy();
    session_unset();
    header("location: login.php");
    exit();
}

// Handle Register request
function register()
{
    global $db;
    $username = escape($_POST['username']);
    $email = escape($_POST['email']);
    $password = escape($_POST['password']);
    $confirm_password = escape($_POST['confirm-password']);
    $checkuser = mysqli_query($db, "SELECT * FROM users WHERE username='$username'");
    $checkemail = mysqli_query($db, "SELECT * FROM users WHERE email='$email'");
    if ($password != $confirm_password) {
        $_SESSION['message'] = 'Passwords do not match!';
    } elseif (mysqli_num_rows($checkuser) > 0) {
        $_SESSION['message'] = 'User already exist in database!';
    } elseif ((!filter_var($email, FILTER_VALIDATE_EMAIL))) {
        $_SESSION['message'] = 'Invalid Email format!';
    } elseif (mysqli_num_rows($checkemail) > 0) {
        $_SESSION['message'] = 'Email already exist in database!';
    } else {
        $password = md5($password);
        $query = "INSERT INTO users (username, password, email, type) VALUES('$username', '$password', '$email', 'user')";
        mysqli_query($db, $query);
        $_SESSION['message'] = 'Successfully registered!';
        header('location: login.php');
        exit();
    }
}

// Handle Login request
function login()
{
    global $db;
    $username = escape($_POST['username']);
    $password = escape($_POST['password']);
    $password = md5($password);
    $query = "SELECT * FROM users WHERE username='$username' AND password='$password' LIMIT 1";
    $results = mysqli_query($db, $query);
    if (mysqli_num_rows($results) == 1) {
        $user = mysqli_fetch_assoc($results);
        $_SESSION['user'] = $user;
        $_SESSION['message'] = 'Logged In!';
        header('location: index.php');
        exit();
    } else {
        $_SESSION['message'] = 'Wrong Username/Password!';
    }
}

// Display all items by order
function getItems($order)
{
    global $db;
    global $order;
    global $items_per_page;
    $total_pages = $db->query('SELECT COUNT(*) FROM lager')->fetch_row()[0];
    $page = isset($_GET['p']) && is_numeric($_GET['p']) ? $_GET['p'] : 1;
    if ($stmt = $db->prepare('SELECT * FROM lager ORDER BY ' . $order . ' LIMIT ?,?')) {
        $calc_page = ($page - 1) * $items_per_page;
        $stmt->bind_param('ii', $calc_page, $items_per_page);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    }
    return array($total_pages, $page, $order, $result);
}

// Display all items by query
function getQueryItems($query, $order)
{
    global $db;
    global $order;
    global $items_per_page;
    $id = escape($_GET['id']);
    $queryfix = $query . "%";
    if ($id) {
        $total_pages = $db->query("SELECT COUNT(*) FROM `lager` WHERE `id` LIKE '$id'")->fetch_row()[0];
    } else {
        $total_pages = $db->query("SELECT COUNT(*) FROM `lager` WHERE `name` LIKE '$queryfix'")->fetch_row()[0];
    }
    $page = isset($_GET['p']) && is_numeric($_GET['p']) ? $_GET['p'] : 1;
    if ($id) {
        if ($stmt = $db->prepare("SELECT * FROM lager WHERE id LIKE ? ORDER BY '$order' LIMIT ?,?")) {
            $calc_page = ($page - 1) * $items_per_page;
            $stmt->bind_param('iii', $id, $calc_page, $items_per_page);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
        }
        if (mysqli_num_rows($result) == 0) {
            $_SESSION['message'] = "Item not found!";
        }
        return array($total_pages, $page, $order, $result);
    } else {
        if ($stmt = $db->prepare("SELECT * FROM lager WHERE `name` LIKE ? ORDER BY '$order' LIMIT ?,?")) {
            $calc_page = ($page - 1) * $items_per_page;
            $stmt->bind_param('sii', $queryfix, $calc_page, $items_per_page);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
        }
        if (mysqli_num_rows($result) == 0) {
            $_SESSION['message'] = "Item not found!";
        }
        return array($total_pages, $page, $order, $result);
    }
}

// Get single item from database
function getItem($id)
{
    global $db;
    $record = mysqli_query($db, "SELECT * FROM lager WHERE id=$id");
    if (@count($record) == 1) {
        $n = mysqli_fetch_array($record);
    }
    return array($n['name'], $n['quantity'], $n['price'], $n['image']);
}

// Process Add request
if (isset($_POST['add'])) {
    $name = escape($_POST['name']);
    $quantity = escape($_POST['quantity']);
    $price = escape($_POST['price']);
    if ($_FILES['image']['name'] == "") {
        $image = "img/default.png";
    } else {
        $newFileName = uniqid('uploaded-', true) . '.' . strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $image = "upload/" . $newFileName;
        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    }
    $query = mysqli_query($db, "INSERT INTO lager (name, quantity, price, image) VALUES ('$name', '$quantity', '$price', '$image')");
    $db->query($query);
    $_SESSION['message'] = "Item added!";
    header('location:?id=' . $db->insert_id);
    exit();
}

// Process Update request
if (isset($_POST['update'])) {
    $id = escape($_POST['id']);
    $name = escape($_POST['name']);
    $quantity = escape($_POST['quantity']);
    $price = escape($_POST['price']);
    if ($_FILES['image']['name'] == "") {
        mysqli_query($db, "UPDATE lager SET name='$name', quantity='$quantity', price='$price' WHERE id=$id");
    } else {
        $newFileName = uniqid('uploaded-', true) . '.' . strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $image = "upload/" . $newFileName;
        move_uploaded_file($_FILES['image']['tmp_name'], $image);
        mysqli_query($db, "UPDATE lager SET name='$name', quantity='$quantity', price='$price', image='$image' WHERE id=$id");
    }
    $_SESSION['message'] = "Item updated!";
}

// Process Delete request
if (isset($_POST['delete'])) {
    $id = escape($_POST['id']);
    imageRemove($id);
    mysqli_query($db, "DELETE FROM lager WHERE id=$id");
    $_SESSION['message'] = "Item deleted!";
}

// Remove image
function imageRemove($id)
{
    global $db;
    list(, , , $image) = getItem($id);
    if ($image !== "img/default.png") {
        unlink($image);
        $image = "img/default.png";
        mysqli_query($db, "UPDATE lager SET image='$image' WHERE id=$id");
    }
}



/* Process Delete request
if (isset ($_GET['delete'])) {
    $id = $_GET['delete'];
    $page = $_GET['page'];
    $query = $_GET['query'];
    $order = $_GET['order'];
    mysqli_query($db, "DELETE FROM lager WHERE id=$id");
    $_SESSION['message'] = "Item deleted!";
    if ($_GET['query'] == "") {
        list($total_pages, $page, $order, $result) = getItems($order);
        $last_page = ceil($total_pages / $items_per_page);
        if ($page < $last_page) {
            header('location: index.php?page=' . $page . '&order=' . $order);
            exit();
        } else {
            header('location: index.php?page=' . $last_page . '&order=' . $order);
            exit();
        }
    } else {
        list($total_pages, $page, $order, $result) = getQueryItems($query, $order);
        $last_page = ceil($total_pages / $items_per_page);
        if ($page < $last_page) {
            header('location: search1.php?query=' . $query . '&page=' . $page . '&order=' . $order);
            exit();
        } else {
            header('location: search1.php?query=' . $query . '&page=' . $last_page . '&order=' . $order);
            exit();
        }
    }
}*/