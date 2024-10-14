<?php
header('Content-Type: text/html; charset=utf8');
$system_name = "emplacarrn";
include 'contador.php';
include 'db.php';
include 'fun.php';
include 'User.php';
session_id($_COOKIE[session_name()]);
session_set_cookie_params(3600); 
session_start(); // ready to go!
date_default_timezone_set('America/Fortaleza');
if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
	$user_id = $user->getId();
	if($user->getRole() == 1 || $user->getRole() == 3 || $user->getRole() == 5){
		$establishment = $user->getEstablishment();
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
	
	<script>
		function verify(frm){
			var description = document.getElementById('description');
			var value = document.getElementById('value');
			if(confirm("Deseja confirmar a transferência?")){
				return true;
			} else {
				return false;
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
                    <h2 class="page-header">Nova trasferência</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-12">
                    <?php
                        if($e == 1){
                            echo '<div class="alert alert-danger alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                Existe mais de uma transferência com o mesmo identificador, procure o suporte com urgencia.
                            </div>';
                        } elseif($e == 2){
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
                        if($n == 1){
                            echo '<div class="alert alert-success alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								Transferência realizada com sucesso.
                            </div>';
                        }
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Selecione o destino, descrição e valor
                        </div>
                        <div class="panel-body">
                            <form role="form" action="dotransfer" method="POST" onsubmit="return verify(this);">
							<div class="row">
                                <div class="col-lg-3">
									<div class="form-group">
										<label>Destino</label>
										<?php
										if(connect()){
											$sql = "SELECT * FROM accounts ORDER BY name ASC";
											$con = $_SESSION["con"];
											$result = mysqli_query($con,$sql);
											$cont = mysqli_affected_rows($con);
											// Verifica se a consulta retornou linhas 
											if ($cont > 0) {
												// Atribui o código HTML para montar uma tabela 
												$return = "<select data-placeholder='Escolha uma conta' class='chosen-select form-control' name='account' id='account' required>";
												// Captura os dados da consulta e inseri na tabela HTML 
												while ($linha = mysqli_fetch_array($result)) {
													if(getAccount($establishment) != $linha['id']){
														if($linha['id'] == 1){
															$return.= "<option value='".$linha['id']."' selected>".$linha['name']."</option>";
														} else {
															$return.= "<option value='".$linha['id']."'>".$linha['name']."</option>";
														}
													}
												}
												$return .= "</select>";
												echo $return;
											} else {
												// Se a consulta nao retornar nenhum valor, exibi mensagem para o usuário 
												echo "<p>Nao há destinos cadastrados.</p>";
											}
										} else {
											echo "<p>Nao foi possível estabalecer conexao com o banco de dados!</p>";
										}
										?>
									</div>    
                                </div>
                                <div class="col-lg-7">
									<div class="form-group">
										<label>Descriç&atilde;o</label>
										<input class="form-control" type="text" placeholder="Descrição da transferência" name="description" maxlength="60" required autofocus>
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
								<div class="col-lg-12">
									<label>&nbsp;</label>
									<p>
									<button type="submit" class="btn btn-success">Transferir</button>
                                    <button type="reset" class="btn btn-danger" onclick="window.history.back();">Cancelar</button>
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
    <script src="../../portal/<?php echo $system_name;?>/vendor/jquery/jquery.min.js"></script>
	
	<!-- jQuery Masked Input -->
	<script type="text/javascript" src="../../portal/<?php echo $system_name;?>/js/jquery.maskedinput.js"></script>
	
	<!-- jQuery MaskMoney -->
	<script type="text/javascript" src="../../portal/<?php echo $system_name;?>/js/jquery.maskMoney.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="../../portal/<?php echo $system_name;?>/vendor/bootstrap/js/bootstrap.min.js"></script>
	
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.7.5/js/bootstrap-select.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="../../portal/<?php echo $system_name;?>/vendor/metisMenu/metisMenu.min.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="../../portal/<?php echo $system_name;?>/dist/js/sb-admin-2.js"></script>
	
	<script>
		$(document).ready(function () { 
			var $seuCampoCpf = $("#value");
			$seuCampoCpf.maskMoney({allowNegative: false, thousands:'.', decimal:',', affixesStay: false});
		});
	</script>
</body>
</html>
<?php
	} else {
		header("Location: ../home");
	}
} else {
	header("Location: ../login");
}
?>