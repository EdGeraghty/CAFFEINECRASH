<?php require_once 'bootstrap.php'; 

// Check if installation is complete
$installation = new \App\Installation();
if (!$installation->isInstalled()) {
    redirect('/install.php');
}

$auth = new \App\Auth();
if ($auth->isLoggedIn()) {
    redirect('/dashboard.php');
} else {
    redirect('/login.php');
}
