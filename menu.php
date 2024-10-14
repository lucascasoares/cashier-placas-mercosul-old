            <div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
                        <li>
                            <a href="../home"><i class="fa fa-home fa-fw"></i> Início</a>
                        </li>
						<?php
							$establis = $user->getEstablishment();
						?>
                        <li>
							<?php
								if($establis != 3){
							?>
									<a href="#"><i class="fa fa-shopping-cart fa-fw"></i> Pedidos <span class="fa arrow"></span></a>
							<?php
								} else {
							?>	
									<a href="#"><i class="fa fa-shopping-cart fa-fw"></i> Vendas <span class="fa arrow"></span></a>
							<?php
								}
							?>
                            <ul class="nav nav-second-level">
                                <li>
									<?php
									if($establis == 3){
										if($user->getRole() == 1 || $user->getRole() == 3 || $user->getRole() == 5){
											echo '<a href="../sales/new?pf=1">Nova</a>';
										}
									} else {
										echo '<a href="../sales/new">Novo</a>';
									}
									?>
                                </li>
                                <li>
									<?php
									if($establis == 3){
										echo '<a href="../sales/search?pf=1"> Consultar</a>';
									} else {
										echo '<a href="../sales/search"> Consultar</a>';
									}
									?>
                                </li>
								<?php
								if( $establis != 3 && ($user->getRole() == 1 || $user->getRole() == 3) ){
								?>
								<li>
									<a href="#"><i class="fa fa-refresh fa-fw"></i> Transferências <span class="fa arrow"></span></a>
									<ul class="nav nav-third-level">
										<li>
											<a href="../sales/transfer"> Nova</a>
										</li>
										<li>
											<a href="../sales/searchtransfer"> Consultar</a>
										</li>
										<li>
											<a href="../sales/seetransfers"> Pendentes</a>
										</li>
									</ul>
								</li>
								<?php
								}
								?>								
                            </ul>
                            <!-- /.nav-second-level -->
                        </li>
						<?php
							if( $user->getRole() == 1 || $user->getRole() == 2 || $user->getRole() == 3 ){
						?>
                        <li>
							<?php
								if($establis == 3){
							?>
							<a href="#"><i class="fa fa-check-square-o fa-fw"></i> Entregas <span class="fa arrow"></span></a>
							<?php
								} else {
							?>
							<a href="#"><i class="fa fa-motorcycle fa-fw"></i> Entregas <span class="fa arrow"></span></a>
							<?php
								}
							?>
                            <ul class="nav nav-second-level">
                                <li>
									<?php
									if($establis == 3){
										echo '<a href="../sales/delivery?pf=1">Chamadas do painel</a>';
									} else {
										echo '<a href="../sales/delivery">Nova</a>';
									}
									?>
                                </li>
                                <li>
									<?php
									if($establis == 3){
										echo '<a href="../sales/searchdelivery?pf=1">Placas para entrega</a>';
									} else {
										echo '<a href="../sales/searchdelivery">Consultar</a>';
									}
									?>
                                </li>								
                            </ul>
                            <!-- /.nav-second-level -->
                        </li>
						<?php
						}
						?>
						<?php
						$user_id = $user->getId();
						if( $user->getRole() == 1 || $user->getRole() == 3 || $user->getRole() == 5){
							$data_agora = date("Y-m-d");
						?>
						<li>
							<a href="#"><i class="fa fa-money fa-fw"></i> Caixa<span class="fa arrow"></span></a>
							<ul class="nav nav-second-level">
								<?php
									if($establis != 3){
								?>
								<li>
									<a href="../cashier/salesopened?date=<?php echo $data_agora;?>">Protocolos em aberto</a>
								</li>
								<?php
									} else {
								?>
								<li>
									<a href="../cashier/salesopened?date=<?php echo $data_agora;?>&pf=1">Protocolos em aberto</a>
								</li>
								<?php
									}
								?>
								<li>
									<a href="#">Movimento de caixa <span class="fa arrow"></span></a>
									<ul class="nav nav-third-level">
										<li>
											<a href="../cashier/newmovement">Novo</a>
										</li>
										<li>
											<a href="../cashier/cashmovement?date=<?php echo $data_agora;?>">Consultar</a>
										</li>
										<!-- <li>
											<a href="../cashier/transfer">Transferir</a>
										</li> -->
									</ul>
									<!-- /.nav-third-level -->
								</li>
								<li>
									<a href="#">Depósitos <span class="fa arrow"></span></a>
									<ul class="nav nav-third-level">
										<li>
											<a href="../cashier/deposit"> Novo</a>
										</li>
										<li>
											<a href="../cashier/searchdeposit?date=<?php echo $data_agora;?>"> Consultar</a>
										</li>
									</ul>
								</li>
							</ul>
							<!-- /.nav-second-level -->
						</li>
                        <?php
						}
						?>
						<li>
							<a href="#"><i class="fa fa-edit fa-fw"></i> Cadastros<span class="fa arrow"></span></a>
							<ul class="nav nav-second-level">
								<li>
									<a href="#"><i class="fa fa-users fa-fw"></i> Clientes <span class="fa arrow"></span></a>
									<ul class="nav nav-third-level">
										<li>
											<a href="../customers/new"> Novo</a>
										</li>
										<li>
											<a href="../customers/search"> Consultar</a>
										</li>
									</ul>
								</li>
								<?php
								if($user->getRole() == 1 || $user->getRole() == 3 || $user->getRole() == 5){
								?>
								<li>
									<a href="#"><i class="fa fa-suitcase fa-fw"></i> Fornecedores <span class="fa arrow"></span></a>
									<ul class="nav nav-third-level">
										<li>
											<a href="../providers/new">Novo</a>
										</li>
										<li>
											<a href="../providers/search">Consultar</a>
										</li>
									</ul>
								</li>
								<li>
									<a href="#"><i class="fa fa-bar-chart fa-fw"></i> Plano de contas <span class="fa arrow"></span></a>
									<ul class="nav nav-third-level">
										<li>
											<a href="../chartofaccounts/new">Novo</a>
										</li>
										<li>
											<a href="../chartofaccounts/search">Consultar</a>
										</li>
									</ul>
								</li>
								<?php
								}
								?>
								<?php
								if($user->getRole() == 1){
								?>
								<li>
									<a href="#"><i class="fa fa-user fa-fw"></i> Usuários <span class="fa arrow"></span></a>
									<ul class="nav nav-third-level">
										<li>
											<a href="../users/new">Novo</a>
										</li>
										<li>
											<a href="../users/search">Consultar</a>
										</li>
									</ul>
									<!-- /.nav-third-level -->
								</li>
								<?php
								}
								?>
							</ul>
							<!-- /.nav-second-level -->
						</li>
						<?php
							if($establis == 3 && $user->getRole() != 5 && $user->getRole() != 6){
						?>
                        <li>
                            <a href="#"><i class="fa fa-car fa-fw"></i> Troca de placas <span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li>
									<a href="../sales/changeplate">Chamada do painel </a>
                                </li>
                                <li>
									<a href="../sales/seal?date=<?php echo $data_agora;?>">Lista para baixa de lacre </a>
                                </li>
                            </ul>
                            <!-- /.nav-second-level -->
                        </li>
						<?php
							}
						?>
						<?php
						if($user->getRole() == 6){
						?>
                        <li>
                            <a href="#"><i class="fa fa-desktop fa-fw"></i> Painéis <span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li>
									<a href="../sales/deliverypanel">Entrega </a>
                                </li>
                                <li>
                                    <a href="../sales/changeplatepanel">Troca de placas </a>
                                </li>
                            </ul>
                            <!-- /.nav-second-level -->
                        </li>
                        <?php
						}
						?>
						<?php
						if($user->getRole() == 3 && $user->getEstablishment() == 2){
						?>
                        <li>
                            <a href="../sales/coupons"><i class="fa fa-motorcycle fa-fw"></i> Cupons</a>
                        </li>
                        <?php
						}
						?>
						<?php
						if($user->getRole() == 1){
						?>
                        <li>
                            <a href="../sales/reports"><i class="fa fa-bar-chart fa-fw"></i> Relatórios</a>
                        </li>
                        <?php
						}
						?>
                    </ul>
                </div>
                <!-- /.sidebar-collapse -->
            </div>
            <!-- /.navbar-static-side -->