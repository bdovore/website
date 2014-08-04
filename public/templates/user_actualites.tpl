{MENUCOLL}
<div class="cadre1">
	<div class="genTitle">Mon actualité</div>
	<form name ="selType" action="{ACTION}" method="post">
	<div class="genBlock">
		<table border="0">
		    <tr> 
				<td>
					<span class="texte">
						Voir les nouveautés concernant :			        <select name="lstSource" size="1">
				        {OPTSOURCE}
			        </select>
					</span>
				</td>
				<td>&nbsp;</td>
			    <td>
					<span class="texte">
						Parues depuis :			        <select name="lstDuree">
				        {OPTDUREE}
			        </select>
					</span>
				</td>
				<td>&nbsp;</td>

			    <td>
					<input type="submit" name="Submit" value="Rafraîchir" />
				</td>

			</tr>
		</table>
	</div>

	</form>
	<div class="genBlock">
		<table width="100%" height="29" class="genTable">
			<tr> 
			    <td width="15%" class="genTitle"><a href="{URLSITE}membres/actualites.php?cle=1&sort=asc&duree={DUREE}&lstSource={SRC}"><img src="{URLSITEIMAGE}flechehautr.gif" width="12" height="12" hspace="5" border="0"></a>Genre<a href="{URLSITE}membres/actualites.php?cle=1&sort=DESC&duree={DUREE}&lstSource={SRC}"><img src="{URLSITEIMAGE}flechebasr.gif" width="12" height="12" hspace="5" border="0"></a></td>
				<td width="20%" class="genTitle"><a href="{URLSITE}membres/actualites.php?cle=4&sort=asc&duree={DUREE}&lstSource={SRC}"><img src="{URLSITEIMAGE}flechehautr.gif" width="12" height="12" hspace="5" border="0"></a>Auteur<a href="{URLSITE}membres/actualites.php?cle=4&sort=DESC&duree={DUREE}&lstSource={SRC}"><img src="{URLSITEIMAGE}flechebasr.gif" width="12" height="12" hspace="5" border="0"></a></td>
			    <td width="20%" class="genTitle"><a href="{URLSITE}membres/actualites.php?cle=2&sort=asc&duree={DUREE}&lstSource={SRC}"><img src="{URLSITEIMAGE}flechehautr.gif" width="12" height="12" hspace="5" border="0"></a>S&eacute;rie<a href="{URLSITE}membres/actualites.php?cle=2&sort=DESC&duree={DUREE}&lstSource={SRC}"><img src="{URLSITEIMAGE}flechebasr.gif" width="12" height="12" hspace="5" border="0"></a></td>
			    <td width="5%" class="genTitle">Tome</td>
			    <td width="25%" class="genTitle">Titre</td>
			    <td width="15%" class="genTitle"><a href="{URLSITE}membres/actualites.php?cle=3&sort=asc&duree={DUREE}&lstSource={SRC}"><img src="{URLSITEIMAGE}flechehautr.gif" width="12" height="12" hspace="5" border="0"></a>Date Parution <a href="{URLSITE}membres/actualites.php?cle=3&sort=DESC&duree={DUREE}&lstSource={SRC}"><img src="{URLSITEIMAGE}flechebasr.gif" width="12" height="12" hspace="5" border="0"></a></td>
			</tr>
			<!-- BEGIN DetailBlock -->
			<tr> 
			    <td width="15%" class="genData"><div align="center">{GENRE}</div></td>
				<td width="20%" class="genData"><div align="center">{AUTEUR}</div></td>
			    <td width="20%" class="genData"><div align="center"><a href="{URLSITE}serie.php?id_serie={SERID}">{SERIE}</a></div></td>
			    <td width="5%" class="genData"><div align="center">{TOME}</div></td>
			    <td width="25%" class="genData"><a href="{URLTITRE}">{TITRE}</a></td>
			    <td width="15%" class="genData"> <div align="center">{DTEPAR}</div></td>
			</tr>
			<!-- END DetailBlock -->
		</table>
	</div>
	<p class="texte">{BARRENAVIGATION}</p>
</div>