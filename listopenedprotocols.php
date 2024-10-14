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

	$today = date("Y-m-d");
	if (!connect())
		die("Erro na conexão com o banco de dados!");

	$con = $_SESSION['con'];
	$query = "SELECT closed FROM total_protocols_day WHERE establishment = '$establishment' AND date = '$today'";
	$result = mysqli_query($con, $query);
	$row = mysqli_fetch_assoc($result);
	$closed = $row['closed'];
	closedb();

	if ($user->getRole() == 1 || $user->getRole() == 3  || $user->getRole() == 5) {
		$data = $_GET['date'];
		if (strcmp($data, "") != 0) {
			$data_inicial = $data;
			$data_final = $data;
		} else {
			$data_inicial = "2018-12-26";
			$data_final = date("Y-m-d");
		}
		if (strtotime($data_inicial) < strtotime("2019-03-18")) {
			$data_inicial = "2019-03-18";
		}
?>
		<div class="row">
			<div class="col-lg-12">
				<?php
				if (connect()) {
					$con = $_SESSION['con'];
					$valid = 1;
					$i = 1;
					$total = 0;
					// Receber faixa de datas e fazer um looping
					$query_gtd = "SELECT * FROM report_total_opened_protocols WHERE date >= '$data_inicial' AND date <= '$data_final' AND establishment = '$establishment'";
					$result_gtd = mysqli_query($con, $query_gtd);
					$num_rows_gtd = mysqli_num_rows($result_gtd);
					if ($num_rows_gtd > 0) {
						while ($row_gtd = mysqli_fetch_array($result_gtd)) {
							$id_prot = $row_gtd['protocol'];
							$total_protocol = $row_gtd['total'];
							$pago_protocol = $row_gtd['total_paid'];
							$date_cre = $row_gtd['date'];
							$time_cre = $row_gtd['time'];
							$customer_name = $row_gtd['customer_name'];
							if ($total_protocol - $pago_protocol != 0) {
								// Imprimir cabeçalho
								if ($valid == 1) {
									echo '<div class="row">
										<div class="col-lg-12">
											<div class="panel panel-default">
												<div class="panel-heading">
													Protocolos com pagamento em aberto
												</div>
												<!-- /.panel-heading -->
												<div class="panel-body">
													<div class="table-responsive">
														<table class="table">
															<thead>
																<tr>
																	<th>#</th>
																	<th>Data/Hora</th>
																	<th>Cliente</th>
																	<th><center>Protocolo</center></th>
																	<th><center>Valor</center></th>
																</tr>
															</thead>
															<tbody>';
									// Imprimindo da gambiarra
									$sbt = 0;
									if ($establishment == 2 && $_GET["date"] == "") {
										$data_inicial_real = "2018-12-26";
										$query_gg = "SELECT * FROM `gambiarra` WHERE date >= '$data_inicial_real' AND date <= '$data_final'";
										$result_gg = mysqli_query($con, $query_gg);
										$num_rows_gg = mysqli_num_rows($result_gg);
										if ($num_rows_gg > 0) {
											while ($rrr = mysqli_fetch_array($result_gg)) {
												$ptc = $rrr['protocol'];
												$val = $rrr['value'];
												$sbt += $val;
												$qgc = "SELECT * FROM sales WHERE id = '$ptc'";
												$result_qgc = mysqli_query($con, $qgc);
												while ($row_qgc = mysqli_fetch_array($result_qgc)) {
													$cqgc = $row_qgc['customer'];
													$date_cqgc = $row_qgc['date'];
													$time_cqgc = $row_qgc['time'];
													$query_gcn = "SELECT * FROM customers WHERE id = '$cqgc'";
													$result_gcn = mysqli_query($con, $query_gcn);
													while ($row_gcn = mysqli_fetch_array($result_gcn)) {
														$custname = $row_gcn['name'];
														echo	'<tr>
																<td>' . $i . '</td>
																<td>' . mudaData($date_cqgc) . ' ' . $time_cqgc . '</td>
																<td>' . $custname . '</td>';
														if ($closed) {
															echo '<td><center>' . $ptc . '</center></td>';
														} else {
															if (isset($_GET['date']) && $_GET['date'] != "") {
																echo	'<td><center><a href=payment?protocol=' . $ptc . '&date=' . $data . '>' . $ptc . '</a></center></td>';
															} else {
																echo	'<td><center><a href=payment?protocol=' . $ptc . '>' . $ptc . '</a></center></td>';
															}
														}
														echo	'<td><center>' . number_format($val, 2, ",", ".") . '</center></td>
															</tr>';
														$i++;
													}
												}
											}
										}
									}
								}
								$valid++;
								echo	'<tr>
										<td>' . $i . '</td>
										<td>' . mudaData($date_cre) . ' ' . $time_cre . '</td>
										<td>' . $customer_name . '</td>';
								if (isset($_GET['date']) && $_GET['date'] != "") {
									if ($closed) {
										echo '<td><center>' . $id_prot . '</center></td>';
									} else {
										if (isset($_GET['pf']) && $_GET['pf'] == 1) {
											echo	'<td><center><a href=payment?protocol=' . $id_prot . '&date=' . $data . '&pf=1>' . $id_prot . '</a></center></td>';
										} else {
											echo	'<td><center><a href=payment?protocol=' . $id_prot . '&date=' . $data . '>' . $id_prot . '</a></center></td>';
										}
									}
								} else {
									if ($closed) {
										echo '<td><center>' . $id_prot . '</center></td>';
									} else {
										if (isset($_GET['pf']) && $_GET['pf'] == 1) {
											echo	'<td><center><a href=payment?protocol=' . $id_prot . '&pf=1>' . $id_prot . '</a></center></td>';
										} else {
											echo	'<td><center><a href=payment?protocol=' . $id_prot . '>' . $id_prot . '</a></center></td>';
										}
									}
								}
								echo	'<td><center>' . number_format($total_protocol - $pago_protocol, 2, ",", ".") . '</center></td>
									</tr>';
								$i++;
								$total += $total_protocol - $pago_protocol;
							}
						}
						if ($valid > 1) {
							echo	'<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td><center><b>Total:</b></center></td>
									<td><center><b>' . number_format($total + $sbt, 2, ",", ".") . '</b></center></td>
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
						}
					}
					// O validador continua igual a 1
					if ($valid == 1) {
						echo "<p>Não há protocolos em aberto para o período e estabeleciomento escolhidos.</p>";
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
		header("Location: ../login");
	}
} else {
	header("Location: ../login");
}
?>