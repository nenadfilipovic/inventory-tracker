<?php

// Start session
session_start();

// Connect database
$db = mysqli_connect('localhost', '', '', '');

// Number of items per page
$items_per_page = '5';

// Default order
$order = isset($_GET['o']) ? $_GET['o'] : 'add_date';