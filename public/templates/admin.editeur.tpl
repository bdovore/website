<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Document sans titre</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<script type="text/javascript">
function valider(){
  // si la valeur du champ prenom est non vide
  if(document.forms['input_form'].elements['txtNomEditeur'].value != "") {
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
            <td width="18%" height="27">Nom</td>
            <td width="82%"><input name="txtIdEditeur" type="hidden" value="{IDEDITEUR}">
              <input name="txtNomEditeur" type="text" value="{NOM}" size="38"></td>
          </tr>
          <tr bordercolor="#FFFFCC">
            <td height="28">Site WEB</td>
            <td> <input name="txtUrlSite" type="text" id="txtUrlSite" value="{URLWEBSITE}" size="35"></td>
          </tr>
          <tr bordercolor="#FFFFCC">
            <td colspan="2"> <input type="submit" name="Submit" value="{ACTIONNAME}"></td>
          </tr>
        </table></td>
      <td width="35%" valign="top" bgcolor="#FFFFCC"> <p>Nombre de collections
          : {NBCOLLEC}<br>
          <!-- BEGIN ColBlock -->
        <a href="{COL_LIEN}">{COL_TITRE}</a><br>
            <!-- END ColBlock -->
        </p>
        </td>
  </tr>
    <tr bgcolor="#FFFFCC">
      <td>
        </td>
      <td>
        <p><strong>Menu Express</strong></p>
        <p><a href="{URLDELETE}">Supprimer cet editeur</a><br>
        <a href="{URLFUSION}">Fusionner cet editeur</a><br>
          <a href="{URLADDCOLLEC}">Ajouter une collection &agrave; cet &eacute;diteur</a>
        </p>
        </td>
  </tr>
</table>


</form>
</body>
</html>
