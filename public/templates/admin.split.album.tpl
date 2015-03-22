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
<table width="100%" border="1" cellpadding="10" cellspacing="10" bordercolor="#333333">
  <tr> 
    <td width="66%">
<form action="{URLACTION}" method="post" enctype="multipart/form-data" onsubmit="return confirm('Voulez-vous vraiment continuer ?');" name="myform" target="_self" id="myform">
		<table width="100%" border="1" cellpadding="2" cellspacing="0" bordercolor="#333333" bgcolor="#FFFFCC">
        <!--DWLayoutTable-->
        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC"> 
          <td colspan="5" class="label"><strong>Série sur laquelle reporter les éditions sélectionnées </strong></td>
        </tr>
        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC"> 
          <td width="46" class="label"> <div align="center">Série</div></td>
          <td width="47"> <div align="left"> 
                <input name="txtNewSerieId" type="text" id="txtNewSerieID" value="{SERIEID}" size="5">
            </div></td>
          <td width="246"> <input name="txtNewSerie" type="text" id="txtNewSerie" value="{SERIE}" size="40"></td>
          <td width="60"><img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('serie',document.myform.txtNewSerie.value,'myform','txtNewSerie',true)"></td>
          </tr>
      </table>
			  
      <br> <table width="100%" border="1" cellpadding="1" cellspacing="0" bordercolor="#333333" bgcolor="#CCCCCC">
        <!--DWLayoutTable-->
        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC"> 
          <td colspan="5" class="label"><strong>Album &agrave; traiter</strong></td>
        </tr>
        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC"> 
          <td width="117" class="label"> <div align="center">Titre :</div></td>
          <td width="17"> <div align="left"> 
              <input name="txtTomeId" type="hidden" id="txtTomeId" value="{IDTOME}">
            </div></td>
          <td width="268">{TITRE}</td>
          <td width="46">Tome :</td>
          <td width="96">{TOME}</td>
        </tr>
        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC"> 
          <td valign="top" class="label"> <div align="center">Serie :</div></td>
          <td><!--DWLayoutEmptyCell-->&nbsp;</td>
          <td colspan="3">{SERIE}</td>
        </tr>
        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC"> 
          <td valign="top" class="label"> <div align="center">Sc&eacute;nariste(s) 
              :</div></td>
          <td><!--DWLayoutEmptyCell-->&nbsp;</td>
          <td colspan="3">{SCENARISTES}</td>
        </tr>
        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC"> 
          <td valign="top" class="label"> <div align="center">Dessinateur(s) :</div></td>
          <td><!--DWLayoutEmptyCell-->&nbsp;</td>
          <td colspan="3">{DESSINATEURS}</td>
        </tr>
        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC"> 
          <td valign="top" class="label"> <div align="center">Coloriste(s) :</div></td>
          <td><!--DWLayoutEmptyCell-->&nbsp;</td>
          <td colspan="3">{COLORISTES}</td>
        </tr>
        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC"> 
          <td valign="top" class="label"> <div align="center">Editeur / Collec 
              :</div></td>
          <td><!--DWLayoutEmptyCell-->&nbsp;</td>
          <td colspan="3">{EDCOLLEC}</td>
        </tr>
      </table>
      <br> 
        <table width="100%" border="1" bordercolor="#333333" bgcolor="#FFFFCC">
          <tr> 
            <td bordercolor="#FFFFCC" class="label"> <p>Différentes éditions existant 
                sur l'album &agrave; supprimer : {NBEDITIONS}</p>
              <p> 
              <table width="100%" border="0">
                <tr> 
                  <td width="10%" class="entete">Reporter</td>
                  <td width="28%" class="entete">Editeur</td>
                  <td width="23%" class="entete">Collection</td>
                  <td width="14%" class="entete">Dte Parution</td>
                  <td width="16%" class="entete">Couverture</td>
                </tr>
                <!-- BEGIN EditionBlock -->
                <tr> 
                  <td class="data"><input type="checkbox" name="chkEdition[]" value="{IDEDITION}"></td>
                  <td class="data">{EDITEUR}</td>
                  <td class="data">{COLLECTION}</td>
                  <td class="data">{DTPAR}</td>
                  <td align="center" class="data"><a href="{URLIMAGE}" target="_blank"><img src="{URLIMAGE}" width="50" height="60" border="0" align="absmiddle"></a> 
                    <input name="txtCouv[{IDEDITION}]" type="hidden" value="{URLCOUV}"></td>
                </tr>
                <!-- END EditionBlock -->
              </table>
              </p>
              </td>
          </tr>
        </table>
        <p> 
        <div align="center"></p> 
          <input type="submit" name="Submit" value="{ACTIONNAME}">
          <br>
        </div>
      </form></td></td>
    <td width="287" bordercolor="#333333" bgcolor="#FFFFCC" > <p><strong>Aide</strong></p>
      <p>Les éditions cochées seront reportées sur un nouvel album : les utilisateurs 
        en possession des éditions cochées se retrouveront avec le nouvel album 
        dans la nouvelle série. <br>
        <br>
        Aucun changement ne sera enregistrés par les utilisateurs en possession d'une édition non cochée. <br>
        <br>
        L'édition par défaut ne peut pas être sélectionnée.<br>
        <br>
        Si aucune édition n'est cochée, rien ne se passe. </p>
      <p><strong>Menu Express</strong></p>
      <p><a href="{URLRETOURFICHE}">Retour à la fiche album</a><br>
      </p></td>
  </tr>
</table>
