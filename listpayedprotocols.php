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
	$hoje = date("Y-m-d");
	if ($user->getRole() == 1 || $user->getRole() == 3  || $user->getRole() == 5) {
		if ($box != 0 || strcmp($box, "T") == 0) {
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
						if (isset($data) && $data != "") {
							$is_an_day = TRUE;
						} else {
							$is_an_day = FALSE;
						}
						echo '<div class="row">
							<div class="col-lg-12">';
						$not_in = FALSE;
						$not_out = FALSE;
						$con = $_SESSION['con'];
						$query_product_name = "SELECT * FROM `show_payed_sales` WHERE date = '$data' AND establishment = '$establishment' AND (";
						for ($t = 0; $t < $qt; $t++) {
							$box_l = $boxes_list[$t];
							if ($t == 0) {
								$query_product_name .= "box = '$box_l'";
							} else {
								$query_product_name .= " OR box = '$box_l'";
							}
						}
						$query_product_name .= ") ORDER BY protocol";
						$result_product_name = mysqli_query($con, $query_product_name);
						$num_rows_product_name = mysqli_num_rows($result_product_name);
						if ($num_rows_product_name > 0) {
							echo '<div class="row">
									<div class="col-lg-12">
										<p><button class="btn btn-success" onclick="showLess()">Resumido</button></p>
									</div>
								</div>';
							echo '<div class="row">
							<div class="col-lg-12">
								<div class="panel panel-default">
									<div class="panel-heading">
										VENDAS
									</div>
									<!-- /.panel-heading -->
									<div class="panel-body">
										<div class="table-responsive">
											<table class="table">
												<thead>
													<tr>
														<th>#</th>
														<th>Data</th>
														<th>Cliente</th>
														<th><center>Protocolo</center></th>
														<th><center>Forma de pagamento</center></th>
														<th><center>Valor (R$)</center></th>
													</tr>
												</thead>
												<tbody>';
							$i = 1;
							while ($row_gl = mysqli_fetch_array($result_product_name)) {
								$customer_name = $row_gl['customer_name'];
								$protocol_id = $row_gl['protocol'];
								$payment_name = $row_gl['payment_method'];
								$value_gl = $row_gl['value'];
								$date_protocol = $row_gl['date'];
								echo	'<tr>
										<td>' . $i . '</td>
										<td>' . mudaData($date_protocol) . '</td>
										<td>' . $customer_name . '</td>';
								if ($user->getRole() == 1) {
									echo '<td><center><a href="payment?protocol=' . $protocol_id . '">' . $protocol_id . '</a></center></td>';
								} else {
									echo '<td><center>' . $protocol_id . '</center></td>';
								}
								echo	'<td><center>' . $payment_name . '</center></center></td>
										<td><center>' . number_format($value_gl, 2, ",", ".") . '</center></td>
										</tr>';
								$i++;
							}
							$total = 0;
							// Pegando o total em dinheiro
							$query_gsm = "SELECT sum(value) AS valor FROM show_summary_sales WHERE date = '$data' AND establishment = '$establishment' AND (";
							for ($t = 0; $t < $qt; $t++) {
								$box_l = $boxes_list[$t];
								if ($t == 0) {
									$query_gsm .= "box = '$box_l'";
								} else {
									$query_gsm .= " OR box = '$box_l'";
								}
							}
							$query_gsm .= ") AND payment_method = 'Dinheiro'";
							$result_gsm = mysqli_query($con, $query_gsm);
							while ($row_gsm = mysqli_fetch_array($result_gsm)) {
								$payment_method_gsm = 'Dinheiro';
								$value_gsm = $row_gsm['valor'];
								if ($value_gsm > 0) {
									echo	'<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td><center><b>Total ' . $payment_method_gsm . ':</b></center></td>
									<td><center><b>' . number_format($value_gsm, 2, ",", ".") . '</b></center></td>
									</tr>';
									$total += $value_gsm;
								}
							}
							// Pegando o total em débito
							$query_gsm = "SELECT sum(value) AS valor FROM show_summary_sales WHERE date = '$data' AND establishment = '$establishment' AND (";
							for ($t = 0; $t < $qt; $t++) {
								$box_l = $boxes_list[$t];
								if ($t == 0) {
									$query_gsm .= "box = '$box_l'";
								} else {
									$query_gsm .= " OR box = '$box_l'";
								}
							}
							$query_gsm .= ") AND payment_method = 'Débito'";
							$result_gsm = mysqli_query($con, $query_gsm);
							while ($row_gsm = mysqli_fetch_array($result_gsm)) {
								$payment_method_gsm = 'Débito';
								$value_gsm = $row_gsm['valor'];
								if ($value_gsm > 0) {
									echo	'<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td><center><b>Total ' . $payment_method_gsm . ':</b></center></td>
									<td><center><b>' . number_format($value_gsm, 2, ",", ".") . '</b></center></td>
									</tr>';
									$total += $value_gsm;
								}
							}
							// Pegando o total em crédito
							$query_gsm = "SELECT sum(value) AS valor FROM show_summary_sales WHERE date = '$data' AND establishment = '$establishment' AND (";
							for ($t = 0; $t < $qt; $t++) {
								$box_l = $boxes_list[$t];
								if ($t == 0) {
									$query_gsm .= "box = '$box_l'";
								} else {
									$query_gsm .= " OR box = '$box_l'";
								}
							}
							$query_gsm .= ") AND payment_method = 'Crédito'";
							$result_gsm = mysqli_query($con, $query_gsm);
							while ($row_gsm = mysqli_fetch_array($result_gsm)) {
								$payment_method_gsm = 'Crédito';
								$value_gsm = $row_gsm['valor'];
								if ($value_gsm > 0) {
									echo	'<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td><center><b>Total ' . $payment_method_gsm . ':</b></center></td>
									<td><center><b>' . number_format($value_gsm, 2, ",", ".") . '</b></center></td>
									</tr>';
									$total += $value_gsm;
								}
							}
							// Pegando o total em carteira
							$query_gsm = "SELECT sum(value) AS valor FROM show_summary_sales WHERE date = '$data' AND establishment = '$establishment' AND (";
							for ($t = 0; $t < $qt; $t++) {
								$box_l = $boxes_list[$t];
								if ($t == 0) {
									$query_gsm .= "box = '$box_l'";
								} else {
									$query_gsm .= " OR box = '$box_l'";
								}
							}
							$query_gsm .= ") AND payment_method = 'Carteira'";
							$result_gsm = mysqli_query($con, $query_gsm);
							while ($row_gsm = mysqli_fetch_array($result_gsm)) {
								$payment_method_gsm = 'Carteira';
								$value_gsm = $row_gsm['valor'];
								if ($value_gsm > 0) {
									echo	'<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td><center><b>Total ' . $payment_method_gsm . ':</b></center></td>
									<td><center><b>' . number_format($value_gsm, 2, ",", ".") . '</b></center></td>
									</tr>';
									$total += $value_gsm;
								}
							}
							// Pegando o total em Boleto
							$query_gsm = "SELECT sum(value) AS valor FROM show_summary_sales WHERE date = '$data' AND establishment = '$establishment' AND (";
							for ($t = 0; $t < $qt; $t++) {
								$box_l = $boxes_list[$t];
								if ($t == 0) {
									$query_gsm .= "box = '$box_l'";
								} else {
									$query_gsm .= " OR box = '$box_l'";
								}
							}
							$query_gsm .= ") AND payment_method = 'Boleto'";
							$result_gsm = mysqli_query($con, $query_gsm);
							while ($row_gsm = mysqli_fetch_array($result_gsm)) {
								$payment_method_gsm = 'Boleto';
								$value_gsm = $row_gsm['valor'];
								if ($value_gsm > 0) {
									echo	'<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td><center><b>Total ' . $payment_method_gsm . ':</b></center></td>
									<td><center><b>' . number_format($value_gsm, 2, ",", ".") . '</b></center></td>
									</tr>';
									$total += $value_gsm;
								}
							}
							// Pegando o total em Cheque
							$query_gsm = "SELECT sum(value) AS valor FROM show_summary_sales WHERE date = '$data' AND establishment = '$establishment' AND (";
							for ($t = 0; $t < $qt; $t++) {
								$box_l = $boxes_list[$t];
								if ($t == 0) {
									$query_gsm .= "box = '$box_l'";
								} else {
									$query_gsm .= " OR box = '$box_l'";
								}
							}
							$query_gsm .= ") AND payment_method = 'Cheque'";
							$result_gsm = mysqli_query($con, $query_gsm);
							while ($row_gsm = mysqli_fetch_array($result_gsm)) {
								$payment_method_gsm = 'Cheque';
								$value_gsm = $row_gsm['valor'];
								if ($value_gsm > 0) {
									echo	'<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td><center><b>Total ' . $payment_method_gsm . ':</b></center></td>
									<td><center><b>' . number_format($value_gsm, 2, ",", ".") . '</b></center></td>
									</tr>';
									$total += $value_gsm;
								}
							}
							// Pegando o total em Transferência Bancária
							$query_gsm = "SELECT sum(value) AS valor FROM show_summary_sales WHERE date = '$data' AND establishment = '$establishment' AND (";
							for ($t = 0; $t < $qt; $t++) {
								$box_l = $boxes_list[$t];
								if ($t == 0) {
									$query_gsm .= "box = '$box_l'";
								} else {
									$query_gsm .= " OR box = '$box_l'";
								}
							}
							$query_gsm .= ") AND payment_method = 'Transferência Bancária'";
							$result_gsm = mysqli_query($con, $query_gsm);
							while ($row_gsm = mysqli_fetch_array($result_gsm)) {
								$payment_method_gsm = 'Transferência Bancária';
								$value_gsm = $row_gsm['valor'];
								if ($value_gsm > 0) {
									echo	'<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td><center><b>Total ' . $payment_method_gsm . ':</b></center></td>
									<td><center><b>' . number_format($value_gsm, 2, ",", ".") . '</b></center></td>
									</tr>';
									$total += $value_gsm;
								}
							}
							// Pegando o total em Boleto Site
							$query_gsm = "SELECT sum(value) AS valor FROM show_summary_sales WHERE date = '$data' AND establishment = '$establishment' AND (";
							for ($t = 0; $t < $qt; $t++) {
								$box_l = $boxes_list[$t];
								if ($t == 0) {
									$query_gsm .= "box = '$box_l'";
								} else {
									$query_gsm .= " OR box = '$box_l'";
								}
							}
							$query_gsm .= ") AND payment_method = 'Boleto Site'";
							$result_gsm = mysqli_query($con, $query_gsm);
							while ($row_gsm = mysqli_fetch_array($result_gsm)) {
								$payment_method_gsm = 'Boleto Site';
								$value_gsm = $row_gsm['valor'];
								if ($value_gsm > 0) {
									echo	'<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td><center><b>Total ' . $payment_method_gsm . ':</b></center></td>
									<td><center><b>' . number_format($value_gsm, 2, ",", ".") . '</b></center></td>
									</tr>';
									$total += $value_gsm;
								}
							}
							// Pegando o total em Pix
							$query_gsm = "SELECT sum(value) AS valor FROM show_summary_sales WHERE date = '$data' AND establishment = '$establishment' AND (";
							for ($t = 0; $t < $qt; $t++) {
								$box_l = $boxes_list[$t];
								if ($t == 0) {
									$query_gsm .= "box = '$box_l'";
								} else {
									$query_gsm .= " OR box = '$box_l'";
								}
							}
							$query_gsm .= ") AND payment_method = 'Pix'";
							$result_gsm = mysqli_query($con, $query_gsm);
							while ($row_gsm = mysqli_fetch_array($result_gsm)) {
								$payment_method_gsm = 'Pix';
								$value_gsm = $row_gsm['valor'];
								if ($value_gsm > 0) {
									echo	'<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td><center><b>Total ' . $payment_method_gsm . ':</b></center></td>
									<td><center><b>' . number_format($value_gsm, 2, ",", ".") . '</b></center></td>
									</tr>';
									$total += $value_gsm;
								}
							}
							echo	'<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td><center><b>Total Vendas:</b></center></td>
									<td><center><b>' . number_format($total, 2, ",", ".") . '</b></center></td>
									</tr>
									</tbody>
									</table>
									</div>
									<!-- /.table-responsive -->
									</div>
									<!-- /.panel-body -->
									</div>
									<!-- /.panel -->
									</div>
									</div>';
						} else {
							$not_in = TRUE;
						}


						// Imprimindo os crï¿½ditos
						$query_cred = "SELECT id, description, value, provider, chart_of_accounts FROM cash_movement WHERE date = '$data' AND in_out = '0' AND (";
						for ($t = 0; $t < $qt; $t++) {
							$box_l = $boxes_list[$t];
							if ($t == 0) {
								$query_cred .= "cashier = '$box_l'";
							} else {
								$query_cred .= " OR cashier = '$box_l'";
							}
						}
						$query_cred .= ")";
						//$query_cred = "SELECT description, value, provider, chart_of_accounts FROM cash_movement WHERE date = '$data' AND in_out = '0' AND cashier ='$box'";
						$result_cred = mysqli_query($con, $query_cred);
						$num_rows_cred = mysqli_num_rows($result_cred);
						if ($num_rows_cred > 0) {
							if ($valid == 1) {
								echo '<div class="row">
									<div class="col-lg-12">
										<p><button class="btn btn-success" onclick="showLess()">Resumido</button></p>
									</div>
								</div>';
							}
							$valid++;

							echo '<div class="row">
							<div class="col-lg-12">
								<div class="panel panel-default">
									<div class="panel-heading">
										CRï¿½DITOS
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
														<th><center>Valor (R$)</center></th>
														<th><center>Fornecedor</center></th>
														<th><center>Plano de contas</center></th>';
							if ($user->getRole() == 1) {
								echo					'<th>Ação</th>';
							}
							echo						'</tr>
												</thead>
												<tbody>';
							$h = 1;
							$total_cred = 0;
							while ($row_cred = mysqli_fetch_array($result_cred)) {
								$id_movement = $row_cred['id'];
								$description_cred = $row_cred['description'];
								$value_cred = $row_cred['value'];
								$total_cred += $value_cred;
								$provider_cred = $row_cred['provider'];
								$chart_of_accounts_cred = $row_cred['chart_of_accounts'];

								// Pegar nome do fornecedor
								$query_name_provider_cred = "SELECT name FROM providers WHERE id = '$provider_cred'";
								$result_name_provider_cred = mysqli_query($con, $query_name_provider_cred);
								$num_rows_name_provider_cred = mysqli_num_rows($result_name_provider_cred);
								if ($num_rows_name_provider_cred > 0) {
									while ($row_name_provider_cred = mysqli_fetch_array($result_name_provider_cred)) {
										$provider_name_cred = $row_name_provider_cred['name'];
									}
								} else {
									echo "Fornecedor não encontrado.";
								}

								// Pegar nome do plano de contas
								$query_name_coa_cred = "SELECT name FROM chart_of_accounts WHERE id = '$chart_of_accounts_cred'";
								$result_name_coa_cred = mysqli_query($con, $query_name_coa_cred);
								$num_rows_name_coa_cred = mysqli_num_rows($result_name_coa_cred);
								if ($num_rows_name_coa_cred > 0) {
									while ($row_name_coa_cred = mysqli_fetch_array($result_name_coa_cred)) {
										$coa_name_cred = $row_name_coa_cred['name'];
									}
								} else {
									echo "Plano de contas nao encontrado.";
								}
								echo	'<tr>
										<td>' . $h . '</td>
										<td>' . mudaData($data) . '</td>
										<td>' . $description_cred . '</td>
										<td><center>' . number_format($value_cred, 2, ",", ".") . '</center></td>
										<td><center>' . $provider_name_cred . '</center></td>
										<td><center>' . $coa_name_cred . '</center></td>';
								if ($user->getRole() == 1) {
									echo '<td><button type="button" class="btn btn-danger btn-circle" onclick="confdel(' . $id_movement . ')"><i class="fa fa-times"></i></button></td>';
								}
								echo	'</tr>';
								$h++;
							}
							echo	'<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td><center><b>Total Entradas:</b></center></td>
									<td><center><b>' . number_format($total_cred, 2, ",", ".") . '</b></center></td>';
							if ($user->getRole() == 1) {
								echo '<td>&nbsp;</td>';
							}
							echo	'</tr>
									</tbody>
									</table>
									</div>
									<!-- /.table-responsive -->
									</div>
									<!-- /.panel-body -->
									</div>
									<!-- /.panel -->
									</div>
									</div>';
						} else {
							$not_in_cred = TRUE;
						}


						// Imprimindo os débitos
						$query_debitos = "SELECT id, description, value, provider, chart_of_accounts FROM cash_movement WHERE date = '$data' AND in_out = '1' AND (";
						for ($t = 0; $t < $qt; $t++) {
							$box_l = $boxes_list[$t];
							if ($t == 0) {
								$query_debitos .= "cashier = '$box_l'";
							} else {
								$query_debitos .= " OR cashier = '$box_l'";
							}
						}
						$query_debitos .= ")";
						//$query_debitos = "SELECT description, value, provider, chart_of_accounts FROM cash_movement WHERE date = '$data' AND in_out = '1' AND cashier ='$box'";
						$result_debitos = mysqli_query($con, $query_debitos);
						$num_rows_debitos = mysqli_num_rows($result_debitos);
						if ($num_rows_debitos > 0) {
							if ($valid == 1) {
								echo '<div class="row">
									<div class="col-lg-12">
										<p><button class="btn btn-success" onclick="showLess()">Resumido</button></p>
									</div>
								</div>';
							}

							echo '<div class="row">
							<div class="col-lg-12">
								<div class="panel panel-default">
									<div class="panel-heading">
										DÉBITOS
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
														<th><center>Valor (R$)</center></th>
														<th><center>Fornecedor</center></th>
														<th><center>Plano de contas</center></th>';
							if ($user->getRole() == 1) {
								echo					'<th>Ação</th>';
							}
							echo				'</tr>
												</thead>
												<tbody>';
							$h = 1;
							while ($row_debitos = mysqli_fetch_array($result_debitos)) {
								$id_movement = $row_debitos['id'];
								$description = $row_debitos['description'];
								$value = $row_debitos['value'];
								$total_debitos += $value;
								$provider = $row_debitos['provider'];
								$chart_of_accounts = $row_debitos['chart_of_accounts'];

								// Pegar nome do fornecedor
								$query_name_provider = "SELECT name FROM providers WHERE id = '$provider'";
								$result_name_provider = mysqli_query($con, $query_name_provider);
								$num_rows_name_provider = mysqli_num_rows($result_name_provider);
								if ($num_rows_name_provider > 0) {
									while ($row_name_provider = mysqli_fetch_array($result_name_provider)) {
										$provider_name = $row_name_provider['name'];
									}
								} else {
									echo "Fornecedor nï¿½o encontrado.";
								}

								// Pegar nome do plano de contas
								$query_name_coa = "SELECT name FROM chart_of_accounts WHERE id = '$chart_of_accounts'";
								$result_name_coa = mysqli_query($con, $query_name_coa);
								$num_rows_name_coa = mysqli_num_rows($result_name_coa);
								if ($num_rows_name_coa > 0) {
									while ($row_name_coa = mysqli_fetch_array($result_name_coa)) {
										$coa_name = $row_name_coa['name'];
									}
								} else {
									echo "Plano de contas nao encontrado.";
								}
								echo	'<tr>
										<td>' . $h . '</td>
										<td>' . mudaData($data) . '</td>
										<td>' . $description . '</td>
										<td><center>-' . number_format($value, 2, ",", ".") . '</center></td>
										<td><center>' . $provider_name . '</center></td>
										<td><center>' . $coa_name . '</center></td>';
								if ($user->getRole() == 1) {
									echo '<td><button type="button" class="btn btn-danger btn-circle" onclick="confdel(' . $id_movement . ')"><i class="fa fa-times"></i></button></td>';
								}
								echo 	'</tr>';
								$h++;
							}
							echo	'<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td><center><b>Total Saídas:</b></center></td>
									<td><center><b>-' . number_format($total_debitos, 2, ",", ".") . '</b></center></td>';
							if ($user->getRole() == 1) {
								echo '<td>&nbsp;</td>';
							}
							echo	'</tr>
									</tbody>
									</table>
									</div>
									<!-- /.table-responsive -->
									</div>
									<!-- /.panel-body -->
									</div>
									<!-- /.panel -->
									</div>
									</div>';
						} else {
							$not_out = TRUE;
						}


						// Imprimindo os depï¿½sitos
						$query_deposits = "SELECT * FROM deposits_group WHERE date = '$data' AND (";
						for ($t = 0; $t < $qt; $t++) {
							$box_l = $boxes_list[$t];
							if ($t == 0) {
								$query_deposits .= "box = '$box_l'";
							} else {
								$query_deposits .= " OR box = '$box_l'";
							}
						}
						$query_deposits .= ")";
						//$query_deposits = "SELECT * FROM deposits_group WHERE date = '$data' AND box = '$box'";
						$result_deposits = mysqli_query($con, $query_deposits);
						$num_rows_deposits = mysqli_num_rows($result_deposits);
						if ($num_rows_deposits > 0) {
							if ($valid == 1) {
								echo '<div class="row">
									<div class="col-lg-12">
										<p><button class="btn btn-success" onclick="showLess()">Resumido</button></p>
									</div>
								</div>';
							}

							echo '<div class="row">
							<div class="col-lg-12">
								<div class="panel panel-default">
									<div class="panel-heading">
										DEPï¿½SITOS
									</div>
									<!-- /.panel-heading -->
									<div class="panel-body">
										<div class="table-responsive">
											<table class="table">
												<thead>
													<tr>
														<th>#</th>
														<th>Data/Hora</th>
														<th>Descrição</th>
														<th><center>Código do envelope</center></th>
														<th><center>Valor (R$)</center></th>
													</tr>
												</thead>
												<tbody>';
							$f = 1;
							$total_deposits = 0;
							while ($row_dep = mysqli_fetch_array($result_deposits)) {
								$id_group = $row_dep['id'];
								$date_group = $row_dep['date'];
								$time_group = $row_dep['time'];
								$responsible = $row_dep['responsible'];
								$query_du = "SELECT * FROM deposits WHERE id_group = '$id_group'";
								$result_du = mysqli_query($con, $query_du);
								$num_rows_du = mysqli_num_rows($result_du);
								if ($num_rows_du > 0) {
									while ($row_du = mysqli_fetch_array($result_du)) {
										$description_du = $row_du['description'];
										$code_du = $row_du['code'];
										$value_du = $row_du['value'];
										$total_deposits += $value_du;
										echo	'<tr>
												<td>' . $f . '</td>
												<td>' . mudaData($date_group) . ' ' . $time_group . '</td>
												<td>' . $description_du . '</td>
												<td><center>' . $code_du . '</center></td>
												<td><center>' . number_format($value_du, 2, ",", ".") . '</center></td>
												</tr>';
									}
								}
								$f++;
							}
							echo	'<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td><center><b>Saldo de depósitos:</b></center></td>
									<td><center><b>' . number_format($total_deposits, 2, ",", ".") . '</b></center></td>
									</tr>
									</tbody>
									</table>
									</div>
									<!-- /.table-responsive -->
									</div>
									<!-- /.panel-body -->
									</div>
									<!-- /.panel -->
									</div>
									</div>';
						} else {
							$not_deposits = TRUE;
						}


						// Imprimindo as transferências
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
						//$query_trans = "SELECT * FROM accounts_movement WHERE date = '$data' AND (origin ='$origin_destination' OR destination = '$origin_destination')";
						$result_trans = mysqli_query($con, $query_trans);
						$num_rows_trans = mysqli_num_rows($result_trans);
						if ($num_rows_trans > 0) {
							if ($valid == 1) {
								echo '<div class="row">
									<div class="col-lg-12">
										<p><button class="btn btn-success" onclick="showLess()">Resumido</button></p>
									</div>
								</div>';
							}

							echo '<div class="row">
							<div class="col-lg-12">
								<div class="panel panel-default">
									<div class="panel-heading">
										TRANSFERÊNCIAS
									</div>
									<!-- /.panel-heading -->
									<div class="panel-body">
										<div class="table-responsive">
											<table class="table">
												<thead>
													<tr>
														<th>#</th>
														<th>Data/Hora</th>
														<th>Descrição</th>
														<th><center>Valor (R$)</center></th>
														<th><center>Origem/Destino</center></th>
													</tr>
												</thead>
												<tbody>';
							$f = 1;
							$total_trans = 0;
							while ($row_trans = mysqli_fetch_array($result_trans)) {
								$description_trans = $row_trans['description'];
								$value_trans = $row_trans['value'];
								$origin_trans = $row_trans['origin'];
								$destination_trans = $row_trans['destination'];
								if ($origin_trans == $origin_destination) {
									$total_trans -= $value_trans;
								} else {
									$total_trans += $value_trans;
								}
								$date_trans = $row_trans['date'];
								$time_trans = $row_trans['time'];

								// Pegar nome da conta
								if ($origin_trans == $origin_destination) {
									$account_trans = $destination_trans;
								} else {
									$account_trans = $origin_trans;
								}
								$query_name_account = "SELECT name FROM accounts WHERE id = '$account_trans'";
								$result_name_account = mysqli_query($con, $query_name_account);
								$num_rows_name_account = mysqli_num_rows($result_name_account);
								if ($num_rows_name_account > 0) {
									while ($row_name_account = mysqli_fetch_array($result_name_account)) {
										$account_name = $row_name_account['name'];
									}
								} else {
									echo "Conta nï¿½o encontrada.";
								}
								echo	'<tr>
										<td>' . $f . '</td>
										<td>' . mudaData($date_trans) . ' ' . $time_trans . '</td>
										<td>' . $description_trans . '</td>';
								if ($origin_trans == $origin_destination) {
									echo '<td><center>-' . number_format($value_trans, 2, ",", ".") . '</center></td>';
								} else {
									echo '<td><center>' . number_format($value_trans, 2, ",", ".") . '</center></td>';
								}
								echo	'<td><center>' . $account_name . '</center></td>
										</tr>';
								$f++;
							}
							echo	'<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td><center><b>Saldo de transferências:</b></center></td>
									<td><center><b>' . number_format($total_trans, 2, ",", ".") . '</b></center></td>
									</tr>
									</tbody>
									</table>
									</div>
									<!-- /.table-responsive -->
									</div>
									<!-- /.panel-body -->
									</div>
									<!-- /.panel -->
									</div>
									</div>';
						} else {
							$not_transfer_out = TRUE;
						}

						// Imprimindo o total dos protocolos em aberto do dia
						// Pegando o total dos protocolos
						if ($establishment != 3 || strcmp($box, "T") == 0) {
							$total_prot_day = 0;
							$query_get_tot = "SELECT value FROM `total_protocols_day` WHERE establishment = '$establishment' AND date = '$data' LIMIT 1";
							$result_get_tot = mysqli_query($con, $query_get_tot);
							$num_rows_get_tot = mysqli_num_rows($result_get_tot);
							if ($num_rows_get_tot > 0) {
								while ($row_get_tot = mysqli_fetch_array($result_get_tot)) {
									$total_prot_day = $row_get_tot['value'];
								}
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

						if ($not_in && $not_out && $not_in_cred && $not_transfer_out && $not_deposits) {
							if ($is_an_day) {
								if ($total_prot_day > 0) {
									if ($establishment != 3 || strcmp($box, "T") == 0) {
										echo '<div class="row">';
										echo '<div class="col-lg-4">
											<div class="panel panel-default">
												<div class="panel-heading">
													Para conferência no Caixa Digital
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
											echo '</div>';

											echo '</div>';
										}
									}
								} else {
									echo "<p>Não há movimento de caixa nesse dia.</p>";
								}
							} else {
								echo "<p>Selecione um dia para ver o movimento de caixa.</p>";
							}
						} else {
							// Caixa
							echo '<div class="row">';
							echo '<div class="col-lg-2">&nbsp;</div>';
							if ($establishment != 3 || strcmp($box, "T") == 0) {
								echo	'<div class="col-lg-3">
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
								echo			'</div>
											</div>
										</div>';
							}
							if ($establishment != 3 || strcmp($box, "T") == 0) {
								echo	'<div class="col-lg-3">
											<div class="panel panel-green">
												<div class="panel-heading">
													<center>Protocolos do dia em aberto</center>
												</div>
												<div class="panel-footer">
													<center>' . number_format($total_prot_day - $total_prot_paid_day, 2, ",", ".") . '</center>
												</div>
											</div>
										</div>';
							}
							echo	'<div class="col-lg-2">';
							if ($total + $total_cred + $total_trans >= $total_debitos) {
								echo '<div class="panel panel-green">';
							} else {
								echo '<div class="panel panel-red">';
							}
							echo				'<div class="panel-heading">
												<center>Total Geral</center>
											</div>
											<div class="panel-footer">
												<center>' . number_format($total + $total_cred - $total_debitos + $total_trans, 2, ",", ".") . '</center>
											</div>
										</div>
									</div>
									<div class="col-lg-2">&nbsp;</div>
								</div>';
						}
						echo '</div>
							</div>';
					} else {
						echo "Você não tem autorização para acessar o caixa deste estabelecimento.";
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
