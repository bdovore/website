<SCRIPT LANGUAGE="JavaScript">
window.moveTo(0,0);
if (document.getElementById || document.all) {
    window.resizeTo(screen.availWidth, screen.availHeight);
}else if (document.layers) {
    if (window.outerHeight<screen.availHeight || window.outerWidth<screen.availWidth) {
        window.outerHeight = screen.availHeight;
        window.outerWidth = screen.availWidth;
    }
}
</SCRIPT>


<SCRIPT LANGUAGE="JavaScript"><!--
function newWindow(myCategory,myText,myForm,myElement,isMandatory,myEditeur) {
	if ((isMandatory == true) && (myText.length < 2)) {
		alert('Veuillez saisir au moins 2 caractères');
	}
	else {
		if (myCategory == "collection") {
			if (myEditeur == '') {
				alert("Veuillez saisir l'éditeur en premier");
			}
			else {
			mywindow=open("{URLSITE}admin/pickup.php?cat=" + myCategory + "&text=" + myText + "&ReturnDoc=" + myForm + "&ReturnElement=" + myElement + "&IdEditeur=" + myEditeur,'myname','scrollbars=yes,resizable=yes,width=350,height=270');
			}
		}
		else {
			mywindow=open('{URLSITE}admin/pickup.php?cat=' + myCategory + "&text=" + myText + "&ReturnDoc=" + myForm + "&ReturnElement=" + myElement,'myname','scrollbars=yes,resizable=yes,width=350,height=270');
		}
	}
}
//--></SCRIPT>

<div class="cadre1">
<h2>Proposition de correction</h2>

<form action="{URLACTION}" method="post" enctype="multipart/form-data" name="myform" target="_self" id="myform">
<input name="txtTomeId" type="hidden" id="txtTomeId" value="{IDTOME}"><input name="txtEditionId" type="hidden" id="txtEditionId" value="{IDEDITION}">
<table width="100%" border="0">
  <tr>
      <td> <table width="100%" align="center" cellpadding="10" cellspacing="10" bgcolor="EED8BF">
          <!--DWLayoutTable-->
          <tbody>
            <tr bordercolor="#FFFFFF">
              <td width="907" rowspan="4" valign="top" bordercolor="#FFFFFF">
              <p>Avant, tout merci de prendre le temps de lire les guides de saisie disponibles :
			  		  <ul type=SQUARE>
			  		  	<li><a href="http://forum.bdovore.com/viewtopic.php?t=352" target="_blank">Tutorial : comment faire une demande d'ajout ou de correction</a>
			  		  	<li><a href="http://forum.bdovore.com/viewtopic.php?t=617" target="_blank">Tutorial : les mangas V.O</a>
			  		  	<li><a href="http://forum.bdovore.com/viewtopic.php?t=392" target="_blank">Tutorial : les BD et Comics V.O</a>
			  		  	<li><a href="http://forum.bdovore.com/viewtopic.php?t=618" target="_blank">Tutorial : les séries francaises traduites en d'autres langues</a>
			  		  	<li><a href="http://forum.bdovore.com/viewtopic.php?t=391" target="_blank">Tutorial : les artbooks</a>
			  		  </ul>
			  			</p>
			  			<p>Nos valideurs et correcteurs travaillent d'arrache pied (et bénévolement) pour que notre base de données soit la plus complète possible, aidez les en renseignant au mieux toutes les informations à votre disposition et en utilisant les loupes !</p>
			<br>
                <table width="100%" border="1" cellpadding="5" cellspacing="0" bordercolor="#FFFFCC" bgcolor="#DAC1B3"   frame="box" rules="groups">
                  <!--DWLayoutTable-->
                  <tbody>
                    <tr bordercolor="#DAC1B3">
                      <td colspan="4" bordercolor="#DAC1B3" class="label"><font color="#990000" size="-1"><strong>Informations
                        sur l'album</strong></font></td>
                    </tr>
                    <tr bordercolor="#DAC1B3">
                      <td width="81" bordercolor="#DAC1B3" class="label"><font color="#990000" size="-1"><strong>Titre</strong></font></td>
                      <td> <div align="left"> </div></td>
                      <td> <input name="txtTitre" id="txtTitre" value="{TITRE}" size="50" class="flat" type="text"></td>
                      <td width="10%"> </td>
                    </tr>
                    <tr bordercolor="#DAC1B3">
                      <td colspan="4" bordercolor="#DAC1B3" class="label"> <table width="100%" border="0">
                          <tr>
                            <td width="25%">&nbsp;</td>
                            <td width="25%" align="center" class="label"><font color="#990000" size="-1"><strong>Int&eacute;grale</strong></font></td>
                            <td width="25%" align="center" class="lable"><font color="#990000" size="-1"><strong>Type</strong></font></td>
                            <td width="25%">&nbsp;</td>
                          </tr>
                          <tr>
                            <td>&nbsp;</td>
                            <td class="data" align="center"><input type="checkbox" name="chkIntegrale" value="checkbox" {ISINT}></td>
                            <td class="data" align="center"><select name="lstType" size="1">{OPTTYPE}
                              </select></td>
                            <td>&nbsp;</td>
                          </tr>
                        </table></td>
                    </tr>
                    <tr bordercolor="#DAC1B3">
                      <td bordercolor="#DAC1B3" class="label"><font color="#990000" size="-1"><strong>Tome</strong></font></td>
                      <td> <div align="left"> </div></td>
                      <td colspan="2"> <input name="txtNumTome" id="txtNumTome" value="{TOME}" size="2" class="flat" type="text"></td>
                    </tr>
                    <tr bordercolor="#DAC1B3">
                      <td bordercolor="#DAC1B3" class="label"><font color="#990000" size="-1"><strong>Synopsis</strong></font></td>
                      <td><!--DWLayoutEmptyCell-->&nbsp;</td>
                      <td colspan="2"><textarea name="txtHistoire" cols="60" rows="8" id="txtHistoire">{HISTOIRE}</textarea></td>
                    </tr>
                  </tbody>
                </table>

                <br>

                <table width="100%" border="1" cellpadding="5" bordercolor="#FFFFCC" bgcolor="#DAC1B3" frame="box" rules="groups">

                  <tbody>

                    <tr bordercolor="#DAC1B3">

                      <td colspan="4" bordercolor="#DAC1B3" class="label"><font color="#990000" size="-1"><strong>Informations

                        sur la série</strong></font><br>

                        <font size="-2">(Les modifications s'appliqueront &agrave; tous les albums

                        de la s&eacute;rie)</font></td>

                    </tr>

					<tr bordercolor="#DAC1B3">

                      <td bordercolor="#DAC1B3" class="label"><font color="#990000" size="-1"><strong>Serie</strong></font></td>

                      <td width="39"> <div align="left">

                          <input name="txtSerieId" id="txtSerieId" value="{IDSERIE}" type="hidden">

                        </div></td>

                      <td width="629"> <input name="txtSerie" id="txtSerie" value="{SERIE}" size="50" class="flat" type="text"></td>

                      <td><img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('serie',document.myform.txtSerie.value,'myform','txtSerie',true)" height="14" width="24"></td>

                    </tr>

                    <tr bordercolor="#DAC1B3">

                      <td width="8%" bordercolor="#DAC1B3" class="label"><font color="#990000" size="-1"><strong>Genre</strong></font></td>

                      <td width="6%"> <input name="txtGenreId" type="hidden" value="{IDGENRE}" size="3" class="flat"></td>

                      <td width="73%"> <input name="txtGenre" value="{GENRE}" size="30" class="flat" type="text"></td>

                      <td width="10%"> <img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('genre',document.myform.txtGenre.value,'myform','txtGenre',false)" height="14" width="24"></td>

                    </tr>

                    <tr bordercolor="#DAC1B3">

                      <td bordercolor="#DAC1B3" class="label"><font color="#990000" size="-1"><strong>Achevée</strong></font></td>

                      <td>&nbsp;</td>

                      <td><select name="lstAchevee" size="1">

                          {OPTSTATUS}

                        </select></td>

                      <td>&nbsp;</td>

                    </tr>

                  </tbody>

                </table>

                <br>

				 <table width="100%" border="1" cellpadding="5" cellspacing="0" bordercolor="#FFFFCC" bgcolor="#DAC1B3"  frame="box" rules="groups">

                  <tbody>

                    <tr bordercolor="#DAC1B3">

                      <td colspan="4" bordercolor="#DAC1B3" class="label"><font color="#990000" size="-1"><strong>Informations

                        sur les auteurs</strong></font><br> <font size="-2">(Vous

                        pouvez proposer deux scénaristes / dessinateurs par tome)</font></td>

                    </tr>

                    <tr bordercolor="#DAC1B3">

                      <td width="18%" bordercolor="#DAC1B3" class="label"><font color="#990000" size="-1"><strong>Scenariste(s)</strong></font></td>

                      <td width="10%"> <input name="txtScenarId" value="{IDSCEN}" size="5" type="hidden"></td>

                      <td width="59%"> <input name="txtScenar" value="{SCENARISTE}" size="40" class="flat" type="text"></td>

                      <td width="10%"> <img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('auteur',document.myform.txtScenar.value,'myform','txtScenar',true)" height="14" width="24"></td>

                    </tr>

                    <tr bordercolor="#DAC1B3">

                      <td bordercolor="#DAC1B3" class="label">&nbsp;</td>

                      <td> <input name="txtScenarAltId" value="{IDSCENALT}" size="5" type="hidden"></td>

                      <td> <input name="txtScenarAlt" value="{SCENARISTEALT}" size="40" class="flat" type="text"></td>

                      <td> <img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('auteur',document.myform.txtScenarAlt.value,'myform','txtScenarAlt',true)" height="14" width="24"></td>

                    </tr>

                    <tr bordercolor="#DAC1B3">

                      <td bordercolor="#DAC1B3" class="label"><font color="#990000" size="-1"><strong>Dessinateur(s)</strong></font></td>

                      <td> <input name="txtDessiId" value="{IDDESS}" size="5" type="hidden"></td>

                      <td bordercolor="#DAC1B3"> <input name="txtDessi" value="{DESSINATEUR}" size="40" class="flat" type="text"></td>

                      <td bordercolor="#DAC1B3"> <img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('auteur',document.myform.txtDessi.value,'myform','txtDessi',true)" height="14" width="24"></td>

                    </tr>

                    <tr bordercolor="#DAC1B3">

                      <td bordercolor="#DAC1B3" class="label"><font color="#990000" size="-1">&nbsp;</font></td>

                      <td> <input name="txtDessiAltId" value="{IDDESSALT}" size="5" type="hidden"></td>

                      <td bordercolor="#DAC1B3"> <input name="txtDessiAlt" value="{DESSINATEURALT}" size="40" class="flat" type="text"></td>

                      <td bordercolor="#DAC1B3"> <img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('auteur',document.myform.txtDessiAlt.value,'myform','txtDessiAlt',true)" height="14" width="24"></td>

                    </tr>

                    <tr bordercolor="#DAC1B3">

                      <td bordercolor="#DAC1B3" class="label"><font color="#990000" size="-1"><strong>Coloriste</strong></font></td>

                      <td> <input name="txtColorId" value="{IDCOLOR}" size="5" type="hidden"></td>

                      <td> <input name="txtColor" value="{COLORISTE}" size="40" class="flat" type="text">

                      </td>

                      <td> <img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('auteur',document.myform.txtColor.value,'myform','txtColor',true)" height="14" width="24"></td>

                    </tr>

                  </tbody>

                </table>

                <br>

                <table width="100%" border="1" cellpadding="5" bordercolor="#FFFFCC" bgcolor="#DAC1B3"  frame="box" rules="groups">

                  <tbody>

                    <tr bordercolor="#DAC1B3">

                      <td colspan="4" class="label"><font color="#990000" size="-1"><strong>Informations

                        sur l'&eacute;dition</strong></font></td>

                    </tr>

                    <tr bordercolor="#DAC1B3">

                      <td width="26%" class="label"> <font color="#990000" size="-1"><strong>Editeur</strong></font></td>

                      <td width="2%" bordercolor="#DAC1B3"> <input name="txtEditeurId" value="{IDEDIT}" size="7" type="hidden"></td>

                      <td width="59%" bordercolor="#DAC1B3"> <input name="txtEditeur" value="{EDITEUR}" size="40" class="flat" type="text"></td>

                      <td width="10%" bordercolor="#DAC1B3"> <img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('editeur',document.myform.txtEditeur.value,'myform','txtEditeur',true)" height="14" width="24"></td>

                    </tr>

                    <tr bordercolor="#DAC1B3">

                      <td class="label"> <font color="#990000" size="-1"><strong>Collection</strong></font></td>

                      <td bordercolor="#DAC1B3"> <input name="txtCollecId" value="{IDCOLLEC}" size="7" type="hidden"></td>

                      <td bordercolor="#DAC1B3"> <input name="txtCollec" value="{COLLECTION}" size="40" class="flat" type="text"></td>

                      <td bordercolor="#DAC1B3"> <img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('collection',document.myform.txtCollec.value,'myform','txtCollec',false,document.myform.txtEditeurId.value)" height="14" width="24"></td>

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

                    <tr bordercolor="#DAC1B3">

                      <td class="label"> <font color="#990000" size="-1"><strong>Date de sortie</strong> <font color="#000000" size="-1">(aaaa-mm-jj)</font></font></td>

                      <td>&nbsp; </td>

                      <td> <input name="txtDateParution" id="txtDateParution3" value="{DTPAR}" size="15" class="flat" type="text">

                        <span class="texte"><u>BDovore NE gère PAS les réimpressions</u>.<br />Assurez-vous que la date de l'album que vous proposez correspond à une édition originale ou à une réédition (couverture ou contenu différent), PAS à une réimpression (même contenu que la dernière édition)</span> </td>

                      <td>&nbsp;</td>

                    </tr>

                    <!-- BEGIN EditionBlock -->

                    <!-- <tr bordercolor="#DAC1B3">

                      <td class="label"><font color="#990000" size="-1"><strong>Edition</strong></font></td>

                      <td colspan="3"><font size="-1">Cet album comporte plusieurs

                        éditions. Veuillez selectionner celle qui doit faire l'objet

                        d'une correction (celle que vous poss&eacute;dez est selectionn&eacute;e

                        par d&eacute;faut).</font></td>

                    </tr>

                    <tr bordercolor="#DAC1B3">

                      <td class="label">&nbsp;</td>

                      <td colspan="3"><select name="lstEdition" size="1" onChange="test_value()">{OPTEDITION}

                      </select></td>

                    </tr>

                    <tr bordercolor="#DAC1B3">

                      <td class="label">&nbsp;</td>

                      <td colspan="3"><font size="-2">Les corrections relatives

                        &agrave; la date d'&eacute;dition ou &agrave; la couverture

                        ne peuvent s'appliquer qu'&agrave; un album &agrave; la

                        fois.</font></td>

                    </tr> -->

					<!-- END EditionBlock -->

                  </tbody>

                </table>

                <p> </p>

                <div align="center">

                  <p></p>

                  <input name="Submit2" value="{ACTIONNAME}" type="submit">

                  <br>

                </div></td>

            </tr>

          </tbody>

        </table>

        <p>

        </td>

    <td valign="top">

	<table width="100%" align="center" cellpadding="10" cellspacing="10" bgcolor="EED8BF">

	<tr>

	<td>

	<table width="100%" border="1" cellpadding="5" bordercolor="#FFFFCC" bgcolor="#DAC1B3" frame="box" rules="groups">

          <tbody>

            <tr bordercolor="#DAC1B3">

              <td bordercolor="#DAC1B3"> <input name="txtImgCouv" type="hidden" value="{IMGCOUV}">

              </td>

            </tr>

            <tr bordercolor="#DAC1B3">

              <td bordercolor="#DAC1B3"><table width="100%" border="1" cellpadding="5" bordercolor="#FFFFCC" bgcolor="#DAC1B3"  frame="box" rules="groups">

                  <tbody>

                    <tr bordercolor="#DAC1B3">

                      <td bordercolor="#DAC1B3" class="label"><font color="#990000" size="-1"><strong>Couverture</strong></font> <div class="texte">(180px de large si possible)</div></td>

                            <td><img src="{URLIMAGE}" width="169" height="220" border="0" align="middle">

                            </td>

                    </tr>

                    <tr bordercolor="#DAC1B3">

                      <td width="48%" class="label"><font size="-1">Envoyer depuis

                        un fichier local</font></td>

                      <td width="52%"> <input name="txtFileLoc" id="txtFileLoc" size="25" class="flat" type="file"></td>

                    </tr>

                    <tr bordercolor="#DAC1B3">

                      <td class="label"><font size="-1">Envoyer depuis une URL</font></td>

                      <td> <input name="txtFileURL" id="txtFileURL" size="40" class="flat" type="text"></td>

                    </tr>

                  </tbody>

                </table></td>

            </tr>

            <tr> </tr>

          </tbody>

        </table>

		<br>

		<table width="100%" border="1" cellpadding="5" bordercolor="#FFFFCC" bgcolor="#DAC1B3"  frame="box" rules="groups">

			  <tbody>

				<tr>

				    <td bordercolor="#DAC1B3" class="label"><font color="#990000" size="-1"><strong>Ajoutez

                      ici votre commentaire sur votre correction</strong></font><br><font size="-2">(Vous

                        pouvez également utiliser ce champs pour signaler un doublon ou demander un effacement)</font><br> <br>

                      <textarea name="txtCommentaire" cols="60" rows="8" id="textarea"></textarea>

					<p> </p></td>

				</tr>

			  </tbody>

			</table>

			</td></tr></table>

	   </td>

  </tr>

</table>

</form>



</div>