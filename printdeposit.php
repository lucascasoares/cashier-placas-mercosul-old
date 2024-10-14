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
	
    <!-- Chosen CSS -->
    <link href="../../portal/<?php echo $system_name;?>/vendor/chosen/css/chosen.min.css" rel="stylesheet" type="text/css">
	
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
	// New sales return
	$id = $_GET['id'];
	$s = $_GET['s'];
    ?>

</head>

<body onload='window.print()' onafterprint='window.history.back()'>
    <div id="wrapper">

        <?php include 'nav.php';?>

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="page-header">Emplacar RN</h2>
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
			<?php
			//Gravando no banco de dados ! conectando com o localhost - mysql
			if (!connect())
				die ("Erro de conexao com localhost, o seguinte erro ocorreu -> ".mysqli_connect_error());
			$con = $_SESSION['con'];
			
			// Pegando nome do estabelecimento
			$query_get_establishment_name = "SELECT * FROM establishment WHERE id = '$establishment'";
			$result_get_establishment_name = mysqli_query($con,$query_get_establishment_name);
			$row_get_establishment_name = mysqli_fetch_array($result_get_establishment_name);
			if($result_get_establishment_name){
				$establishment_name = $row_get_establishment_name['name'];
			} else {
				$establishment_name = "Erro ao pegar nome do estabelecimento";
			}
			
			// Imprimindo lista de depósitos após adicionar
			$query_print = "SELECT * FROM deposits_group WHERE id = '$id' ORDER BY id";
			$result_print = mysqli_query($con, $query_print);
			$num_rows_print = mysqli_num_rows($result_print);
			if($num_rows_print > 0){
				while ( $row = mysqli_fetch_array($result_print) ){
					$data = $row['date'];
					$hora = $row['time'];
					$box = $row['box'];
					$responsible = $row['responsible'];
				}
				// Pegando nome do caixa
				$query_get_establishment_name = "SELECT * FROM boxes_list WHERE id = '$box'";
				$result_get_establishment_name = mysqli_query($con,$query_get_establishment_name);
				$row_get_box_name = mysqli_fetch_array($result_get_establishment_name);
				if($result_get_establishment_name){
					$box_name = $row_get_box_name['description'];
				} else {
					$box_name = "Erro ao pegar nome do caixa.";
				}
				
			?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
						<div class="panel-heading">
                            Protocolo de depósito: <b><?php echo $id;?></b> <small><em>(criado pelo usuário <b><?php echo $user->getUsername();?></b> no <b><?php echo $box_name;?></b> da <b><?php echo $establishment_name;?></b>)</em></small>
							<div class="pull-right">
							<?php
								echo "<b>".mudaData($data)." ".$hora."</b>";
							?>
							</div>
                        </div>
						<div class="panel-body">
							<div class="row">
								<div class="col-lg-12">
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
											<tbody>
											<?php
												$query_products = "SELECT * FROM deposits WHERE id_group = '$id' ORDER BY id DESC";
												$result_products = mysqli_query($con, $query_products);
												$num_rows_products = mysqli_num_rows($result_products);
												if($num_rows_products > 0){
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
														</tr>';
												} else {
													echo "Não existe depósitos nesse grupo.";
												}
											?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
							<!-- /.table-responsive -->
						</div>
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
			<div class="row">
				<div class="col-lg-12">								
					<?php
					if($responsible != 0){
						if(connect()){
							$sql = "SELECT * FROM deposits_responsible WHERE id = '$responsible'";
							$con = $_SESSION["con"];
							$result = mysqli_query($con,$sql);
							$cont = mysqli_affected_rows($con);
							// Verifica se a consulta retornou linhas 
							if ($cont > 0) {
								while ($linha = mysqli_fetch_array($result)) {
									$name = $linha['name'];
								}
							} else {
								// Se a consulta não retornar nenhum valor, exibi mensagem para o usuário 
								$name = "Responsável não cadastrado.";
							}
						} else {
							echo "<p>Não foi possível estabalecer conexão com o banco de dados!</p>";
						}
					} else {
						echo "<p>Responsável não cadastrado.</p>";
					}
					$data_por_extenso = formataData(date("Y-m-d"));
					echo "<br/><br/>";
					echo "<center><p>Natal/RN, ".$data_por_extenso.".</p></center><br/><br/>";
					echo '<center>__________________________________</center>';
					echo '<center>'.$name.'</center>';
					?>
				</div>
			</div>
			<?php
			} else {
				echo "Esse grupo de depósitos não existe.";
			}
			?>
            <!-- /.row -->
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->
    
    <!-- jQuery -->
    <script src="../../portal/<?php echo $system_name;?>/vendor/jquery/jquery.min.js"></script>
	
	<!-- jQuery Masked Input -->
	<script type="text/javascript" src="../../portal/emplacarrn/js/jquery.maskedinput.js"></script>

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
	
</body>
</html>
<?php
} else {
    header("Location: ../login");
}
?>