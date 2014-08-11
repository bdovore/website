<SCRIPT LANGAUGE="JavaScript"><!--
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

function refreshpage()
{
if (document.forms['formRefresh'].elements['txtNewTitreId'].value == "")
	{
	alert ("Saisissez un titre");
	return false;
	}else{
	document.forms['formRefresh'].action = "{REFRESHPAGE}"+"&report_id="+document.forms['formRefresh'].elements['txtNewTitreId'].value;
	return true;
	}
}
//--></SCRIPT>
<table width="100%" border="1" cellpadding="10" cellspacing="10" bordercolor="#333333">
  <tr> 
    <td width="66%">
	<form action="" method="post" name="formRefresh" onsubmit="return refreshpage();">
	<table width="100%" border="1" cellpadding="2" cellspacing="0" bordercolor="#333333" bgcolor="#FFFFCC">
        <!--DWLayoutTable-->
        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC"> 
          <td colspan="5" class="label"><strong>Album sur lequel reporter les 
            &eacute;ditions existantes </strong></td>
        </tr>
        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC"> 
          <td width="46" class="label"> <div align="center">Titre</div></td>
          <td width="47"> <div align="left"> 
                <input name="txtNewTitreId" type="text" id="txtNewTitreId" value="{IDTOME2}" size="5">
            </div></td>
          <td width="246"> <input name="txtNewTitre" type="text" id="txtNewTitre" value="{TITRE2}" size="40"></td>
          <td width="60"><img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('album',document.formRefresh.txtNewTitre.value,'formRefresh','txtNewTitre',true)"></td>
          <td width="145"><input type="submit" name="Refresh" value="Rafraichir"> 
          </tr>
      </table>
</form>
<form action="{URLACTION}" method="post" enctype="multipart/form-data" onsubmit="return confirm('Voulez-vous vraiment continuer ?');" name="myform" target="_self" id="myform">	  
      <br> <table width="100%" border="1" cellpadding="1" cellspacing="0" bordercolor="#333333" bgcolor="#CCCCCC">
        <!--DWLayoutTable-->
        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC"> 
          <td colspan="5" class="label"><strong>Album sur lequel les &eacute;ditions 
            seront report&eacute;es</strong></td>
        </tr>
        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC"> 
          <td width="117" class="label"> <div align="center">Titre :</div></td>
          <td width="17"> <div align="left"> 
              <input name="txtTome2Id" type="hidden" id="txtTome2Id" value="{IDTOME2}">
            </div></td>
          <td width="268">{TITRE2}</td>
          <td width="46">Tome :</td>
          <td width="96">{TOME2}</td>
        </tr>
        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC"> 
          <td valign="top" class="label"><div align="center">Serie :</div></td>
          <td><!--DWLayoutEmptyCell-->&nbsp;</td>
          <td colspan="3">{SERIE2}</td>
        </tr>
        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC"> 
          <td valign="top" class="label"><div align="center">Sc&eacute;nariste(s) 
              :</div></td>
          <td><!--DWLayoutEmptyCell-->&nbsp;</td>
          <td colspan="3">{SCENARISTES2}</td>
        </tr>
        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC"> 
          <td valign="top" class="label"><div align="center">Dessinateur(s) :</div></td>
          <td><!--DWLayoutEmptyCell-->&nbsp;</td>
          <td colspan="3">{DESSINATEURS2}</td>
        </tr>
        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC"> 
          <td valign="top" class="label"><div align="center">Coloriste(s) :</div></td>
          <td><!--DWLayoutEmptyCell-->&nbsp;</td>
          <td colspan="3">{COLORISTES2}</td>
        </tr>
        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC"> 
          <td valign="top" class="label"><div align="center">Editeur / Collec 
              :</div></td>
          <td><!--DWLayoutEmptyCell-->&nbsp;</td>
          <td colspan="3">{EDCOLLEC2}</td>
        </tr>
      </table>
	  <br>

        <table width="100%" border="1" bordercolor="#666666" bgcolor="#CCCCCC">
          <tr> 
            <td> <p><strong>Editions existantes:</strong> 
                {NBEDITIONS2}</p>
              <table width="100%" border="0">
                <tr> 
                  <td width="28%" class="entete">Editeur</td>
                  <td width="23%" class="entete">Collection</td>
                  <td width="14%" class="entete">Dte Parution</td>
                  <td width="16%" class="entete">Couverture</td>
                </tr>
                <!-- BEGIN Edition2Block -->
                <tr> 
                  <td>{EDITEUR2}</td>
                  <td>{COLLECTION2}</td>
                  <td>{DTPAR2}</td>
                  <td align="center"><a href="{URLIMAGE}" target="_blank"><img src="{URLIMAGE2}" width="50" height="60" border="0" align="absmiddle"></a> 
                  </td>
                </tr>
                <!-- END Edition2Block -->
              </table>
              </td>
          </tr>
        </table>
			  
      <br> <table width="100%" border="1" cellpadding="1" cellspacing="0" bordercolor="#333333" bgcolor="#CCCCCC">
        <!--DWLayoutTable-->
        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC"> 
          <td colspan="5" class="label"><strong>Album &agrave; supprimer</strong></td>
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
                  <td class="data"><input type="checkbox" name="chkEdition[]" value="{IDEDITION}" checked></td>
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
      <p>Les éditions cochées seront reportées sur un album existant : les utilisateurs 
        en possession des éditions cochées se retrouvereront avec le nouvel album 
        et l'ancienne édition. <br>
        <br>
        Les utilisateurs en possession d'une édition non cochée se retrouveront 
        avec le nouvel album et avec l'édition par défaut. <br>
        <br>
        Si aucune édition n'est cochée, ce form se comporte comme le form de fusion 
        normal. </p>
      <p><strong>Menu Express</strong></p>
      <p><a href="{URLRETOURFICHE}">Retour à la fiche album</a><br>
      </p></td>
  </tr>
</table>
