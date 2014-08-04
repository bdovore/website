<SCRIPT LANGUAGE="JavaScript"><!--

function newWindow(myCategory,myText,myForm,myElement,isMandatory,myEditeur) {
	if ((isMandatory == true) && (myText.length < 3)) {
		alert('Veuillez saisir au moins 3 caractères');
	}
	else {
		if (myCategory == "collection") {
			if (myEditeur == '') {
				alert("Veuillez saisir l'éditeur en premier");
			}
			else {
			mywindow=open("pickup.php?cat=" + myCategory + "&text=" + myText + "&ReturnDoc=" + myForm + "&ReturnElement=" + myElement + "&IdEditeur=" + myEditeur,'myname','scrollbars=yes,resizable=yes,width=350,height=270');
			}
		}
		else {
			mywindow=open('pickup.php?cat=' + myCategory + "&text=" + myText + "&ReturnDoc=" + myForm + "&ReturnElement=" + myElement,'myname','scrollbars=yes,resizable=yes,width=350,height=270');
		}
	}
}

//--></SCRIPT>

<form action="{URLACTION}" method="post" enctype="multipart/form-data" name="myform" target="_self" id="myform">
	<table width="100%" border="0">
		<tr>
			<td>
				<table width="100%" border="1" cellpadding="5" bordercolor="#333333" bgcolor="#FFFFCC">
			    	<tr bordercolor="#FFFFCC"> 
			        	<td width="38" class="label">Titre</td>
			            <td width="34">
							<div align="left"> 
								<input name="txtPropId" type="hidden" id="txtPropId" value="{PROPID}" />
							</div>
						</td>
			            <td width="300">
							<input name="txtTitre" type="text" id="txtTitre2" value="{TITRE}" size="40" class="{CLTITRE}" />
						</td>
			            <td width="70"><!--DWLayoutEmptyCell-->&nbsp;</td>
			        </tr>
			        <tr bordercolor="#FFFFCC"> 
			            <td><!--DWLayoutEmptyCell-->&nbsp;</td>
			            <td><!--DWLayoutEmptyCell-->&nbsp;</td>
			            <td class="original">{ORITITRE}</td>
			            <td><!--DWLayoutEmptyCell-->&nbsp;</td>
			        </tr>
					<tr bordercolor="#FFFFCC"> 
			            <td class="label">Tome</td>
			            <td> </td>
			            <td>
							<div align="left"> 
								<input name="txtNumTome" type="text" id="txtNumTome2" value="{TOME}" size="2" class="{CLTOME}" />
				            </div>
						</td>
			            <td></td>
					</tr>
					<tr bordercolor="#FFFFCC"> 
						<td class="label">&nbsp;</td>
						<td></td>
						<td class="original">{ORITOME}</td>
						<td>&nbsp;</td>
					</tr>
					<tr bordercolor="#FFFFCC"> 
						<td colspan="4" class="label">
							<table width="100%" border="0">
				                <tr> 
					                <td width="25%">&nbsp;</td>
					                <td width="25%" align="center" class="entete">Int&eacute;grale{NEW_FLG_INT}</td>
					                <td width="25%" align="center" class="entete">Type{NEW_FLG_TYPE}</td>
					                <td width="25%">&nbsp;</td>
				                </tr>
				                <tr> 
					                <td>&nbsp;</td>
						            <td class="data" align="center">
										<input type="checkbox" name="chkIntegrale" value="checkbox" {ISINT} />
									</td>
					                <td class="data" align="center">
										<select name="lstType" size="1">
											{OPTTYPE}
					                    </select>
									</td>
					                <td>&nbsp;</td>
				             	</tr>
							</table>
						</td>
					</tr>
			        <tr bordercolor="#FFFFCC"> 
			            <td colspan="4" class="label">
							Histoire
							<br />
							<br />
							<textarea name="txtHistoire" cols="50" rows="8" id="textarea" class="{CLHISTOIRE}">{HISTOIRE}</textarea> 
				            <br />
						</td>
					</tr>
			        <tr bordercolor="#FFFFCC">
			            <td colspan="4" class="original">{ORIHISTOIRE}</td>
			        </tr>
				</table>
				<br /> 
		        <table width="100%" border="1" cellpadding="5" bordercolor="#333333" bgcolor="#FFFFCC">
			        <tr bordercolor="#FFFFCC"> 
			            <td class="label">Serie</td>
			            <td>
							<div align="left"> 
				                <input name="txtSerieId" type="text" id="txtSerieId2" value="{IDSERIE}" size="5" class="{CLIDSERIE}" />
				            </div>
						</td>
			            <td><input name="txtSerie" type="text" id="txtSerie3" value="{SERIE}" size="37" class="{CLSERIE}" /> {LIENEDITNEWSERIE}</td>
			            <td> 
							<img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('serie',document.myform.txtSerie.value,'myform','txtSerie',true)" alt="Loupe" />
						</td>
					</tr>
			        <tr bordercolor="#FFFFCC"> 
			            <td><!--DWLayoutEmptyCell-->&nbsp;</td>
			            <td><!--DWLayoutEmptyCell-->&nbsp;</td>
			            <td class="original">{ORISERIE}</td>
			            <td><!--DWLayoutEmptyCell-->&nbsp;</td>
			        </tr>
			        <tr bordercolor="#FFFFCC"> 
			            <td width="10%" class="label">Genre</td>
			            <td width="8%">
							<div align="center"> 
				                <input name="txtGenreId" type="text" value="{IDGENRE}" size="3" class="{CLIDGENRE}" />
				            </div>
						</td>
			            <td width="66%">
							<input name="txtGenre" type="text" value="{GENRE}" size="30"class="{CLGENRE}" />
						</td>
			            <td width="16%">
							<img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('genre',document.myform.txtGenre.value,'myform','txtGenre',false)" alt="Loupe" />
						</td>
					</tr>
			        <tr bordercolor="#FFFFCC"> 
			            <td>&nbsp;</td>
			            <td>&nbsp;</td>
			            <td class="original">{ORIGENRE}</td>
			            <td>&nbsp;</td>
			        </tr>
			        <tr bordercolor="#FFFFCC"> 
			            <td class="label">Status</td>
			            <td>
							<div align="center"> </div>
						</td>
			            <td>
							<select name="lstStatus" size="1" >
								{OPTSTATUS}
				            </select>
				            {NEW_FLG_FINI}
						</td>
			            <td>&nbsp; </td>
					</tr>
			        <tr bordercolor="#FFFFCC"> 
			            <td>&nbsp;</td>
			            <td>&nbsp;</td>
			            <td class="original">{ORISERIEFINI}</td>
			            <td>&nbsp;</td>
			        </tr>
		        </table>
		        <br /> 
		        <table width="100%" border="1" cellpadding="5" bordercolor="#666666" bgcolor="#FFFFCC">
			        <tr bordercolor="#FFFFCC"> 
			            <td width="18%" class="label">Scenariste</td>
			            <td width="10%">
							<input name="txtScenarId" type="text" value="{IDSCEN}" size="4" class="{CLIDSCEN}" />
						</td>
			            <td width="59%">
							<input name="txtScenar" type="text" value="{SCENARISTE}" size="33"class="{CLSCENARISTE}" />
						</td>
			            <td width="13%">
							<img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('auteur',document.myform.txtScenar.value,'myform','txtScenar',true)" alt="Loupe" />
						</td>
					</tr>
			        <tr bordercolor="#FFFFCC"> 
			            <td>&nbsp;</td>
			            <td>&nbsp;</td>
			            <td class="original">{ORISCENARISTE}</td>
			            <td>&nbsp;</td>
			        </tr>
			        <tr bordercolor="#FFFFCC"> 
			            <td class="label">Scenariste #2</td>
			            <td>
							<input name="txtScenarAltId" type="text" value="{IDSCENALT}" size="4" class="{CLIDSCENALT}" />
						</td>
			            <td>
							<input name="txtScenarAlt" type="text" value="{SCENARISTEALT}" size="33"class="{CLSCENARISTEALT}" />
						</td>
			            <td>
							<img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('auteur',document.myform.txtScenarAlt.value,'myform','txtScenarAlt',true)" alt="Loupe" />
						</td>
					</tr>
			        <tr bordercolor="#FFFFCC"> 
			            <td>&nbsp;</td>
			            <td>&nbsp;</td>
			            <td class="original">{ORISCENARISTEALT}</td>
			            <td>&nbsp;</td>
			        </tr>
			        <tr bordercolor="#FFFFCC"> 
			            <td class="label">Dessinateur</td>
			            <td>
							<input name="txtDessiId" type="text" value="{IDDESS}" size="4" class="{CLIDDESS}" />
						</td>
			            <td>
							<input name="txtDessi" type="text" value="{DESSINATEUR}" size="33" class="{CLDESSINATEUR}" />
						</td>
			            <td>
							<img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('auteur',document.myform.txtDessi.value,'myform','txtDessi',true)" alt="Loupe" />
						</td>
			        </tr>
			        <tr bordercolor="#FFFFCC"> 
			            <td>&nbsp;</td>
			            <td>&nbsp;</td>
			            <td class="original">{ORIDESSINATEUR}</td>
			            <td>&nbsp;</td>
			        </tr>
			        <tr bordercolor="#FFFFCC"> 
			            <td class="label">Dessinateur #2</td>
			            <td>
							<input name="txtDessiAltId" type="text" value="{IDDESSALT}" size="4" class="{CLIDDESSALT}" />
						</td>
			            <td>
							<input name="txtDessiAlt" type="text" value="{DESSINATEURALT}" size="33" class="{CLDESSINATEURALT}" />
						</td>
			            <td>
							<img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('auteur',document.myform.txtDessiAlt.value,'myform','txtDessiAlt',true)" alt="Loupe" />
						</td>
			        </tr>
			        <tr bordercolor="#FFFFCC"> 
			            <td>&nbsp;</td>
			            <td>&nbsp;</td>
			            <td class="original">{ORIDESSINATEURALT}</td>
			            <td>&nbsp;</td>
			        </tr>
			        <tr bordercolor="#FFFFCC"> 
			            <td class="label">Coloriste</td>
			            <td>
							<input name="txtColorId" type="text" value="{IDCOLOR}" size="4" class="{CLIDCOLOR}" />
						</td>
			            <td>
							<input name="txtColor" type="text" value="{COLORISTE}" size="33" class="{CLCOLORISTE}" />
						</td>
			            <td>
							<img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('auteur',document.myform.txtColor.value,'myform','txtColor',true)" alt="Loupe" />
						</td>
			        </tr>
			        <tr bordercolor="#FFFFCC"> 
			            <td>&nbsp;</td>
			            <td>&nbsp;</td>
			            <td class="original">{ORICOLORISTE}</td>
			            <td>&nbsp;</td>
			        </tr>
				</table>
		        <p> 
			        <div align="center"> 
				        <p></p>
				        <input type="submit" name="Submit" value="{ACTIONNAME}" />
				        <br />
			        </div>
			</td>
		    <td valign="top">
				<table width="100%" border="1" cellpadding="5" bordercolor="#333333" bgcolor="#FFFFCC">
					<tr bordercolor="#FFFFCC"> 
						<td class="label">
							{USEROWN}
							<br />
				            {USERHASEDITION}
							<br />
				            <input name="txtDefEdition" type="hidden" id="txtDefEdition" value="{DEFEDITIONID}" />
				            {MODIFONDEFAULT}
						</td>
					</tr>
				</table>
				<br />
		        <table width="100%" border="1" cellpadding="5" bordercolor="#666666" bgcolor="#FFFFCC">
			        <tr bordercolor="#FFFFCC"> 
			            <td width="21%" class="label">Editeur</td>
			            <td width="9%">
							<input name="txtEditeurId" type="text" value="{IDEDIT}" size="3" class="{CLIDEDIT}" />
						</td>
			            <td width="58%">
							<input name="txtEditeur" type="text" value="{EDITEUR}" size="33"class="{CLEDITEUR}" />
						</td>
			            <td width="12%">
							<img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('editeur',document.myform.txtEditeur.value,'myform','txtEditeur',true)" alt="Loupe" />
						</td>
			        </tr>
			        <tr bordercolor="#FFFFCC"> 
			            <td>&nbsp;</td>
			            <td>&nbsp;</td>
			            <td class="original">{ORIEDITEUR}</td>
			            <td>&nbsp;</td>
					</tr>
			        <tr bordercolor="#FFFFCC"> 
			            <td class="label">Collection</td>
			            <td> 
							<input name="txtCollecId" type="text" value="{IDCOLLEC}" size="3" class="{CLIDCOLLEC}" />
						</td>
			            <td>
							<input name="txtCollec" type="text" value="{COLLECTION}" size="33" class="{CLCOLLECTION}" />
						</td>
					    <td>
							<img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('collection',document.myform.txtCollec.value,'myform','txtCollec',false,document.myform.txtEditeurId.value)" alt="Loupe" />
						</td>
			        </tr>
			        <tr bordercolor="#FFFFCC"> 
			            <td>&nbsp;</td>
			            <td>&nbsp;</td>
			            <td class="original">{ORICOLLECTION}</td>
			            <td>&nbsp;</td>
			        </tr>	
					<tr bordercolor="#FFFFCC"> 
			            <td colspan="4" class="label">
							<table width="100%" border="0">
				                <tr> 
				                	<td align="center"><a href="{URLEAN}" title="Teste le code EAN sur BDNet" target="_blank">EAN</a> : 
				                	<input name="txtEAN" type="text" value="{EAN}" size="14" class="{CLEAN}" />{ISEAN}</td>
				                	<td align="center"><a href="{URLISBN}" title="Teste le code ISBN sur Amazon" target="_blank">ISBN</a> : 
				                    <input name="txtISBN" type="text" value="{ISBN}" size="10" class="{CLISBN}" />{ISISBN}</td>
				                </tr>
						<tr>
                                                    <td class="original" align="center">{ORIEAN}</td>
                                                    <td class="original" align="center">{ORIISBN}</td>
                                                </tr>
			            	</table>
						</td>
			        </tr>
			        <tr bordercolor="#FFFFCC"> 
			            <td class="label">Date de parution</td>
			            <td>&nbsp; </td>
			            <td>
							<input name="txtDateParution" type="text" id="txtDateParution" value="{DTPAR}" size="15" class="{CLDTPAR}" />
						</td>
			            <td>&nbsp;</td>
			        </tr>
			        <tr bordercolor="#FFFFCC"> 
			            <td class="label">&nbsp;</td>
			            <td>&nbsp;</td>
			            <td class="original">{ORIDTPAR}</td>
			            <td>&nbsp;</td>
			        </tr>
			        <tr bordercolor="#FFFFCC"> 
			            <td colspan="4" class="label">
							Ignorer les modifications relatives &agrave; l'&eacute;dition 
				            <input type="checkbox" name="chkModifEdition" value="checked" />
						</td>
			        </tr>
				</table>
				<br />
				<table width="100%" border="1" cellpadding="5" bordercolor="#666666" bgcolor="#FFFFCC">
			        <tr> 
			            <td width="50%" align="center" bordercolor="#FFFFCC" bgcolor="#FFFFCC">
							<img src="{URLIMAGE}" width="180" border="0" align="absmiddle" alt="Couverture" />
							<br />
							{DIMIMAGE}
						</td>
			            <td width="50%" align="center" bordercolor="#FFFFCC" bgcolor="#FFFFCC" class="original">
							<img src="{URLORIIMAGE}" width="180" border="0" align="absmiddle" alt="Couverture originale" />
							<br />
							{ORIDIMIMAGE}
						</td>
			        </tr>
			        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC"> 
			            <td colspan="2" class="label">
							Ignorer le changement de couverture (les dimensions de l'image de gauche n'apparaissent pas si l'utilisateur ne propose pas de nouvelle couverture)
							<input type="checkbox" name="chkDelete" value="checked" />
				        </td>
	        		</tr>
	        		<tr bordercolor="#FFFFCC" bgcolor="#FFFFCC"> 
			            <td class="label">Redimensionner la couverture</td>
			            <td><input type="checkbox" name="chkResize" value="checked"></td>
		            </tr>
				</table>
				<br />
				<table width="100%" border="1" cellpadding="5" bordercolor="#333333" bgcolor="#FFFFCC">
					<tr bordercolor="#FFFFCC"> 
						<td class="label">Commentaire de l'utilisateur :</td>
					</tr>
					<tr bordercolor="#FFFFCC"> 
						<td class="label">{USERCOMMENT}</td>
					</tr>
				</table>
				<br />
				<table width="100%" border="1" bordercolor="#333333" bgcolor="#FFFFCC">
					<tr bordercolor="#FFFFCC">
						<td>
							<p class="texte">
								Contact
								<br />
					            <a href="mailto:{ADRESSEMAIL}?subject={MAILSUBJECT}" style="font-weight: bold;">
									Envoyer un email à {MEMBRE}
								</a>
								({ADRESSEMAIL})
							</p>
						</td>
					</tr>
				</table>
				<br /> 
		        <table width="100%" border="1" cellpadding="5" bordercolor="#333333" bgcolor="#FFFFCC">
			        <tr bordercolor="#FFFFCC"> 
			            <td width="18%" class="label">
							En rouge <div align="center"> </div>
						</td>
			            <td width="82%" class="texte">
							Incoh&eacute;rence entre l'ID et la valeur saisie
						</td>
			        </tr>
			        <tr bordercolor="#FFFFCC"> 
			            <td class="label">En orange</td>
			            <td class="texte">Champ modifi&eacute; par l'utilisateur</td>
			        </tr>
			        <tr bordercolor="#FFFFCC">
			            <td class="label">En gris</td>
			            <td class="texte">Information actuellement dans la base</td>
			        </tr>
				</table>
			</td>
		</tr>
	</table>
</form>
<table width="635">
    <tr align="center">
    	<td width="100">
    		{BOUTONPRECEDENT}
    	</td>
    	<td width="435">
   			&nbsp;
		</td>
		<td width="100">
    		{BOUTONSUIVANT}
    	</td>
	</tr>
	<tr align="center">
		<td width="100">
    		&nbsp;
    	</td>
    	<td width="435">
   			<form action="{URLDELETE}">
    			<a href="{URLDELETE}"><input type="submit" value="Supprimer"></a>
			</form>
		</td>
		<td width="100">
    		&nbsp;
    	</td>
	</tr>
</table>	
