<!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <a class="navbar-brand" href="../home"><img alt="Emplacar RN" src="/portal/emplacarrn/img/logo-emplacar-rn.png"></a>
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>
            <!-- /.navbar-header -->
			<script>
				function changeEst(){
					changeEstablishment();
					setTimeout(function(){ location.reload(); }, 300);
				}
			</script>
			<?php
				if($user->getRole() == 1){
					if(connect()){
						$con = $_SESSION['con'];
						$query_establishment = "SELECT * FROM establishment WHERE 1";
						$result_establishment = mysqli_query($con,$query_establishment);
						$num_rows_establishment = mysqli_num_rows($result_establishment);
						if($num_rows_establishment > 0){
							echo '<ul class="nav navbar-top-links navbar-left" style="display: inline-block; vertical-align: middle;">';
							echo '<li class="drowpdown">';
							echo '<div class="form-group">';
							echo '<select class="selectpicker" name="opt_establishment" id="opt_establishment" onchange="changeEst()">';
							while($row_establishment = mysqli_fetch_array($result_establishment)){
								if($row_establishment['id'] == $user->getEstablishment()){
									echo '<option value="'.$row_establishment['id'].'" selected>'.$row_establishment['name'].'</option>';
								} else {
									echo '<option value="'.$row_establishment['id'].'">'.$row_establishment['name'].'</option>';
								}
							}
							echo '</select>';
							echo '</div>';
							echo '</li>';
							echo '</ul>';
						}
						closedb();
					} else {
						echo "Nao foi possível conectar ao banco de dados.";
					}
				}
			?>
            <ul class="nav navbar-top-links navbar-right">
                <!-- /.dropdown -->
				<li class="drowpdown">
					<small><div id="hora" style="color:#777777; padding-left:13px;">Sess&atilde;o 59min59</div></small>
				</li>
                <!-- /.dropdown -->
				<?php
				if($user->getRole() == 1 || $user->getRole() == 3){
					if(connect()){
						$con = $_SESSION['con'];
						$establi = $user->getEstablishment();
						$query_get_transfer = "SELECT * FROM transfer WHERE destination = '$establi' AND confirmation = '0' ORDER BY date, time ASC";
						$result_get_transfer = mysqli_query($con,$query_get_transfer);
						$num_rows_get_transfer = mysqli_num_rows($result_get_transfer);
						if($num_rows_get_transfer > 0){
				?>
							<li class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#">
									<i class="fa fa-bell fa-fw" style="color:#F0AD4E;"></i> <i class="fa fa-caret-down" style="color:#F0AD4E;"></i>
								</a>
								<ul class="dropdown-menu dropdown-alerts">
									<?php
									$ct = 1;
									while($row_get_transfer = mysqli_fetch_array($result_get_transfer)){
										$id_transfer = $row_get_transfer['id'];
										if($ct < 6){
											echo '<li>
												<a href="../sales/showtransfer?id='.$id_transfer.'">
													<div>
														<i class="fa fa-refresh fa-fw"></i> Nova transferencia: '.$id_transfer.'
													</div>
												</a>
											</li>
											<li class="divider"></li>';
											$ct++;
										} else {
											break;
										}
									}
									?>
									<li>
										<a class="text-center" href="../sales/seetransfers">
											<strong>Ver todas as transferencias pendentes</strong>
											<i class="fa fa-angle-right"></i>
										</a>
									</li>
								</ul>
							</li>
				<?php
						}
					} else {
						echo "Nao foi possível se conectar com o banco de dados.";
					}
				}
				?>
                <!-- /.dropdown -->
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-user fa-fw"></i> <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-user">
                        <li><a href="../users/show?id=<?=$user->getId();?>"><i class="fa fa-user fa-fw"></i> Meus dados</a>
                        </li>
                        <!-- <li><a href="#"><i class="fa fa-gear fa-fw"></i> Configurações</a> -->
                        </li>
                        <li class="divider"></li>
                        <li><a href="../logout"><i class="fa fa-sign-out fa-fw"></i> Sair</a>
                        </li>
                    </ul>
                    <!-- /.dropdown-user -->
                </li>
                <!-- /.dropdown -->
            </ul>
            <!-- /.navbar-top-links -->

            <?php include 'menu.php';?>
        </nav>