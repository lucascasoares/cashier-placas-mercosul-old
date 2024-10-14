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
	$date = $_GET['date'];
	$pf = $_GET['pf'];
	if($user->getRole() == 1 || $user->getRole() == 3  || $user->getRole() == 5 ){
?>
<!DOCTYPE html>
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

    <script type="text/javascript" src="../../portal/<?php echo $system_name;?>/js/ajaxsales.js?token=df54g545tty546"></script>
	<script type="text/javascript" src="../../portal/emplacarrn/js/ajaxestablishment.js"></script>

</head>

<body onload="listOpenedProtocols()">

    <div id="wrapper">

        <?php include 'nav.php';?>

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="page-header">Protocolos em aberto</h2>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Selecione a data, deixe em branco para ver todos ou busque por um protocolo específico 
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <!-- /.col-lg-6 (nested) -->
                                <div class="col-lg-4">
                                    <div class="form-group input-group">
                                        <input class="form-control hackdate" type="date" id="date" name="date" value="<?php echo $date;?>" min="<?php echo "2018-12-26";?>" onKeyDown="if(event.keyCode==13) listOpenedProtocols();" autofocus>
                                        <span class="input-group-btn">
                                            <button class="btn btn-default" type="button" onclick="listOpenedProtocols();"><i class="fa fa-search"></i>
                                            </button>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group input-group">
                                        <input class="form-control hackdate" type="text" id="protocol" name="protocol" placeholder="Digite o protocolo" onKeyDown="if(event.keyCode==13) searchOpenedProtocol();">
                                        <span class="input-group-btn">
                                            <button class="btn btn-default" type="button" onclick="searchOpenedProtocol();"><i class="fa fa-search"></i>
                                            </button>
                                        </span>
                                    </div>
                                </div>
                                <!-- /.col-lg-6 (nested) -->
                            </div>
                            <!-- /.row (nested) -->
                            <input type="hidden" id="pf" value="<?php echo $pf;?>">
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
    <script src="../../portal/<?php echo $system_name;?>/vendor/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="../../portal/<?php echo $system_name;?>/vendor/bootstrap/js/bootstrap.min.js"></script>
	
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.7.5/js/bootstrap-select.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="../../portal/<?php echo $system_name;?>/vendor/metisMenu/metisMenu.min.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="../../portal/<?php echo $system_name;?>/dist/js/sb-admin-2.js"></script>

</body>
</html>
<?php
include 'contador.php';
	} else {
		header("Location: ../home");
	}
} else {
    header("Location: ../../".$system_name."/login");
}
?>