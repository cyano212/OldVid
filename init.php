<?php
session_start();
$db = new PDO('sqlite:retroshow.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    login TEXT UNIQUE,
    pass TEXT
)");

$db->exec("CREATE TABLE IF NOT EXISTS videos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT,
    description TEXT,
    file TEXT,
    preview TEXT,
    user TEXT,
    FOREIGN KEY (user) REFERENCES users(login)
)");
?>
