<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Document sans titre</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<script type="text/javascript">
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

function valider(){
  // si la valeur du champ prenom est non vide
  if((document.forms['input_form'].elements['txtNomColl'].value != "") && (document.forms['input_form'].elements['txtEditeurId'].value != "")) {
    // les données sont ok, on peut envoyer le formulaire
    return true;
  }
  else {
    // sinon on affiche un message
    alert("Saisissez les champs requis");
    // et on indique de ne pas envoyer le formulaire
    return false;
  }
}
</script>


<body bgcolor="#FFFFCC" onLoad="{BODYONLOAD}">
<form action="{URLACTION}" method="post" name="input_form" onSubmit="return valider()">
  <table width="100%" border="1">
    <tr>
      <td width="65%" rowspan="2"><table width="100%" border="1" bgcolor="#FFFFCC">
          <tr bordercolor="#FFFFCC">
            <td width="18%" height="27">Nom</td>
            <td width="82%"><input name="txtIdColl" type="hidden" id="txtIdColl" value="{IDCOLL}">
              <input name="txtNomColl" type="text" id="txtNomColl" value="{NOM}" size="38"></td>
          </tr>
          <tr bordercolor="#FFFFCC">
            <td height="28">Editeur</td>
            <td> <input name="txtEditeurId" type="text" id="txtEditeurId" value="{IDEDITEUR}" size="2">
              <input name="txtEditeur" type="text" id="txtEditeur" value="{EDITEUR}" size="35">
              <a href="{URLEDITEDIT}"><img src="{URLSITEIMAGE}edit.gif" width="18" height="13" border="0"></a> <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('editeur',document.input_form.txtEditeur.value,'input_form','txtEditeur',true)"></td>
          </tr>
          <tr bordercolor="#FFFFCC">
            <td colspan="2"> <input type="submit" name="Submit" value="{ACTIONNAME}"></td>
          </tr>
        </table></td>
    </tr>
    <tr bgcolor="#FFFFCC">
      <td> <p><strong>Statistiques</strong></p>
        <p>Nombres d'albums dans cette collection : {NBCOLALB}</p>
        <p><strong>Menu Express</strong></p>
        <p><a href="{URLDELETE}">Supprimer cette collection</a><br>
          Fusionner cette collection</p></td>
    </tr>
  </table>


</form>
</body>
</html>
