<?php
header('Content-Type: text/html; charset=utf8');
$system_name = "emplacarrn";
include 'contador.php';
include 'db.php';
include 'User.php';
include 'fun.php';
session_id($_COOKIE[session_name()]);
session_set_cookie_params(3600);
session_start(); // ready to go!
date_default_timezone_set('America/Fortaleza');
if (isset($_SESSION['user'])) {
	$user = $_SESSION['user'];
	$user_id = $user->getId();
	$establishment = $user->getEstablishment();
	if ($user->getRole() == 1 || $user->getRole() == 3  || $user->getRole() == 5) {
		// Gerando o token aleatório
		$token = randString(32);
		// Mudando token caso exista no banco
		while (existToken($token)) {
			$token = randString(32);
		}
		// Verificar se o token não já existe
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

			<!-- Chosen CSS -->
			<link href="../../portal/<?php echo $system_name; ?>/vendor/chosen/css/chosen.min.css" rel="stylesheet" type="text/css">

			<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.7.5/css/bootstrap-select.min.css" rel="stylesheet">

			<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
			<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
			<!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

			<script type="text/javascript" src="../../portal/emplacarrn/js/ajaxsales.js?token=l54jjo34"></script>
			<script type="text/javascript" src="../../portal/emplacarrn/js/ajaxestablishment.js"></script>

			<?php
			$pf = $_GET['pf'];
			$protocol = $_GET['protocol'];
			$data = $_GET['date'];
			// Fazer consulta para esse protocolo
			if (!connect())
				die('Não foi possível estabalecer conexão com o banco de dados!');

			$con = $_SESSION["con"];
			$date = date("Y-m-d");
			$query = "SELECT closed FROM total_protocols_day WHERE establishment = '$establishment' AND date = '$date'";
			$result = mysqli_query($con, $query);
			$row = mysqli_fetch_assoc($result);
			$closed = $row['closed'];
			if (!$closed) {
				$query = "SELECT * FROM sales WHERE id = '$protocol' AND establishment = '$establishment'";
				$res = mysqli_query($con, $query);
				$cont = mysqli_affected_rows($con);
				// Verifica se a consulta retornou linhas
				if ($cont > 0) {
					while ($row = mysqli_fetch_array($res)) {
						$customer = $row['customer'];
					}
					$exist = true;
				} else {
					// Se a consulta não retornar nenhum valor, exibi mensagem para o usuário
					$exist = false;
				}


				$s = $_GET['s'];
				$e = $_GET['e']; ?>

				<script>
					function changeValue(campo) {
						var receb = document.getElementById("receb");
						var receb_input = document.getElementById("receb_input");
						var receb_input_value = receb_input.value;
						var fracionado = document.getElementById("fracionado");
						var qt = document.getElementById("qt_chb").value;

						// Aqui começa a questão do pagamento fracionado
						if (campo.checked) {
							fracionado.disabled = false;
						}
						// Verificar se todos estão deschecados
						var qt_unchecked = 0;
						var j;
						for (j = 1; j <= qt; j++) {
							var cp = document.getElementById("checkbox" + j);
							if (cp.checked == false) {
								qt_unchecked += 1;
							}
						}
						if (qt_unchecked == qt) {
							fracionado.disabled = true;
							fracionado.value = "";
						}

						// Verificar se todos estão checados
						var qt_checked = 0;
						var i;
						for (i = 1; i <= qt; i++) {
							var cp = document.getElementById("checkbox" + i);
							if (cp.checked) {
								qt_checked += 1;
							}
						}
						var all = document.getElementById("checkboxall");
						if (qt_checked == qt) {
							all.checked = true;
						} else {
							all.checked = false;
						}
						// Fazer a mudança de fato
						var value_product = campo.value;
						if (campo.checked) {
							var soma = parseFloat(receb_input_value) + parseFloat(value_product);
						} else {
							var soma = parseFloat(receb_input_value) - parseFloat(value_product);
						}
						receb.innerHTML = '<center><b>' + soma.toFixed(2).replace('.', ',') + '</b></center>';
						receb_input.value = soma;
					}

					function checkAll() {
						var fracionado = document.getElementById("fracionado");
						var all = document.getElementById("checkboxall");
						var qt = document.getElementById("qt_chb").value;
						var receb = document.getElementById("receb");
						var receb_input = document.getElementById("receb_input");
						var soma = parseFloat(receb_input.value);
						var i;
						for (i = 1; i <= qt; i++) {
							var cp = document.getElementById("checkbox" + i);
							if (all.checked == true) {
								if (cp.checked == false) {
									if (cp.disabled == false) {
										fracionado.disabled = false;
										cp.checked = true;
										var value_products = cp.value;
										soma = soma + parseFloat(value_products);
									}
								}
							} else {
								if (cp.checked == true) {
									fracionado.disabled = true;
									fracionado.value = "";
									receb_input.value = 0;
									cp.checked = false;
									soma = 0.0;
									//var value_product = cp.value;
									//soma = soma - parseFloat(value_product);
								}
							}
						}
						receb.innerHTML = '<center><b>' + soma.toFixed(2).replace('.', ',') + '</b></center>';
						receb_input.value = soma;
					}

					<?php
					if ($pf != 1) {
					?>

						function init() {
							var fracionado = document.getElementById("fracionado");
							fracionado.disabled = true;
							var all = document.getElementById("checkboxall");
							all.checked = false;
							var qt = document.getElementById("qt_chb").value;
							var ct = 0;
							var i;
							for (i = 1; i <= qt; i++) {
								var cp = document.getElementById("checkbox" + i);
								cp.checked = false;
								if (cp.disabled) {
									ct++;
								}
							}
							if (ct == qt) {
								all.disabled = true;
							}
							var receb_input = document.getElementById("receb_input");
							receb_input.value = "0,00";
						}
					<?php
					} ?>

					function changeToValue() {
						var method = document.getElementById("method");
						if (method.value != 0) {
							var troco = document.getElementById("troco");
							if (method.value == 1) {
								troco.innerHTML = "Troco (R$): 0,00";
							} else {
								troco.innerHTML = "";
							}
							var valor_recebido = document.getElementById('valor_recebido');
							valor_recebido.focus();
						}
					}

					function sendForm() {
						var valor_recebido = document.getElementById("valor_recebido");
						var method = document.getElementById("method");
						var total = document.getElementById("total");
						if (method.value == 0) {
							method.focus();
						} else if (valor_recebido.value != "" && valor_recebido.value != "0,00") {
							// Verificar a forma de pagamento para definir se o valor está "correto"
							if (method.value != 1) {
								var val_rec = (valor_recebido.value).replace(',', '.');
								var val_tot = (total.value).replace(',', '.');
								if (parseFloat(val_rec) > parseFloat(val_tot)) {
									alert(parseFloat(val_tot));
									alert("Você não pode cobrar um valor maior do que o preço do produto.");
								} else {
									var frm = document.getElementById("frm");
									switch (parseInt(method.value)) {
										case 2:
											if (confirm("Tem certeza que a forma de pagamento é DÉBITO?")) {
												frm.submit();
											}
											break;
										case 3:
											if (confirm("Tem certeza que a forma de pagamento é CRÉDITO?")) {
												frm.submit();
											}
											break;
										case 4:
											if (confirm("Tem certeza que a forma de pagamento é CARTEIRA?")) {
												frm.submit();
											}
											break;
										case 5:
											if (confirm("Tem certeza que a forma de pagamento é BOLETO?")) {
												frm.submit();
											}
											break;
										case 6:
											if (confirm("Tem certeza que a forma de pagamento é CHEQUE?")) {
												frm.submit();
											}
											break;
										case 7:
											if (confirm("Tem certeza que a forma de pagamento é TRANSFERÊNCIA BANCÁRIA?")) {
												frm.submit();
											}
											break;
										case 8:
											if (confirm("Tem certeza que a forma de pagamento é BOLETO SITE?")) {
												frm.submit();
											}
											break;
										case 9:
											if (confirm("Tem certeza que a forma de pagamento é PIX?")) {
												frm.submit();
											}
											break;
										default:
											alert("Defina a forma de pagamento.");
									}
								}
							} else {
								if (confirm("Tem certeza que a forma de pagamento é DINHEIRO?")) {
									var frm = document.getElementById("frm");
									frm.submit();
								}
							}
						} else {
							valor_recebido.focus();
						}
					}

					function verify(frm) {
						var receb_input_value = document.getElementById("receb_input").value;
						if (parseFloat(receb_input_value) > 0) {
							var method = document.getElementById("method").value;
							switch (parseInt(method)) {
								case 0:
									alert("Defina a forma de pagamento.");
									break;
								case 1:
									if (confirm("Tem certeza que a forma de pagamento é DINHEIRO?")) {
										frm.submit();
									}
									break;
								case 2:
									if (confirm("Tem certeza que a forma de pagamento é DÉBITO?")) {
										frm.submit();
									}
									break;
								case 3:
									if (confirm("Tem certeza que a forma de pagamento é CRÉDITO?")) {
										frm.submit();
									}
									break;
								case 4:
									if (confirm("Tem certeza que a forma de pagamento é CARTEIRA?")) {
										frm.submit();
									}
									break;
								case 5:
									if (confirm("Tem certeza que a forma de pagamento é BOLETO?")) {
										frm.submit();
									}
									break;
								case 6:
									if (confirm("Tem certeza que a forma de pagamento é CHEQUE?")) {
										frm.submit();
									}
									break;
								case 7:
									if (confirm("Tem certeza que a forma de pagamento é TRANSFERÊNCIA BANCÁRIA?")) {
										frm.submit();
									}
									break;
								case 8:
									if (confirm("Tem certeza que a forma de pagamento é BOLETO SITE?")) {
										frm.submit();
									}
									break;
								case 9:
									if (confirm("Tem certeza que a forma de pagamento é PIX?")) {
										frm.submit();
									}
									break;
								default:
									alert("Defina a forma de pagamento.");
							}
						}
					}

					function calcTroco() {
						var valor_recebido = document.getElementById("valor_recebido");
						var total = document.getElementById("total");
						var val_vr = (valor_recebido.value).replace(',', '.');
						var val_total = (total.value).replace(',', '.');
						var troco = document.getElementById("troco");
						var method = document.getElementById("method");
						if (method.value == 1) {
							if (parseFloat(val_vr) > parseFloat(val_total)) {
								var val_troco = (val_vr - val_total).toFixed(2);
								var val_troco_st = val_troco.replace('.', ',')
								troco.innerHTML = "Troco (R$): " + val_troco_st;
							} else {
								troco.innerHTML = "Troco (R$): 0,00";
							}
						} else {
							troco.innerHTML = "";
						}
					}

					function confop(protocol) {
						if (confirm("Confirmar exclusão do pagamento do protocolo " + protocol + "?")) {
							window.location.href = "../cashier/deletepayment?id=" + protocol;
						}
					}
				</script>

		</head>

		<body onload="init()">

			<div id="wrapper">

				<?php include 'nav.php'; ?>

				<div id="page-wrapper">
					<?php
					if ($pf != 1) {
						echo '<form action="dopayment" method="POST" onsubmit="verify(this); return false;">';
					} else {
						echo '<form id="frm" action="dopayment" method="POST" onsubmit="return false;">';
					} ?>
					<div class="row">
						<div class="col-lg-12">
							<h2 class="page-header">Emplacar RN</h2>
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
											while ($row = mysqli_fetch_array($result)) {
												$box = $row['id'];
											}
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
										echo "Este estabelecimento não tem caixa.";
									}
								} else {
									echo "Erro ao acessar banco de dados.";
								} ?>
							</div>
						</div>
						<!-- /.col-lg-12 -->
					</div>
					<div class="row">
						<div class="col-lg-12">
							<?php
							if ($s == 1) {
								echo '<div class="alert alert-success alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                Pagamento recebido com sucesso.
                            </div>';
							} ?>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<?php
							if ($e == 1) {
								echo '<div class="alert alert-danger alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                O valor do pagamento fracionado não pode ser maior do que o valor dos produtos que foram selecionados.
                            </div>';
							} elseif ($e == 2) {
								echo '<div class="alert alert-danger alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                Houve algum erro, procure o suporte.
                            </div>';
							} ?>
						</div>
					</div>
					<!-- /.row -->
					<div class="row">
						<div class="col-lg-12">
							<div class="panel panel-default">
								<div class="panel-heading">
									Protocolo: <a href="https://badusoft.com/emplacarrn/sales/show?protocol=<?=$protocol;?>"><?php echo $protocol; ?></a>
									<div class="pull-right">
										<b>
											<div id="troco"></div>
										</b>
									</div>
								</div>
								<div class="panel-body">
									<?php
									if ($exist) {
									?>
										<div class="row">
											<div class="col-lg-12">
												<div class="row">
													<div class="col-lg-4">
														<div class="form-group">
															<label>Cliente</label>
															<?php
															if ($customer != 0) {
																if (connect()) {
																	$sql = "SELECT * FROM customers WHERE id = '$customer'";
																	$con = $_SESSION["con"];
																	$result = mysqli_query($con, $sql);
																	$cont = mysqli_affected_rows($con);
																	// Verifica se a consulta retornou linhas
																	if ($cont > 0) {
																		while ($linha = mysqli_fetch_array($result)) {
																			echo "<p><a href='https://badusoft.com/emplacarrn/customers/show?id=" . $linha['id'] . "'>" . $linha['name'] . "</a></p>";
																		}
																	} else {
																		// Se a consulta não retornar nenhum valor, exibi mensagem para o usuário
																		echo "<p>Não há clientes cadastrados.</p>";
																	}
																} else {
																	echo "<p>Não foi possível estabalecer conexão com o banco de dados!</p>";
																}
															} else {
																echo "<p>CLIENTE NÃO CADASTRADO</p>";
															} ?>
														</div>
													</div>
													<input type="hidden" name="date" id="date" value="<?php echo $data; ?>">
													<input type="hidden" name="pf" value="<?php echo $pf; ?>">
													<input type="hidden" name="protocol" id="protocol" value="<?php echo $protocol; ?>">
													<input type="hidden" name="token" value="<?php echo $token; ?>">
													<?php
													// Pegando valor total
													$query_soma = "SELECT * FROM sale_products WHERE sale = '$protocol'";
													$result_soma = mysqli_query($con, $query_soma);
													$num_tot = mysqli_num_rows($result_soma);
													$tot_protocol = 0;
													if ($num_tot > 0) {
														while ($row_tot = mysqli_fetch_array($result_soma)) {
															$quantity_dois = $row_tot['quantity'];
															$value_dois = $row_tot['value'];
															$tot_prod = $quantity_dois * $value_dois;
															$tot_protocol += $tot_prod;
														}
													}


													$query_payed = "SELECT * FROM payments WHERE protocol = '$protocol'";
													$result_payed = mysqli_query($con, $query_payed);
													$num_payed = mysqli_num_rows($result_payed);
													$tot_payed = 0;
													if ($num_payed > 0) {
														while ($row_payed = mysqli_fetch_array($result_payed)) {
															$value_payed = $row_payed['value'];
															$tot_payed += $value_payed;
														}
													}

													$query_payed_wo_carteira = "SELECT * FROM payments WHERE protocol = '$protocol' AND payment_method != '4'";
													$result_payed_wo_carteira = mysqli_query($con, $query_payed_wo_carteira);
													$num_payed_wo_carteira = mysqli_num_rows($result_payed_wo_carteira);

													if ($tot_protocol != $tot_payed) {
													?>
														<div class="col-lg-3">
															<div class="form-group">
																<label>Forma de pagamento</label>
																<?php
																if (connect()) {
																	$sql = "SELECT * FROM payment_methods ORDER BY id ASC";
																	$con = $_SESSION["con"];
																	$result = mysqli_query($con, $sql);
																	$cont = mysqli_affected_rows($con);
																	// Verifica se a consulta retornou linhas
																	if ($cont > 0) {
																		// Atribui o código HTML para montar uma tabela
																		$return = "<select class='form-control' name='method' id='method' onKeyDown='if(event.keyCode==13) changeToValue();' onchange='calcTroco();' required autofocus>";
																		$return .= "<option disabled selected value='0'>Escolha a forma de pagamento</option>";
																		// Captura os dados da consulta e inseri na tabela HTML
																		while ($linha = mysqli_fetch_array($result)) {
																			if ($pf == 1) {
																				if (($linha['id'] >= 1 && $linha['id'] <= 3) || ($linha['id']  >= 7 && $linha['id']  <= 9)) {
																					$return .= "<option value='" . $linha['id'] . "'>" . $linha['method'] . "</option>";
																				}
																			} else {
																				$return .= "<option value='" . $linha['id'] . "'>" . $linha['method'] . "</option>";
																			}
																		}
																		$return .= "</select>";
																		echo $return;
																	} else {
																		// Se a consulta não retornar nenhum valor, exibi mensagem para o usuário
																		echo "<p>Não há formas de pagamento cadastrados.</p>";
																	}
																} else {
																	echo "<p>Não foi possível estabalecer conexão com o banco de dados!</p>";
																} ?>

															</div>
														</div>
														<?php
														if ($pf != 1) {
														?>
															<div class="col-lg-5">
																<label>&nbsp;</label>
																<p>
																	<button type="submit" class="btn btn-success">Confirmar pagamento</button>
																	<?php
																	if (isset($_GET['date']) && $_GET['date'] != "") {
																		echo '<a href="salesopened?date=' . $data . '"><button type="button" class="btn btn-warning">Recebimentos em aberto</button></a>';
																	} else {
																		echo '<a href="salesopened"><button type="button" class="btn btn-warning">Recebimentos em aberto</button></a>';
																	}
																	if ($tot_payed > 0) {
																		if ($user->getRole() == 1) {
																			echo ' <button type="button" class="btn btn-danger" onclick="confop(' . $protocol . ')">Excluir pagamento</button>';
																		}
																	} ?>
																</p>
															</div>
														<?php
														} else {
														?>
															<div class="col-lg-2">
																<label>&nbsp;</label>
																<p><input class="form-control" type="text" placeholder="0,00" id="valor_recebido" name="valor_recebido" onKeyDown='if(event.keyCode==13) sendForm()' onKeyUp='calcTroco()' style="max-width:160px; text-align:center;"></p>
															</div>
															<div class="col-lg-3">
																<label>&nbsp;</label>
																<p>
																	<button type="button" class="btn btn-success" onclick="sendForm()">Confirmar pagamento</button>
																	<?php
																	if ($tot_payed > 0) {
																		if ($user->getRole() == 1) {
																			echo ' <button type="button" class="btn btn-danger" onclick="confop(' . $protocol . ')">Excluir pagamento</button>';
																		}
																	} ?>
																</p>
															</div>
														<?php
														} ?>
													<?php
													} else {
													?>
														<div class="col-lg-3">
															<div class="alert alert-success">
																<center>O pedido foi completamente pago.</center>
															</div>
														</div>
														<div class="col-lg-5" style="margin-top:8px;">
															<p>
																<?php
																if ($pf != 1) {
																	if (isset($_GET['date']) && $_GET['date'] != "") {
																		echo '<a href="salesopened?date=' . $data . '"><button type="button" class="btn btn-warning">Recebimentos em aberto</button></a>';
																	} else {
																		echo '<a href="salesopened"><button type="button" class="btn btn-warning">Recebimentos em aberto</button></a>';
																	}
																	echo ' <a href="../sales/print40?protocol=' . $protocol . '"><button type="button" class="btn btn-primary">Imprimir</button></a>';
																	if ($user->getRole() == 1) {
																		echo ' <button type="button" class="btn btn-danger" onclick="confop(' . $protocol . ')">Excluir pagamento</button>';
																	}
																} else {
																	echo '<a href="../sales/new?pf=1"><button type="button" class="btn btn-success">Novo pedido</button></a>';
																	echo ' <a href="../sales/print40?protocol=' . $protocol . '"><button type="button" class="btn btn-primary">Imprimir</button></a>';
																	if ($user->getRole() == 1) {
																		echo ' <button type="button" class="btn btn-danger" onclick="confop(' . $protocol . ')">Excluir pagamento</button>';
																	}
																} ?>
															</p>
														</div>
													<?php
													} ?>
												</div>
												<?php
												// Não está todo pago
												if ($tot_protocol != $tot_payed) {
													// Aqui começava antes
													$query_products = "SELECT * FROM sale_products WHERE sale = '$protocol' ORDER BY id DESC";
													$result_products = mysqli_query($con, $query_products);
													$num_rows_products = mysqli_num_rows($result_products);
													if ($num_rows_products > 0) {
														echo '<div class="row">
												<div class="col-lg-12">
													<div class="panel panel-default">
														<div class="panel-heading">
															Pagamento em aberto
														</div>
														<!-- /.panel-heading -->
														<div class="panel-body">
															<div class="table-responsive">
																<table class="table">
																	<thead>
																		<tr>';
														if ($pf != 1) {
															echo '<th><input type="checkbox" id="checkboxall" name="checkboxall" value="all" onchange="checkAll(this);"></th>';
														}
														echo	'<th>#</th>
																			<th>Produto</th>
																			<th><center>Placa</center></th>
																			<th><center>Quantidade</center></th>
																			<th><center>Valor unitário (R$)</center></th>
																			<th><center>Subtotal (R$)</center></th>
																		</tr>
																	</thead>
																	<tbody>';
														$i = 0;
														$total = 0;
														$pago = 0;
														$qt_chb = 0;
														$pmup = false;
														while ($row_products = mysqli_fetch_array($result_products)) {
															$product_id = $row_products['product'];
															$payed_value_plate = $row_products['value'];
															$payed_quantity_plate = $row_products['quantity'];
															$tot_plate = $payed_quantity_plate * $payed_value_plate;
															$query_product_name = "SELECT * FROM products WHERE id = '$product_id'";
															$result_product_name = mysqli_query($con, $query_product_name);
															$num_rows_product_name = mysqli_num_rows($result_product_name);
															if ($num_rows_product_name > 0) {
																while ($row_product_name = mysqli_fetch_array($result_product_name)) {
																	$product = $row_product_name['name'];
																}
															} else {
																$product = "Erro, produto não cadastrado!";
															}
															$id_plate = $row_products['id'];

															// Verificar os que foram pagos, para desativar
															$query_pagos = "SELECT * FROM payments WHERE id_sale_product = '$id_plate'";
															$result_pagos = mysqli_query($con, $query_pagos);
															$num_rows_pagos = mysqli_num_rows($result_pagos);
															$tot_payed_plate = 0;
															if ($num_rows_pagos > 0) {
																while ($row_payed_pagos = mysqli_fetch_array($result_pagos)) {
																	$produto_pago = $row_payed_pagos['value'];
																	$tot_payed_plate += $produto_pago;
																}
															}
															$plate = $row_products['plate'];
															$quantity = $row_products['quantity'];
															$value = number_format($row_products['value'], 2, ",", ".");
															$subtotal = $tot_plate - $tot_payed_plate;
															if ($tot_plate == $tot_payed_plate) {
																$pmup = true;
																$pago += $subtotal;
															} else {
																$qt_chb++;
																$i++;
																if ($tot_payed_plate == 0) {
																	echo	'<tr>';
																	if ($pf != 1) {
																		echo '<td><input type="checkbox" id="checkbox' . $i . '" name="checkbox' . $i . '" value="' . number_format($subtotal, 2, ",", ".") . '" onchange="changeValue(this);"><input type="hidden" name="checkbox' . $i . 'v" value="' . $id_plate . '"></td>';
																	}
																	echo	'<td>' . $i . '</td>
																						<td>' . $product . '</td>';
																	if ($product_id == 1 or $product_id == 2) {
																		echo '<td><center>' . $plate . '</center></td>';
																	} else {
																		echo '<td><center>-</center></td>';
																	}
																	echo	'<td><center>' . $quantity . '</center></td>
																						<td><center>' . $value . '</center></td>
																						<td><center>' . number_format($tot_plate - $tot_payed_plate, 2, ",", ".") . '</center></td>
																					</tr>';
																} else {
																	echo	'<tr>';
																	if ($pf != 1) {
																		echo '<td><input type="checkbox" id="checkbox' . $i . '" name="checkbox' . $i . '" value="' . number_format($subtotal, 2, ",", ".") . '" onchange="changeValue(this);"><input type="hidden" name="checkbox' . $i . 'v" value="' . $id_plate . '"></td>';
																	}
																	echo '<td>' . $i . '</td>
																						<td>' . $product . '</td>';
																	if ($product_id == 1 or $product_id == 2) {
																		echo '<td><center>' . $plate . '</center></td>';
																	} else {
																		echo '<td><center>-</center></td>';
																	}
																	echo '<td><center>-</center></td>
																						<td><center>-</center></td>
																						<td><center>' . number_format($tot_plate - $tot_payed_plate, 2, ",", ".") . '</center></td>
																					</tr>';
																}
															}
															$total += $subtotal;
														}
														echo	'<tr>';
														if ($pf != 1) {
															echo '<td>&nbsp;</td>';
														}
														echo  '<td>&nbsp;</td>
																			<td>&nbsp;</td>
																			<td>&nbsp;</td>
																			<td>&nbsp;</td>
																			<td><center><b>Total:</b></center></td>
																			<td><center><b>' . number_format($total - $pago, 2, ",", ".") . '</b></center></td>
																		</tr>';
														$recebimento = 0;
														echo "<input type='hidden' id='qt_chb' name='qt_chb' value='" . $qt_chb . "'>";
														echo "<input type='hidden' id='receb_input' name='receb_input' value='" . $recebimento . "'>";
														echo "<input type='hidden' id='total' name='total' value='" . ($total - $pago) . "'>";

														if ($pago != $total) {
															if ($pf != 1) {
																echo	'<tr>
																					<td>&nbsp;</td>
																					<td>&nbsp;</td>
																					<td>&nbsp;</td>
																					<td>&nbsp;</td>
																					<td>&nbsp;</td>
																					<td><center><b>Pagamento fracionado:</b></center></td>
																					<td><div id="parcial"><center><input class="form-control" type="text" placeholder="0,00" id="fracionado" name="fracionado" style="max-width:80px; text-align:center;"></center></div></td>
																				</tr>';
																echo	'<tr>
																					<td>&nbsp;</td>
																					<td>&nbsp;</td>
																					<td>&nbsp;</td>
																					<td>&nbsp;</td>
																					<td>&nbsp;</td>
																					<td><center><b>Pagamento total:</b></center></td>
																					<td><div id="receb"><center><b>' . number_format($recebimento, 2, ",", ".") . '</b></center></div></td>
																				</tr>';
															}
														}
														echo '</tbody>
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
														echo "Ainda não foram adicionados produtos a esse pedido!";
													}
												}
												// se pelo menos 1 pagou
												if ($num_payed > 0) {
												?>
													<div class="row">
														<div class="col-lg-12">
															<div class="panel panel-default">
																<div class="panel-heading">
																	Histórico de pagamentos
																</div>
																<!-- /.panel-heading -->
																<div class="panel-body">
																	<div class="table-responsive">
																		<table class="table">
																			<thead>
																				<tr>
																					<th>#</th>
																					<th>
																						<center>Produto</center>
																					</th>
																					<th>
																						<center>Placa</center>
																					</th>
																					<th>
																						<center>Valor Produto (R$)</center>
																					</th>
																					<th>
																						<center>Valor Pago (R$)</center>
																					</th>
																					<th>
																						<center>Pagamento</center>
																					</th>
																					<th>Data/Hora</th>
																				</tr>
																			</thead>
																			<tbody>

																				<?php
																				//Pegar placas do protocolo e depois buscá-las no pagamento
																				$counter = 1;
																				$total_pagos = 0;
																				$query_pega_pagos = "SELECT * FROM payments WHERE protocol = '$protocol'";
																				$result_pega_pagos = mysqli_query($con, $query_pega_pagos);
																				while ($row_pega_pagos = mysqli_fetch_array($result_pega_pagos)) {
																					$data_pag = $row_pega_pagos['date'];
																					$time_pag = $row_pega_pagos['time'];
																					$method_pag = $row_pega_pagos['payment_method'];
																					$id_sale_prod = $row_pega_pagos['id_sale_product'];
																					$payed_value = $row_pega_pagos['value'];

																					// Pegando nome do pagamento
																					$query_pv = "SELECT method AS name FROM payment_methods WHERE id = '$method_pag'";
																					$result_pv = mysqli_query($con, $query_pv);
																					$row_pv = mysqli_fetch_assoc($result_pv);
																					$nome_pagamento = $row_pv['name'];

																					// Pegando placa, quantidade, valor unitário
																					$query_ppqvu = "SELECT * FROM sale_products WHERE id = '$id_sale_prod'";
																					$result_ppqvu = mysqli_query($con, $query_ppqvu);
																					while ($row_ppqvu = mysqli_fetch_array($result_ppqvu)) {
																						$placa = $row_ppqvu['plate'];
																						$quantidade = $row_ppqvu['quantity'];
																						$valor_unitario = $row_ppqvu['value'];
																						$valor_tot = $quantidade * $valor_unitario;
																						$tipo_do_produto = $row_ppqvu['product'];
																					}

																					// Pegando nome do produto
																					$query_tp = "SELECT name AS name_prod FROM products WHERE id = '$tipo_do_produto'";
																					$result_tp = mysqli_query($con, $query_tp);
																					$row_tp = mysqli_fetch_assoc($result_tp);
																					$nome_produto = $row_tp['name_prod'];


																					echo	'<tr>
																			<td>' . $counter . '</td>
																			<td><center>' . $nome_produto . '</center></td>';
																					if (strcmp($placa, "") == 0) {
																						echo '<td><center>-</center></td>';
																					} else {
																						echo '<td><center>' . $placa . '</center></td>';
																					}
																					echo '<td><center>' . number_format($valor_tot, 2, ",", ".") . '</center></td>
																			<td><center>' . number_format($payed_value, 2, ",", ".") . '</center></td>
																			<td><center>' . $nome_pagamento . '</center></td>
																			<td>' . mudaData($data_pag) . ' ' . $time_pag . '</td>
																		</tr>';
																					$counter++;
																					$total_pagos += $payed_value;
																				}
																				// Pegando o total em dinheiro
																				$query_gsm = "SELECT value FROM show_payed_sales WHERE protocol = '$protocol' AND payment_method = 'Dinheiro'";
																				$result_gsm = mysqli_query($con, $query_gsm);
																				while ($row_gsm = mysqli_fetch_array($result_gsm)) {
																					$payment_method_gsm = 'Dinheiro';
																					$value_gsm = $row_gsm['value'];
																					if ($value_gsm > 0) {
																						echo	'<tr>
																			<td>&nbsp;</td>
																			<td>&nbsp;</td>
																			<td>&nbsp;</td>
																			<td><center><b>Total ' . $payment_method_gsm . ':</b></center></td>
																			<td><center><b>' . number_format($value_gsm, 2, ",", ".") . '</b></center></td>
																			<td>&nbsp;</td>
																			<td>&nbsp;</td>
																			</tr>';
																					}
																				}
																				// Pegando o total em débito
																				$query_gsm = "SELECT value FROM show_payed_sales WHERE protocol = '$protocol' AND payment_method = 'Débito'";
																				$result_gsm = mysqli_query($con, $query_gsm);
																				while ($row_gsm = mysqli_fetch_array($result_gsm)) {
																					$payment_method_gsm = 'Débito';
																					$value_gsm = $row_gsm['value'];
																					if ($value_gsm > 0) {
																						echo	'<tr>
																			<td>&nbsp;</td>
																			<td>&nbsp;</td>
																			<td>&nbsp;</td>
																			<td><center><b>Total ' . $payment_method_gsm . ':</b></center></td>
																			<td><center>' . number_format($value_gsm, 2, ",", ".") . '</center></td>
																			<td>&nbsp;</td>
																			<td>&nbsp;</td>
																			</tr>';
																					}
																				}
																				// Pegando o total em crédito
																				$query_gsm = "SELECT value FROM show_payed_sales WHERE protocol = '$protocol' AND payment_method = 'Crédito'";
																				$result_gsm = mysqli_query($con, $query_gsm);
																				while ($row_gsm = mysqli_fetch_array($result_gsm)) {
																					$payment_method_gsm = 'Crédito';
																					$value_gsm = $row_gsm['value'];
																					if ($value_gsm > 0) {
																						echo	'<tr>
																			<td>&nbsp;</td>
																			<td>&nbsp;</td>
																			<td>&nbsp;</td>
																			<td><center><b>Total ' . $payment_method_gsm . ':</b></center></td>
																			<td><center>' . number_format($value_gsm, 2, ",", ".") . '</center></td>
																			<td>&nbsp;</td>
																			<td>&nbsp;</td>
																			</tr>';
																					}
																				}
																				// Pegando o total em carteira
																				$query_gsm = "SELECT value FROM show_payed_sales WHERE protocol = '$protocol' AND payment_method = 'Carteira'";
																				$result_gsm = mysqli_query($con, $query_gsm);
																				while ($row_gsm = mysqli_fetch_array($result_gsm)) {
																					$payment_method_gsm = 'Carteira';
																					$value_gsm = $row_gsm['value'];
																					if ($value_gsm > 0) {
																						echo	'<tr>
																			<td>&nbsp;</td>
																			<td>&nbsp;</td>
																			<td>&nbsp;</td>
																			<td><center><b>Total ' . $payment_method_gsm . ':</b></center></td>
																			<td><center>' . number_format($value_gsm, 2, ",", ".") . '</center></td>
																			<td>&nbsp;</td>
																			<td>&nbsp;</td>
																			</tr>';
																					}
																				}
																				// Pegando o total em Boleto
																				$query_gsm = "SELECT value FROM show_payed_sales WHERE protocol = '$protocol' AND payment_method = 'Boleto'";
																				$result_gsm = mysqli_query($con, $query_gsm);
																				while ($row_gsm = mysqli_fetch_array($result_gsm)) {
																					$payment_method_gsm = 'Boleto';
																					$value_gsm = $row_gsm['value'];
																					if ($value_gsm > 0) {
																						echo	'<tr>
																			<td>&nbsp;</td>
																			<td>&nbsp;</td>
																			<td>&nbsp;</td>
																			<td><center><b>Total ' . $payment_method_gsm . ':</b></center></td>
																			<td><center>' . number_format($value_gsm, 2, ",", ".") . '</center></td>
																			<td>&nbsp;</td>
																			<td>&nbsp;</td>
																			</tr>';
																					}
																				}
																				// Pegando o total em Cheque
																				$query_gsm = "SELECT value FROM show_payed_sales WHERE protocol = '$protocol' AND payment_method = 'Cheque'";
																				$result_gsm = mysqli_query($con, $query_gsm);
																				while ($row_gsm = mysqli_fetch_array($result_gsm)) {
																					$payment_method_gsm = 'Cheque';
																					$value_gsm = $row_gsm['value'];
																					if ($value_gsm > 0) {
																						echo	'<tr>
																			<td>&nbsp;</td>
																			<td>&nbsp;</td>
																			<td>&nbsp;</td>
																			<td><center><b>Total ' . $payment_method_gsm . ':</b></center></td>
																			<td><center>' . number_format($value_gsm, 2, ",", ".") . '</center></td>
																			<td>&nbsp;</td>
																			<td>&nbsp;</td>
																			</tr>';
																					}
																				}
																				// Pegando o total em Transferência Bancária
																				$query_gsm = "SELECT value FROM show_payed_sales WHERE protocol = '$protocol' AND payment_method = 'Transferência Bancária'";
																				$result_gsm = mysqli_query($con, $query_gsm);
																				while ($row_gsm = mysqli_fetch_array($result_gsm)) {
																					$payment_method_gsm = 'Transferência Bancária';
																					$value_gsm = $row_gsm['value'];
																					if ($value_gsm > 0) {
																						echo	'<tr>
																			<td>&nbsp;</td>
																			<td>&nbsp;</td>
																			<td>&nbsp;</td>
																			<td><center><b>Total ' . $payment_method_gsm . ':</b></center></td>
																			<td><center>' . number_format($value_gsm, 2, ",", ".") . '</center></td>
																			<td>&nbsp;</td>
																			<td>&nbsp;</td>
																			</tr>';
																					}
																				}
																				// Pegando o total em Boleto Site
																				$query_gsm = "SELECT value FROM show_payed_sales WHERE protocol = '$protocol' AND payment_method = 'Boleto Site'";
																				$result_gsm = mysqli_query($con, $query_gsm);
																				while ($row_gsm = mysqli_fetch_array($result_gsm)) {
																					$payment_method_gsm = 'Boleto Site';
																					$value_gsm = $row_gsm['value'];
																					if ($value_gsm > 0) {
																						echo	'<tr>
																			<td>&nbsp;</td>
																			<td>&nbsp;</td>
																			<td>&nbsp;</td>
																			<td><center><b>Total ' . $payment_method_gsm . ':</b></center></td>
																			<td><center>' . number_format($value_gsm, 2, ",", ".") . '</center></td>
																			<td>&nbsp;</td>
																			<td>&nbsp;</td>
																			</tr>';
																					}
																				}
																				// Total pago
																				echo	'<tr>
																		<td>&nbsp;</td>
																		<td>&nbsp;</td>
																		<td>&nbsp;</td>
																		<td><center><b>Total:</b></center></td>
																		<td><center>' . number_format($total_pagos, 2, ",", ".") . '</center></td>
																		<td>&nbsp;</td>
																		<td>&nbsp;</td>
																	</tr>'; ?>
																			</tbody>
																		</table>
																	</div>
																	<!-- /.table-responsive -->
																</div>
																<!-- /.panel-body -->
															</div>
															<!-- /.panel -->
														</div>
													</div>
												<?php
												} ?>
											</div>
											<!-- /.col-lg-6 (nested) -->
										</div>
										</form>
										<!-- /.row (nested) -->
									<?php
									} else {
										echo "<p>O pedido buscado não existe, ou você não tem autorização para acessá-lo.</p>";
									} ?>
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
			<script type="text/javascript" src="../../portal/emplacarrn/js/jquery.maskedinput.js"></script>

			<!-- jQuery MaskMoney -->
			<script type="text/javascript" src="../../portal/emplacarrn/js/jquery.maskMoney.js"></script>

			<!-- jQuery Masked Input -->
			<script type="text/javascript" src="../../portal/emplacarrn/js/jquery.maskedinput.js"></script>

			<!-- Bootstrap Core JavaScript -->
			<script src="../../portal/<?php echo $system_name; ?>/vendor/bootstrap/js/bootstrap.min.js"></script>

			<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.7.5/js/bootstrap-select.min.js"></script>

			<!-- Metis Menu Plugin JavaScript -->
			<script src="../../portal/<?php echo $system_name; ?>/vendor/metisMenu/metisMenu.min.js"></script>

			<!-- Custom Theme JavaScript -->
			<script src="../../portal/<?php echo $system_name; ?>/dist/js/sb-admin-2.js"></script>

			<!-- Custom Theme JavaScript -->
			<script src="../../portal/<?php echo $system_name; ?>/vendor/chosen/chosen.jquery.min.js"></script>
			<script src="../../portal/<?php echo $system_name; ?>/vendor/chosen/init.js"></script>

			<script>
				$(document).ready(function() {
					var $seuCampoCpf = $("#fracionado");
					$seuCampoCpf.maskMoney({
						allowNegative: false,
						thousands: '.',
						decimal: ',',
						affixesStay: false
					});

					var $seuCampoVR = $("#valor_recebido");
					$seuCampoVR.maskMoney({
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
				header("Location: ../login");
			}
		} else {
			header("Location: ../login");
		}
	} else {
		header("Location: ../login");
	}
?>