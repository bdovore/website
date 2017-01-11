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

function urlsource(){
	document.forms['form1'].action = "{URLREFRESH}?serie="+document.forms['form1'].elements['txtSerieId'].value;
    //alert("Saisissez les champs requis");
	return true;
}
//--></SCRIPT>
<table width="100%" border="1">
  <tr>
  	<!-- Debut colonne Gauche-->
    <td width="50%" valign="top">
	<form name="form1" method="post" action="" onSubmit="return urlsource()">
	    <table width="100%" border="1" cellpadding="5" cellspacing="0" bordercolor="#333333" bgcolor="#FFFFCC">
          <tr bordercolor="#FFFFCC">
            <td width="10%" class="label"> <div align="center">Serie</div></td>
            <td width="10%"> <div align="left">
                <input name="txtSerieId" type="text" id="txtSerieId" value="{IDSERIE}" size="5">
              </div></td>
            <td width="27%"> <input name="txtSerie" type="text" id="txtSerie" value="{SERIE}" size="40"></td>
            <td width="28%"><img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('serie',document.form1.txtSerie.value,'form1','txtSerie',true)"></td>
            <td width="51">
              <input name="Submit1" type="submit" id="Submit1" value="Rafraichir"></td>
          </tr>
        </table>
</form>
<br>
<form action="{URLACTION}" method="post" enctype="multipart/form-data" name="myform" target="_self" id="myform">
	<table width="100%" border="1" cellpadding="5" bordercolor="#666666" bgcolor="#FFFFCC">
          <tr bordercolor="#FFFFCC">
            <td width="88" class="label">Scenariste</td>
            <td width="37"> <input name="txtScenarId" type="text" size="5"></td>
            <td width="278"> <input name="txtScenar" type="text" size="30"></td>
            <td width="62"> <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('auteur',document.myform.txtScenar.value,'myform','txtScenar',true)"></td>
          </tr>
          <tr bordercolor="#FFFFCC">
            <td class="label">(Optionnel)</td>
            <td><input name="txtScenarAltId" type="text" size="5"></td>
            <td><input name="txtScenarAlt" type="text" size="30"></td>
            <td> <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('auteur',document.myform.txtScenarAlt.value,'myform','txtScenarAlt',true)"></td>
          </tr>
          <tr bordercolor="#FFFFCC">
            <td class="label">Dessinateur</td>
            <td> <input name="txtDessiId" type="text" size="5"></td>
            <td> <input name="txtDessi" type="text" size="30"></td>
            <td> <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('auteur',document.myform.txtDessi.value,'myform','txtDessi',true)"></td>
          </tr>
          <tr bordercolor="#FFFFCC">
            <td class="label">(optionnel)</td>
            <td><input name="txtDessiAltId" type="text" size="5"></td>
            <td><input name="txtDessiAlt" type="text" size="30"></td>
            <td> <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('auteur',document.myform.txtDessiAlt.value,'myform','txtDessiAlt',true)"></td>
          </tr>
          <tr bordercolor="#FFFFCC">
            <td class="label">Coloriste</td>
            <td> <input name="txtColorId" type="text" size="5"></td>
            <td> <input name="txtColor" type="text" size="30"></td>
            <td> <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('auteur',document.myform.txtColor.value,'myform','txtColor',true)"></td>
          </tr>
          <tr bordercolor="#FFFFCC">
            <td class="label">(Optionnel)</td>
            <td><input name="txtColorAltId" type="text" size="5"></td>
            <td><input name="txtColorAlt" type="text" size="30"></td>
            <td> <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('auteur',document.myform.txtColorAlt.value,'myform','txtColorAlt',true)"></td>
          </tr>
        </table>
		<br>
	<table width="100%" border="1" cellpadding="5" bordercolor="#666666" bgcolor="#FFFFCC">
          <tr bordercolor="#FFFFCC">
            <td width="16%" class="label"> <div align="center">Editeur</div></td>
            <td width="12%"> <input name="txtEditeurId" type="text" size="7"></td>
            <td width="58%"> <input name="txtEditeur" type="text" size="30"></td>
            <td width="14%"> <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('editeur',document.myform.txtEditeur.value,'myform','txtEditeur',true)"></td>
          </tr>
          <tr bordercolor="#FFFFCC">
            <td class="label"> <div align="center">Collection</div></td>
            <td> <input name="txtCollecId" type="text" size="7"></td>
            <td> <input name="txtCollec" type="text" size="30"></td>
            <td> <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('collection',document.myform.txtCollec.value,'myform','txtCollec',false,document.myform.txtEditeurId.value)"></td>
          </tr>
        </table>
        <p>
        <div align="center"></p>
          <input type="submit" name="Submit" value="{ACTIONNAME}">
          <br>
        </div>
		</form>
	</td>
	<!-- Debut colonne Gauche-->
    <td width="50%">
<table width="100%" border="1">
  <tr>
    <td bordercolor="#FFFFCC" bgcolor="#FFFFCC"><p><strong>Sc&eacute;naristes
          principaux</strong><br>
          <!-- BEGIN Scen1Block -->
          <a href="{URLSCENARISTE1}">{IDSCENARISTE1} - {SCENARISTE1}</a> <br>
          <!-- END Scen1Block -->
        </p>
        <p><strong>Sc&eacute;naristes secondaires</strong><br>
          <!-- BEGIN Scen2Block -->
          <a href="{URLSCENARISTE2}">{IDSCENARISTE2} - {SCENARISTE2}</a> <br>
          <!-- END Scen2Block -->
        </p>
        <p><strong>Dessinateurs principaux</strong><br>
          <!-- BEGIN Dess1Block -->
          <a href="{URLDESSINATEUR1}">{IDDESSINATEUR1} - {DESSINATEUR1}</a> <br>
          <!-- END Dess1Block -->
        </p>
        <p><strong>Dessinateurs secondaires</strong><br>
          <!-- BEGIN Dess2Block -->
          <a href="{URLDESSINATEUR2}">{IDDESSINATEUR2} - {DESSINATEUR2}</a> <br>
          <!-- END Dess2Block -->
        </p>
        <p><strong>Coloristes principaux</strong><br>
          <!-- BEGIN Color1Block -->
          <a href="{URLCOLORISTE1}">{IDCOLORISTE1} - {COLORISTE1}</a> <br>
          <!-- END Color1Block -->
        </p>
        <p><strong>Coloristes secondaires</strong><br>
          <!-- BEGIN Color2Block -->
          <a href="{URLCOLORISTE2}">{IDCOLORISTE2} - {COLORISTE2}</a> <br>
          <!-- END Color2Block -->
        </p>
                <p><strong>Editeurs</strong><br>
          <!-- BEGIN EditeurBlock -->
          <a href="{URLEDITEUR}">{IDEDITEUR} - {EDITEUR}</a> <br>
          <!-- END EditeurBlock -->
        </p>
        <p><strong>Collections</strong><br>
          <!-- BEGIN CollectionBlock -->
          <a href="{URLCOLLECTION}">{IDCOLLECTION} - {COLLECTION}</a> <br>
          <!-- END CollectionBlock -->
        </p>
        <p><strong>Menu Express</strong><br>
        <a href="{URLEDITSERIE}">Retour à la fiche série</a><br>
 </p></td>
  </tr>
</table>


	</td>
  </tr>
</table>

