<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>	
	<title>{WNDTITLE}</title>	
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />	
	<link href="{URLSITE}style/style1.css" rel="stylesheet" type="text/css" />
</head>

<body>

<div class="cadre1">	
			<!-- BEGIN AddBlock -->			
			<table width="100%" border="0" align="center">			
				<tr> 						    
					<td width="70%">					
						<p>							
							<strong>Noter et donner votre avis de lecture pour <font color="#990000">{ALBTITLE} {ALBAUT}</font></strong>						
						</p>					
					</td>				    		
				</tr>				
				<form name="form1" method="post" action="album.php">				
				    <tr> 									    
						<td>						
							Note: 					        
							<select name="note" id="note">{OPTNOTE}</select>					        
							<em>(notez de 1-bof &agrave; 10 - culte)</em>							
							<br />						
						</td>					    		   
					</tr>				    
					<tr>					  			    
						<td>Avis de lecture * : </td>					    		    
					</tr>				    
					<tr> 					 				    
						<td>						
							<p align="left">						    
							    <textarea name="comment" class="champ_commentaire">{COMMENT}</textarea>					        
							</p>				        
						</td>					    			   
					</tr>				    
					<tr>				    			    	
						<td>				    	
							<p>							
								<em>								
									<font size="-1">									
										* : Avis de lecture qui apparaîtra pour tous les visiteurs sur le site
										<br />									
									</font>								
								</em>							
							</p>						
						</td>					
					</tr>				    
					<tr>			    
						<td>						
							<p align="center"> 			
								<input name="id_tome" type="hidden" id="id_tome" value="{TOMEID}" />						        
								<input type="submit" name="Submit" value="Valider" />					        
							</p>						
						</td>					    			   
					</tr>				
				</form>			
			</table>			
			<!-- END AddBlock -->			
			{LISTCOMMENT}		
		</div>

</body>
</html>