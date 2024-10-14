<?php
header('Content-Type: text/html; charset=utf8');
$system_name = "emplacarrn";
include 'db.php';
include 'User.php';
session_start();

$establishment = $_GET['establishment'];

$user = $_SESSION['user'];
if(isset($user) && isset($establishment)){
	$user->SetEstablishment($establishment);
}
?>