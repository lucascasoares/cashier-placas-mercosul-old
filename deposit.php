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
?>
<html lang="pt-br">
<head><meta http-equiv="Content-Type" content="text/html; charset=utf8">
    
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<link rel="icon" type="image/png" href="/portal/emplacarrn/img/favicon-emplacar-rn.png">
    <title>Sistema - Emplacar RN</title>

    <!-- Bootstrap Core CSS -->
    <link href="../../portal/<?php echo $system_name;?>/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="../../portal/<?php echo $system_name;?>/vendor/metisMenu/metisMenu.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="../../portal/<?php echo $system_name;?>/dist/css/sb-admin-2.css?version=12" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="../../portal/<?php echo $system_name;?>/vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
	
    <!-- Chosen CSS -->
    <link href="../../portal/<?php echo $system_name;?>/vendor/chosen/css/chosen.min.css" rel="stylesheet" type="text/css">
	
	<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.7.5/css/bootstrap-select.min.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
	
	<script type="text/javascript" src="../../portal/emplacarrn/js/ajaxcashier.js"></script>
	<script type="text/javascript" src="../../portal/emplacarrn/js/ajaxestablishment.js"></script>

	<?php
	if (!connect())
		die ("Erro de conexão com localhost, o seguinte erro ocorreu -> ".mysqli_connect_error());
	$con = $_SESSION['con'];
	$user_id = $user->getId();
	$query = "SELECT * FROM deposits_group_temp WHERE user = '$user_id'";
	$result = mysqli_query($con, $query);
	$num_rows = mysqli_num_rows($result);
	if( $num_rows > 0 ){
		while ($row = mysqli_fetch_array($result)) {
			// Apagando dados temporários
			$id_group = $row['id'];
			$query_del = "DELETE FROM `deposits_temp` WHERE id_group = '$id_group'";
			$result_del = mysqli_query($con, $query_del);
			$query_del_deposits_temp = "DELETE FROM `deposits_group_temp` WHERE id = '$id_group' AND user = '$user_id'";
			$result_del_deposits_temp = mysqli_query($con, $query_del_deposits_temp);
		}
	}
	$query_new = "INSERT INTO `deposits_group_temp` (`id`, `user`) VALUES (NULL,'$user_id')";
	$result_new = mysqli_query($con, $query_new);
	if (!$result_new) {
		if(mysqli_errno($con) == 1062){
			echo "Já existe esse número de controle temporário!";
		} else {
			echo "Houve algum erro, procure o suporte!";
		}
	} else {
		$query_new_sel = "SELECT * FROM deposits_group_temp WHERE user = '$user_id'";
		$result_new_sel = mysqli_query($con, $query_new_sel);
		$row_sel = mysqli_fetch_array($result_new_sel);
		$id = $row_sel['id'];
	}
	closedb();
	?>

    <?php
	// Messages
	// New sales return
	$e = $_GET['e'];
	$pf = $_GET['pf'];
	$s = $_GET['s'];
    ?>

	<script>
	function newCustomer(){
		var cust = document.getElementById("divcustomer");
		var btn = document.getElementById("btnewcustomer");
		cust.innerHTML = '<div class="col-lg-3"><div class="form-group"><label>Responsável</label><input class="form-control" type="text" placeholder="Nome completo" id="name" name="name" maxlength="60" autocomplete="off" style="text-transform: uppercase;" onkeypress="changeToDescription()" required></div></div>';
		btn.innerHTML = "";
		var name = document.getElementById("name");
		name.focus();
	}
	
	function addGet(){
		var res = document.getElementById("res");
		var description = document.getElementById("description");
		var code = document.getElementById("code");
		var value = document.getElementById("value");
		if(description.value == ""){
			alert("A descrição não pode ficar em branco.");
			description.focus();
		} else if(code.value == "" || code.value == "_________"){
			alert("O código do envelope não pode ficar em branco.");
			code.focus();
		} else if(value.value == "" || value.value == "0,00"){
			alert("O valor não pode ser zero.");
			value.focus();
		} else {
			addDeposit();
			res.innerHTML = "";
			description.value = "";
			code.value = "";
			value.value = "";
			description.focus();
		}
	}

	function changeToDescription(){
		var tecla = event.charCode;
		var description = document.getElementById("description");
		if(tecla == 13){
			description.focus();
		}
	}
	
	function changeToCode(){
		var tecla = event.charCode;
		var code = document.getElementById("code");
		if(tecla == 13){
			code.focus();
		}
	}
	
	function changeToValue(){
		var tecla = event.charCode;
		var value = document.getElementById("value");
		if(tecla == 13){
			value.focus();
		}
	}
	
	function validate(){
		var tecla = event.charCode;
		var description = document.getElementById("description");
		var code = document.getElementById("code");
		var value = document.getElementById("value");
		if(tecla == 13){
			if(description.value == ""){
				alert("A descrição não pode ficar em branco.");
				description.focus();
			} else if(code.value == "" || code.value == "_________"){
				alert("O código do envelope não pode ficar em branco.");
				code.focus();
			} else if(value.value == "" || value.value == "0,00"){
				alert("O valor não pode ser zero.");
				value.focus();
			} else {
				addDeposit();
				res.innerHTML = "";
				description.value = "";
				code.value = "";
				value.value = "";
				description.focus();
			}
		}
	}
	</script>

</head>

<body>
    <div id="wrapper">

        <?php include 'nav.php';?>

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="page-header">Novo depósito</h2>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-12">
                    <?php
						if($s == 1){
                            echo '<div class="alert alert-success alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                Depósito cadastrado com sucesso.
                            </div>';
						}
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
					<form action="finishdeposit" method="POST">
                    <div class="panel panel-default">
						<div class="panel-heading">
                            Selecione responsável, descrição, código do envelope e valor
							<div class="pull-right">
								<?php
									if(connect()){
										$con = $_SESSION['con'];
										$establishment = $user->getEstablishment();
										$query = "SELECT * FROM boxes_list WHERE establishment = '$establishment'";
										$result = mysqli_query($con,$query);
										$num_rows = mysqli_num_rows($result);
										if($num_rows > 0){
											if($num_rows == 1){
												$row = mysqli_fetch_array($result);
												$box = $row['id'];
												$query_ver = "SELECT * FROM boxes_users WHERE box = '$box' AND user = '$user_id'";
												$result_ver = mysqli_query($con,$query_ver);
												$num_rows_ver = mysqli_num_rows($result_ver);
												if($num_rows_ver > 0){
													echo '<input type="hidden" name="box" id="box" value="'.$box.'">';
												} else {
													echo '<input type="hidden" name="box" id="box" value="0">';
												}
											} else {
												$i = 0;
												$j = 0;
												while( $row = mysqli_fetch_array($result) ){
													$box = $row['id'];
													$query_ver = "SELECT * FROM boxes_users WHERE box = '$box' AND user = '$user_id'";
													$result_ver = mysqli_query($con,$query_ver);
													$num_rows_ver = mysqli_num_rows($result_ver);
													if($num_rows_ver > 0){
														$boxes[] = $row['id'];
														$boxes[] = $row['description'];
													}
												}
												if(count($boxes) == 0){
													echo '<input type="hidden" name="box" id="box" value="0">';
												} elseif(count($boxes) == 2){
													echo '<p class="lead">'.$boxes[1].'</p>';
													echo '<input type="hidden" name="box" id="box" value="'.$boxes[0].'">';
												} else {
													$qt = count($boxes);
													echo '<div class="form-group">
													<select class="form-control" name="box" id="box" onchange="changeBox()">';
													for($i = 0; $i < $qt; $i += 2){
														echo '<option value="'.$boxes[$i].'">'.$boxes[$i+1].'</option>';
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
                                <div class="col-lg-12">
									<div class="row">
										<div id="divcustomer">
										<div class="col-lg-3">								
											<div class="form-group">
												<?php
												if(connect()){
													$sql = "SELECT * FROM deposits_responsible ORDER BY name ASC";
													$con = $_SESSION["con"];
													$result = mysqli_query($con,$sql);
													$cont = mysqli_affected_rows($con);
													// Verifica se a consulta retornou linhas 
													if ($cont > 0) {
														echo "<label>Responsável&nbsp;&nbsp;<button type='button' class='btn btn-success btn-xs' onclick='newCustomer()'>+ Novo</button></label>";
														// Atribui o código HTML para montar uma tabela 
														$return = "<select class='chosen-select form-control' data-placeholder='Escolha um responsável' tabindex='1' name='responsible' id='responsible' required'>";
														$return .= "<option disabled selected value='0'>Escolha um responsável</option>";
														// Captura os dados da consulta e inseri na tabela HTML 
														while ($linha = mysqli_fetch_array($result)) {
																if($linha['id'] == $customer){
																	$return.= "<option value='".$linha['id']."' selected>".$linha['name']."</option>";
																} else {
																	$return.= "<option value='".$linha['id']."'>".$linha['name']."</option>";
																}
														}
														$return .= "</select>";
														echo $return;
													} else {
														// Se a consulta não retornar nenhum valor, exibi mensagem para o usuário 
														echo "<label>Responsável</label>";
														echo '<input class="form-control" type="text" placeholder="Nome completo" id="name" name="name" maxlength="60" autocomplete="off" style="text-transform: uppercase;" onkeypress="changeToDescription()" required>';
													}
												} else {
													echo "<p>Não foi possível estabalecer conexão com o banco de dados!</p>";
												}
												?>
											</div>
										</div>
										</div>
										<div class="col-lg-3">
											<div class="form-group">
												<label>Descrição</label>
												<input class="form-control" type="text" placeholder="Descrição do depósito" name="description" id="description" maxlength="60" autocomplete="off" onkeypress="changeToCode()">
											</div>     
										</div>
										<div class="col-lg-2">
											<div class="form-group">
												<center><label>Código do envelope</label></center>
												<input class="form-control" type="text" name="code" id="code" maxlength="9" autocomplete="off" onkeypress="changeToValue()" style="text-align:center;">
											</div>     
										</div>
										<div class="col-lg-2">
											<div class="form-group">
												<center><label>Valor (R$)</label></center>
												<input class="form-control" type="text" name="value" id="value" maxlength="10" autocomplete="off" onkeypress="validate()" style="text-align:center;">
											</div>     
										</div>
										<div class="col-lg-2">
											<div class="form-group">
												<label>&nbsp;</label>
												<p><button type="button" class="btn btn-success" onclick="addGet()">Adicionar</button></p>
											</div>
										</div>
										<input type="hidden" name="id_temp" id="id_temp" value="<?php echo $id;?>">
									</div>
									<div id="res"></div>
									<div id="result"></div>
                                </div>
                                <!-- /.col-lg-6 (nested) -->
                            </div>
                            <!-- /.row (nested) -->
                        </div>
						</form>
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
    <script src="../../portal/<?php echo $system_name;?>/vendor/jquery/jquery.min.js"></script>
	
	<!-- jQuery Masked Input -->
	<script type="text/javascript" src="../../portal/emplacarrn/js/jquery.maskedinput.js"></script>
	
	<!-- jQuery MaskMoney -->
	<script type="text/javascript" src="../../portal/emplacarrn/js/jquery.maskMoney.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="../../portal/<?php echo $system_name;?>/vendor/bootstrap/js/bootstrap.min.js"></script>
	
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.7.5/js/bootstrap-select.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="../../portal/<?php echo $system_name;?>/vendor/metisMenu/metisMenu.min.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="../../portal/<?php echo $system_name;?>/dist/js/sb-admin-2.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="../../portal/<?php echo $system_name;?>/vendor/chosen/chosen.jquery.min.js"></script>	
	<script src="../../portal/<?php echo $system_name;?>/vendor/chosen/init.js"></script>
	
	<script>
	$(document).ready(function () {
		
		
		var $seuCampoCode = $("#code");
		$seuCampoCode.mask('999999999', {reverse: true});
		var $seuCampoValue = $("#value");
		$seuCampoValue.maskMoney({allowNegative: false, thousands:'.', decimal:',', affixesStay: false});

	});
	</script>
	
</body>
</html>
<?php
} else {
    header("Location: ../login");
}
?>