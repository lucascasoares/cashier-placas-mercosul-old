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

	// Verificando se o protocolo existe
	$query_ver = "SELECT * FROM sales WHERE id = '$id'";
	$result_ver = mysqli_query($con,$query_ver);
	$num_rows_ver = mysqli_num_rows($result_ver);
	if($num_rows_ver > 0){
		$row_ver = mysqli_fetch_array($result_ver);
		$date_protocol = $row_ver['date'];
		$establishment_protocol = $row_ver['establishment'];
		
		// Definindo variáveis
		$total = 0;
		
		// Atualizando o show_summary_sales
		$query_sps = "SELECT * FROM show_payed_sales WHERE protocol = '$id'";
		$result_sps = mysqli_query($con,$query_sps);
		$num_rows_sps = mysqli_num_rows($result_sps);
		if($num_rows_sps > 0){
			while($row_sps = mysqli_fetch_array($result_sps)){
				$date = $row_sps['date'];
				$payment_method = $row_sps['payment_method'];
				$value = $row_sps['value'];
				$box = $row_sps['box'];
				// Atualizando o valor total
				$total += $value;
				// Pegando os dados
				$query_sptv = "SELECT value AS valor FROM show_summary_sales WHERE payment_method = '$payment_method' AND box = '$box' AND date = '$date'";
				$result_sptv = mysqli_query($con,$query_sptv);
				$row_sptv = mysqli_fetch_assoc($result_sptv);
				$valor_tot = $row_sptv['valor'];
				if($valor_tot == $value){
					// Delete
					$query_del_sptv = "DELETE FROM `show_summary_sales` WHERE payment_method = '$payment_method' AND box = '$box' AND date = '$date'";
					$result_del_sptv = mysqli_query($con,$query_del_sptv);
				} else {
					// UPDATE
					$new_value = $valor_tot - $value;
					$query_up_sptv = "UPDATE `show_summary_sales` SET `value`='$new_value' WHERE payment_method = '$payment_method' AND box = '$box' AND date = '$date'";
					$result_up_sptv = mysqli_query($con,$query_up_sptv);
				}
			}
			
			// Deletando o show_payed_sales
			$query_del_sps = "DELETE FROM show_payed_sales WHERE protocol = '$id'";
			$result_del_sps = mysqli_query($con,$query_del_sps);
			
			// Diminuindo o total de pagamentos do dia
			$query_ver_tot = "SELECT value FROM total_paid_protocols_day WHERE date = '$date_protocol' AND establishment = '$establishment_protocol'";
			$result_ver_tot = mysqli_query($con,$query_ver_tot);
			$num_rows_ver_tot = mysqli_num_rows($result_ver_tot);
			if($num_rows_ver_tot > 0){
				$row_ver_tot = mysqli_fetch_array($result_ver_tot);
				$valor_tot_day = $row_ver_tot['value'];
				if($valor_tot_day == $total){
					// Delete
					$query_del_tot_day = "DELETE FROM total_paid_protocols_day WHERE date = '$date_protocol' AND establishment = '$establishment_protocol'";
					$result_del_tot_day = mysqli_query($con,$query_del_tot_day);
				} else {
					// Update
					$new_value_tot_day = $valor_tot_day - $total;
					$query_up_tot_day = "UPDATE total_paid_protocols_day SET value = '$new_value_tot_day' WHERE date = '$date_protocol' AND establishment = '$establishment_protocol'";
					$result_up_tot_day = mysqli_query($con,$query_up_tot_day);
				}
			}
			
			// Deletando no payment
			$query_del_pay = "DELETE FROM `payments` WHERE protocol = '$id'";
			$result_del_pay = mysqli_query($con,$query_del_pay);
			
			// Atualizando os relatórios
			$query_rep = "UPDATE report_total_opened_protocols SET total_paid = '0' WHERE protocol = '$id'";
			$result_rep = mysqli_query($con,$query_rep);
			
			header("Location: ../cashier/payment?protocol=".$id);			
		} else {
			echo "Não existem pagamentos referentes a esse protocolo.";
		}
	} else {
		echo "O protocolo que você está tentando deletar o pagamento não existe.";
	}
} else {
    header("Location: ../login");
}
?>