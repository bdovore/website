<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Document sans titre</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<script type="text/javascript">
function valider(){
  // si la valeur du champ prenom est non vide
  if(document.forms['input_form'].elements['txtGenre'].value != "") {
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
      <td width="65%"><table width="100%" border="1" bgcolor="#FFFFCC">
          <tr bordercolor="#FFFFCC">
            <td width="25%" height="27">Genre</td>
            <td width="75%"><input name="txtIdGenre" type="hidden" id="txtIdGenre" value="{IDGENRE}">
              <input name="txtGenre" type="text" id="txtGenre" value="{GENRE}" size="38"></td>
          </tr>
          <tr bordercolor="#FFFFCC">
            <td colspan="2"> <input type="submit" name="Submit" value="{ACTIONNAME}"></td>
          </tr>
        </table></td>
      <td width="35%" valign="top" bgcolor="#FFFFCC"> <p><strong>Statistiques</strong></p>
        <p>Ce genre est applicable &agrave; {NBSERIES} s&eacute;rie(s).</p>
        <p><strong>Menu Express</strong></p>
        <p><a href="{URLDELETE}">Supprimer ce genre</a><br>
          <a href="{URLFUSION}">Fusionner ce genre</a></p>
    </td>
    </tr>
  </table>


</form>
</body>
</html>
