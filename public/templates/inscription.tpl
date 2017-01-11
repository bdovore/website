<div class="cadre1">
<h2>Inscription</h2>
</div>
<div class="cadre1">
			<form name="form1" method="post" action="../membres/inscription.php?act=post">
				<table align="center" width="70%" border="1" cellpadding="5" cellspacing="0" bordercolor="#FFFFCC" bgcolor="#DAC1B3"  frame="box" rules="groups">
				    <tr align="center">
				      <td width="30%"><font color="{COLORNEWUSER}">Nom de l'utilisateur</font></td>
				      <td width="40%"><input type="Text" name="NewUser" value="{USERNAME}"></td>
				    </tr>
				    <tr align="center">
				      <td width="30%"><font color="{COLORNEWPASS1}">Mot de passe</font></td>
				      <td width="40%"><input type="Password" name="NewPass1" value="{PASSWORD1}"></td>
				    </tr>
				    <tr align="center">
				      <td width="30%"><font color="{COLORNEWPASS2}">Mot de passe (confimation)</font></td>
				      <td width="30%"><input type="password" name="Newpass2" value="{PASSWORD2}"></td>
				    </tr>
				    <tr align="center">
				    	<td width="30%">
							<font color="{COLORNEWEMAIL}">Adresse E-mail *</font>
						</td>
				    	<td width="30%"><input type="Text" name="NewEmail" value="{EMAIL}"></td>
				    </tr>
					<tr align="center">
						<td colspan=2>
							<font size="1.8px">
								*L'indication d'une adresse que vous <u>consultez régulièrement</u> est <u>indispensable</u> pour un fonctionnement correct du site
								 et notamment afin de garantir une <u>bonne communication</u> entre l'équipe du site et vous.
								 Dans le cas contraire, nous nous verrons dans l'obligation de verrouiller votre compte jusqu'à ce que vous nous contactiez.<br />
								Votre adresse ne sera transmise à aucun tiers et ne sera l'objet d'aucune publicité.
							</font>
						</td>
					</tr>
				    <tr align="center">
				      <td width="50%"></td>
				      <td width="50%"><input type="submit" name="Validation" value="Soumettre"></td>
				    </tr>
				</table>
				<div style="margin-top: 10px;">
					Vous pouvez également indiquer votre localisation géographique sur la
			  		<a href="http://www.mappemonde.net/carte/BDovore/Monde.html" style="color: #990000; font-weight: bold;" onclick="window.open(this.href); return false;" title="Ouvre la mappe-BDO-monde dans une nouvelle fenêtre ou un nouvel onglet">
						mappe-BDO-monde
					</a> et découvrir la répartition de la communauté BDOvore dans le monde.
				</div>
			</form>
		<font color="#000000" size="3" face="Arial, Helvetica, sans-serif">{ERRORTEXT}</font>
</div>
