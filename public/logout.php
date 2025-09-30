<?php require_once 'bootstrap.php'; 

$auth = new \App\Auth();
$auth->logout();
redirect('/login.php');
