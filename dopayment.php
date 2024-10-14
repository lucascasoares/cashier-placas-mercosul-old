<?php
header('Content-Type: text/html; charset=utf8');
$system_name = "emplacarrn";
include 'db.php';
include 'User.php';
session_start();
date_default_timezone_set('America/Fortaleza');

function methodname($method){
	switch ($method) {
		// Em espécie
		case 1:
			$name = "Dinheiro";
			break;
		// Débito
		case 2:
			$name = "Débito";
			break;
		// Crédito
		case 3:
			$name = "Crédito";
			break;
		// Carteira
		case 4:
			$name = "Carteira";
			break;
		// Boleto
		case 5:
			$name = "Boleto";
			break;
		// Cheque
		case 6:
			$name = "Cheque";
			break;
		// Transferencia
		case 7:
			$name = "Transferencia Bancária";
			break;
		// Boleto pelo site
		case 8:
			$name = "Boleto Site";
			break;
		case 9:
			$name = "Pix";
			break;
	}
	return $name;
}
if (isset($_SESSION['user'])) {
	$user = $_SESSION['user'];
	$user_id = $user->getId();
	$establishment = $user->getEstablishment();
	$pf = $_POST['pf'];
	$box = $_POST['box'];
	if($pf != 1){
		if($user->getRole() == 1 || $user->getRole() == 3 || $user->getRole() == 5){
			$cashier = $box;
			$token = $_POST['token'];
			$protocol = $_POST['protocol'];
			$qt = $_POST['qt_chb'];
			$date = $_POST['date'];
			// Depois verificar se o método realmente existe
			$method = $_POST['method'];
			// Depois ver se bate o que foi pago com o que tem no banco
			$receb = $_POST['receb_input']; // Valor total recebido
			$date_now = date("Y-m-d");
			$time = date("H:i:s");
			$fracionado = $_POST['fracionado'];
			$fracionado = str_replace(".", "", $fracionado); // Removendo pontos que porventura a máscara possa ter colocado
			$fracionado = str_replace(",", ".", $fracionado); // Trocando vírgula por ponto
			// Caso o fracionado nao tenha sido setado, ele recebe zero
			if(!isset($_POST['fracionado'])){
				$fracionado = 0;
			}

			// Verificar se o token nao já foi pago
			if(connect()){
				$con = $_SESSION['con'];
				$query_token = "SELECT * FROM payments WHERE token = '$token'";
				$result_token = mysqli_query($con,$query_token);
				$num_results = mysqli_num_rows($result_token);
				if($num_results > 0){
					if($date != ""){
						header("Location: payment?protocol=".$protocol."&date=".$date."&s=1");
					} else {
						header("Location: payment?protocol=".$protocol."&s=1");
					}
				} else {
					// Fazendo a inserçao de fato
					if($fracionado <= $receb){
						$error = FALSE;
						$diferenca = $fracionado;
						if($fracionado != 0){
							for($i = 1; $i <= $qt; $i++){
								$check_name = "checkbox".$i;
								$valor_prod = $_POST[$check_name];
								if( isset($valor_prod) ){
									$id_name_plate = "checkbox".$i."v";
									$id_plate = $_POST[$id_name_plate];
									// Depois verificar se o valor pago é igual ao do produto lá na venda
									$valor_prod = str_replace(",", ".", $valor_prod);
									if(($diferenca > $valor_prod) && $i != $qt){
										$q = "INSERT INTO `payments` ( `id`, `cashier`, `protocol`, `id_sale_product`, `payment_method`, `value`, `date`, `time`, `user`, `token`) VALUES (NULL, '$cashier', '$protocol', '$id_plate', '$method', '$valor_prod', '$date_now', '$time', '$user_id', '$token')";
										$diferenca -= $valor_prod;
										$res = mysqli_query($con, $q);
										$affected_rows = mysqli_affected_rows($con);
										if ($affected_rows <= 0) {
											$error = TRUE;
										}
										
										// Atualizando tabelas de relatórios
										$method_name = methodname($method);
										$query_sps = "SELECT * FROM show_payed_sales WHERE protocol = '$protocol' AND payment_method = '$method_name'";
										$result_sps = mysqli_query($con,$query_sps);
										$num_rows_sps = mysqli_num_rows($result_sps);
										if($num_rows_sps > 0){
											while($row_sps = mysqli_fetch_array($result_sps)){
												$new_value = $row_sps['value'];
											}
											$new_value += $valor_prod;
											$query_up_nv = "UPDATE `show_payed_sales` SET `value`='$new_value' WHERE protocol = '$protocol' AND payment_method = '$method_name'";
											$result_up_nv = mysqli_query($con,$query_up_nv);
										} else {
											$query_gcid = "SELECT * FROM sales WHERE id = '$protocol'";
											$result_gcid = mysqli_query($con,$query_gcid);
											while($row_gcid = mysqli_fetch_array($result_gcid)){
												$customer_id = $row_gcid['customer'];
												if($customer_id == 0){
													$customer_name = "Cliente não cadastrado";
												} else {
													$query_gcn = "SELECT * FROM customers WHERE id = '$customer_id'";
													$result_gcn = mysqli_query($con,$query_gcn);
													while($row_gcn = mysqli_fetch_array($result_gcn)){
														$customer_name = $row_gcn['name'];
													}
												}
											}									
											$query_in_sps = "INSERT INTO `show_payed_sales`(`id`, `date`, `customer_name`, `protocol`, `payment_method`, `value`, `box`, `establishment`) VALUES (NULL,'$date_now','$customer_name','$protocol','$method_name','$valor_prod','$cashier','$establishment')";
											$result_in_sps = mysqli_query($con,$query_in_sps);
										}
										// Atualizando tabelas de relatórios 2
										$query_sps = "SELECT * FROM report_total_opened_protocols WHERE protocol = '$protocol'";
										$result_sps = mysqli_query($con,$query_sps);
										$num_rows_sps = mysqli_num_rows($result_sps);
										if($num_rows_sps > 0){
											while($row_sps = mysqli_fetch_array($result_sps)){
												$new_value = $row_sps['total_paid'];
											}
											$new_value += $valor_prod;
											$query_up_nv = "UPDATE `report_total_opened_protocols` SET `total_paid`='$new_value' WHERE protocol = '$protocol'";
											$result_up_nv = mysqli_query($con,$query_up_nv);
										}
										// Atualizando os resumos
										$query_sss = "SELECT * FROM show_summary_sales WHERE date = '$date_now' AND payment_method = '$method_name' AND box = '$cashier'";
										$result_sss = mysqli_query($con,$query_sss);
										$num_rows_sss = mysqli_num_rows($result_sss);
										if($num_rows_sss > 0){
											while($row_sss = mysqli_fetch_array($result_sss)){
												$new_value_ss = $row_sss['value'];
											}
											$new_value_ss += $valor_prod;
											$query_up_nvss = "UPDATE `show_summary_sales` SET `value`='$new_value_ss' WHERE date = '$date_now' AND payment_method = '$method_name' AND box = '$cashier'";
											$result_up_nvss = mysqli_query($con,$query_up_nvss);
										} else {		
											$query_in_sss = "INSERT INTO `show_summary_sales`(`id`, `date`, `payment_method`, `value`, `box`, `establishment`) VALUES (NULL,'$date_now','$method_name','$valor_prod','$cashier','$establishment')";
											$result_in_sss = mysqli_query($con,$query_in_sss);
										}
										
										// Salvando total pago do dia no banco
										$query_verify = "SELECT date FROM sales WHERE id = '$protocol'";
										$result_verify = mysqli_query($con,$query_verify);
										$row_verify = mysqli_fetch_assoc($result_verify);
										$date_prot = $row_verify['date'];
										$query_sel_tot = "SELECT * FROM total_paid_protocols_day WHERE date = '$date_prot' AND establishment = '$establishment'";
										$result_sel_tot = mysqli_query($con,$query_sel_tot);
										$num_rows_in_tot = mysqli_num_rows($result_sel_tot);
										if($num_rows_in_tot > 0){
											while($row_in_tot = mysqli_fetch_array($result_sel_tot)){
												$vtd = $row_in_tot['value'];
											}
											// UPDATE
											$vtd += $valor_prod;
											$query_up_tot = "UPDATE `total_paid_protocols_day` SET `value`='$vtd' WHERE date = '$date_prot' AND `establishment`='$establishment'";
											mysqli_query($con,$query_up_tot);
										} else {
											// INSERT
											$query_in_tot = "INSERT INTO `total_paid_protocols_day`(`id`, `date`, `value`, `establishment`) VALUES (NULL,'$date_prot','$valor_prod','$establishment')";
											mysqli_query($con,$query_in_tot);
										}
									} else {
										$q = "INSERT INTO `payments` ( `id`, `cashier`, `protocol`, `id_sale_product`, `payment_method`, `value`, `date`, `time`, `user`, `token`) VALUES (NULL, '$cashier', '$protocol', '$id_plate', '$method', '$diferenca', '$date_now', '$time', '$user_id', '$token')";
										$res = mysqli_query($con, $q);
										$affected_rows = mysqli_affected_rows($con);
										if ($affected_rows <= 0) {
											$error = TRUE;
										}
										
										// Atualizando tabelas de relatórios
										$method_name = methodname($method);
										$query_sps = "SELECT * FROM show_payed_sales WHERE protocol = '$protocol' AND payment_method = '$method_name'";
										$result_sps = mysqli_query($con,$query_sps);
										$num_rows_sps = mysqli_num_rows($result_sps);
										if($num_rows_sps > 0){
											while($row_sps = mysqli_fetch_array($result_sps)){
												$new_value = $row_sps['value'];
											}
											$new_value += $diferenca;
											$query_up_nv = "UPDATE `show_payed_sales` SET `value`='$new_value' WHERE protocol = '$protocol' AND payment_method = '$method_name'";
											$result_up_nv = mysqli_query($con,$query_up_nv);
										} else {
											$query_gcid = "SELECT * FROM sales WHERE id = '$protocol'";
											$result_gcid = mysqli_query($con,$query_gcid);
											while($row_gcid = mysqli_fetch_array($result_gcid)){
												$customer_id = $row_gcid['customer'];
												if($customer_id == 0){
													$customer_name = "Cliente não cadastrado";
												} else {
													$query_gcn = "SELECT * FROM customers WHERE id = '$customer_id'";
													$result_gcn = mysqli_query($con,$query_gcn);
													while($row_gcn = mysqli_fetch_array($result_gcn)){
														$customer_name = $row_gcn['name'];
													}
												}
											}										
											$query_in_sps = "INSERT INTO `show_payed_sales`(`id`, `date`, `customer_name`, `protocol`, `payment_method`, `value`, `box`, `establishment`) VALUES (NULL,'$date_now','$customer_name','$protocol','$method_name','$diferenca','$cashier','$establishment')";
											$result_in_sps = mysqli_query($con,$query_in_sps);
										}
										// Atualizando tabelas de relatórios 2
										$query_sps = "SELECT * FROM report_total_opened_protocols WHERE protocol = '$protocol'";
										$result_sps = mysqli_query($con,$query_sps);
										$num_rows_sps = mysqli_num_rows($result_sps);
										if($num_rows_sps > 0){
											while($row_sps = mysqli_fetch_array($result_sps)){
												$new_value = $row_sps['total_paid'];
											}
											$new_value += $diferenca;
											$query_up_nv = "UPDATE `report_total_opened_protocols` SET `total_paid`='$new_value' WHERE protocol = '$protocol'";
											$result_up_nv = mysqli_query($con,$query_up_nv);
										}
										// Atualizando os resumos
										$query_sss = "SELECT * FROM show_summary_sales WHERE date = '$date_now' AND payment_method = '$method_name' AND box = '$cashier'";
										$result_sss = mysqli_query($con,$query_sss);
										$num_rows_sss = mysqli_num_rows($result_sss);
										if($num_rows_sss > 0){
											while($row_sss = mysqli_fetch_array($result_sss)){
												$new_value_ss = $row_sss['value'];
											}
											$new_value_ss += $diferenca;
											$query_up_nvss = "UPDATE `show_summary_sales` SET `value`='$new_value_ss' WHERE date = '$date_now' AND payment_method = '$method_name' AND box = '$cashier'";
											$result_up_nvss = mysqli_query($con,$query_up_nvss);
										} else {										
											$query_in_sss = "INSERT INTO `show_summary_sales`(`id`, `date`, `payment_method`, `value`, `box`, `establishment`) VALUES (NULL,'$date_now','$method_name','$diferenca','$cashier','$establishment')";
											$result_in_sss = mysqli_query($con,$query_in_sss);
										}
										
										// Salvando total pago do dia no banco
										$query_verify = "SELECT date FROM sales WHERE id = '$protocol'";
										$result_verify = mysqli_query($con,$query_verify);
										$row_verify = mysqli_fetch_assoc($result_verify);
										$date_prot = $row_verify['date'];
										$query_sel_tot = "SELECT * FROM total_paid_protocols_day WHERE date = '$date_prot' AND establishment = '$establishment'";
										$result_sel_tot = mysqli_query($con,$query_sel_tot);
										$num_rows_in_tot = mysqli_num_rows($result_sel_tot);
										if($num_rows_in_tot > 0){
											while($row_in_tot = mysqli_fetch_array($result_sel_tot)){
												$vtd = $row_in_tot['value'];
											}
											// UPDATE
											$vtd += $diferenca;
											$query_up_tot = "UPDATE `total_paid_protocols_day` SET `value`='$vtd' WHERE date = '$date_prot' AND `establishment`='$establishment'";
											mysqli_query($con,$query_up_tot);
										} else {
											// INSERT
											$query_in_tot = "INSERT INTO `total_paid_protocols_day`(`id`, `date`, `value`, `establishment`) VALUES (NULL,'$date_prot','$diferenca','$establishment')";
											mysqli_query($con,$query_in_tot);
										}											
										
										break;
									}
								}
							}
						} else {
							// Nesse caso o pagamento nao é fracionado
							for($i = 1; $i <= $qt; $i++){
								$check_name = "checkbox".$i;
								$valor_prod = $_POST[$check_name];
								if( isset($valor_prod) ){
									$id_name_plate = "checkbox".$i."v";
									$id_plate = $_POST[$id_name_plate];
									// Depois verificar se o valor pago é igual ao do produto lá na venda
									$valor_prod = str_replace(",", ".", $valor_prod);
									if(connect()){
										$con = $_SESSION['con'];
										$q = "INSERT INTO `payments` ( `id`, `cashier`, `protocol`, `id_sale_product`, `payment_method`, `value`, `date`, `time`, `user`, `token`) VALUES (NULL, '$cashier', '$protocol', '$id_plate', '$method', '$valor_prod', '$date_now', '$time', '$user_id', '$token')";
										$diferenca -= $valor_prod;
										$res = mysqli_query($con, $q);
										$affected_rows = mysqli_affected_rows($con);
										if ($affected_rows <= 0) {
											$error = TRUE;
										}
										
										// Atualizando tabelas de relatórios
										$method_name = methodname($method);
										$query_sps = "SELECT * FROM show_payed_sales WHERE protocol = '$protocol' AND payment_method = '$method_name'";
										$result_sps = mysqli_query($con,$query_sps);
										$num_rows_sps = mysqli_num_rows($result_sps);
										if($num_rows_sps > 0){
											while($row_sps = mysqli_fetch_array($result_sps)){
												$new_value = $row_sps['value'];
											}
											$new_value += $valor_prod;
											$query_up_nv = "UPDATE `show_payed_sales` SET `value`='$new_value' WHERE protocol = '$protocol' AND payment_method = '$method_name'";
											$result_up_nv = mysqli_query($con,$query_up_nv);
										} else {
											$query_gcid = "SELECT * FROM sales WHERE id = '$protocol'";
											$result_gcid = mysqli_query($con,$query_gcid);
											while($row_gcid = mysqli_fetch_array($result_gcid)){
												$customer_id = $row_gcid['customer'];
												if($customer_id == 0){
													$customer_name = "Cliente não cadastrado";
												} else {
													$query_gcn = "SELECT * FROM customers WHERE id = '$customer_id'";
													$result_gcn = mysqli_query($con,$query_gcn);
													while($row_gcn = mysqli_fetch_array($result_gcn)){
														$customer_name = $row_gcn['name'];
													}
												}
											}										
											$query_in_sps = "INSERT INTO `show_payed_sales`(`id`, `date`, `customer_name`, `protocol`, `payment_method`, `value`, `box`, `establishment`) VALUES (NULL,'$date_now','$customer_name','$protocol','$method_name','$valor_prod','$cashier','$establishment')";
											$result_in_sps = mysqli_query($con,$query_in_sps);
										}
										// Atualizando tabelas de relatórios 2
										$query_sps = "SELECT * FROM report_total_opened_protocols WHERE protocol = '$protocol'";
										$result_sps = mysqli_query($con,$query_sps);
										$num_rows_sps = mysqli_num_rows($result_sps);
										if($num_rows_sps > 0){
											while($row_sps = mysqli_fetch_array($result_sps)){
												$new_value = $row_sps['total_paid'];
											}
											$new_value += $valor_prod;
											$query_up_nv = "UPDATE `report_total_opened_protocols` SET `total_paid`='$new_value' WHERE protocol = '$protocol'";
											$result_up_nv = mysqli_query($con,$query_up_nv);
										}
										// Atualizando os resumos
										$query_sss = "SELECT * FROM show_summary_sales WHERE date = '$date_now' AND payment_method = '$method_name' AND box = '$cashier'";
										$result_sss = mysqli_query($con,$query_sss);
										$num_rows_sss = mysqli_num_rows($result_sss);
										if($num_rows_sss > 0){
											while($row_sss = mysqli_fetch_array($result_sss)){
												$new_value_ss = $row_sss['value'];
											}
											$new_value_ss += $valor_prod;
											$query_up_nvss = "UPDATE `show_summary_sales` SET `value`='$new_value_ss' WHERE date = '$date_now' AND payment_method = '$method_name' AND box = '$cashier'";
											$result_up_nvss = mysqli_query($con,$query_up_nvss);
										} else {										
											$query_in_sss = "INSERT INTO `show_summary_sales`(`id`, `date`, `payment_method`, `value`, `box`, `establishment`) VALUES (NULL,'$date_now','$method_name','$valor_prod','$cashier','$establishment')";
											$result_in_sss = mysqli_query($con,$query_in_sss);
										}
										
										// Salvando total pago do dia no banco
										$query_verify = "SELECT date FROM sales WHERE id = '$protocol'";
										$result_verify = mysqli_query($con,$query_verify);
										$row_verify = mysqli_fetch_assoc($result_verify);
										$date_prot = $row_verify['date'];
										$query_sel_tot = "SELECT * FROM total_paid_protocols_day WHERE date = '$date_prot' AND establishment = '$establishment'";
										$result_sel_tot = mysqli_query($con,$query_sel_tot);
										$num_rows_in_tot = mysqli_num_rows($result_sel_tot);
										if($num_rows_in_tot > 0){
											while($row_in_tot = mysqli_fetch_array($result_sel_tot)){
												$vtd = $row_in_tot['value'];
											}
											// UPDATE
											$vtd += $valor_prod;
											$query_up_tot = "UPDATE `total_paid_protocols_day` SET `value`='$vtd' WHERE date = '$date_prot' AND `establishment`='$establishment'";
											mysqli_query($con,$query_up_tot);
										} else {
											// INSERT
											$query_in_tot = "INSERT INTO `total_paid_protocols_day`(`id`, `date`, `value`, `establishment`) VALUES (NULL,'$date_prot','$valor_prod','$establishment')";
											mysqli_query($con,$query_in_tot);
										}
										
									} else{
										echo "Nao foi possível conectar-se com o banco de dados.";
									}
								}
							}
						}
						if(!$error){
							// Tudo ok
							if($date != ""){
								if($pf != 1){
									header("Location: payment?protocol=".$protocol."&date=".$date."&s=1");
								} else {
									header("Location: payment?protocol=".$protocol."&date=".$date."&pf=1&s=1");
								}
							} else {
								if($pf != 1){
									header("Location: payment?protocol=".$protocol."&s=1");
								} else {
									header("Location: payment?protocol=".$protocol."&pf=1&s=1");
								}
							}
						} else {
							header("Location: payment?protocol=".$protocol."&date=".$date."&e=2");
						}	
					} else {
						header("Location: payment?protocol=".$protocol."&date=".$date."&e=1");
					}	
				}
			} else{
				echo "Nao foi possível conectar-se com o banco de dados.";
			}
		} else {
			header("Location: ../login");
		}
	} else {

		// Valor recebido nao pode ser maior que o valor total
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
								$total = $_POST['total'];
								$total = str_replace(".", "", $total); // Removendo pontos que porventura a máscara possa ter colocado
								$total = str_replace(",", ".", $total); // Trocando vírgula por ponto
								$valor_recebido = $_POST['valor_recebido'];
								$valor_recebido = str_replace(".", "", $valor_recebido); // Removendo pontos que porventura a máscara possa ter colocado
								$valor_recebido = str_replace(",", ".", $valor_recebido); // Trocando vírgula por ponto
								if($valor_recebido > 0){
									if($valor_recebido > $total){
										$receb = $total;
									} else {
										$receb = $valor_recebido;
									}
									$cashier = $box;
									$token = $_POST['token'];
									$protocol = $_POST['protocol'];
									// Depois verificar se o método realmente existe
									$method = $_POST['method'];
									// Depois ver se bate o que foi pago com o que tem no banco
									$date_now = date("Y-m-d");
									$time = date("H:i:s");
									
									// Verificar se o token nao já foi pago
									$query_token = "SELECT * FROM payments WHERE token = '$token'";
									$result_token = mysqli_query($con,$query_token);
									$num_results = mysqli_num_rows($result_token);
									if($num_results > 0){
										header("Location: payment?protocol=".$protocol."&pf=1&s=1");
									} else {
										// Fazendo a inserçao de fato
										$error = FALSE;
										// Pegar valor do produto - faz um loop
										$query_get_valores = "SELECT * FROM sale_products WHERE sale = '$protocol'";
										$result_get_valores = mysqli_query($con,$query_get_valores);
										$num_rows_get_valores = mysqli_num_rows($result_get_valores);
										if($num_rows_get_valores > 0){
											while($row_get_valores = mysqli_fetch_array($result_get_valores)){
												$id_product = $row_get_valores['id'];
												$plate = $row_get_valores['plate'];
												$product = $row_get_valores['product'];
												$value_product = $row_get_valores['value'];
												$quantity_product = $row_get_valores['quantity'];
												$total_product = $value_product*$quantity_product;
												
												//Verificar se já está pago algo
												$query_ver_pay = "SELECT * FROM payments WHERE id_sale_product = '$id_product'";
												$result_ver_pay = mysqli_query($con,$query_ver_pay);
												$num_rows_ver_pay = mysqli_num_rows($result_ver_pay);
												if($num_rows_ver_pay > 0){
													$total_product_payed = 0;
													while($row_ver_pay = mysqli_fetch_array($result_ver_pay)){
														$total_product_payed += $row_ver_pay['value'];
													}
													if($total_product_payed != $total_product){
														$diferenca = $total_product - $total_product_payed;
														if($receb >= $diferenca){
															$q = "INSERT INTO `payments` ( `id`, `cashier`, `protocol`, `id_sale_product`, `payment_method`, `value`, `date`, `time`, `user`, `token`) VALUES (NULL, '$cashier', '$protocol', '$id_product', '$method', '$diferenca', '$date_now', '$time', '$user_id', '$token')";
															$res = mysqli_query($con, $q);
															$affected_rows = mysqli_affected_rows($con);
															if ($affected_rows <= 0) {
																$error = TRUE;
															} else {
																$receb -= $diferenca;
																if($product == 1 || $product == 2){
																	// Aqui eu devo inserir a placa para produçao, se ela foi totalmente paga
																	$query_plate_production = "INSERT INTO `production_plates`(`id`, `protocol`, `plate`, `date_created`, `time_created`, `user_created`, `catched`, `date_catched`, `time_catched`, `responsible`, `establishment`) VALUES (NULL,'$protocol','$plate','$date_now','$time','$user_id','0',NULL,NULL,'0','$establishment')";
																	$result_plate_production = mysqli_query($con,$query_plate_production);
																	$affected_rows_pp = mysqli_affected_rows($con);
																	if($affected_rows_pp <= 0){
																		echo "Erro ao inserir placa na tabela de produçao.";
																	}
																}
															}
															
															// Atualizando tabelas de relatórios
															$method_name = methodname($method);
															$query_sps = "SELECT * FROM show_payed_sales WHERE protocol = '$protocol' AND payment_method = '$method_name'";
															$result_sps = mysqli_query($con,$query_sps);
															$num_rows_sps = mysqli_num_rows($result_sps);
															if($num_rows_sps > 0){
																while($row_sps = mysqli_fetch_array($result_sps)){
																	$new_value = $row_sps['value'];
																}
																$new_value += $diferenca;
																$query_up_nv = "UPDATE `show_payed_sales` SET `value`='$new_value' WHERE protocol = '$protocol' AND payment_method = '$method_name'";
																$result_up_nv = mysqli_query($con,$query_up_nv);
															} else {
																$query_gcid = "SELECT * FROM sales WHERE id = '$protocol'";
																$result_gcid = mysqli_query($con,$query_gcid);
																while($row_gcid = mysqli_fetch_array($result_gcid)){
																	$customer_id = $row_gcid['customer'];
																	if($customer_id == 0){
																		$customer_name = "Cliente não cadastrado";
																	} else {
																		$query_gcn = "SELECT * FROM customers WHERE id = '$customer_id'";
																		$result_gcn = mysqli_query($con,$query_gcn);
																		while($row_gcn = mysqli_fetch_array($result_gcn)){
																			$customer_name = $row_gcn['name'];
																		}
																	}
																}										
																$query_in_sps = "INSERT INTO `show_payed_sales`(`id`, `date`, `customer_name`, `protocol`, `payment_method`, `value`, `box`, `establishment`) VALUES (NULL,'$date_now','$customer_name','$protocol','$method_name','$diferenca','$cashier','$establishment')";
																$result_in_sps = mysqli_query($con,$query_in_sps);
															}
															// Atualizando tabelas de relatórios 2
															$query_sps = "SELECT * FROM report_total_opened_protocols WHERE protocol = '$protocol'";
															$result_sps = mysqli_query($con,$query_sps);
															$num_rows_sps = mysqli_num_rows($result_sps);
															if($num_rows_sps > 0){
																while($row_sps = mysqli_fetch_array($result_sps)){
																	$new_value = $row_sps['total_paid'];
																}
																$new_value += $diferenca;
																$query_up_nv = "UPDATE `report_total_opened_protocols` SET `total_paid`='$new_value' WHERE protocol = '$protocol'";
																$result_up_nv = mysqli_query($con,$query_up_nv);
															}
															// Atualizando os resumos
															$query_sss = "SELECT * FROM show_summary_sales WHERE date = '$date_now' AND payment_method = '$method_name' AND box = '$cashier'";
															$result_sss = mysqli_query($con,$query_sss);
															$num_rows_sss = mysqli_num_rows($result_sss);
															if($num_rows_sss > 0){
																while($row_sss = mysqli_fetch_array($result_sss)){
																	$new_value_ss = $row_sss['value'];
																}
																$new_value_ss += $diferenca;
																$query_up_nvss = "UPDATE `show_summary_sales` SET `value`='$new_value_ss' WHERE date = '$date_now' AND payment_method = '$method_name' AND box = '$cashier'";
																$result_up_nvss = mysqli_query($con,$query_up_nvss);
															} else {										
																$query_in_sss = "INSERT INTO `show_summary_sales`(`id`, `date`, `payment_method`, `value`, `box`, `establishment`) VALUES (NULL,'$date_now','$method_name','$diferenca','$cashier','$establishment')";
																$result_in_sss = mysqli_query($con,$query_in_sss);
															}
															
															// Salvando total pago do dia no banco
															$query_verify = "SELECT date FROM sales WHERE id = '$protocol'";
															$result_verify = mysqli_query($con,$query_verify);
															$row_verify = mysqli_fetch_assoc($result_verify);
															$date_prot = $row_verify['date'];
															$query_sel_tot = "SELECT * FROM total_paid_protocols_day WHERE date = '$date_prot' AND establishment = '$establishment'";
															$result_sel_tot = mysqli_query($con,$query_sel_tot);
															$num_rows_in_tot = mysqli_num_rows($result_sel_tot);
															if($num_rows_in_tot > 0){
																while($row_in_tot = mysqli_fetch_array($result_sel_tot)){
																	$vtd = $row_in_tot['value'];
																}
																// UPDATE
																$vtd += $diferenca;
																$query_up_tot = "UPDATE `total_paid_protocols_day` SET `value`='$vtd' WHERE date = '$date_prot' AND `establishment`='$establishment'";
																mysqli_query($con,$query_up_tot);
															} else {
																// INSERT
																$query_in_tot = "INSERT INTO `total_paid_protocols_day`(`id`, `date`, `value`, `establishment`) VALUES (NULL,'$date_prot','$diferenca','$establishment')";
																mysqli_query($con,$query_in_tot);
															}
															
														} else {
															$q = "INSERT INTO `payments` ( `id`, `cashier`, `protocol`, `id_sale_product`, `payment_method`, `value`, `date`, `time`, `user`, `token`) VALUES (NULL, '$cashier', '$protocol', '$id_product', '$method', '$receb', '$date_now', '$time', '$user_id', '$token')";
															$res = mysqli_query($con, $q);
															$affected_rows = mysqli_affected_rows($con);
															if ($affected_rows <= 0) {
																$error = TRUE;
															}
															
															// Atualizando tabelas de relatórios
															$method_name = methodname($method);
															$query_sps = "SELECT * FROM show_payed_sales WHERE protocol = '$protocol' AND payment_method = '$method_name' AND box = '$cashier'";
															$result_sps = mysqli_query($con,$query_sps);
															$num_rows_sps = mysqli_num_rows($result_sps);
															if($num_rows_sps > 0){
																while($row_sps = mysqli_fetch_array($result_sps)){
																	$new_value = $row_sps['value'];
																}
																$new_value += $receb;
																$query_up_nv = "UPDATE `show_payed_sales` SET `value`='$new_value' WHERE protocol = '$protocol' AND payment_method = '$method_name' AND box = '$cashier'";
																$result_up_nv = mysqli_query($con,$query_up_nv);
															} else {
																$query_gcid = "SELECT * FROM sales WHERE id = '$protocol'";
																$result_gcid = mysqli_query($con,$query_gcid);
																while($row_gcid = mysqli_fetch_array($result_gcid)){
																	$customer_id = $row_gcid['customer'];
																	if($customer_id == 0){
																		$customer_name = "Cliente não cadastrado";
																	} else {
																		$query_gcn = "SELECT * FROM customers WHERE id = '$customer_id'";
																		$result_gcn = mysqli_query($con,$query_gcn);
																		while($row_gcn = mysqli_fetch_array($result_gcn)){
																			$customer_name = $row_gcn['name'];
																		}
																	}
																}										
																$query_in_sps = "INSERT INTO `show_payed_sales`(`id`, `date`, `customer_name`, `protocol`, `payment_method`, `value`, `box`, `establishment`) VALUES (NULL,'$date_now','$customer_name','$protocol','$method_name','$receb','$cashier','$establishment')";
																$result_in_sps = mysqli_query($con,$query_in_sps);
															}
															// Atualizando tabelas de relatórios 2
															$query_sps = "SELECT * FROM report_total_opened_protocols WHERE protocol = '$protocol'";
															$result_sps = mysqli_query($con,$query_sps);
															$num_rows_sps = mysqli_num_rows($result_sps);
															if($num_rows_sps > 0){
																while($row_sps = mysqli_fetch_array($result_sps)){
																	$new_value = $row_sps['total_paid'];
																}
																$new_value += $receb;
																$query_up_nv = "UPDATE `report_total_opened_protocols` SET `total_paid`='$new_value' WHERE protocol = '$protocol'";
																$result_up_nv = mysqli_query($con,$query_up_nv);
															}
															// Atualizando os resumos
															$query_sss = "SELECT * FROM show_summary_sales WHERE date = '$date_now' AND payment_method = '$method_name' AND box = '$cashier'";
															$result_sss = mysqli_query($con,$query_sss);
															$num_rows_sss = mysqli_num_rows($result_sss);
															if($num_rows_sss > 0){
																while($row_sss = mysqli_fetch_array($result_sss)){
																	$new_value_ss = $row_sss['value'];
																}
																$new_value_ss += $receb;
																$query_up_nvss = "UPDATE `show_summary_sales` SET `value`='$new_value_ss' WHERE date = '$date_now' AND payment_method = '$method_name' AND box = '$cashier'";
																$result_up_nvss = mysqli_query($con,$query_up_nvss);
															} else {										
																$query_in_sss = "INSERT INTO `show_summary_sales`(`id`, `date`, `payment_method`, `value`, `box`, `establishment`) VALUES (NULL,'$date_now','$method_name','$receb','$cashier','$establishment')";
																$result_in_sss = mysqli_query($con,$query_in_sss);
															}
															
															// Salvando total pago do dia no banco
															$query_verify = "SELECT date FROM sales WHERE id = '$protocol'";
															$result_verify = mysqli_query($con,$query_verify);
															$row_verify = mysqli_fetch_assoc($result_verify);
															$date_prot = $row_verify['date'];
															$query_sel_tot = "SELECT * FROM total_paid_protocols_day WHERE date = '$date_prot' AND establishment = '$establishment'";
															$result_sel_tot = mysqli_query($con,$query_sel_tot);
															$num_rows_in_tot = mysqli_num_rows($result_sel_tot);
															if($num_rows_in_tot > 0){
																while($row_in_tot = mysqli_fetch_array($result_sel_tot)){
																	$vtd = $row_in_tot['value'];
																}
																// UPDATE
																$vtd += $receb;
																$query_up_tot = "UPDATE `total_paid_protocols_day` SET `value`='$vtd' WHERE date = '$date_prot' AND `establishment`='$establishment'";
																mysqli_query($con,$query_up_tot);
															} else {
																// INSERT
																$query_in_tot = "INSERT INTO `total_paid_protocols_day`(`id`, `date`, `value`, `establishment`) VALUES (NULL,'$date_prot','$receb','$establishment')";
																mysqli_query($con,$query_in_tot);
															}

															break;
														}
													}
												} else {
													// Tem que pegar a placa que vai receber os valores
													if($receb >= $total_product){
														$q = "INSERT INTO `payments` ( `id`, `cashier`, `protocol`, `id_sale_product`, `payment_method`, `value`, `date`, `time`, `user`, `token`) VALUES (NULL, '$cashier', '$protocol', '$id_product', '$method', '$total_product', '$date_now', '$time', '$user_id', '$token')";
														$res = mysqli_query($con, $q);
														$affected_rows = mysqli_affected_rows($con);
														if ($affected_rows <= 0) {
															$error = TRUE;
														} else {
															$receb -= $total_product;
															if($product == 1 || $product == 2){
																// Aqui eu devo inserir a placa para produçao, se ela foi totalmente paga
																$query_plate_production = "INSERT INTO `production_plates`(`id`, `protocol`, `plate`, `date_created`, `time_created`, `user_created`, `catched`, `date_catched`, `time_catched`, `responsible`, `establishment`) VALUES (NULL,'$protocol','$plate','$date_now','$time','$user_id','0',NULL,NULL,'0','$establishment')";
																$result_plate_production = mysqli_query($con,$query_plate_production);
																$affected_rows_pp = mysqli_affected_rows($con);
																if($affected_rows_pp <= 0){
																	echo "Erro ao inserir placa na tabela de produçao.";
																}
															}
														}
														
														// Atualizando tabelas de relatórios
														$method_name = methodname($method);
														$query_sps = "SELECT * FROM show_payed_sales WHERE protocol = '$protocol' AND payment_method = '$method_name'";
														$result_sps = mysqli_query($con,$query_sps);
														$num_rows_sps = mysqli_num_rows($result_sps);
														if($num_rows_sps > 0){
															while($row_sps = mysqli_fetch_array($result_sps)){
																$new_value = $row_sps['value'];
															}
															$new_value += $total_product;
															$query_up_nv = "UPDATE `show_payed_sales` SET `value`='$new_value' WHERE protocol = '$protocol' AND payment_method = '$method_name'";
															$result_up_nv = mysqli_query($con,$query_up_nv);
														} else {
															$query_gcid = "SELECT * FROM sales WHERE id = '$protocol'";
															$result_gcid = mysqli_query($con,$query_gcid);
															while($row_gcid = mysqli_fetch_array($result_gcid)){
																$customer_id = $row_gcid['customer'];
																if($customer_id == 0){
																	$customer_name = "Cliente não cadastrado";
																} else {
																	$query_gcn = "SELECT * FROM customers WHERE id = '$customer_id'";
																	$result_gcn = mysqli_query($con,$query_gcn);
																	while($row_gcn = mysqli_fetch_array($result_gcn)){
																		$customer_name = $row_gcn['name'];
																	}
																}
															}										
															$query_in_sps = "INSERT INTO `show_payed_sales`(`id`, `date`, `customer_name`, `protocol`, `payment_method`, `value`, `box`, `establishment`) VALUES (NULL,'$date_now','$customer_name','$protocol','$method_name','$total_product','$cashier','$establishment')";
															$result_in_sps = mysqli_query($con,$query_in_sps);
														}
														// Atualizando tabelas de relatórios 2
														$query_sps = "SELECT * FROM report_total_opened_protocols WHERE protocol = '$protocol'";
														$result_sps = mysqli_query($con,$query_sps);
														$num_rows_sps = mysqli_num_rows($result_sps);
														if($num_rows_sps > 0){
															while($row_sps = mysqli_fetch_array($result_sps)){
																$new_value = $row_sps['total_paid'];
															}
															$new_value += $total_product;
															$query_up_nv = "UPDATE `report_total_opened_protocols` SET `total_paid`='$new_value' WHERE protocol = '$protocol'";
															$result_up_nv = mysqli_query($con,$query_up_nv);
														}
														// Atualizando os resumos
														$query_sss = "SELECT * FROM show_summary_sales WHERE date = '$date_now' AND payment_method = '$method_name' AND box = '$cashier'";
														$result_sss = mysqli_query($con,$query_sss);
														$num_rows_sss = mysqli_num_rows($result_sss);
														if($num_rows_sss > 0){
															while($row_sss = mysqli_fetch_array($result_sss)){
																$new_value_ss = $row_sss['value'];
															}
															$new_value_ss += $total_product;
															$query_up_nvss = "UPDATE `show_summary_sales` SET `value`='$new_value_ss' WHERE date = '$date_now' AND payment_method = '$method_name' AND box = '$cashier'";
															$result_up_nvss = mysqli_query($con,$query_up_nvss);
														} else {										
															$query_in_sss = "INSERT INTO `show_summary_sales`(`id`, `date`, `payment_method`, `value`, `box`, `establishment`) VALUES (NULL,'$date_now','$method_name','$total_product','$cashier','$establishment')";
															$result_in_sss = mysqli_query($con,$query_in_sss);
														}
														
														// Salvando total pago do dia no banco
														$query_verify = "SELECT date FROM sales WHERE id = '$protocol'";
														$result_verify = mysqli_query($con,$query_verify);
														$row_verify = mysqli_fetch_assoc($result_verify);
														$date_prot = $row_verify['date'];
														$query_sel_tot = "SELECT * FROM total_paid_protocols_day WHERE date = '$date_prot' AND establishment = '$establishment'";
														$result_sel_tot = mysqli_query($con,$query_sel_tot);
														$num_rows_in_tot = mysqli_num_rows($result_sel_tot);
														if($num_rows_in_tot > 0){
															while($row_in_tot = mysqli_fetch_array($result_sel_tot)){
																$vtd = $row_in_tot['value'];
															}
															// UPDATE
															$vtd += $total_product;
															$query_up_tot = "UPDATE `total_paid_protocols_day` SET `value`='$vtd' WHERE date = '$date_prot' AND `establishment`='$establishment'";
															mysqli_query($con,$query_up_tot);
														} else {
															// INSERT
															$query_in_tot = "INSERT INTO `total_paid_protocols_day`(`id`, `date`, `value`, `establishment`) VALUES (NULL,'$date_prot','$total_product','$establishment')";
															mysqli_query($con,$query_in_tot);
														}
												
													} else {
														$q = "INSERT INTO `payments` ( `id`, `cashier`, `protocol`, `id_sale_product`, `payment_method`, `value`, `date`, `time`, `user`, `token`) VALUES (NULL, '$cashier', '$protocol', '$id_product', '$method', '$receb', '$date_now', '$time', '$user_id', '$token')";
														$res = mysqli_query($con, $q);
														$affected_rows = mysqli_affected_rows($con);
														if ($affected_rows <= 0) {
															$error = TRUE;
														}
														
														// Atualizando tabelas de relatórios
														$method_name = methodname($method);
														$query_sps = "SELECT * FROM show_payed_sales WHERE protocol = '$protocol' AND payment_method = '$method_name'";
														$result_sps = mysqli_query($con,$query_sps);
														$num_rows_sps = mysqli_num_rows($result_sps);
														if($num_rows_sps > 0){
															while($row_sps = mysqli_fetch_array($result_sps)){
																$new_value = $row_sps['value'];
															}
															$new_value += $receb;
															$query_up_nv = "UPDATE `show_payed_sales` SET `value`='$new_value' WHERE protocol = '$protocol' AND payment_method = '$method_name'";
															$result_up_nv = mysqli_query($con,$query_up_nv);
														} else {
															$query_gcid = "SELECT * FROM sales WHERE id = '$protocol'";
															$result_gcid = mysqli_query($con,$query_gcid);
															while($row_gcid = mysqli_fetch_array($result_gcid)){
																$customer_id = $row_gcid['customer'];
																if($customer_id == 0){
																	$customer_name = "Cliente não cadastrado";
																} else {
																	$query_gcn = "SELECT * FROM customers WHERE id = '$customer_id'";
																	$result_gcn = mysqli_query($con,$query_gcn);
																	while($row_gcn = mysqli_fetch_array($result_gcn)){
																		$customer_name = $row_gcn['name'];
																	}
																}
															}										
															$query_in_sps = "INSERT INTO `show_payed_sales`(`id`, `date`, `customer_name`, `protocol`, `payment_method`, `value`, `box`, `establishment`) VALUES (NULL,'$date_now','$customer_name','$protocol','$method_name','$receb','$cashier','$establishment')";
															$result_in_sps = mysqli_query($con,$query_in_sps);
														}
														// Atualizando tabelas de relatórios 2
														$query_sps = "SELECT * FROM report_total_opened_protocols WHERE protocol = '$protocol'";
														$result_sps = mysqli_query($con,$query_sps);
														$num_rows_sps = mysqli_num_rows($result_sps);
														if($num_rows_sps > 0){
															while($row_sps = mysqli_fetch_array($result_sps)){
																$new_value = $row_sps['total_paid'];
															}
															$new_value += $receb;
															$query_up_nv = "UPDATE `report_total_opened_protocols` SET `total_paid`='$new_value' WHERE protocol = '$protocol'";
															$result_up_nv = mysqli_query($con,$query_up_nv);
														}
														// Atualizando os resumos
														$query_sss = "SELECT * FROM show_summary_sales WHERE date = '$date_now' AND payment_method = '$method_name' AND box = '$cashier'";
														$result_sss = mysqli_query($con,$query_sss);
														$num_rows_sss = mysqli_num_rows($result_sss);
														if($num_rows_sss > 0){
															while($row_sss = mysqli_fetch_array($result_sss)){
																$new_value_ss = $row_sss['value'];
															}
															$new_value_ss += $receb;
															$query_up_nvss = "UPDATE `show_summary_sales` SET `value`='$new_value_ss' WHERE date = '$date_now' AND payment_method = '$method_name' AND box = '$cashier'";
															$result_up_nvss = mysqli_query($con,$query_up_nvss);
														} else {
															$query_in_sss = "INSERT INTO `show_summary_sales`(`id`, `date`, `payment_method`, `value`, `box`, `establishment`) VALUES (NULL,'$date_now','$method_name','$receb','$cashier','$establishment')";															
															$result_in_sss = mysqli_query($con,$query_in_sss);
														}
																		
														// Salvando total pago do dia no banco
														$query_verify = "SELECT date FROM sales WHERE id = '$protocol'";
														$result_verify = mysqli_query($con,$query_verify);
														$row_verify = mysqli_fetch_assoc($result_verify);
														$date_prot = $row_verify['date'];
														$query_sel_tot = "SELECT * FROM total_paid_protocols_day WHERE date = '$date_prot' AND establishment = '$establishment'";
														$result_sel_tot = mysqli_query($con,$query_sel_tot);
														$num_rows_in_tot = mysqli_num_rows($result_sel_tot);
														if($num_rows_in_tot > 0){
															while($row_in_tot = mysqli_fetch_array($result_sel_tot)){
																$vtd = $row_in_tot['value'];
															}
															// UPDATE
															$vtd += $receb;
															$query_up_tot = "UPDATE `total_paid_protocols_day` SET `value`='$vtd' WHERE date = '$date_prot' AND `establishment`='$establishment'";
															mysqli_query($con,$query_up_tot);
														} else {
															// INSERT
															$query_in_tot = "INSERT INTO `total_paid_protocols_day`(`id`, `date`, `value`, `establishment`) VALUES (NULL,'$date_prot','$receb','$establishment')";
															mysqli_query($con,$query_in_tot);
														}

														break;
													}
												}
											}
										} else {
											echo "Esse protocolo não existe.";
										}
										if(!$error){
											// Tudo ok
											header("Location: payment?protocol=".$protocol."&pf=1&s=1");
										} else {
											header("Location: payment?protocol=".$protocol."&pf=1&e=2");
										}
									}
								} else {
									echo "Você nao pode pagar 0,00 (zero reais).";
								}
							} else {
								echo "Voce nao tem autorização para acessar o caixa deste estabelecimento.";
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
			header("Location: ../login");
		}
	}
} else {
    header("Location: ../login");
}
?>