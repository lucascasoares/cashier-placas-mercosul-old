<?php
	function getAccount($box){
		if(connect()){
			$con = $_SESSION['con'];
			$query_ac = "SELECT * FROM boxes_account WHERE box = '$box'";
			$result_ac = mysqli_query($con,$query_ac);
			$num_rows_ac = mysqli_num_rows($result_ac);
			if($num_rows_ac > 0){
				while($row_ac = mysqli_fetch_array($result_ac)){
					$ac = $row_ac['account'];
					return $ac;
				}
			} else {
				echo "Erro ao pegar número da conta.";
			}
			closedb();
		} else {
			echo "Não foi possível se conectar ao banco de dados.";
		}
	}

	function formataData($data){
		$data_quebrada = explode("-", $data);
		$ano = $data_quebrada[0];
		$mes = $data_quebrada[1];
		$dia = $data_quebrada[2];
		switch ($mes) {
			case 1:
				$mes_extenso = "Janeiro";
				break;
			case 2:
				$mes_extenso = "Fevereiro";
				break;
			case 3:
				$mes_extenso = "Março";
				break;
			case 4:
				$mes_extenso = "Abril";
				break;
			case 5:
				$mes_extenso = "Maio";
				break;
			case 6:
				$mes_extenso = "Junho";
				break;
			case 7:
				$mes_extenso = "Julho";
				break;
			case 8:
				$mes_extenso = "Agosto";
				break;
			case 9:
				$mes_extenso = "Setembro";
				break;
			case 10:
				$mes_extenso = "Outubro";
				break;
			case 11:
				$mes_extenso = "Novembro";
				break;
			case 12:
				$mes_extenso = "Dezembro";
				break;
		}
		return $dia." de ".$mes_extenso." de ".$ano;
	}
	
	function mudaData($data){
		$nova_data = explode("-",$data);
		$ano = $nova_data[0];
		$mes = $nova_data[1];
		$dia = $nova_data[2];
		return $dia."/".$mes."/".$ano;
	}
	
    //Essa funçao gera um valor de String aleatório do tamanho recebendo por parametros
    function randString($size){
        //String com valor possíveis do resultado, os caracteres pode ser adicionado ou retirados conforme sua necessidade
        $basic = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $return= "";
        for($count= 0; $size > $count; $count++){
            //Gera um caracter aleatorio
            $return.= $basic[mt_rand(0, strlen($basic) - 1)];
        }
        return $return;
    }
	
	function existToken($tk){
		if(connect()){
			$con = $_SESSION['con'];
			$query_token = "SELECT * FROM payments WHERE token = '$tk'";
			$result_token = mysqli_query($con,$query_token);
			$num_results = mysqli_num_rows($result_token);
			if($num_results > 0){
				return true;
			} else {
				return false;
			}
		} else {
			echo "Erro ao conectar na base de dados para verificar token de pagamento.";
			return true;
		}
	}
?>