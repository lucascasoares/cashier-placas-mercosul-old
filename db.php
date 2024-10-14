<?php
function connect(){
	$con = mysqli_connect("","","","");
	$con->set_charset("utf8");
	if (!$con){
		die('Could not connect: ' . mysqli_connect_error());
		return false;
	} else {
		$_SESSION['con'] = $con;
		return true;
	}
}

function closedb(){
	$con = $_SESSION['con'];
	mysqli_close($con);
}
?>