<?php

$host = "localhost";
$dbname = "parse_db";
$username = "bekzod";
$password = "64@2421Bek";

$db = new mysqli($host, $username, $password, $dbname);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
