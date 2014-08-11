<SCRIPT LANGUAGE="JavaScript"><!--
function newWindow(myCategory,myText,myForm,myElement,isMandatory,myEditeur) {
	if ((isMandatory == true) && (myText.length < 2)) {
		alert('Veuillez saisir au moins 2 caractères');
	}else {
		if (myCategory == "collection") {
			if (myEditeur == '') {
				alert("Veuillez saisir l'éditeur en premier");
			}else {
                mywindow=open("{URLSITE}admin/pickup.php?cat=" + myCategory + "&text=" + myText + "&ReturnDoc=" + myForm + "&ReturnElement=" + myElement + "&IdEditeur=" + myEditeur,'myname','scrollbars=yes,resizable=yes,width=350,height=270');
			}
		}else {
			mywindow=open('{URLSITE}admin/pickup.php?cat=' + myCategory + "&text=" + myText + "&ReturnDoc=" + myForm + "&ReturnElement=" + myElement,'myname','scrollbars=yes,resizable=yes,width=350,height=270');
		}
	}
}
//--></SCRIPT>

<form action="{URLACTION}" method="post" enctype="multipart/form-data" name="myform" target="_self" id="myform">
<div class="cadre1">
	<table width="100%" align="center" cellpadding="10" cellspacing="10" bgcolor="EED8BF">
	  <!--DWLayoutTable-->
	  <tbody>
		<tr bordercolor="#FFFFFF">
		  <td width="907" rowspan="4" valign="top" bordercolor="#FFFFFF">
		  <p>Avant tout, merci de prendre le temps de lire les guides de saisie disponibles :
		  <ul type=SQUARE>
		  	<li><a href="http://forum.bdovore.com/viewtopic.php?t=352" target="_blank">Tutorial : comment faire une demande d'ajout ou de correction</a>
		  	<li><a href="http://forum.bdovore.com/viewtopic.php?t=617" target="_blank">Tutorial : les mangas V.O.</a>
		  	<li><a href="http://forum.bdovore.com/viewtopic.php?t=392" target="_blank">Tutorial : les BD et Comics V.O.</a>
		  	<li><a href="http://forum.bdovore.com/viewtopic.php?t=618" target="_blank">Tutorial : les séries françaises traduites en d'autres langues</a>
		  	<li><a href="http://forum.bdovore.com/viewtopic.php?t=391" target="_blank">Tutorial : les artbooks</a>
		  </ul>
			</p>
			<p>Les validateurs et correcteurs travaillent d'arrache-pied (et bénévolement) pour que notre base de données soit la plus complète possible, aidez-les en <b><u>renseignant au mieux toutes les informations à votre disposition et en les validant avec les loupes !</u></b></p>
			<p>Avant de commencer à remplir ce formulaire d'ajout, vous pouvez consulter la <a href="/listpropal.php?type=AJOUT">liste des demandes d'ajout en cours de traitement</a> afin de vous éviter de saisir une proposition déjà faite récemment</p>
			<br />
			<table width="100%" border="1" cellpadding="5" cellspacing="0" bordercolor="#FFFFCC" bgcolor="#DAC1B3"   frame="box" rules="groups">
                  <!--DWLayoutTable-->
                  <tbody>
                    <tr bordercolor="#DAC1B3">
                      <td width="61" bordercolor="#DAC1B3" class="label"><font color="#990000" size="-1"><strong>Titre</strong></font></td>
                      <td> <div align="left"> </div></td>
                      <td> <input name="txtTitre" id="txtTitre" value="{TITRE}" size="50" class="flat" type="text"></td>
                      <td width="370"> </td>
                      <td width="78"></td>
                    </tr>
                    <tr bordercolor="#DAC1B3">
                      <td bordercolor="#DAC1B3" class="label"><font color="#990000" size="-1"><strong>Série</strong></font></td>
                      <td width="19"> <div align="left">
                          <input name="txtSerieId" id="txtSerieId" value="" type="hidden">
                        </div></td>
                      <td width="317"> <input name="txtSerie" id="txtSerie" value="{SERIE}" size="50" class="flat" type="text"></td>
                      <td><img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('serie',document.myform.txtSerie.value,'myform','txtSerie',true)" height="14" width="24"></td>
                      <td><!--DWLayoutEmptyCell-->&nbsp;</td>
                    </tr>
                    <tr bordercolor="#DAC1B3">
			            <td bordercolor="#DAC1B3" class="label"><font color="#990000" size="-1"><strong>Tome</strong></font></td>
	                    <td> <div align="left"> </div></td>
	                    <td colspan="3"><input name="txtNumTome" id="txtNumTome" value="{TOME}" size="2" class="flat" type="text" /></td>
                    </tr>
                    
                    <tr bordercolor="#DAC1B3">
						<td bordercolor="#DAC1B3" class="label"><font color="#990000" size="-1"><strong>ISBN-13</strong></font></td>
	                    <td> <div align="left"> </div></td>
						<td colspan="3">
							<input name="txtEAN" id="txtEAN" value="{EAN}" size="13" class="flat" type="text" />
							<span class="texte">ISBN à 13 chiffres <u>sans</u> trait d'union ni espace (aussi appelé EAN)</span>
						</td>
                    </tr>

					<tr bordercolor="#DAC1B3">
						<td bordercolor="#DAC1B3" class="label"><font color="#990000" size="-1"><strong>ISBN-10</strong></font></td>
	                    <td> <div align="left"> </div></td>
						<td colspan="3">
							<input name="txtISBN" id="txtISBN" value="{ISBN}" size="10" class="flat" type="text" />
							<span class="texte">ISBN à 10 chiffres <u>sans</u> trait d'union ni espace (obsolète depuis janvier 2007)</span>
						</td>
                    </tr>

                  </tbody>

                </table>

			<br>

			<table width="100%" border="1" cellpadding="5" bordercolor="#FFFFCC" bgcolor="#DAC1B3" frame="box" rules="groups">

                  <tbody>

                    <tr bordercolor="#DAC1B3">

                      <td width="7%" bordercolor="#DAC1B3" class="label"><font color="#990000" size="-1"><strong>Genre</strong></font></td>

                      <td width="5%"> <div align="center">

                          <input name="txtGenreId" id="txtGenreId" value="" type="hidden">

                        </div></td>

                      <td width="23%"> <input name="txtGenre" value="{GENRE}" size="30" class="flat" type="text"></td>

                      <td width="54%"> <img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('genre',document.myform.txtGenre.value,'myform','txtGenre',false)" height="14" width="24"></td>

                      <td width="11%">&nbsp;</td>

                    </tr>

                  </tbody>

                </table>

			<br>

			<table width="100%" border="1" cellpadding="5" cellspacing="0" bordercolor="#FFFFCC" bgcolor="#DAC1B3"  frame="box" rules="groups">

                  <tbody>

                    <tr bordercolor="#DAC1B3">

                      <td width="14%" bordercolor="#DAC1B3" class="label"><font color="#990000" size="-1"><strong>Scenariste(s)</strong></font></td>

                      <td width="3%"> <input name="txtScenarId" value="" size="5" type="hidden"></td>

                      <td width="28%"> <input name="txtScenar" value="{SCENARISTE}" size="40" class="flat" type="text"></td>

                      <td width="10%"> <img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('auteur',document.myform.txtScenar.value,'myform','txtScenar',true)" height="14" width="24"></td>

                      <td width="3%"><input name="txtScenarAltId" value="" size="5" type="hidden"></td>

                      <td width="25%"><input name="txtScenarAlt" value="{SCENARISTEALT}" size="40" class="flat" type="text"></td>

                      <td width="17%"><img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('auteur',document.myform.txtScenarAlt.value,'myform','txtScenarAlt',true)" height="14" width="24">

                      </td>

                    </tr>

                    <tr bordercolor="#DAC1B3">

                      <td bordercolor="#DAC1B3" class="label"><font color="#990000" size="-1"><strong>Dessinateur(s)</strong></font></td>

                      <td> <input name="txtDessiId" value="" size="5" type="hidden"></td>

                      <td bordercolor="#DAC1B3"> <input name="txtDessi" value="{DESSINATEUR}" size="40" class="flat" type="text"></td>

                      <td bordercolor="#DAC1B3"> <img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('auteur',document.myform.txtDessi.value,'myform','txtDessi',true)" height="14" width="24"></td>

                      <td bordercolor="#DAC1B3"><input name="txtDessiAltId" value="" size="5" type="hidden" /></td>

                      <td bordercolor="#DAC1B3"><input name="txtDessiAlt" value="{DESSINATEURALT}" size="40" class="flat" type="text" /></td>

                      <td bordercolor="#DAC1B3"><img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('auteur',document.myform.txtDessiAlt.value,'myform','txtDessiAlt',true)" height="14" width="24" /></td>

                    </tr>

                    <tr bordercolor="#DAC1B3">
                    	<td bordercolor="#DAC1B3" class="label"><font color="#990000" size="-1"><strong>Coloriste(s)</strong></font></td>
                    	<td><input name="txtColorId" value="" size="5" type="hidden" /></td>
                    	<td><input name="txtColor" value="{COLORISTE}" size="40" class="flat" type="text" /></td>
                    	<td><img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('auteur',document.myform.txtColor.value,'myform','txtColor',true)" height="14" width="24" /></td>
						<td bordercolor="#DAC1B3"><input name="txtColoAltId" value="" size="5" type="hidden" /></td>
	                    <td bordercolor="#DAC1B3"><input name="txtColoAlt" value="{DESSINATEURALT}" size="40" class="flat" type="text" /></td>
	                    <td bordercolor="#DAC1B3"><img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('auteur',document.myform.txtColoAlt.value,'myform','txtColoAlt',true)" height="14" width="24"></td>
    				</tr>
    				
					<tr bordercolor="#DAC1B3">
						<td>&nbsp;</td>
						<td>&nbsp;</td>
	                	<td>&nbsp;</td>
						<td>&nbsp;</td>
	                	<td>&nbsp;</td>
                    	<td><div class="texte" align="center">Champs ci-dessus optionnels<br />(utiliser en cas d'auteurs multiples)</div></td>
                    	<td>&nbsp;</td>
                    </tr>

                  </tbody>

                </table>

			<br>

			<table width="100%" border="1" cellpadding="5" bordercolor="#FFFFCC" bgcolor="#DAC1B3"  frame="box" rules="groups">

                  <tbody>

                    <tr bordercolor="#DAC1B3">

                      <td width="15%" class="label"> <font color="#990000" size="-1"><strong>Editeur</strong></font></td>

                      <td width="1%" bordercolor="#DAC1B3"> <input name="txtEditeurId" value="" size="7" type="hidden"></td>

                      <td width="28%" bordercolor="#DAC1B3"> <input name="txtEditeur" value="{EDITEUR}" size="40" class="flat" type="text"></td>

                      <td width="39%" bordercolor="#DAC1B3"> <img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('editeur',document.myform.txtEditeur.value,'myform','txtEditeur',true)" height="14" width="24"></td>

                      <td width="17%" bordercolor="#DAC1B3">&nbsp;</td>

                    </tr>

                    <tr bordercolor="#DAC1B3">

                      <td class="label"> <font color="#990000" size="-1"><strong>Collection</strong></font></td>

                      <td bordercolor="#DAC1B3"> <input name="txtCollecId" value="" size="7" type="hidden"></td>

                      <td bordercolor="#DAC1B3"> <input name="txtCollec" value="{COLLECTION}" size="40" class="flat" type="text"></td>

                      <td bordercolor="#DAC1B3"> <img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('collection',document.myform.txtCollec.value,'myform','txtCollec',false,document.myform.txtEditeurId.value)" height="14" width="24"></td>

                      <td bordercolor="#DAC1B3">&nbsp;</td>

                    </tr>

                    <tr bordercolor="#DAC1B3">

                      <td class="label"> <font color="#990000" size="-1"><strong>Date de sortie</strong> <span class="texte">(aaaa-mm-jj)</span></font></td>

                      <td>&nbsp; </td>

                      <td colspan="3"> <input name="txtDateParution" id="txtDateParution3" value="{DTPAR}" size="15" class="flat" type="text">

                        <span class="texte"><u>BDovore NE gère PAS les réimpressions</u>.<br />Assurez-vous que la date de l'album que vous proposez correspond à une édition originale ou à une réédition (couverture ou contenu différent), PAS à une réimpression (même contenu que la dernière édition)</span></td>

                      <td>&nbsp;</td>

                      <td>&nbsp;</td>

                    </tr>

                  </tbody>

                </table>

			<br>

			<table width="100%" border="1" cellpadding="5" bordercolor="#FFFFCC" bgcolor="#DAC1B3"  frame="box" rules="groups">

                  <tbody>

                    <tr bordercolor="#DAC1B3">

                      <td bordercolor="#DAC1B3" class="label"><font color="#990000" size="-1"><strong>Couverture</strong></font></td>

                      <td> </td>

                      <td></td>

                    </tr>

                    <tr bordercolor="#DAC1B3">

                      <td width="28%" class="label"><font size="-1">Envoyer depuis

                        un fichier local</font></td>

                      <td width="16%"> <input name="txtFileLoc" id="txtFileLoc" size="25" class="flat" type="file"></td>

                      <td width="56%">&nbsp;</td>

                    </tr>

                    <tr bordercolor="#DAC1B3">

                      <td class="label"><font size="-1">Envoyer depuis une adresse URL</font></td>

                      <td> <input name="txtFileURL" id="txtFileURL" size="40" class="flat" type="text"></td>

                      <td>&nbsp;</td>

                    </tr>

                  </tbody>

                </table>

			<br>

			<table width="100%" border="1" cellpadding="5" bordercolor="#FFFFCC" bgcolor="#DAC1B3"  frame="box" rules="groups">

			  <tbody>

				<tr>

					<td bordercolor="#DAC1B3" class="label"><font color="#990000" size="-1"><strong>Synopsis</strong></font><span class="texte">Utiliser ce champ pour indiquer le résumé de l'album. <u>Indiquez votre source !</u>(votre pseudo ou le nom de l'éditeur uniquement).<br />
						Vous pouvez également utiliser ce champ pour <u>laisser des commentaires pour les correcteurs</u></span><br /> <br />

                    	<textarea name="txtHistoire" cols="60" rows="8" id="txtHistoire">{HISTOIRE}</textarea>

					<p> </p></td>

				</tr>

			  </tbody>

			</table>

			<br>

			<table width="100%" border="1" cellpadding="5" bordercolor="#FFFFCC" bgcolor="#DAC1B3"  frame="box" rules="groups">

			  <tbody>

				<tr>

				  <td bordercolor="#DAC1B3" class="label"><font size="-1">Après ajout

					dans la base</font>

					<select name="cmbAction" size="1" id="cmbAction">

					 {OPTIONS}

					</select> </td>

				</tr>

				<tr>

				  <td bordercolor="#DAC1B3" class="label">

					<input name="chkNotEmail" value="checked" type="checkbox">

					<font size="-1">me notifier par email lorsque cet album est inclu

					dans la base</font> </td>

				</tr>

			  </tbody>

			</table>

			<p> </p>

			<div align="center">

			  <p></p>

			  <input name="Submit" value="{ACTIONNAME}" type="submit">

			  <br>

			</div></td>

		</tr>

	  </tbody>

	</table>

	</div>

</form>

