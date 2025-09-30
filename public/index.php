<?php require_once 'bootstrap.php'; 

$auth = new \App\Auth();
if ($auth->isLoggedIn()) {
    redirect('/dashboard.php');
} else {
    redirect('/login.php');
}
