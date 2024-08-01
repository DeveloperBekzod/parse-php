<?php

$host = "localhost";
$dbname = "parse_db";
$username = "bekzod";
$password = "64@2421Bek";

$db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
