<?php
header('Content-Type: text/html; charset=utf8');
$system_name = "emplacarrn";
include 'db.php';
include 'User.php';
session_id($_COOKIE[session_name()]);
session_set_cookie_params(3600);
session_start(); // ready to go!
date_default_timezone_set('America/Fortaleza');
if (isset($_SESSION['user'])) {
	$user = $_SESSION['user'];
	$establishment = $user->getEstablishment();
	$user_id = $user->getId();
	$date = $_GET['date'];
	$n = $_GET['n'];

	// Pegando nome do estabelecimento
	if (connect()) {
		$con = $_SESSION['con'];
		$query_gnoe = "SELECT name FROM establishment WHERE id = '$establishment'";
		$result_gnoe = mysqli_query($con, $query_gnoe);
		$row_gnoe = mysqli_fetch_array($result_gnoe);
		$establishment_name = $row_gnoe['name'];
		closedb();
	} else {
		echo "Erro ao pegar o nome do estabelecimento.";
	}

	if ($user->getRole() == 1 || $user->getRole() == 3 || $user->getRole() == 5) {
?>
		<!DOCTYPE html>
		<html lang="pt-br">

		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf8">

			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<meta name="description" content="">
			<meta name="author" content="">
			<link rel="icon" type="image/png" href="/portal/emplacarrn/img/favicon-emplacar-rn.png">
			<title>Sistema - Emplacar RN</title>

			<!-- Bootstrap Core CSS -->
			<link href="../../portal/<?php echo $system_name; ?>/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

			<!-- MetisMenu CSS -->
			<link href="../../portal/<?php echo $system_name; ?>/vendor/metisMenu/metisMenu.min.css" rel="stylesheet">

			<!-- Custom CSS -->
			<link href="../../portal/<?php echo $system_name; ?>/dist/css/sb-admin-2.css?version=12" rel="stylesheet">

			<!-- Custom Fonts -->
			<link href="../../portal/<?php echo $system_name; ?>/vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

			<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.7.5/css/bootstrap-select.min.css" rel="stylesheet">

			<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
			<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
			<!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

			<script type="text/javascript" src="../../portal/<?php echo $system_name; ?>/js/ajaxsales.js?token=ertr65657yuttyuytu"></script>
			<script type="text/javascript" src="../../portal/emplacarrn/js/ajaxestablishment.js"></script>

			<script>
				function doaction() {
					listPayedProtocols();
					showBt();
				}

				function changeBox() {
					doaction();
				}

				function confdel(id_movement) {
					if (confirm("Confirmar exclusão do movimento de caixa?")) {
						window.location.href = "../cashier/deletemovement?id=" + id_movement;
					}
				}
			</script>

		</head>

		<body onload="doaction()">
			<!-- Modal -->
			<div class="modal fade" id="ExemploModalCentralizado" tabindex="-1" role="dialog" aria-labelledby="TituloModalCentralizado" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="TituloModalCentralizado">Fechamento de Caixa</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							Confirmar fechamento do caixa?
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-success" id="btn-confirm" data-dismiss="modal">Sim</button>
							<button type="button" class="btn btn-danger" data-dismiss="modal">Não</button>
						</div>
					</div>
				</div>
			</div>

			<div id="wrapper">

				<?php include 'nav.php'; ?>

				<div id="page-wrapper">
					<div class="row">
						<div class="col-lg-12">
							<h2 class="page-header">Movimento de caixa da <?= $establishment_name; ?></h2>
						</div>
						<!-- /.col-lg-12 -->
					</div>
					<!-- /.row -->
					<div class="row">
						<div class="col-lg-12" id="msg">
							<?php
							if ($n == 1) {
								echo '<div class="alert alert-success alert-dismissable">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								Movimento realizado com sucesso.
							</div>';
							}
							?>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<div class="panel panel-default">
								<div class="panel-heading">
									Selecione a data que deseja ver o movimento de caixa
									<div class="pull-right">
										<?php
										if (connect()) {
											$con = $_SESSION['con'];
											$query = "SELECT * FROM boxes_list WHERE establishment = '$establishment'";
											$result = mysqli_query($con, $query);
											$num_rows = mysqli_num_rows($result);
											if ($num_rows > 0) {
												if ($num_rows == 1) {
													$row = mysqli_fetch_array($result);
													$box = $row['id'];
													$query_ver = "SELECT * FROM boxes_users WHERE box = '$box' AND user = '$user_id'";
													$result_ver = mysqli_query($con, $query_ver);
													$num_rows_ver = mysqli_num_rows($result_ver);
													if ($num_rows_ver > 0) {
														echo '<input type="hidden" name="box" id="box" value="' . $box . '">';
													} else {
														echo '<input type="hidden" name="box" id="box" value="0">';
													}
												} else {
													$i = 0;
													$j = 0;
													while ($row = mysqli_fetch_array($result)) {
														$box = $row['id'];
														$query_ver = "SELECT * FROM boxes_users WHERE box = '$box' AND user = '$user_id'";
														$result_ver = mysqli_query($con, $query_ver);
														$num_rows_ver = mysqli_num_rows($result_ver);
														if ($num_rows_ver > 0) {
															$boxes[] = $row['id'];
															$boxes[] = $row['description'];
														}
													}
													if (count($boxes) == 0) {
														echo '<input type="hidden" name="box" id="box" value="0">';
													} elseif (count($boxes) == 2) {
														echo '<p class="lead">' . $boxes[1] . '</p>';
														echo '<input type="hidden" name="box" id="box" value="' . $boxes[0] . '">';
													} else {
														$qt = count($boxes);
														echo '<div class="form-group">
													<select class="form-control" name="box" id="box" onchange="changeBox()">';
														for ($i = 0; $i < $qt; $i += 2) {
															echo '<option value="' . $boxes[$i] . '">' . $boxes[$i + 1] . '</option>';
														}
														echo '<option value="T">Todos</option>';
														echo '</select></div>';
													}
												}
											} else {
												echo "Este estabelecimento não tem caixa.";
											}
										} else {
											echo "Erro ao acessar banco de dados.";
										}
										?>
									</div>

								</div>
								<div class="panel-body">
									<div class="row">
										<!-- /.col-lg-6 (nested) -->
										<div class="col-lg-4">
											<div class="form-group input-group">
												<input class="form-control hackdate" type="date" id="date" name="date" value="<?php echo $date; ?>" min="<?php echo "2018-12-26"; ?>" max="<?php echo date("Y-m-d"); ?>" onKeyDown="if(event.keyCode==13) listPayedProtocols();" required autofocus>
												<span class="input-group-btn">
													<button class="btn btn-default" type="button" onclick="doaction();"><i class="fa fa-search"></i>
													</button>
												</span>
											</div>
										</div>
										<div class="col-lg-4">
											<div id="btnew"></div>
										</div>
										<!-- /.col-lg-6 (nested) -->
									</div>
									<!-- /.row (nested) -->
									<div class="row">
										<div class="col-lg-12">
											<div id="result"></div>
										</div>
									</div>
									<!-- /.row (nested) -->
								</div>
								<!-- /.panel-body -->
							</div>
							<!-- /.panel -->
						</div>
						<!-- /.col-lg-12 -->
					</div>
					<!-- /.row -->
				</div>
				<!-- /#page-wrapper -->

			</div>
			<!-- /#wrapper -->

			<!-- jQuery -->
			<script src="../../portal/<?php echo $system_name; ?>/vendor/jquery/jquery.min.js"></script>

			<!-- Bootstrap Core JavaScript -->
			<script src="../../portal/<?php echo $system_name; ?>/vendor/bootstrap/js/bootstrap.min.js"></script>

			<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.7.5/js/bootstrap-select.min.js"></script>

			<!-- Metis Menu Plugin JavaScript -->
			<script src="../../portal/<?php echo $system_name; ?>/vendor/metisMenu/metisMenu.min.js"></script>

			<!-- Custom Theme JavaScript -->
			<script src="../../portal/<?php echo $system_name; ?>/dist/js/sb-admin-2.js"></script>

			<script>
				$(document).ready(function() {
					$("#btn-confirm").on("click", function() {
						var establishment = $('#opt_establishment');
						var date = $('#date');
						$.ajax({
							url: "setclosed?date=" + date.val() + "&establishment=" + establishment.val(),
							success: function(data) {
								$('#msg').html(data);
								showBt();
							}
						});
					});
				});
			</script>

		</body>

		</html>
<?php
		include 'contador.php';
	} else {
		header("Location: ../login");
	}
} else {
	header("Location: ../login");
}
?>