<form action="{URLACTION}" method="post" enctype="multipart/form-data" name="myform" target="_self" id="myform">
	<input name="txtFlgDef" type="hidden" value="{FLGDEF}" />
	<input name="txtEditionId" type="hidden" value="{IDEDITION}" />
	<table class="fond_admin">
	    <tr>
			<td valign="top" width="60%">
				<table class="tableaux_admin">
					<!--DWLayoutTable-->
          			<tr bordercolor="#FFFFCC">
			            <td width="10%" class="label">
							<div align="center">
								Album
							</div>
						</td>
			            <td width="10%">
							<div align="left">
			                	<input name="txtTomeId" type="text" id="txtTomeId" value="{IDTOME}" size="5" class="champ_form_desactive" />
			            	</div>
						</td>
			            <td colspan="2" width="80%">
                            <input name="txtTome" type="text" id="txtTome" value="{TITRE}" class="champ_form_desactive" style="width: 100%;" />
                        </td>
			        </tr>
			    </table>
        		{ADMINEDITIONDETAIL}
                <div class="submit">
                    <input type="submit" name="Submit" value="{ACTIONNAME}" />
                </div>
			</td>

            <!-- colonne de droite -->
			<td valign="top" width="40%">
				{ADMINEDITIONCOUV}
				<table class="cadre_texte-admin">
	          		<tr>
	            		<td>
							<p class="b">Statistiques</p>
				            <p>{NBUSERS} utilisateur(s) ont cette edition dans leur collection {VIEWUSEREDITION}</p>
				            <p class="b">Menu Express</p>
				            <p>
                                {ACTIONAUTORIZE} {CONTACTUSER}
								<ul>
                                    <a href="{URLFUSIONEDITION}"><li>Fusionner cette édition</li></a>
                                    <a href="{URLDELETE}"><li>Supprimer cette edition</li></a>
                                    <a href="#" onclick="javascript:window.open('addauteur.php','myauteur','scrollbars=no,resizable=yes,width=400,height=150');return(false)"></a>
                                </ul>
                            </p>
						</td>
	          		</tr>
        		</table>
			</td>
    	</tr>
	</table>
</form>