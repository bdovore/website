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
			mywindow=open("{URLSITE}admin/pickup.php?cat=" + myCategory + "&text=" + myText + "&ReturnDoc=" + myForm + "&ReturnElement=" + myElement + "&IdEditeur=" + myEditeur,'myname','scrollbars=yes,resizable=yes,width=350,height=270');
			}
		}
		else {
			mywindow=open('{URLSITE}admin/pickup.php?cat=' + myCategory + "&text=" + myText + "&ReturnDoc=" + myForm + "&ReturnElement=" + myElement,'myname','scrollbars=yes,resizable=yes,width=350,height=270');
		}
	}
}
//--></SCRIPT>
{MENUCOLL}

<div class="cadre1">
 <h3>Mes auteurs pr&eacute;f&eacute;r&eacute;s</h3>
      Choisissez dans la liste cidessous jusqu'&agrave; 20 auteurs pour lesquels 
      vous souhaitez &ecirc;tre inform&eacute;s de leurs nouvelles productions 
      via le panneau mon actualit&eacute;.<br> <form action="{URLACTION}" method="post" name="myform" target="_self" id="myform">
        <table width="100%" border="0">
          <!-- BEGIN DetailBlock -->
          <tr> 
            <td width="6%"><input name="txtAuteur{ID}Id" value="{IDAUTEUR}" size="5" type="text" class="flat"></td>
            <td width="26%"><input name="txtAuteur{ID}" value="{AUTEUR}" size="40" class="flat" type="text"></td>
            <td width="68%"><img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('auteur',document.myform.txtAuteur{ID}.value,'myform','txtAuteur{ID}',true)" height="14" width="24"></td>
          </tr>
          <!-- END DetailBlock -->
          <tr> 
            <td colspan="2" align="center"><input name="Submit2" value="{ACTIONNAME}" type="submit"></td>
            <td width="68%">&nbsp;</td>
          </tr>
        </table>
		
      </form>
</div>
