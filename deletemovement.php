<?php
header('Content-Type: text/html; charset=utf8');
$system_name = "emplacarrn";
include 'db.php';
include 'User.php';
session_start();
$user = $_SESSION['user'];

if (isset($user) && $user->getRole() == 1) {
	$id = $_GET['id'];

	//Gravando no banco de dados ! conectando com o localhost - mysql
	if (!connect())
		die ("Erro de conexão com localhost, o seguinte erro ocorreu -> ".mysqli_connect_error());
	
	$con = $_SESSION['con'];

	// Verificando se o movimento existe
	$query_ver = "SELECT * FROM cash_movement WHERE id = '$id'";
	$result_ver = mysqli_query($con,$query_ver);
	$num_rows_ver = mysqli_num_rows($result_ver);
	if($num_rows_ver > 0){
		$row_ver = mysqli_fetch_array($result_ver);
		$date = $row_ver['date'];
		
		// Deletando o show_payed_sales
		$query_del = "DELETE FROM cash_movement WHERE id = '$id'";
		$result_del = mysqli_query($con,$query_del);
		
		header("Location: ../cashier/cashmovement?date=".$date);
	} else {
		echo "O movimento que você está tentando deletar não existe.";
	}
} else {
    header("Location: ../login");
}
?>