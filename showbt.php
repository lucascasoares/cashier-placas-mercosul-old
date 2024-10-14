<?php
header('Content-Type: text/html; charset=utf8');
include 'db.php';
include 'User.php';
include 'fun.php';
session_start();
date_default_timezone_set('America/Fortaleza');

if (isset($_SESSION['user'])) {
	$user = $_SESSION['user'];
	$establishment = $user->getEstablishment();
	if ($user->getRole() == 1 || $user->getRole() == 3  || $user->getRole() == 5) {
		$return = "";
		$date = $_GET['date'];
		$date_now = date("Y-m-d");
		if (strcmp($date, $date_now) == 0) {
			if (connect()) {
				$con = $_SESSION['con'];
				$query = "SELECT closed FROM total_protocols_day WHERE establishment = '$establishment' AND date = '$date'";
				$result = mysqli_query($con, $query);
				$row = mysqli_fetch_assoc($result);
				$closed = $row['closed'];
				if (!$closed) {
					$return = '<a href="newmovement"><button type="button" class="btn btn-primary">Novo movimento</button></a>';
				} elseif ($closed && $user->getRole() != 1) {
					$return .= '<div class="alert alert-danger">Caixa fechado!</div>';
				}
				if ($user->getRole() == 1) {
					if ($closed) {
						$return .= '<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#ExemploModalCentralizado">Reabrir caixa</button>';
					} else {
						$return .= '<button type="button" class="btn btn-danger" style="margin-left:10px;" data-toggle="modal" data-target="#ExemploModalCentralizado">Fechar caixa</button>';
					}
				}
			}
		}
		echo $return;
	} else {
		header("Location: ../login");
	}
} else {
	header("Location: ../login");
}
