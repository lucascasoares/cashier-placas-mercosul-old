<?php
header('Content-Type: text/html; charset=utf8');
include 'db.php';
include 'fun.php';
include 'User.php';
session_start();
date_default_timezone_set('America/Fortaleza');
$user = $_SESSION['user'];

if (isset($user)) {
$establishment = $user->getEstablishment();
$account = $_POST['account'];
$origin_est = $establishment;
$origin = getAccount($establishment);
$description = $_POST['description'];
$value = $_POST['value'];
$value = str_replace(".", "", $value);
$value = str_replace(",", ".", $value);
$date = date("Y-m-d");
$time = date("H:i:s");
$user_id = $user->getId();

if($user->getRole() == 1 || $user->getRole() == 3 || $user->getRole() == 5){
	if(connect()){
		$con = $_SESSION['con'];
		$query_box = "SELECT * FROM boxes_users WHERE user = '$user_id' ORDER BY id LIMIT 1";
		$result_box = mysqli_query($con,$query_box);
		$num_rows_box = mysqli_num_rows($result_box);
		if( $num_rows_box > 0 ){
			while($row_box = mysqli_fetch_array($result_box)){
				$box = $row_box['box'];
			}
			$query_box_est = "SELECT * FROM boxes_list WHERE id = '$box'";
			$result_box_est = mysqli_query($con,$query_box_est);
			$num_rows_box_est = mysqli_num_rows($result_box_est);
			if($num_rows_box_est > 0){
				while($row_box_est = mysqli_fetch_array($result_box_est)){
					$est_box = $row_box_est['establishment'];
				}
				if($est_box == $establishment){
					$cashier = $box;
					//Query que realiza a inserção dos dados no banco de dados na tabela indicada acima
					$query = "INSERT INTO `accounts_movement` ( `id`, `origin`, `destination`, `description`, `value`, `date`, `time`, `user`) VALUES (NULL, '$origin', '$account', '$description', '$value', '$date', '$time', '$user_id')";
					$result = mysqli_query($con, $query);
					if (!$result) {
						if(mysqli_errno($con) == 1062){
							header("Location: ../cashier/transfer?e=1");
						} else {
							header("Location: ../cashier/transfer?e=2");
						}
					} else {
						$que = "SELECT * FROM `accounts_movement` WHERE account = '$account' AND in_out = '$in_out' AND origin_destination = '$origin_destination' AND description = '$description' AND value = '$value' AND date = '$date' AND time = '$time' AND user = '$user_id'";
						$res = mysqli_query($con, $que);
						$row = mysqli_fetch_array($res);
						$id = $row['id'];
						header("Location: ../cashier/transfer?n=1");
					}
				} else {
					echo "Você não tem autorização para fazer nada nesse estabelecimento.";
				}
			} else {
				echo "Esse caixa nao está na lista de caixas.";
			}
		} else {
			echo "Você não tem autorização para movimentar o caixa.";
		}
	} else {
		echo "Não é possível se conectar ao banco de dados.";
	}
} else {
	header("Location: ../login");
}

// fim da autenticação
} else {
    header("Location: ../login");
}
?>