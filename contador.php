<script type="text/javascript">
var tempo = new Number();
// Tempo em segundos
tempo = 3598;

function startCountdown(){

	// Se o tempo não for zerado
	if((tempo - 1) >= 0){

		// Pega a parte inteira dos minutos
		var min = parseInt(tempo/60);
		// Calcula os segundos restantes
		var seg = tempo%60;

		// Formata o número menor que dez, ex: 08, 07, ...
		if(min < 10){
			min = "0"+min;
			min = min.substr(0, 2);
		}
		if(seg <=9){
			seg = "0"+seg;
		}

		// Cria a variável para formatar no estilo hora/cronômetro
		horaImprimivel = 'Sess&atilde;o ' + min + 'min' + seg;
		//JQuery pra setar o valor
		$("#hora").html(horaImprimivel);

		// Define que a função será executada novamente em 1000ms = 1 segundo

		// diminui o tempo
		tempo--;

	// Quando o contador chegar a zero faz esta ação
	} else {
		horaImprimivel = 'SESSÃO EXPIRADA';
		$("#hora").html(horaImprimivel);
	}

}
// Chama a função ao carregar a tela
setInterval('startCountdown()',1000);
</script>
