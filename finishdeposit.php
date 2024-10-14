<?php
header('Content-Type: text/html; charset=utf8');
$system_name = "emplacarrn";
include 'db.php';
include 'User.php';
session_start();
date_default_timezone_set('America/Fortaleza');
$user = $_SESSION['user'];

if (isset($user)) {

// receiving data form
$id = $_POST['id_temp'];
$responsible = $_POST['responsible'];
$name = $_POST['name'];
$name = strtoupper($name);
$box = $_POST['box'];
$user_id = $user->getId();
$date = date("Y-m-d");
$time = date("H:i:s");

//Gravando no banco de dados ! conectando com o localhost - mysql
if (!connect())
	die ("Erro de conexao com localhost, o seguinte erro ocorreu -> ".mysqli_connect_error());

$con = $_SESSION['con'];
$query = "SELECT * FROM deposits_group_temp WHERE id = '$id' AND user = '$user_id'";
$result = mysqli_query($con, $query);
$num_rows = mysqli_num_rows($result);
if($num_rows > 0){
	// Inserir cliente primeiro
	if( $name != "" || isset($_POST['responsible']) ){
		// Definindo o cliente
		if($name != ""){
			$query_in_cus = "INSERT INTO `deposits_responsible` (`id`, `name`) VALUES (NULL, '$name')";
			$result_in_cus = mysqli_query($con,$query_in_cus);
			$affected_rows_in_cus = mysqli_affected_rows($con);
			if($affected_rows_in_cus <= 0){
				echo "Erro ao cadastrar responsável.";
			}
			// Pegar id do entregador adicionado
			$query_get_customer = "SELECT * FROM deposits_responsible WHERE name = '$name'";
			$result_get_customer = mysqli_query($con,$query_get_customer);
			$num_rows_get_customer = mysqli_num_rows($result_get_customer);
			if($num_rows_get_customer > 0){
				while($row_get_deposits_responsible = mysqli_fetch_array($result_get_customer)){
					$responsible_id = $row_get_deposits_responsible['id'];
				}
			} else {
				echo "Erro ao consultar responsável cadastrado.";
			}
		} else {
			$responsible_id = $_POST['responsible'];
		}
		// Criando a venda	
		$query_in = "INSERT INTO `deposits_group` (`id`, `responsible`, `date`, `time`, `user`, `box`) VALUES (NULL,'$responsible_id','$date','$time','$user_id','$box')";
		$result_in = mysqli_query($con,$query_in);
		$affected_rows_in = mysqli_affected_rows($con);
		if($affected_rows_in > 0){
			$query_get_sale_id = "SELECT * FROM deposits_group WHERE responsible = '$responsible_id' AND date = '$date' AND time = '$time' AND user = '$user_id' AND box = '$box'";
			$result_get_sale_id = mysqli_query($con,$query_get_sale_id);
			$num_rows_get_sale_id = mysqli_num_rows($result_get_sale_id);
			if($num_rows_get_sale_id > 0){
				while($row_get_sale_id = mysqli_fetch_array($result_get_sale_id)){
					$deposit_group_id = $row_get_sale_id['id'];
				}
				$query_products_insert = "SELECT * FROM deposits_temp WHERE id_group = '$id'";
				$result_products_insert = mysqli_query($con,$query_products_insert);
				$num_rows_products_insert = mysqli_num_rows($result_products_insert);
				if($num_rows_products_insert > 0){
					while($row_products_insert = mysqli_fetch_array($result_products_insert)){
						$description = $row_products_insert['description'];
						$code = $row_products_insert['code'];
						$value = $row_products_insert['value'];
						$query_insert_products = "INSERT INTO deposits (`id`, `id_group`, `description`, `code`, `value`) VALUES (NULL, '$deposit_group_id', '$description', '$code', '$value')";
						$result_insert_products = mysqli_query($con,$query_insert_products);
						$affected_rows_insert_products = mysqli_affected_rows($con);
						if($affected_rows_insert_products <= 0){
							echo "Erro ao inserir despósito na lista definitiva.";
						}
					}
					// Update para apagar dados temporários
					$query_del = "DELETE FROM `deposits_temp` WHERE id_group = '$id'";
					$result_del = mysqli_query($con, $query_del);
					$affected_rows_del = mysqli_affected_rows($con);
					if($affected_rows_del > 0) {
						$query_del_sales_temp = "DELETE FROM `deposits_group_temp` WHERE id = '$id' AND user = '$user_id'";
						$result_del_sales_temp = mysqli_query($con, $query_del_sales_temp);
						$affected_rows_del_sales_temp = mysqli_affected_rows($con);
						if($affected_rows_del_sales_temp > 0){
							header("Location: ../cashier/showdeposit?id=".$deposit_group_id);
						} else {
							echo "Erro ao deletar identificador temporário.";
						}
					} else {
						echo "Erro ao excluir depósitos temporários.";
					}
				} else {
					echo "Não há protocolos de entrega temporários gravados!";
				}					
			} else {
				echo "Erro ao consultar protocolo criado.";
			}
		} else {
			echo "Erro ao criar protocolo.";
		}
	} else {
		echo "O campo responsável não pode ficar em branco.";
	}
} else {
	echo "Esse identificador temporário nao existe.";
}

// fim da autenticaçao
} else {
    header("Location: ../login");
}
?>