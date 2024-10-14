<?php
header('Content-Type: text/html; charset=utf8');
include 'db.php';
include 'User.php';
session_start();
date_default_timezone_set('America/Fortaleza');
$user = $_SESSION['user'];

if (isset($user)) {
$establishment = $user->getEstablishment();
$user_id = $user->getId();
$box = $_POST['box'];
$in_out = $_POST['in_out'];
$description = addslashes($_POST['description']);
$value = $_POST['value'];
$value = str_replace(".", "", $value);
$value = str_replace(",", ".", $value);
$provider = $_POST["provider"];
$chart_of_accounts = $_POST['chart_of_accounts'];
$date = date("Y-m-d");
$time = date("H:i:s");

if($user->getRole() == 1 || $user->getRole() == 3 || $user->getRole() == 5){
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
						$cashier = $box;
						//Query que realiza a inserção dos dados no banco de dados na tabela indicada acima
						$query = "INSERT INTO `cash_movement` ( `id`, `cashier`, `in_out`, `description`, `value`, `provider`, `chart_of_accounts`, `date`, `time`, `user`) VALUES (NULL, '$cashier', '$in_out', '$description', '$value', '$provider', '$chart_of_accounts', '$date', '$time', '$user_id')";
						$result = mysqli_query($con, $query);
						if (!$result) {
							if(mysqli_errno($con) == 1062){
								header("Location: ../cashier/cashmovement?e=1");
							} else {
								header("Location: ../cashier/cashmovement?e=2");
							}
						} else {
							$que = "SELECT * FROM `cash_movement` WHERE in_out = '$in_out' AND description = '$description' AND value = '$value' AND provider = '$provider' AND chart_of_accounts = '$chart_of_accounts'";
							$res = mysqli_query($con, $que);
							$row = mysqli_fetch_array($res);
							$id = $row['id'];
							header("Location: ../cashier/cashmovement?date=".$date."&n=1");
						}
					} else {
						echo "Você não tem autorização para acessar o caixa deste estabelecimento.";
					}
				}
			} else {
				echo "O caixa que você está tentando acessar não pertence a esse estabelecimento ou não existe.";
			}
		} else {
			echo "Não é possível se conectar ao banco de dados.";
		}
	} else {
		echo "Você não tem autorização para movimentar o caixa dessa loja.";
	}
} else {
	header("Location: ../login");
}

// fim da autenticação
} else {
    header("Location: ../login");
}
?>