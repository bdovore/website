{MENUCOLL}
<div class="cadre1">									   
			<h3>S&eacute;ries &agrave; compl&eacute;ter </h3>			   
      		<form action="{ACTION}" method="post">				   
  				<br />														   
		        <table width="63%" border="0">						   
		        	<tr> 												   
		            	<td>
							<span class="texte">
								Voir les albums manquant dans :
							</span>
						</td>
		            	<td>&nbsp;</td>													   
		          	</tr>																   
		          	<tr> 																   
		            	<td width="37%">
							<select name="lstSerie" size="1" id="lstSerie">   
					          {OPTSOURCE}														   
					         </select>
						</td>													   
					    <td width="40%">
							<input type="submit" name="Submit" value="Rafraichir" />
						</td>
					</tr>																		   
		        </table>																	   
			</form>																				   
		    <table width="100%" height="29" class="donnees">								   
		        <tr> 																		   
			    	<td width="25%" class="entete">
						<div align="center">
							<b>
								<font color="#0000FF">
									<b>
										<a href="{URLSITE}membres/albmanquant.php?cle=1&sort=asc&id_serie={SERID}">
											<img src="{URLSITEIMAGE}flechehaut.gif" width="12" height="12" hspace="5" border="0" />
										</a>
									</b>
									Titre
									<b>
										<a href="{URLSITE}membres/albmanquant.php?cle=1&sort=DESC&id_serie={SERID}">
											<img src="{URLSITEIMAGE}flechebas.gif" width="12" height="12" hspace="5" border="0" />
										</a>
									</b>
								</font>
							</b>
						</div>
					</td>
			        <td width="5%" class="entete">
						<div align="center">
							<b>
								<font color="#0000FF">
									<b>
										<a href="{URLSITE}membres/albmanquant.php?cle=2&sort=asc&id_serie={SERID}">
											<img src="{URLSITEIMAGE}flechehaut.gif" width="12" height="12" hspace="5" border="0" />
										</a>
									</b>
									Tome
									<b>
										<a href="{URLSITE}membres/albmanquant.php?cle=2&sort=DESC&id_serie={SERID}">
											<img src="{URLSITEIMAGE}flechebas.gif" width="12" height="12" hspace="5" border="0" />
										</a>
									</b>
								</font>
							</b>
						</div>
					</td>
			        <td width="15%" class="entete">
						<div align="center">
							<font color="#0000FF">
								<b>
									<a href="{URLSITE}membres/albmanquant.php?cle=3&sort=asc&id_serie={SERID}">
										<img src="{URLSITEIMAGE}flechehaut.gif" width="12" height="12" hspace="5" border="0" />
									</a>
									Date Parution 
									<a href="{URLSITE}membres/albmanquant.php?cle=3&sort=DESC&id_serie={SERID}">
										<img src="{URLSITEIMAGE}flechebas.gif" width="12" height="12" hspace="5" border="0" />
									</a>
								</b>
							</font>
						</div>
					</td>		
		        </tr>																			 		
		        <!-- BEGIN DetailBlock -->														 		
		        <tr> 																			 		
			    	<td width="25%" class="data">
						<a href="#" onclick="window.open('{URLTITRE}','Album','width=500,height=400,scrollbars=1')">
							{TITRE}
						</a>
					</td>		
			        <td width="5%" class="data">
						<div align="center">
							{TOME}
						</div>
					</td>																			
			        <td width="15%" class="data">
						<div align="center">
							{DTEPAR}
						</div>
					</td>																		
		        </tr>																																		
		        <!-- END DetailBlock -->																													
			</table>																																
			<p class="texte">
				{BARRENAVIGATION}
			</p>																												
		</div>