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
			mywindow=open("{URLSITE}membres/pickup.php?cat=" + myCategory + "&text=" + myText + "&ReturnDoc=" + myForm + "&ReturnElement=" + myElement + "&IdEditeur=" + myEditeur,'myname','scrollbars=yes,resizable=yes,width=550,height=300');
			}
		}
		else {
			mywindow=open('{URLSITE}membres/pickup.php?cat=' + myCategory + "&text=" + myText + "&ReturnDoc=" + myForm + "&ReturnElement=" + myElement,'myname','scrollbars=yes,resizable=yes,width=550,height=300');
		}
	}
}
//--></SCRIPT>

{MENUCOLL}
<div class="cadre1"> <h3>Les albums de mon carr&eacute; magique</h3>
Choisissez dans la liste ci-dessous les 9 albums que vous souhaitez voir
apparaître dans votre carr&eacute; magique puis cliquez sur "Enregistrer".<br />
<span class="texte">(Entrez simplement les ID-BDovores des albums si vous les connaissez
sinon faites une recherche sur le titre de l'album grâce au champ de droite et à la loupe)</span><br /><br />
  <table width="100%" border="0">
          <tr>
         	<td width="5%">ID-BDovore</td>
            <td width="26%"><center>Titre de l'album</center></td>
          </tr>

	  <form action="{URLACTION}" method="post" name="myform" target="_self" id="myform">
          <!-- BEGIN DetailBlock -->
         <tr>
            <td width="5%"><input name="txtAlbum{ID}Id" type="text" class="flat" value="{TOMEID}" size="6" maxlength="6"></td>
            <td width="26%"><input name="txtAlbum{ID}" value="{TITRE}" size="40" class="flat" type="text"></td>
            <td width="69%"><img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('album',document.myform.txtAlbum{ID}.value,'myform','txtAlbum{ID}',true)" height="14" width="24"></td>
          </tr>
          <!-- END DetailBlock -->
          <tr>
            <td colspan="2" align="center"><input name="Submit2" value="{ACTIONNAME}" type="submit"></td>
            <td width="69%">&nbsp;</td>
          </tr>
        </table>
      </form>
</div>

