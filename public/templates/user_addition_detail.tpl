<link href="{URLSITE}style/addition.css" rel="stylesheet" type="text/css" />
<div style="position:absolute;top:1em;left:60%">
	<form action="usersearch.php" method="post" name="search" target="_self">
	<div class="cadre1">
					Recherche : 
   			    	<input name="str_search" type="text" id="str_search" value="" size="20" maxlength="50" /> 
			    	<input type="submit" name="Submit" value="Envoyer" />
					<br />
			    	Albums: 
		        	<input name="cb_album" type="checkbox" id="cb_album" value="cb_album" checked />
		        	S&eacute;ries: 
		        	<input name="cb_serie" type="checkbox" id="cb_serie" value="cb_serie" />
		        	Auteurs: 
		        	<input name="cb_aut" type="checkbox" id="cb_aut" value="cb_aut" />

					<table width="100%" border="0">
		            	<tr> 
				            <td width="73%">
								<div align="left"> 
					                <input name="rb_mode" type="radio" value="1" checked />
					                Mes BD 
					                <input type="radio" name="rb_mode" value="2" />
					                Pr&ecirc;t 
					                <input type="radio" name="rb_mode" value="3" />
					                A pr&eacute;voir
								</div>
							</td>
					        <td width="27%">
								<div align="right">
									<a href="userbrowser.php">
										BD Browser
									</a>
								</div>
							</td>
			            </tr>
					</table>
				</div>
	</form>
</div>

{MENUCOLL}
<div class="cadre1">
			<table width="132" border="1">
		        <tr> 
			        <td width="77" align="center">
						<a href="{URLSITE}membres/addition.php">
							Statistiques
						</a>
					</td>
			        <td width="39" align="center">
						<a href="{URLAIDE}">
							Aide
						</a>
					</td>
				</tr>
			</table>
			<div class="addTitle">
				R&eacute;sum&eacute; de la valorisation
			</div>
			<div class="addFreeBlock">
				<div class="addTitle2">
					Valorisation de la collection
				</div>
				<table width="400" class = "addStats">
		            <tr> 
		                <td class="addTitle">&nbsp;</td>
		                <td class="addTitle">Nombres</td>
		                <td class="addTitle">Valeur</td>
		            </tr>
		            <tr> 
			            <td class="addDataLeft">
							Album au prix d'achat
						</td>
		                <td class="addData">
							<a href="{URLNBPA}" title="{TITRENBPA}">{NBPA}</a>
						</td>
		                <td class="addData">{VALPA}</td>
		            </tr>
		            <tr> 
			            <td class="addDataLeft">Album au prix bdovore</td>
		                <td class="addData">
							<a href="{URLNBPB}" title="{TITRENBPB}">{NBPB}</a>
						</td>
		                <td class="addData">{VALPB}</td>
		            </tr>
		            <tr> 
			            <td class="addDataLeft">Album au prix par d&eacute;faut</td>
		                <td class="addData">
							<a href="{URLNBPD}" title="{TITRENBPD}">{NBPD}</a>
						</td>
		                <td class="addData">{VALPD}</td>
		            </tr>
		            <tr> 
		                <td class="addDataLeft">Coffrets</td>
		                <td class="addData">
							<p><a href="{URLNBC}" title="{TITRENBC}">{NBC}</a></p>
						</td>
		                <td class="addData">{VALC}</td>
		            </tr>
		            <tr> 
			            <td class="addDataLeft">Non valoris&eacute;s</td>
		                <td class="addData">
							<a href="{URLNBNV}" title="{TITRENBNV}">{NBNV}</a>
						</td>
		                <td class="addData">{VALNV}</td>
		            </tr>
		            <tr> 
		                <td class="addDataLeft">Total</td>
		                <td class="addData">
							<a href="{URLNBTOT}" title="{TITRENBTOT}">{NBTOT}</a>
						</td>
		                <td class="addData">{VALTOT}</td>
		            </tr>
	            </table>
			</div>
			<div class="addFreeBlock">
				<form name="form1" method="post" action="{URLRECALC}">
					<div class="addTitle2">
						Param&egrave;tres de valorisation
					</div>
					<table class="addStats">
			            <tr> 
				            <td class="addDataLeft">Prix par d&eacute;faut d'un album</td>
				            <td>
								<input name="txtPrixAlbum" type="text" value="{DEFALB}" size="6" class="flat" />
				            </td>
			            </tr>
			            <tr> 
				            <td class="addDataLeft">Prix par d&eacute;faut d'une int&eacute;grale</td>
				            <td>
								<input name="txtPrixIntegrale" type="text" value="{DEFINT}" size="6" class="flat" />
				            </td>
			            </tr>
			            <tr> 
				            <td class="addDataLeft">Prix par d&eacute;fault / surco&ucirc;t d'un coffret</td>
				            <td>
								<input name="txtPrixCoffret" type="text" value="{DEFCOFFRET}" size="6" class="flat" />
				            </td>
			            </tr>
			            <tr> 
				            <td class="addDataLeft">Valorisation des coffrets</td>
				            <td>
								<select name="lstCoffret" class="flat">
									{OPTVALOCOFFRET}
			                    </select>
				            </td>
			            </tr>
			            <tr> 
				            <td height="31">&nbsp;</td>
				            <td valign="bottom">
								<input type="submit" name="Submit2" value="Recalculer">
							</td>
				        </tr>
					</table>
		        </form>
			</div>
			<hr class="addSep" />
			<div class="addTitle">D&eacute;tail de la valorisation</div>
			<form action="{URLACTION}" method="post" name="formMAJ">
				<table width="100%" border="1">
			        <tr> 
				        <td></td>
			        </tr>
			        <tr> 
				        <td>
							<table width="100%" height="55" border="1" cellpadding="1" cellspacing="1" class="Tableur">
				                <tr> 
					                <td height="25" class="addTitle">S&eacute;rie</td>
					                <td class="addTitle">Tome</td>
					                <td class="addTitle">Titre</td>
					                <td class="addTitle">Editeur / parution</td>
					                <td width="100" class="addTitle">Prix du march&eacute;</td>
					                <td width="100" class="addTitle">Prix Achat</td>
					                <td width="100" class="addTitle">Prix Retenu</td>
				                </tr>
				                <!-- BEGIN RowBlock -->
				                <tr> 
					                <td class="DataTableur">
										<a href="{URLSERIE}">{ALBSERIE}</a>
									</td>
					                <td class="DataTableur">{ALBTOME}</td>
					                <td class="DataTableur">
										<a href="#" onclick="window.open('{URLTOME}','Album','width=600,height=800,scrollbars=1')">
											{ALBTITRE}
										</a>
									</td>
					                <td class="DataTableur">{ALBEDITEUR}</td>
					                <td class="DataTableur">{PRIX}</td>
					                <td class="DataTableur">
										<input name="prix_achat[{IDEDITION}]" type="text" id="txtPrixAchat" value="{PRIXACHAT}" size="6" class="flat" />
									</td>
					                <td class="DataTableur">{PRIXRETENU}</td>
				                </tr>
				                <!-- END RowBlock -->
				                <tr> 
					                <td colspan="4" class="DataTableur">&nbsp;</td>
					                <td class="DataTableur">&nbsp;</td>
					                <td class="DataTableur">&nbsp;</td>
				                </tr>
				                <tr> 
					                <td colspan="4" class="DataTableur">&nbsp;</td>
					                <td class="DataTableur">&nbsp;</td>
					                <td class="DataTableur">
										<input type="submit" name="Submit3" value="Mettre à jour" />
									</td>
				                </tr>
				            </table>
						</td>
			        </tr>
				</table>
			</form>
			{BARRENAVIGATION}
</div>