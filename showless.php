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
	if ($user->getRole() == 1 || $user->getRole() == 3 || $user->getRole() == 5) {
		if ($box != 0 || strcmp($box, "T") == 0) {
			$data = $_GET['date'];
?>
			<div class="row">
				<div class="col-lg-12">
					<?php
					if (connect()) {
						$con = $_SESSION['con'];
						if (strcmp($box, "T") == 0) {
							$query_box = "SELECT * FROM boxes_list WHERE establishment = '$establishment'";
						} else {
							$query_box = "SELECT * FROM boxes_list WHERE id = '$box' AND establishment = '$establishment'";
						}
						$result_box = mysqli_query($con, $query_box);
						$num_rows_box = mysqli_num_rows($result_box);
						if ($num_rows_box > 0) {
							while ($row_box = mysqli_fetch_array($result_box)) {
								$boxes_list[] = $row_box['id'];
							}
							$query_box_est = "SELECT * FROM boxes_users WHERE (";
							$qt = count($boxes_list);
							for ($t = 0; $t < $qt; $t++) {
								$box_l = $boxes_list[$t];
								if ($t == 0) {
									$query_box_est .= "box = '$box_l'";
								} else {
									$query_box_est .= " OR box = '$box_l'";
								}
							}
							$query_box_est .= ") AND user = '$user_id'";
							$result_box_est = mysqli_query($con, $query_box_est);
							$num_rows_box_est = mysqli_num_rows($result_box_est);
							if ($num_rows_box_est > 0) {
								$not_in = FALSE;
								$not_out = FALSE;
								$saldo = 0;
								$ordem = 1;
								$total_day = 0;
								// Pegando entradas
								$query_cred = "SELECT * FROM cash_movement WHERE date = '$data' AND in_out = '0' AND (";
								for ($t = 0; $t < $qt; $t++) {
									$box_l = $boxes_list[$t];
									if ($t == 0) {
										$query_cred .= "cashier = '$box_l'";
									} else {
										$query_cred .= " OR cashier = '$box_l'";
									}
								}
								$query_cred .= ")";
								$result_cred = mysqli_query($con, $query_cred);
								$num_rows_cred = mysqli_num_rows($result_cred);
								if ($num_rows_cred > 0) {
									$total_cred = 0;
									while ($row_cred = mysqli_fetch_array($result_cred)) {
										$value_cred = $row_cred['value'];
										$total_cred += $value_cred;
									}
								} else {
									$not_in_cred = TRUE;
								}

								// Pegando saídas
								$query_debitos = "SELECT * FROM cash_movement WHERE date = '$data' AND in_out = '1' AND (";
								for ($t = 0; $t < $qt; $t++) {
									$box_l = $boxes_list[$t];
									if ($t == 0) {
										$query_debitos .= "cashier = '$box_l'";
									} else {
										$query_debitos .= " OR cashier = '$box_l'";
									}
								}
								$query_debitos .= ")";
								$result_debitos = mysqli_query($con, $query_debitos);
								$num_rows_debitos = mysqli_num_rows($result_debitos);
								if ($num_rows_debitos > 0) {
									$total_debitos = 0;
									while ($row_debitos = mysqli_fetch_array($result_debitos)) {
										$description = $row_debitos['description'];
										$value = $row_debitos['value'];
										$total_debitos += $value;
										$provider = $row_debitos['provider'];
										$chart_of_accounts = $row_debitos['chart_of_accounts'];
									}
								} else {
									$not_out = TRUE;
								}

								// Pegando depósitos
								$query_deposits_g = "SELECT * FROM deposits_group WHERE date = '$data' AND (";
								for ($t = 0; $t < $qt; $t++) {
									$box_l = $boxes_list[$t];
									if ($t == 0) {
										$query_deposits_g .= "box = '$box_l'";
									} else {
										$query_deposits_g .= " OR box = '$box_l'";
									}
								}
								$query_deposits_g .= ")";
								$result_deposits_g = mysqli_query($con, $query_deposits_g);
								$num_rows_deposits_g = mysqli_num_rows($result_deposits_g);
								if ($num_rows_deposits_g > 0) {
									$total_deposits = 0;
									while ($row_deposits_g = mysqli_fetch_array($result_deposits_g)) {
										$idgod = $row_deposits_g['id'];
										$query_deposits = "SELECT * FROM deposits WHERE id_group = '$idgod'";
										$result_deposits = mysqli_query($con, $query_deposits);
										while ($row_deposits = mysqli_fetch_array($result_deposits)) {
											$value_deposit = $row_deposits['value'];
											$total_deposits += $value_deposit;
										}
									}
								} else {
									$not_deposits = TRUE;
								}

								// Pegando o total de transferências
								$query_trans = "SELECT * FROM accounts_movement WHERE date = '$data' AND (";
								for ($t = 0; $t < $qt; $t++) {
									$box_l = $boxes_list[$t];
									$origin_destination = getAccount($box_l);
									if ($t == 0) {
										$query_trans .= "origin ='$origin_destination' OR destination = '$origin_destination'";
									} else {
										$query_trans .= " OR origin ='$origin_destination' OR destination = '$origin_destination'";
									}
								}
								$query_trans .= ")";
								$result_trans = mysqli_query($con, $query_trans);
								$num_rows_trans = mysqli_num_rows($result_trans);
								if ($num_rows_trans > 0) {
									$total_trans = 0;
									while ($row_trans = mysqli_fetch_array($result_trans)) {
										$value_trans = $row_trans['value'];
										$origin_trans = $row_trans['origin'];
										$destination_trans = $row_trans['destination'];
										if ($origin_trans == $origin_destination) {
											$total_trans -= $value_trans;
										} else {
											$total_trans += $value_trans;
										}
										$date_trans = $row_trans['date'];
									}
								} else {
									$not_transfer_out = TRUE;
								}

								$total = 0;
								$total_especie = 0;
								$total_debito = 0;
								$total_credito = 0;
								$total_carteira = 0;
								$total_boleto = 0;
								$total_cheque = 0;
								$total_transferencia = 0;
								$total_boleto_site = 0;
								$valid = 1;

								// Verificar se algo foi pago
								$query_ver_pago = "SELECT * FROM payments WHERE date = '$data' AND (";
								for ($t = 0; $t < $qt; $t++) {
									$box_l = $boxes_list[$t];
									if ($t == 0) {
										$query_ver_pago .= "cashier = '$box_l'";
									} else {
										$query_ver_pago .= " OR cashier = '$box_l'";
									}
								}
								$query_ver_pago .= ")";
								$result_ver_pago = mysqli_query($con, $query_ver_pago);
								$num_rows_ver_pago = mysqli_num_rows($result_ver_pago);

								if ($num_rows_ver_pago > 0) {
									if ($valid == 1) {
										echo '<div class="row">
												<div class="col-lg-12">
													<p><button class="btn btn-success" onclick="listPayedProtocols()" >Detalhado</button></p>
												</div>
											</div>';
										echo '<div class="row">
										<div class="col-lg-12">
											<div class="panel panel-default">
												<div class="panel-heading">
													Resumo do Movimento de Caixa
												</div>
												<!-- /.panel-heading -->
												<div class="panel-body">
													<div class="table-responsive">
														<table class="table">
															<thead>
																<tr>
																	<th>#</th>
																	<th>Data</th>
																	<th>Descrição</th>
																	<th><center>Entradas (R$)</center></th>
																	<th><center>Saídas (R$)</center></th>
																	<th><center>Saldo (R$)</center></th>
																</tr>
															</thead>
															<tbody>';
										$valid++;
									}

									// Total em espécie
									$query_especie = "SELECT sum(value) AS valor_soma FROM payments WHERE date = '$data' AND payment_method = '1' AND (";
									for ($t = 0; $t < $qt; $t++) {
										$box_l = $boxes_list[$t];
										if ($t == 0) {
											$query_especie .= "cashier = '$box_l'";
										} else {
											$query_especie .= " OR cashier = '$box_l'";
										}
									}
									$query_especie .= ")";
									$result_especie = mysqli_query($con, $query_especie);
									$num_rows_especie = mysqli_num_rows($result_especie);
									if ($num_rows_especie > 0) {
										$row_especie = mysqli_fetch_assoc($result_especie);
										$total_especie = $row_especie['valor_soma'];
									}
									// Total débito
									$query_debito = "SELECT sum(value) AS valor_soma FROM payments WHERE date = '$data' AND payment_method = '2' AND (";
									for ($t = 0; $t < $qt; $t++) {
										$box_l = $boxes_list[$t];
										if ($t == 0) {
											$query_debito .= "cashier = '$box_l'";
										} else {
											$query_debito .= " OR cashier = '$box_l'";
										}
									}
									$query_debito .= ")";
									$result_debito = mysqli_query($con, $query_debito);
									$num_rows_debito = mysqli_num_rows($result_debito);
									if ($num_rows_debito > 0) {
										$row_debito = mysqli_fetch_assoc($result_debito);
										$total_debito = $row_debito['valor_soma'];
									}
									// Total crédito
									$query_credito = "SELECT sum(value) AS valor_soma FROM payments WHERE date = '$data' AND payment_method = '3' AND (";
									for ($t = 0; $t < $qt; $t++) {
										$box_l = $boxes_list[$t];
										if ($t == 0) {
											$query_credito .= "cashier = '$box_l'";
										} else {
											$query_credito .= " OR cashier = '$box_l'";
										}
									}
									$query_credito .= ")";
									$result_credito = mysqli_query($con, $query_credito);
									$num_rows_credito = mysqli_num_rows($result_credito);
									if ($num_rows_debito > 0) {
										$row_credito = mysqli_fetch_assoc($result_credito);
										$total_credito = $row_credito['valor_soma'];
									}
									// Total carteira
									$query_carteira = "SELECT sum(value) AS valor_soma FROM payments WHERE date = '$data' AND payment_method = '4' AND (";
									for ($t = 0; $t < $qt; $t++) {
										$box_l = $boxes_list[$t];
										if ($t == 0) {
											$query_carteira .= "cashier = '$box_l'";
										} else {
											$query_carteira .= " OR cashier = '$box_l'";
										}
									}
									$query_carteira .= ")";
									$result_carteira = mysqli_query($con, $query_carteira);
									$num_rows_carteira = mysqli_num_rows($result_carteira);
									if ($num_rows_carteira > 0) {
										$row_carteira = mysqli_fetch_assoc($result_carteira);
										$total_carteira = $row_carteira['valor_soma'];
									}
									// Total boleto
									$query_boleto = "SELECT sum(value) AS valor_soma FROM payments WHERE date = '$data' AND payment_method = '5' AND (";
									for ($t = 0; $t < $qt; $t++) {
										$box_l = $boxes_list[$t];
										if ($t == 0) {
											$query_boleto .= "cashier = '$box_l'";
										} else {
											$query_boleto .= " OR cashier = '$box_l'";
										}
									}
									$query_boleto .= ")";
									$result_boleto = mysqli_query($con, $query_boleto);
									$num_rows_boleto = mysqli_num_rows($result_boleto);
									if ($num_rows_boleto > 0) {
										$row_boleto = mysqli_fetch_assoc($result_boleto);
										$total_boleto = $row_boleto['valor_soma'];
									}
									// Total cheque
									$query_cheque = "SELECT sum(value) AS valor_soma FROM payments WHERE date = '$data' AND payment_method = '6' AND (";
									for ($t = 0; $t < $qt; $t++) {
										$box_l = $boxes_list[$t];
										if ($t == 0) {
											$query_cheque .= "cashier = '$box_l'";
										} else {
											$query_cheque .= " OR cashier = '$box_l'";
										}
									}
									$query_cheque .= ")";
									$result_cheque = mysqli_query($con, $query_cheque);
									$num_rows_cheque = mysqli_num_rows($result_cheque);
									if ($num_rows_cheque > 0) {
										$row_cheque = mysqli_fetch_assoc($result_cheque);
										$total_cheque = $row_cheque['valor_soma'];
									}
									// Total transferencia
									$query_transferencia = "SELECT sum(value) AS valor_soma FROM payments WHERE date = '$data' AND payment_method = '7' AND (";
									for ($t = 0; $t < $qt; $t++) {
										$box_l = $boxes_list[$t];
										if ($t == 0) {
											$query_transferencia .= "cashier = '$box_l'";
										} else {
											$query_transferencia .= " OR cashier = '$box_l'";
										}
									}
									$query_transferencia .= ")";
									$result_transferencia = mysqli_query($con, $query_transferencia);
									$num_rows_transferencia = mysqli_num_rows($result_transferencia);
									if ($num_rows_transferencia > 0) {
										$row_transferencia = mysqli_fetch_assoc($result_transferencia);
										$total_transferencia = $row_transferencia['valor_soma'];
									}
									// Total pix
									$query_pix = "SELECT sum(value) AS valor_soma FROM payments WHERE date = '$data' AND payment_method = '9' AND (";
									for ($t = 0; $t < $qt; $t++) {
										$box_l = $boxes_list[$t];
										if ($t == 0) {
											$query_pix .= "cashier = '$box_l'";
										} else {
											$query_pix .= " OR cashier = '$box_l'";
										}
									}
									$query_pix .= ")";
									$result_pix = mysqli_query($con, $query_pix);
									$num_rows_transferencia = mysqli_num_rows($result_pix);
									if ($num_rows_transferencia > 0) {
										$row_pix = mysqli_fetch_assoc($result_pix);
										$total_pix = $row_pix['valor_soma'];
									}
									// Total Boleto Site
									$query_boleto_site = "SELECT sum(value) AS valor_soma FROM payments WHERE date = '$data' AND payment_method = '8' AND (";
									for ($t = 0; $t < $qt; $t++) {
										$box_l = $boxes_list[$t];
										if ($t == 0) {
											$query_boleto_site .= "cashier = '$box_l'";
										} else {
											$query_boleto_site .= " OR cashier = '$box_l'";
										}
									}
									$query_boleto_site .= ")";
									$result_boleto_site = mysqli_query($con, $query_boleto_site);
									$num_rows_boleto_site = mysqli_num_rows($result_boleto_site);
									if ($num_rows_boleto_site > 0) {
										$row_boleto_site = mysqli_fetch_assoc($result_boleto_site);
										$total_boleto_site = $row_boleto_site['valor_soma'];
									}

									// Total de protocolos pagos Boleto Site
									$query_boleto_site = "SELECT sum(value) AS valor_soma FROM payments WHERE date = '$data' AND payment_method = '8' AND (";
									for ($t = 0; $t < $qt; $t++) {
										$box_l = $boxes_list[$t];
										if ($t == 0) {
											$query_boleto_site .= "cashier = '$box_l'";
										} else {
											$query_boleto_site .= " OR cashier = '$box_l'";
										}
									}
									$query_boleto_site .= ")";
									$result_boleto_site = mysqli_query($con, $query_boleto_site);
									$num_rows_boleto_site = mysqli_num_rows($result_boleto_site);
									if ($num_rows_boleto_site > 0) {
										$row_boleto_site = mysqli_fetch_assoc($result_boleto_site);
										$total_boleto_site = $row_boleto_site['valor_soma'];
									}
								}
								// Pegando o total dos protocolos
								if ($establishment != 3 || strcmp($box, "T") == 0) {
									$total_prot_day = 0;
									$query_get_tot = "SELECT value FROM `total_protocols_day` WHERE date = '$data' AND establishment = '$establishment'";
									$result_get_tot = mysqli_query($con, $query_get_tot);
									$num_rows_get_tot = mysqli_num_rows($result_get_tot);
									if ($num_rows_get_tot > 0) {
										while ($row_get_tot = mysqli_fetch_array($result_get_tot)) {
											$total_prot_day = $row_get_tot['value'];
										}
									}
								}

								// Pegando o total de suportes do dia
								$total_sup = 0;
								$valor_sc = 0;
								$valor_sm = 0;
								// Pegando os suportes de carro
								$query_qsc = "SELECT sum(quantity) AS total FROM sale_products WHERE sale IN (SELECT id FROM sales WHERE date = '$data' AND establishment = '$establishment') AND product = '3'";
								$result_qsc = mysqli_query($con, $query_qsc);
								$row_qsc = mysqli_fetch_assoc($result_qsc);
								$total_sc = $row_qsc['total'];
								// Pegando o valor do suporte de carros
								$query_gvsc = "SELECT value AS valor FROM sale_products WHERE sale IN (SELECT id FROM sales WHERE date = '$data' AND establishment = '$establishment') AND product = '3' LIMIT 1";
								$result_gvsc = mysqli_query($con, $query_gvsc);
								$row_gvsc = mysqli_fetch_assoc($result_gvsc);
								$valor_sc = $row_gvsc['valor'];
								// Pegando o valor do suporte de motos
								$query_gvsm = "SELECT value AS valor FROM sale_products WHERE sale IN (SELECT id FROM sales WHERE date = '$data' AND establishment = '$establishment') AND product = '4' LIMIT 1";
								$result_gvsm = mysqli_query($con, $query_gvsm);
								$row_gvsm = mysqli_fetch_assoc($result_gvsm);
								$valor_sm = $row_gvsm['valor'];
								// Pegando os suportes de moto
								$query_qsm = "SELECT sum(quantity) AS total FROM sale_products WHERE sale IN (SELECT id FROM sales WHERE date = '$data' AND establishment = '$establishment') AND product = '4'";
								$result_qsm = mysqli_query($con, $query_qsm);
								$row_qsm = mysqli_fetch_assoc($result_qsm);
								$total_sm = $row_qsm['total'];
								$total_sup = ($valor_sc * $total_sc) + ($valor_sm * $total_sm);

								// Pegando o total pago de dias anteriores
								$total_paid_another_day = 0;
								$query_ver_protocol = "SELECT DISTINCT protocol FROM `show_payed_sales` WHERE date = '$data' AND establishment = '$establishment' AND (";
								for ($t = 0; $t < $qt; $t++) {
									$box_l = $boxes_list[$t];
									if ($t == 0) {
										$query_ver_protocol .= "box = '$box_l'";
									} else {
										$query_ver_protocol .= " OR box = '$box_l'";
									}
								}
								$query_ver_protocol .= ")";
								$result_ver_protocol = mysqli_query($con, $query_ver_protocol);
								while ($row_vp = mysqli_fetch_array($result_ver_protocol)) {
									$protocol_vp = $row_vp['protocol'];
									$query_da = "SELECT * FROM sales WHERE id = '$protocol_vp' AND date < '$data'";
									$result_da = mysqli_query($con, $query_da);
									while ($row_da = mysqli_fetch_array($result_da)) {
										$pt = $row_da['id'];
										$query_vda = "SELECT sum(value) AS valor FROM payments WHERE protocol = '$pt' AND date = '$data'";
										$result_vda = mysqli_query($con, $query_vda);
										$row_vda = mysqli_fetch_assoc($result_vda);
										$total_paid_another_day += $row_vda['valor'];
									}
								}

								// Pegando o total de transferências do dia
								$total_trans_prot = 0;
								$query_gt = "SELECT * FROM transfer WHERE (origin = '$establishment' OR destination = '$establishment') AND date = '$data' AND confirmation = '1'";
								$result_gt = mysqli_query($con, $query_gt);
								while ($row_gt = mysqli_fetch_array($result_gt)) {
									$st_tp = 0;
									$trans_id = $row_gt['id'];
									$query_vgt = "SELECT * FROM transfer_protocols WHERE transfer = '$trans_id'";
									$result_vgt = mysqli_query($con, $query_vgt);
									while ($row_vgt = mysqli_fetch_array($result_vgt)) {
										$ptc = $row_vgt['protocol'];
										$query_spt = "SELECT * FROM sale_products WHERE sale = '$ptc'";
										$result_spt = mysqli_query($con, $query_spt);
										while ($row_spt = mysqli_fetch_array($result_spt)) {
											$qt_spt = $row_spt['quantity'];
											$val_spt = $row_spt['value'];
											$st_tp += $qt_spt * $val_spt;
										}
									}
									if ($row_gt['origin'] == $establishment) {
										// Soma
										$total_trans_prot += $st_tp;
									} else {
										// Subtrai
										$total_trans_prot -= $st_tp;
									}
								}

								// Pegando o total dos protocolos pagos do dia
								$total_prot_paid_day = 0;
								$query_get_tot = "SELECT value FROM `total_paid_protocols_day` WHERE date = '$data' AND establishment = '$establishment'";
								$result_get_tot = mysqli_query($con, $query_get_tot);
								$num_rows_get_tot = mysqli_num_rows($result_get_tot);
								if ($num_rows_get_tot > 0) {
									while ($row_get_tot = mysqli_fetch_array($result_get_tot)) {
										$total_prot_paid_day = $row_get_tot['value'];
									}
								}

								// Somando todos os protocolos do estabelecimento anteriores ao dia atual
								if ($establishment != 3 || strcmp($box, "T") == 0) {
									$total_prot_acu = 0;
									if ($establishment != 2) {
										$query_get_tot = "SELECT sum(value) AS valor FROM total_protocols_day WHERE date >= '2019-02-01' AND date < '$data' AND establishment = '$establishment'";
									} else {
										$query_get_tot = "SELECT sum(value) AS valor FROM total_protocols_day WHERE date >= '2019-03-18' AND date < '$data' AND establishment = '$establishment'";
									}
									$result_get_tot = mysqli_query($con, $query_get_tot);
									$num_rows_get_tot = mysqli_num_rows($result_get_tot);
									if ($num_rows_get_tot > 0) {
										$row_get_tot = mysqli_fetch_array($result_get_tot);
										$total_prot_acu = $row_get_tot['valor'];
									}
								}

								// Pegando total de protocolos pagos até o dia anterior					
								if ($establishment != 2) {
									$query_product_name = "SELECT sum(value) AS valor FROM total_paid_protocols_day WHERE date >= '2019-02-01' AND date < '$data' AND establishment = '$establishment'";
								} else {
									$query_product_name = "SELECT sum(value) AS valor FROM total_paid_protocols_day WHERE date >= '2019-03-18' AND date < '$data' AND establishment = '$establishment'";
								}
								$result_product_name = mysqli_query($con, $query_product_name);
								$num_rows_product_name = mysqli_num_rows($result_product_name);
								$soma_pago_geral = 0;
								if ($num_rows_product_name > 0) {
									$row_product_name = mysqli_fetch_assoc($result_product_name);
									$soma_pago_geral = $row_product_name['valor'];
								}

								$array_date = explode('-', $data);
								$year = $array_date[0];
								$month = $array_date[1];
								$day = $array_date[2];
								$first_day = date($year . '-' . $month . '-01');

								// Somando todos os protocolos do mês atual do estabelecimento anteriores ao dia atual
								if ($establishment != 3 || strcmp($box, "T") == 0) {
									$total_prot_acu_month = 0;
									$calculate = false;
									if ($establishment != 2) {
										if (intVal($year) > 2019 || (intVal($year) == 2019 && intVal($month) >= 2)) {
											$query_get_tot = "SELECT sum(value) AS valor FROM total_protocols_day WHERE date >= '$first_day' AND date < '$data' AND establishment = '$establishment'";
											$calculate = true;
										}
									}
									if ($establishment == 2) {
										if (intVal($year) > 2019 || (intVal($year) == 2019 && intVal($month) >= 4)) {
											$query_get_tot = "SELECT sum(value) AS valor FROM total_protocols_day WHERE date >= '$first_day' AND date < '$data' AND establishment = '$establishment'";
											$calculate = true;
										} elseif (intVal($year) > 2019 || (intVal($year) == 2019 && intVal($month) == 3 && intVal($day) > 18)) {
											$query_get_tot = "SELECT sum(value) AS valor FROM total_protocols_day WHERE date >= '2019-03-18' AND date < '$data' AND establishment = '$establishment'";
											$calculate = true;
										}
									}
									if ($calculate ) {
										$result_get_tot = mysqli_query($con, $query_get_tot);
										$num_rows_get_tot = mysqli_num_rows($result_get_tot);
										if ($num_rows_get_tot > 0) {
											$row_get_tot = mysqli_fetch_array($result_get_tot);
											$total_prot_acu_month = $row_get_tot['valor'];
										}
									}
								}

								// Pegando total de protocolos pagos do mês atual até o dia anterior
								$soma_pago_geral_month = 0;
										
								if ( $establishment != 2 ) {
									if (intVal($year) > 2019 || (intVal($year) == 2019 && intVal($month) >= 2)) {
										$query_product_name = "SELECT sum(value) AS valor FROM total_paid_protocols_day WHERE date >= '$first_day' AND date < '$data' AND establishment = '$establishment'";
									}
								}
								if ( $establishment == 2 ) {
									if (intVal($year) > 2019 || (intVal($year) == 2019 && intVal($month) >= 4)) {
										$query_product_name = "SELECT sum(value) AS valor FROM total_paid_protocols_day WHERE date >= '$first_day' AND date < '$data' AND establishment = '$establishment'";
									} elseif (intVal($year) > 2019 || (intVal($year) == 2019 && intVal($month) == 3 && intVal($day) > 18)) {
										$query_product_name = "SELECT sum(value) AS valor FROM total_paid_protocols_day WHERE date >= '2019-03-18' AND date < '$data' AND establishment = '$establishment'";
									}
								}

								if ($calculate) {
									$result_product_name = mysqli_query($con, $query_product_name);
									$num_rows_product_name = mysqli_num_rows($result_product_name);
									
									if ($num_rows_product_name > 0) {
										$row_product_name = mysqli_fetch_assoc($result_product_name);
										$soma_pago_geral_month = $row_product_name['valor'];
									}
								}

								$vendas = $total_especie + $total_debito + $total_credito + $total_carteira + $total_boleto + $total_cheque + $total_transferencia + $total_boleto_site + $total_pix;
								$total += $total_cred - $total_debitos + $total_especie + $total_debito + $total_credito + $total_carteira + $total_boleto + $total_cheque + $total_transferencia + $total_boleto_site + $total_pix + $total_trans;
								if ($total_especie > 0) {
									$saldo = $total_especie;
									echo	'<tr>
											<td>' . $ordem . '</td>
											<td>' . mudaData($data) . '</td>
											<td>Dinheiro</td>
											<td><center>' . number_format($total_especie, 2, ",", ".") . '</center></td>
											<td></td>
											<td><center>' . number_format($saldo, 2, ",", ".") . '</center></td>
											</tr>';
									$ordem++;
								}
								// Entradas
								$saldo += $total_cred;
								if ($total_cred > 0) {
									echo	'<tr>
											<td>' . $ordem . '</td>
											<td>' . mudaData($data) . '</td>
											<td>Entradas</td>
											<td><center>' . number_format($total_cred, 2, ",", ".") . '</center></td>
											<td></td>
											<td><center>' . number_format($saldo, 2, ",", ".") . '</center></td>
											</tr>';
									$ordem++;
								}
								// Pagamentos
								$saldo -= $total_debitos;
								if ($total_debitos > 0) {
									echo	'<tr>
											<td>' . $ordem . '</td>
											<td>' . mudaData($data) . '</td>
											<td>Pagamentos efetuados</td>
											<td></td>
											<td><center>-' . number_format($total_debitos, 2, ",", ".") . '</center></td>
											<td><center>' . number_format($saldo, 2, ",", ".") . '</center></td>
											</tr>';
									$ordem++;
								}
								if ($total_debito > 0) {
									$saldo += $total_debito;
									echo	'<tr>
											<td>' . $ordem . '</td>
											<td>' . mudaData($data) . '</td>
											<td>Débito</td>
											<td><center>' . number_format($total_debito, 2, ",", ".") . '</center></td>
											<td></td>
											<td><center>' . number_format($saldo, 2, ",", ".") . '</center></td>
											</tr>';
									$ordem++;
								}
								if ($total_credito > 0) {
									$saldo += $total_credito;
									echo	'<tr>
											<td>' . $ordem . '</td>
											<td>' . mudaData($data) . '</td>
											<td>Crédito</td>
											<td><center>' . number_format($total_credito, 2, ",", ".") . '</center></td>
											<td></td>
											<td><center>' . number_format($saldo, 2, ",", ".") . '</center></td>
											</tr>';
									$ordem++;
								}
								if ($total_carteira > 0) {
									$saldo += $total_carteira;
									echo	'<tr>
											<td>' . $ordem . '</td>
											<td>' . mudaData($data) . '</td>
											<td>Carteira</td>
											<td><center>' . number_format($total_carteira, 2, ",", ".") . '</center></td>
											<td></td>
											<td><center>' . number_format($saldo, 2, ",", ".") . '</center></td>
											</tr>';
									$ordem++;
								}
								if ($total_boleto > 0) {
									$saldo += $total_boleto;
									echo	'<tr>
											<td>' . $ordem . '</td>
											<td>' . mudaData($data) . '</td>
											<td>Boleto</td>
											<td><center>' . number_format($total_boleto, 2, ",", ".") . '</center></td>
											<td></td>
											<td><center>' . number_format($saldo, 2, ",", ".") . '</center></td>
											</tr>';
									$ordem++;
								}
								if ($total_cheque > 0) {
									$saldo += $total_cheque;
									echo	'<tr>
											<td>' . $ordem . '</td>
											<td>' . mudaData($data) . '</td>
											<td>Cheque</td>
											<td><center>' . number_format($total_cheque, 2, ",", ".") . '</center></td>
											<td></td>
											<td><center>' . number_format($saldo, 2, ",", ".") . '</center></td>
											</tr>';
									$ordem++;
								}
								if ($total_transferencia > 0) {
									$saldo += $total_transferencia;
									echo	'<tr>
											<td>' . $ordem . '</td>
											<td>' . mudaData($data) . '</td>
											<td>Transfer&ecirc;ncia Bancária</td>
											<td><center>' . number_format($total_transferencia, 2, ",", ".") . '</center></td>
											<td></td>
											<td><center>' . number_format($saldo, 2, ",", ".") . '</center></td>
											</tr>';
									$ordem++;
								}
								if ($total_boleto_site > 0) {
									$saldo += $total_boleto_site;
									echo	'<tr>
											<td>' . $ordem . '</td>
											<td>' . mudaData($data) . '</td>
											<td>Boleto Site</td>
											<td><center>' . number_format($total_boleto_site, 2, ",", ".") . '</center></td>
											<td></td>
											<td><center>' . number_format($saldo, 2, ",", ".") . '</center></td>
											</tr>';
									$ordem++;
								}
								if ($total_pix > 0) {
									$saldo += $total_pix;
									echo	'<tr>
											<td>' . $ordem . '</td>
											<td>' . mudaData($data) . '</td>
											<td>Pix</td>
											<td><center>' . number_format($total_pix, 2, ",", ".") . '</center></td>
											<td></td>
											<td><center>' . number_format($saldo, 2, ",", ".") . '</center></td>
											</tr>';
									$ordem++;
								}
								// Transferências
								$saldo += $total_trans;
								if ($total_trans != 0) {
									echo	'<tr>
											<td>' . $ordem . '</td>
											<td>' . mudaData($data) . '</td>
											<td>Saldo de transferências</td>';
									if ($total_trans < 0) {
										echo '<td></td>
												<td><center>' . number_format($total_trans, 2, ",", ".") . '</center></td>';
									} else {
										echo '<td><center>' . number_format($total_trans, 2, ",", ".") . '</center></td>
												<td></td>';
									}
									echo 	'<td><center>' . number_format($saldo, 2, ",", ".") . '</center></td>
											</tr>';
									$ordem++;
								}
								echo	'<tr>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td><b><center>Saldo do dia:</center></b></td>
										<td><center>' . number_format($total, 2, ",", ".") . '</center></td>
										</tr>';
								// Depósitos
								if ($total_deposits > 0) {
									echo	'<tr>
											<td></td>
											<td></td>
											<td></td>
											<td></td>
											<td><b><center>Depósitos efetuados:</center></b></td>
											<td><center>' . number_format($total_deposits, 2, ",", ".") . '</center></td>
											</tr>';
								}
								if ($valid > 1) {
									echo	'</tbody>
											</table>
											</div>
											<!-- /.table-responsive -->
											</div>
											<!-- /.panel-body -->
											</div>
											<!-- /.panel -->
											</div>
											</div>';
								}
								if ($not_in && $not_out && $not_in_cred && $not_transfer_out && $not_deposits) {
									echo "<p>Não há movimento de caixa nesse dia.</p>";
								} else {
									if ($establishment != 3 || strcmp($box, "T") == 0) {
										echo '<div class="row">';
										echo '<div class="col-lg-4">
											<div class="panel panel-primary">
												<div class="panel-heading">
													Para conferência no Caixa Digital (R$)
												</div>
												<!-- /.panel-heading -->
												<div class="panel-body">
													<div class="table-responsive">
														<table class="table">
															<tbody>
																<tr>
																	<td>Protocolos pagos do dia</td>
																	<td><center>' . number_format($total_prot_paid_day - $total_sup, 2, ",", ".") . '</center></td>
																</tr>
																<tr>
																	<td>Protocolos do dia em aberto</td>
																	<td><center>' . number_format($total_prot_day - $total_prot_paid_day, 2, ",", ".") . '</center></td>
																</tr>
																<tr>
																	<td>Transferências</td>
																	<td><center>' . number_format($total_trans_prot, 2, ",", ".") . '</center></td>
																</tr>
																<tr>
																	<td><b>Total</b></td>
																	<td><center>' . number_format($total_prot_day - $total_sup + $total_trans_prot, 2, ",", ".") . '</center></td>
																</tr>
															</tbody>
														</table>
													</div>
													<!-- /.table-responsive -->
												</div>
												<!-- /.panel-body -->
											</div>
											<!-- /.panel -->
										</div>';
										echo '<div class="col-lg-8">';
										echo '<div class="row">';
										echo	'<div class="col-lg-4">
													<div class="panel panel-green">
														<div class="panel-heading">
															<center>Total suportes</center>
														</div>
														<div class="panel-footer">
															<center>' . number_format($total_sup, 2, ",", ".") . '</center>
														</div>
													</div>
													</div>';
										echo	'<div class="col-lg-4">
													<div class="panel panel-green">
														<div class="panel-heading">
															<center>Protocolos pagos de dias anteriores</center>
														</div>
														<div class="panel-footer">
															<center>' . number_format($total_paid_another_day, 2, ",", ".") . '</center>
														</div>
													</div>
													</div>';
										echo	'<div class="col-lg-4">
													<div class="panel panel-green">
														<div class="panel-heading">
															<center>Protocolos acumulados</center>
														</div>
														<div class="panel-footer">';
										if ($establishment != 2) {
											echo '<center>' . number_format($total_prot_acu - $soma_pago_geral, 2, ",", ".") . '</center>';
										} else {
											$query_gamb = "SELECT sum(value) AS valor_soma FROM gambiarra WHERE 1";
											$result_gamb = mysqli_query($con, $query_gamb);
											$row = mysqli_fetch_assoc($result_gamb);
											$gambiarra = $row['valor_soma'];
											echo '<center>' . number_format($total_prot_acu - $soma_pago_geral + $gambiarra, 2, ",", ".") . '</center>';
										}
										echo 		'</div>
													</div>
												</div>';
										echo '</div>';

										// Pegando as placas de carro
										$query_qsc = "SELECT sum(quantity) AS total FROM sale_products WHERE sale IN (SELECT id FROM sales WHERE date = '$data' AND establishment = '$establishment') AND product = '1'";
										$result_qsc = mysqli_query($con, $query_qsc);
										$row_qsc = mysqli_fetch_assoc($result_qsc);
										$total_sc = $row_qsc['total'];
										$total_sc = $row_qsc['total'];
										if (empty($total_sc)) {
											$total_sc = 0;
										}
										// Pegando as placas de moto
										$query_qsm = "SELECT sum(quantity) AS total FROM sale_products WHERE sale IN (SELECT id FROM sales WHERE date = '$data' AND establishment = '$establishment') AND product = '2'";
										$result_qsm = mysqli_query($con, $query_qsm);
										$row_qsm = mysqli_fetch_assoc($result_qsm);
										$total_sm = $row_qsm['total'];
										if (empty($total_sm)) {
											$total_sm = 0;
										}

										if ($user->getRole() == 1) {
											echo '<div class="row">';
											echo	'<div class="col-lg-4">
													<div class="panel panel-primary">
														<div class="panel-heading">
															<center>Caixa Digital (placas de carro)</center>
														</div>
														<div class="panel-footer">
															<center>' . $total_sc . '</center>
														</div>
													</div>
													</div>';
											echo	'<div class="col-lg-4">
													<div class="panel panel-primary">
														<div class="panel-heading">
															<center>Caixa Digital (placas de moto)</center>
														</div>
														<div class="panel-footer">
															<center>' . $total_sm . '</center>
														</div>
													</div>
													</div>';
											echo	'<div class="col-lg-4">
													<div class="panel panel-green">
														<div class="panel-heading">
															<center>Protocolos acumulados (mês atual)</center>
														</div>
														<div class="panel-footer">';
														echo '<center>' . number_format($total_prot_acu_month - $soma_pago_geral_month, 2, ",", ".") . '</center>';
											echo '</div>
													</div>
													</div>';
											echo '</div>';
											echo '</div>';
										}
									}
								}
							} else {
								echo "Você não tem autorização para acessar o caixa deste estabelecimento.";
							}
						} else {
							echo "O caixa que você está tentando acessar não pertence a esse estabelecimento ou não existe.";
						}
					} else {
						echo "Erro ao conectar ao banco de dados.";
					}
					?>
				</div>
				<!-- /.col-lg-12 -->
			</div>
			<!-- /.row -->
<?php
		} else {
			echo "Você não tem autorização para acessar o caixa deste estabelecimento.";
		}
	} else {
		header("Location: ../login");
	}
} else {
	header("Location: ../login");
}
?>