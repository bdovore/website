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
  if((document.forms['input_form'].elements['txtNomSerie'].value != "") && (document.forms['input_form'].elements['txtGenreId'].value != "")) {
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
  <table width="100%" border="1" bgcolor="#FFFFCC">
    <tr bordercolor="#FFFFCC">
      <td width="7%" height="27">Nom</td>
      <td width="93%">
        <input name="txtNomSerie" type="text" size="43"></td>
    </tr>
    <tr bordercolor="#FFFFCC">
      <td height="28">Genre</td>
      <td>
        <input name="txtGenreId" type="text" id="txtGenreId" size="2">
        <input name="txtGenre" type="text" id="txtGenre" size="40">
        <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('genre',document.input_form.txtGenre.value,'input_form','txtGenre',false)"></td>
    </tr>
    <tr bordercolor="#FFFFCC">
      <td colspan="2">
        <input type="submit" name="Submit" value="Envoyer"></td>
    </tr>
  </table>
</form>
</body>
</html>
