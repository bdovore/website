<div class="cadre1">
<h2>Le Guide BD</h2>
		<a href="listlast.php?rb_mode=futur">A paraitre</a>
		- <a href="listlast.php?rb_mode=last_par">Derni&egrave;res parutions</a>
		- <a href="listop.php?rb_mode=top_vote">Top des notes</a>
		- <a href="listop.php?rb_mode=top_rep">Albums les plus répandus</a>
		- <a href="listlast.php?rb_mode=last_cmt">Derniers commentaires</a>

		<table width="100%" align="center" cellpadding="10" cellspacing="10">
			<tr bordercolor="#FFFFFF">
		  		<td valign="top" bordercolor="#FFFFFF">
            		<form name="form1" method="post" action="listlast.php">
              			<table border="1" width="100%" cellpadding="5" cellspacing="0" bordercolor="#FFFFCC" bgcolor="#DAC1B3" frame="box" rules="groups">
              				<tr>
	                            <td>
	                            	<label for="rb_mode1"><input id="rb_mode1" type="radio" name="rb_mode" value="last_ajout" {AJOUTCHECKED} />
				                    Derniers ajouts</label>
									<label for="rb_mode2"><input id="rb_mode2" type="radio" name="rb_mode" value="last_par" {PARCHECKED} />
				                    Derni&egrave;res parutions</label>

				                    <label for="rb_mode3"><input id="rb_mode3" type="radio" name="rb_mode" value="futur" {FUTCHECKED} />
									A paraître</label>
					                <label for="rb_mode4"><input id="rb_mode4" type="radio" name="rb_mode" value="last_cmt" {CMTCHECKED} />
	                  				Derniers commentaires</label>
								</td>
              				</tr>
              				<tr>
							<td align="center">
									Genre :
	                  				<select name="sel_genre" size="1" id="sel_genre">
										{OPTIONS}
									</select>
									<input type="submit" name="Submit" value="Envoyer" />
								</td>
	             			</tr>
	            		</table>
	  				</form>
	  			</td>
	  		</tr>
		</table>
</div>
<div class="cadre1">
		<table width="100%" border="1" align="center" cellpadding="5" cellspacing="0" bordercolor="#000000" frame="hsides" rules="rows">

	        <!-- BEGIN AlbBlock -->
	        <tr float="left" id="{ANCRE}" name="{ANCRE}">
		        <td width="100" valign="top" align="center">
					<a href={URLALBUM} title="{TITRE}">
						<img src='{URLSITEIMAGE}couv/{IMGCOUV}' border="none" width="100" heigh="140" alt="couverture" />
					</a>
	            	<br />
			        <font size="-2">
						&copy; {EDITEUR}
					</font>
				</td>
		        <td valign="top">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
			            <tr valign="top">
			                <td style="min-width:200px">
								<a href={URLALBUM}>
									<font size="-1" face="Verdana, Arial, Helvetica, sans-serif" color="#990000">
										<strong>{TITRE}</strong>
									</font>
								</a>
				                {EDITALBUM}<br />
				                Série : <a href="{URLSERIE}">{SERIE}</a> {EDITSERIE}<br />
				                Editeur : {EDITEUR} <br />
				                Scénario :<a href="{URLSCEN}">{SCEN}</a> <br />
				                Dessin : <a href="{URLDESS}">{DESS}</a><br />
				                Parution : {DTEPARU}
							</td>

							<!-- BEGIN CmtBlock -->
							<td valign="top">
				                <div id="cache" style="display:{DISPLAY}">
									<table width="100%" border="1" align="center" cellpadding="3" cellspacing="0" bordercolor="#B64444">
					                    <tbody>
						                    <tr bordercolor="#FFFFFF">
						                        <td height="25" bordercolor="#B64444" bgcolor="#C8857C">
													<strong>
														<font color="#990000" face="Arial, Helvetica, sans-serif" size="2">
									                        Note : {NOTE}
														</font>
													</strong>
												</td>
						                        <td bordercolor="#B64444" bgcolor="#C8857C">
													<div align="right">
														<font face="Arial, Helvetica, sans-serif" size="1">
															<font color="#0000ff" face="Arial, Helvetica, sans-serif" size="2">
																<font color="#333333">
																	Postée par <strong>{USERNAME}</strong>
																</font>
															</font>
															le {CMTDATE}
														</font>
													</div>
												</td>
											</tr>
											<tr bordercolor="#B64444">
						                        <td colspan="2">
													<font size="2" face="Arial, Helvetica, sans-serif">
														{CMTTEXTE}
													</font>
												</td>
						                    </tr>
						                </tbody>
						             </table>
								</div>
							</td>
							<!-- END CmtBlock -->
			        	</tr>
					</table>

				</td>
			</tr>
        	<!-- END AlbBlock -->

		</table>
		<div align="center">
			<a href="listlast.php?{PREC}">
				Pr&eacute;c&eacute;dent
			</a>
			<a href="listlast.php?{SUIV}">
				Suivant
			</a>
		</div>
</div>