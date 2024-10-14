<?php
include 'db.php';

if(connect()){
	$con = $_SESSION['con'];
	$query = "SELECT * FROM gambiarra WHERE 1";
	$result = mysqli_query($con,$query);
	$num_rows = mysqli_num_rows($result);
	if($num_rows > 0){
		while($row = mysqli_fetch_array($result)){
			$protocol = $row['protocol'];
			$query_ver = "SELECT * FROM payments WHERE protocol = '$protocol'";
			$result_ver = mysqli_query($con,$query_ver);
			$num_rows_ver = mysqli_num_rows($result_ver);
			if($num_rows_ver > 0){
				echo $protocol."</br>";
			}
		}
	}
} else {
	echo "Erro bd";
}
?>