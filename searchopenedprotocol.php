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
	if($user->getRole() == 1 || $user->getRole() == 3  || $user->getRole() == 5 ){
		$data = $_GET['date'];
		$protocol = $_GET['protocol'];
?>
		<div class="row">
			<div class="col-lg-12">									
				<?php
					if(connect()){
						$con = $_SESSION['con'];
						$query_product_name = "SELECT * FROM `sales` WHERE id = '$protocol' AND establishment = '$establishment'";
						$result_product_name = mysqli_query($con, $query_product_name);
						$num_rows_product_name = mysqli_num_rows($result_product_name);
						if($num_rows_product_name > 0){
							$i = 1;
							$total = 0;
							$valid = 1;
							while ( $row_product_name = mysqli_fetch_array($result_product_name) ){
								$customer_id = $row_product_name['customer'];
								$date_create = $row_product_name['date'];
								$time_create = $row_product_name['time'];
								$protocol_id = $row_product_name['id'];
								
								// Verificar se foi pago algo e quanto foi para subtrair
								$query_ver_pago = "SELECT * FROM payments WHERE protocol = '$protocol_id'";
								$result_ver_pago = mysqli_query($con,$query_ver_pago);
								$num_rows_ver_pago = mysqli_num_rows($result_ver_pago);
								$total_pago = 0;
								if($num_rows_ver_pago > 0){
									while($row_ver_pago = mysqli_fetch_array($result_ver_pago)){
										$value_prot = $row_ver_pago['value'];
										$total_pago += $value_prot;
									}
								}
								
								// Verificar quantas placas tem no protocolo
								$query_ver_tot = "SELECT * FROM sale_products WHERE sale = '$protocol_id'";
								$result_ver_tot = mysqli_query($con,$query_ver_tot);
								$num_rows_ver_tot = mysqli_num_rows($result_ver_tot);
								$total_protocolo = 0;
								if($num_rows_ver_tot > 0){
									while($row_ver_tot = mysqli_fetch_array($result_ver_tot)){
										$value_prod = $row_ver_tot['value'];
										$quantity_prod = $row_ver_tot['quantity'];
										$total_protocolo += $value_prod*$quantity_prod;
									}
								}
							
								if($total_pago != $total_protocolo){
									// Pegar nome do cliente
									$query_name_customer = "SELECT * FROM customers WHERE id = '$customer_id'";
									$result_name_customer = mysqli_query($con,$query_name_customer);
									$num_rows_name_customer = mysqli_num_rows($result_name_customer);
									if($num_rows_name_customer > 0){
										while($row_name_customer = mysqli_fetch_array($result_name_customer)){
											$customer_name = $row_name_customer['name'];
										}
									} else {
										$customer_name = "Cliente não cadastrado";
									}
									// Pegar valores e somar o total
									$subtotal = 0;
									$query_total_protocols = "SELECT * FROM sale_products WHERE sale = '$protocol_id'";
									$result_total_protocols = mysqli_query($con,$query_total_protocols);
									while( $row_total_protocols = mysqli_fetch_array($result_total_protocols) ){
										$value = $row_total_protocols['value'];
										$quantity = $row_total_protocols['quantity'];
										$subtotal += $value*$quantity;
									}
									
									// Pegar o id_sale_product do que foi pago
									$query_pvppp = "SELECT * FROM payments WHERE protocol = '$protocol_id'";
									$result_pvppp = mysqli_query($con,$query_pvppp);
									$num_rows_pvppp = mysqli_num_rows($result_pvppp);
									while($row_pvppp = mysqli_fetch_array($result_pvppp)){
										$id_sale_product = $row_pvppp['id_sale_product'];
										// Pegar o value do id_sale_product
										$query_pvisp = "SELECT * FROM sale_products WHERE id = '$id_sale_product'";
										$result_pvisp = mysqli_query($con,$query_pvisp);
										$num_rows_pvisp = mysqli_num_rows($result_pvisp);
										if($num_rows_pvisp > 0){
											while( $row_pvisp = mysqli_fetch_array($result_pvisp) ){
												$value_dois = $row_pvisp['value'];
												$quantity_dois = $row_pvisp['quantity'];
											}
										}
										$subtotal -= $value_dois*$quantity_dois;
									}
									if($valid == 1){
										echo '<div class="row">
											<div class="col-lg-12">
												<div class="panel panel-default">
													<div class="panel-heading">
														Número do protocolo: '.$protocol.'
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
																		<th><center>Valor (R$)</center></th>
																	</tr>
																</thead>
																<tbody>';
									}
									$valid++;									
									
									echo	'<tr>
											<td>'.$i.'</td>
											<td>'.mudaData($date_create).' '.$time_create.'</td>
											<td>'.$customer_name.'</td>';
									if(isset($_GET['date']) && $_GET['date'] != ""){
										if(isset($_GET['pf']) && $_GET['pf'] == 1){
											echo	'<td><center><a href=payment?protocol='.$protocol_id.'&date='.$data.'&pf=1>'.$protocol_id.'</a></center></td>';
										} else {
											echo	'<td><center><a href=payment?protocol='.$protocol_id.'&date='.$data.'>'.$protocol_id.'</a></center></td>';
										}
									} else {
										if(isset($_GET['pf']) && $_GET['pf'] == 1){
											echo	'<td><center><a href=payment?protocol='.$protocol_id.'&pf=1>'.$protocol_id.'</a></center></td>';
										} else {
											echo	'<td><center><a href=payment?protocol='.$protocol_id.'>'.$protocol_id.'</a></center></td>';
										}
									}
									echo	'<td><center>'.number_format($subtotal,2,",",".").'</center></td>
										</tr>';
									$i++;
									$total += $subtotal;
								}
							}
							if($valid > 1){
								echo	'<tr>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td><center><b>Total:</b></center></td>
										<td><center><b>'.number_format($total,2,",",".").'</b></center></td>
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
								echo "<p>Esse protocolo não está em aberto.</p>";
							}
						} else {
							if($protocol != ""){
								echo "<p>Esse protocolo não existe, ou você não tem autorização para acessá-lo.</p>";
							} else {
								echo "<p>Digite o número de um protocolo válido.</p>";
							}
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