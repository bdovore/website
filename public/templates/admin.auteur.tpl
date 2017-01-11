<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Document sans titre</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<script type="text/javascript">
function valider(){
  // si la valeur du champ prenom est non vide
  if(document.forms['input_form'].elements['txtPseudoAuteur'].value != "") {
    // les données sont ok, on peut envoyer le formulaire
    return true;
  }
  else {
  	// renseigner alors nom et prenom
	if (document.forms['input_form'].elements['txtNomAuteur'].value != "" &&
			document.forms['input_form'].elements['txtPrenomAuteur'].value != "" ) {
    		// sinon on affiche un message
			return true;
	}
	else {
    	alert("Si l'auteur signe sous un pseudonyme, vous devez le saisir, dans le cas contraire saisissez nom et prénom !");
    	// et on indique de ne pas envoyer le formulaire
   		 return false;
	}
  }
}
</script>


<body bgcolor="#FFFFCC" onLoad="{BODYONLOAD}">
<form action="{URLACTION}" method="post" name="input_form" onSubmit="return valider()">
  <table width="100%" border="1">
    <tr>
      <td width="65%"><table width="100%" border="1" bgcolor="#FFFFCC">
          <tr bordercolor="#FFFFCC">
            <td width="25%" height="27">Pseudo</td>
            <td colspan="4"><input name="txtIdAuteur" type="hidden" value="{IDAUTEUR}">
              <input name="txtPseudoAuteur" type="text" id="txtPseudoAuteur" value="{PSEUDO}" size="38"></td>
          </tr>
          <tr bordercolor="#FFFFCC">
            <td height="28">Nom - Pr&eacute;nom</td>
            <td colspan="4">
              <input name="txtNomAuteur" type="text" id="txtNomAuteur" value="{NOM}" size="20">
              ,
              <input name="txtPrenomAuteur" type="text" id="txtPrenomAuteur" value="{PRENOM}" size="20"></td>
          </tr>
          <tr bordercolor="#FFFFCC">
            <td height="28">Date Naissance</td>
            <td colspan="4"><input name="txtDateNaiss" type="text" id="txtDateNaiss" value="{DTNAIS}" size="10"></td>
          </tr>
          <tr bordercolor="#FFFFCC">
            <td height="28">Date d&eacute;c&egrave;s</td>
            <td colspan="4"><input name="txtDateDeces" type="text" id="txtDateDeces" value="{DTDECES}" size="10"></td>
          </tr>
          <tr bordercolor="#FFFFCC">
            <td height="28">Nationalite</td>
            <td colspan="4"><input name="txtNation" type="text" id="txtNation" value="{DTNATION}" size="15"></td>
          </tr>
          <tr bordercolor="#FFFFCC">
            <td height="28">&nbsp;</td>
            <td width="12%"> <div align="center">Sc&eacute;nariste</div></td>
            <td width="16%"> <div align="center">Dessinateur</div></td>
            <td width="13%"> <div align="center">Coloriste</div></td>
            <td width="34%">&nbsp;</td>
          </tr>
          <tr bordercolor="#FFFFCC">
            <td height="28">&nbsp;</td>
            <td> <div align="center">
                <input name="chkScen" type="checkbox" id="chkScen" value="checked" {ISSCENAR}>
              </div></td>
            <td> <div align="center">
                <input name="chkDess" type="checkbox" id="chkDess" value="checked" {ISDESSIN}>
              </div></td>
            <td> <div align="center">
                <input name="chkColor" type="checkbox" id="chkColor" value="checked" {ISCOLOR}>
              </div></td>
            <td>&nbsp;</td>
          </tr>
          <tr bordercolor="#FFFFCC">
            <td height="28">Commentaire</td>
            <td colspan="4"><textarea name="txtCommentaire" cols="40">{COMMENT}</textarea></td>
          </tr>
          <tr bordercolor="#FFFFCC">
            <td colspan="5"> <input type="submit" name="Submit" value="{ACTIONNAME}"></td>
          </tr>
        </table></td>
      <td width="35%" valign="top" bgcolor="#FFFFCC"> <p><strong>Statistiques</strong></p>
        <p>Cet auteur a travaill&eacute; sur {NBALBUMS} albums.</p>
        <p><strong>Menu Express</strong></p>
        <p><a href="{URLDELETE}">Supprimer cet auteur</a><br>
          <a href="{URLFUSION}">Fusionner cet auteur</a></p>
        <p><strong>Aide</strong></p>
        <p>Le pseudo ne doit &ecirc;tre renseign&eacute; que si l'auteur signe
          sous un pseudonyme. Dans le cas contraire, les champs nom et pr&eacute;nom
          sont obligatoires.</p></td>
    </tr>
  </table>


</form>
</body>
</html>
