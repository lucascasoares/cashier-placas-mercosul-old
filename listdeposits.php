<?php
header('Content-Type: text/html; charset=utf8');
$system_name = "emplacarrn";
include 'db.php';
include 'User.php';
include 'fun.php';
session_start();
date_default_timezone_set('America/Fortaleza');
if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
	$user_id = $user->getId();
	$establishment = $user->getEstablishment();
	$box = $_GET['box'];
	$data = $_GET['date'];
	if( $user->getRole() == 1 || $user->getRole() == 3  || $user->getRole() == 5 ){
		if($box != 0){
			if(connect()){
				$con = $_SESSION['con'];
				$query_box = "SELECT * FROM boxes_list WHERE id = '$box' AND establishment = '$establishment'";
				$result_box = mysqli_query($con,$query_box);
				$num_rows_box = mysqli_num_rows($result_box);
				if( $num_rows_box > 0 ){
					while($row_box = mysqli_fetch_array($result_box)){
						$query_box_est = "SELECT * FROM boxes_users WHERE box = '$box' AND user = '$user_id'";
						$result_box_est = mysqli_query($con,$query_box_est);
						$num_rows_box_est = mysqli_num_rows($result_box_est);
						if($num_rows_box_est > 0){
							$query_deposits = "SELECT * FROM deposits_group WHERE date = '$data' AND box = '$box'";
							$result_deposits = mysqli_query($con,$query_deposits);
							$num_rows_deposits = mysqli_num_rows($result_deposits);
							if($num_rows_deposits > 0){
								while($row_deposits = mysqli_fetch_array($result_deposits)){
									echo "<p><a href='showdeposit?id=".$row_deposits['id']."'>Depósito ".$row_deposits['id']."</a></p>";
								}
							} else {
								if($data != ""){
									echo "Não houve depósitos nesse caixa, nesse dia.";
								}
							}
						} else {
							echo "Você não tem autorização para acessar o caixa deste estabelecimento.";
						}
					}
				} else {
					echo "O caixa que você está tentando acessar não pertence a esse estabelecimento ou não existe.";
				}
			} else {
				echo "Erro ao conectar ao banco de dados.";
			}
		} else {
			echo "Você não tem autorização para acessar o caixa deste estabelecimento.";
		}
	} else {
		echo "Você não tem autorização para ver o movimento de caixa.";
	}
} else {
    header("Location: ../login");
}
?>