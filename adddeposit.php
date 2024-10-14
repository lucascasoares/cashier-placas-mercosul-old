<?php
header('Content-Type: text/html; charset=utf8');
$system_name = "emplacarrn";
include 'db.php';
include 'User.php';
session_start();
$user = $_SESSION['user'];

if (isset($user)) {
	$establishment = $user->getEstablishment();

// receiving data form
$id = $_GET['id'];
$description = $_GET['description'];
$code = $_GET['code'];
$value = $_GET['value'];
$value = str_replace(".", "", $value);
$value = str_replace(",", ".", $value);
	//Gravando no banco de dados ! conectando com o localhost - mysql
	if (!connect())
		die ("Erro de conexao com localhost, o seguinte erro ocorreu -> ".mysqli_connect_error());
	$con = $_SESSION['con'];
	$query = "SELECT * FROM deposits_group_temp WHERE id = '$id'";
	$result = mysqli_query($con, $query);
	$num_rows = mysqli_num_rows($result);
	if($num_rows > 0){
		$query_v = "SELECT * FROM deposits_temp WHERE group = '$id' AND description = '$description' AND code = '$code' AND value='$value'";
		$resul = mysqli_query($con, $query_v);
		$num_r = mysqli_num_rows($resul);
		if($num_r <= 0){
			$query_in = "INSERT INTO `deposits_temp` (`id`, `id_group`, `description`, `code`, `value`) VALUES (NULL, '$id', '$description','$code', '$value')";
			$result_in = mysqli_query($con, $query_in);
			$affected_rows = mysqli_affected_rows($con);
			if($affected_rows == -1) {
				// Error
				echo	'<div class="alert alert-danger alert-dismissable">
						<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
						Erro desconhecido, favor contatar o suporte.
					</div>';
			}
		} else {
				echo	'<div class="alert alert-danger alert-dismissable">
						<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
						Esse depósito já adicionado a essa lista.
					</div>';
		}
		// Imprimindo lista de depósitos após adicionar
		$query_print = "SELECT * FROM deposits_group_temp WHERE id = '$id' ORDER BY id";
		$result_print = mysqli_query($con, $query_print);
		$num_rows_print = mysqli_num_rows($result_print);
		if($num_rows_print > 0){
			while ( $row = mysqli_fetch_array($result_print) ){
				$query_products = "SELECT * FROM deposits_temp WHERE id_group = '$id' ORDER BY id DESC";
				$result_products = mysqli_query($con, $query_products);
				$num_rows_products = mysqli_num_rows($result_products);
				if($num_rows_products > 0){
					echo '<div class="row">
						<div class="col-lg-12">
							<div class="panel panel-default">
								<div class="panel-heading">
									Depósitos
								</div>
								<!-- /.panel-heading -->
								<div class="panel-body">
									<div class="table-responsive">
										<table class="table">
											<thead>
												<tr>
													<th>#</th>
													<th>Descrição</th>
													<th><center>Código do envelope</center></th>
													<th><center>Valor (R$)</center></th>
												</tr>
											</thead>
											<tbody>';
											$i = 0;
											$total = 0;
											while ( $row_products = mysqli_fetch_array($result_products) ){
												$i++;
												$description = $row_products['description'];
												$code = $row_products['code'];
												$value = $row_products['value'];
												echo	'<tr>
														<td>'.$i.'</td>
														<td>'.$description.'</td>
														<td><center>'.$code.'</center></td>
														<td><center>'.number_format($value,2,",",".").'</center></td>
													</tr>';
												$total += $value;
											}
											echo	'<tr>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td><center><b>Total (R$):</b></center></td>
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
					echo '<div class="row">
						<div class="col-lg-9">&nbsp;</div>
						<div class="col-lg-3"><center><button type="button" class="btn btn-success" onclick="submit()">Finalizar depósito</button></center></div>
						</div>';
				}
			}
		}
	} else {
		echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Esse identificador temporário nao existe.</div>';
	}
// fim da autenticaçao
} else {
    header("Location: ../login");
}
?>