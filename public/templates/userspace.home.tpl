{MENUCOLL}
<div class="cadre1">	
	<p>			
		Vous avez <b>{NBUSERTOME}</b> albums {NBUSEREDITION} dans votre collection r&eacute;partis 		  		
		au travers de <b>{NBSERIES}</b> séries différentes.			
	</p>			
	<span>			
		{NBNOTES} albums notés, {NBCOMMENTS} albums commentés.				
		<br />			
	</span>			
	<br />			
	<table border="0">			
		<tr> 				
		    <td width="30%" height="31">					
				<span style="font: italic">						
					Albums les mieux not&eacute;s :						
				</span>					
			</td>				    
			<td width="30%">					
				<span style="font: italic">						
					Genres les plus repr&eacute;sent&eacute;s :						
				</span>					
			</td>				    
			<td width="30%">					
				<span style="font: italic">						
					Dessinateurs les plus repr&eacute;sent&eacute;s :						
				</span>					
			</td>				
		</tr>				
		<tr> 				
		    <td valign="top">					
				<table width="95%" class="donnees">					   
					<tr> 					        
				     	<td width="12%" class="entete">Note</td>					          	
						<td width="88%" class="entete">Titre</td>					        
					</tr>										        
					<!-- BEGIN Top10Block -->					        
					<tr> 					       
					   	<td class="data">{NOTE}</td>					          	
						<td class="data"><a href="{URLTITRE}">{TOPTITRE}</a></td>					        
					</tr>				        	
					<!-- END Top10Block -->				    	
				</table>					
			</td>				    
			<td valign="top">				
				<table width="95%" class="donnees">			        	
					<tr> 			        		
						<td width="60%" class="entete">Genre</td>			        			
						<td width="40%" class="entete">Nb d'albums</td>		        			
					</tr>					        
					<!-- BEGIN GenreBlock -->					        
					<tr> 					       
					   	<td class="data">{GENRE}</td>					          	
						<td class="data">{NBBYGENRE}</td>					        
					</tr>					        
					<!-- END GenreBlock -->									    
				</table>					
			</td>					
			<td valign="top">					
				<table width="95%" class="donnees">						
					<tr> 					        
						<td width="62%" class="entete">Dessinateur</td>					        	
						<td width="38%" class="entete">Nb d'albums</td>					      	
					</tr>						    
					<!-- BEGIN DessinBlock -->						    
					<tr> 				
						<td class="data">{TOPDESSIN}</td>						        
						<td class="data">{NBBYDESSIN}</td>						    
					</tr>						   
					<!-- END DessinBlock -->					    
				</table>					
			</td>				
		</tr>			
	</table>			
	<p>			
		<a href="usersearch.php">Naviguer dans la collection</a>			
	</p>  		    
	<p>			
		Achats prévus : <a href="usersearch.php?rb_mode=3">{NBACHAT}</a>				
		<br />		    	
		Prêts en cours : <a href="usersearch.php?rb_mode=2">{NBPRET}</a>			
	</p>		    		    
	<p> 		   
	     <b>Exporter</b> la collection, les achats prévus ou les albums manquants sous 				
		 <a href="{URLSITE}membres/export.php">				
		 	<b>Excel</b>, <b>CSV</b> ou <b>XML</b>				
		</a>				 
		ou sous forme d'un <a href="{URLSITE}membres/export_pdf.php">catalogue <b>pdf</b></a>
		<span class="petite_police">(Notre hébergeur, OVH, a restreint les performances du site pour l'export pdf. Le succès de celui-ci dépendra de la taille de votre collection et de la qualité de votre connexion internet)</span>.
	</p>		
	<p> 		   
	     <b>Importer</b> des éditions dans votre collection depuis un fichier <a href="{URLSITE}membres/import.php"><b>CSV</b>.</a>
	</p>		
</div>

<div class="cadre1">		
	<p>5 derniers albums ajoutés : </p>			
	<table width="100%" border="1" align="center" cellpadding="5" cellspacing="0" bordercolor="#000000" frame="border" rules="all">				
		<!-- BEGIN LastBlock -->				
		<tr bgcolor="EED8BF"> 				
		    <td width="489">					
				<a href={URLALBUM}>						
					<font size="-1" face="Verdana, Arial, Helvetica, sans-serif" color="#990000">							
						<strong>{TITRE}</strong>							
					</font>						
				</a>						
				<br />				      	
				Série : <a href="{URLSERIE}">{SERIE}</a>						
				<br />				      	
				Editeur : {EDITEUR} - Edition : {DTEPARUTION}					
			</td>			    	
			<td width=151>					
				<a href="{URLSCEN}">{SCEN}</a>
				{SEP}
				<a href="{URLDESS}">{DESS}</a>					
			</td>			    	
			<td width="18">					
				<a href={URLALBUM} >
					<img src="{URLSITEIMAGE}edit.gif" width="18" height="13" border="0" />
				</a>					
			</td>			    	
			<td width="28">					
				<a href="{URLDELETE}">
					<img src="{URLSITEIMAGE}poubelle.gif" width="25" height="24" border="0" />
				</a>					
			</td>		  		
		</tr>		  		
		<!-- END LastBlock -->			
	</table>		
</div>