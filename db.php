<?php
$host = 'localhost';
$dbName = 'club_sample';
$dbUser = 'root';
$dbPass = '';

$mysqli = new mysqli($host, $dbUser, $dbPass, $dbName);

if ($mysqli->connect_error) {
    die('Database connection failed: ' . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

// Auto-mark past events as completed
$mysqli->query("
    UPDATE events 
    SET status = 'completed' 
    WHERE status = 'upcoming' 
    AND event_date != 'Ongoing'
    AND STR_TO_DATE(event_date, '%M %d, %Y') < CURDATE()
");