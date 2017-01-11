{MENUCOLL}
<div class="cadre1">
			<h3>Historique des propositions</h3>
			Il y a actuellement <a href="http://www.bdovore.com/listpropal.php?type=AJOUT"><b>{NBAJOUT} propositions d'album</b></a>, <a href="http://www.bdovore.com/listpropal.php?type=EDITION"><b>{NBEDITION} propositions de nouvelle édition</b></a>
			 et <a href="http://www.bdovore.com/listpropal.php?type=CORRECTION"><b>{NBCORRECT} propositions de correction</b></a>
			 en cours de traitement pour l'ensemble de la communauté BDovore.
			<br />

			<form action="{ACTION}" method="post">
			  	<br />
		  		<table width="80%" border="1" cellpadding="5" cellspacing="0" bordercolor="#FFFFCC" bgcolor="#DAC1B3"frame="box" rules="groups">
				    <tr>
				      	<td width="31%">
							<span class="texte">Voir : </span>
							<select name="lstSource" size="1">{OPTIONSOURCE}</select>
						</td>
				      	<td width="58%">
							<span class="texte">Status : </span>
					        <select name="lstFiltre">{OPTIONFILTRE}</select>
						</td>
				      	<td width="11%">
							<input type="submit" name="Submit" value="Rafraichir" />
						</td>
				    </tr>
				</table>
			</form>
			<table width="100%" height="29" class="donnees">
			  	<tr>
				    <td width="20%" height="25" class="entete">
						<div align="center">
							<font color="#0000FF">
								<b>
									<a href="{URLSITE}membres/suiviproposals.php?cle=1&sort=asc">
										<img src="{URLSITEIMAGE}flechehaut.gif" width="12" height="12" hspace="5" border="0" />
									</a>
									Date
									<a href="{URLSITE}membres/suiviproposals.php?cle=1&sort=DESC">
										<img src="{URLSITEIMAGE}flechebas.gif" width="12" height="12" hspace="5" border="0" />
									</a>
								</b>
							</font>
						</div>
					</td>
				    <td width="55%" class="entete">
						<div align="center">
							<font color="#0000FF">
								<b>
									<a href="{URLSITE}membres/suiviproposals.php?cle=2&sort=asc">
										<img src="{URLSITEIMAGE}flechehaut.gif" width="12" height="12" hspace="5" border="0" />
									</a>
									Titre
									<a href="{URLSITE}membres/suiviproposals.php?cle=2&sort=DESC">
										<img src="{URLSITEIMAGE}flechebas.gif" width="12" height="12" hspace="5" border="0" />
									</a>
								</b>
							</font>
						</div>
					</td>
				    <td width="20%" class="entete">
						<div align="center">
							<font color="#0000FF">
								<b>
									Status
								</b>
							</font>
						</div>
					</td>
				    <td width="25" class="entete">
						<div align="center">
							<img src="{URLSITEIMAGE}poubelle.gif" width="25" height="24" />
						</div>
					</td>
				</tr>
			  	<!-- BEGIN PropBlock -->
				<tr>
				    <td width="20%" height="25" class="data">
						<div align="center">
							{DATE}
						</div>
					</td>
				    <td width="55%" class="data">
						<div align="center">
							{TITRE}
						</div>
					</td>
				    <td width="20%" class="data">
						<div align="center">
							{STATUS}
						</div>
					</td>
				    <td width="25" class="data">
						<div align="center">
							<a href="{URLDELETE}">
								<img src="{URLSITEIMAGE}poubelle.gif" width="20" height="21" border="0" />
							</a>
						</div>
					</td>
				</tr>
			  	<!-- END PropBlock -->
			</table>
		</div>