<?php
header('Content-Type: text/html; charset=utf8');
$system_name = "emplacarrn";
include 'db.php';
include 'User.php';
session_start();
$user = $_SESSION['user'];

if (isset($user) && $user->getRole() == 1) {

	// receiving data form
	$date = $_GET['date'];
	$establishment = $user->getEstablishment();

	//Gravando no banco de dados ! conectando com o localhost - mysql
	if (!connect())
		die("Erro de conexão com localhost, o seguinte erro ocorreu -> " . mysqli_connect_error());

	$con = $_SESSION['con'];
	$query = "SELECT * FROM total_protocols_day WHERE establishment = '$establishment' AND date = '$date'";
	$result = mysqli_query($con, $query);
	$num_rows = mysqli_num_rows($result);
	if ($num_rows > 0) {
		$row = mysqli_fetch_assoc($result);
		if ($row['closed'] == 1) {
			$query_up = "UPDATE total_protocols_day SET closed = '0' WHERE establishment = '$establishment' AND date = '$date'";
			echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Caixa reaberto com sucesso.</div>';
		} else {
			$query_up = "UPDATE total_protocols_day SET closed = '1' WHERE establishment = '$establishment' AND date = '$date'";
			echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Caixa fechado com sucesso.</div>';
		}
		mysqli_query($con, $query_up);
	} else {
		// Criar fechado
		$query_in = "INSERT INTO `total_protocols_day` (`id`, `date`, `value`, `establishment`, `closed`) VALUES (NULL, '$date', '0', '$establishment', '0')";
		mysqli_query($con, $query_in);
		echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Caixa fechado com sucesso.</div>';
	}
	closedb();
	// fim da autenticação
} else {
	header("Location: ../../" . $system_name . "/login");
}
