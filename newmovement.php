<?php
header('Content-Type: text/html; charset=utf8');
$system_name = "emplacarrn";
include 'contador.php';
include 'db.php';
include 'User.php';
session_id($_COOKIE[session_name()]);
session_set_cookie_params(3600);
session_start(); // ready to go!
date_default_timezone_set('America/Fortaleza');
if (isset($_SESSION['user'])) {
	$user = $_SESSION['user'];
	$user_id = $user->getId();
	$establishment = $user->getEstablishment();

	if (!connect())
		die("Erro na conexão com o banco de dados.");
	$con = $_SESSION['con'];
	$date = date("Y-m-d");
	$query = "SELECT closed FROM total_protocols_day WHERE establishment = '$establishment' AND date = '$date'";
	$result = mysqli_query($con, $query);
	$row = mysqli_fetch_assoc($result);
	$closed = $row['closed'];
	if (!$closed) {
		if ($user->getRole() == 1 || $user->getRole() == 3 || $user->getRole() == 5) {
?>
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

				<script type="text/javascript" src="../../portal/emplacarrn/js/ajaxestablishment.js"></script>

				<?php
				// Messages
				// New patient  return
				$e = $_GET['e'];
				$n = $_GET['n'];
				?>

			</head>

			<body>

				<div id="wrapper">

					<?php include 'nav.php'; ?>

					<div id="page-wrapper">
						<form role="form" action="save" method="POST">
							<div class="row">
								<div class="col-lg-12">
									<h2 class="page-header">Novo movimento de caixa</h2>
								</div>
								<!-- /.col-lg-12 -->
							</div>
							<!-- /.row -->
							<div class="row">
								<div class="col-lg-12">
									<?php
									if ($e == 1) {
										echo '<div class="alert alert-danger alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                Existe mais de um movimento com o mesmo identificador, procure o suporte com urgencia.
                            </div>';
									} elseif ($e == 2) {
										echo '<div class="alert alert-danger alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                Houve um erro, procure o suporte do sistema.
                            </div>';
									}
									?>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-12">
									<?php
									if ($n == 1) {
										echo '<div class="alert alert-success alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								Movimento de caixa realizado com sucesso.
                            </div>';
									}
									?>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-12">
									<div class="panel panel-default">
										<div class="panel-heading">
											Selecione o tipo de movimento, descriç&atilde;o, valor, fornecedor e plano de contas
											<div class="pull-right">
												<?php
												if (connect()) {
													$con = $_SESSION['con'];
													$establishment = $user->getEstablishment();
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
																echo '</select></div>';
															}
														}
													} else {
														echo "Este estabelecimento nao tem caixa.";
													}
												} else {
													echo "Erro ao acessar banco de dados.";
												}
												?>
											</div>
										</div>
										<div class="panel-body">
											<div class="row">
												<div class="col-lg-2">
													<label>Tipo de movimento</label>
													<div class="form-group">
														<label class="radio-inline">
															<input type="radio" name="in_out" value="0">Crédito
														</label>
														<label class="radio-inline">
															<input type="radio" name="in_out" value="1" checked>Débito
														</label>
													</div>
												</div>
												<div class="col-lg-6">
													<div class="form-group">
														<label>Descriç&atilde;o</label>
														<input class="form-control" type="text" placeholder="Descriç&atilde;o do movimento" name="description" maxlength="60" required autofocus>
													</div>
												</div>
												<div class="col-lg-2">
													<div class="form-group">
														<label>Valor (R$)</label>
														<input class="form-control" type="text" name="value" id="value" maxlength="10" required autofocus>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-lg-5">
													<div class="form-group">
														<label>Fornecedor</label>
														<?php
														if (connect()) {
															$sql = "SELECT * FROM providers ORDER BY name ASC";
															$con = $_SESSION["con"];
															$result = mysqli_query($con, $sql);
															$cont = mysqli_affected_rows($con);
															// Verifica se a consulta retornou linhas 
															if ($cont > 0) {
																// Atribui o código HTML para montar uma tabela 
																$return = "<select data-placeholder='Escolha um fornecedor' class='chosen-select form-control' name='provider' id='provider' required>";
																// Captura os dados da consulta e inseri na tabela HTML 
																while ($linha = mysqli_fetch_array($result)) {
																	$return .= "<option value='" . $linha['id'] . "'>" . $linha['name'] . "</option>";
																}
																$return .= "</select>";
																echo $return;
															} else {
																// Se a consulta nao retornar nenhum valor, exibi mensagem para o usuário 
																echo "<p>Nao há fornecedores cadastrados.</p>";
															}
														} else {
															echo "<p>Nao foi possível estabalecer conexao com o banco de dados!</p>";
														}
														?>
													</div>
												</div>
												<div class="col-lg-5">
													<div class="form-group">
														<label>Plano de contas</label>
														<?php
														if (connect()) {
															$sql = "SELECT * FROM chart_of_accounts WHERE in_out = '1' ORDER BY name ASC";
															$con = $_SESSION["con"];
															$result = mysqli_query($con, $sql);
															$cont = mysqli_affected_rows($con);
															// Verifica se a consulta retornou linhas 
															if ($cont > 0) {
																// Atribui o código HTML para montar uma tabela 
																$return = "<select data-placeholder='Escolha um plano de contas' class='chosen-select form-control' name='chart_of_accounts' id='chart_of_accounts' required>";
																// Captura os dados da consulta e inseri na tabela HTML 
																while ($linha = mysqli_fetch_array($result)) {
																	$return .= "<option value='" . $linha['id'] . "'>" . $linha['name'] . "</option>";
																}
																$return .= "</select>";
																echo $return;
															} else {
																// Se a consulta nao retornar nenhum valor, exibi mensagem para o usuário 
																echo "<p>Nao há planos de conta de débito cadastrados.</p>";
															}
														} else {
															echo "<p>Nao foi possível estabalecer conexao com o banco de dados!</p>";
														}
														?>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-lg-12">
													<label>&nbsp;</label>
													<p>
														<button type="submit" class="btn btn-success">Cadastrar</button>
														<button type="reset" class="btn btn-danger">Limpar</button>
													</p>
												</div>
											</div>
						</form>
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

				<!-- jQuery Masked Input -->
				<script type="text/javascript" src="../../portal/<?php echo $system_name; ?>/js/jquery.maskedinput.js"></script>

				<!-- jQuery MaskMoney -->
				<script type="text/javascript" src="../../portal/<?php echo $system_name; ?>/js/jquery.maskMoney.js"></script>

				<!-- Bootstrap Core JavaScript -->
				<script src="../../portal/<?php echo $system_name; ?>/vendor/bootstrap/js/bootstrap.min.js"></script>

				<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.7.5/js/bootstrap-select.min.js"></script>

				<!-- Metis Menu Plugin JavaScript -->
				<script src="../../portal/<?php echo $system_name; ?>/vendor/metisMenu/metisMenu.min.js"></script>

				<!-- Custom Theme JavaScript -->
				<script src="../../portal/<?php echo $system_name; ?>/dist/js/sb-admin-2.js"></script>

				<script>
					$(document).ready(function() {
						var $seuCampoCpf = $("#value");
						$seuCampoCpf.maskMoney({
							allowNegative: false,
							thousands: '.',
							decimal: ',',
							affixesStay: false
						});
					});
				</script>
			</body>

			</html>
<?php
		} else {
			header("Location: ../home");
		}
		closedb();
	} else {
		header("Location: ../cashier/cashmovement?date=" . $date);
	}
} else {
	header("Location: ../login");
}
?>