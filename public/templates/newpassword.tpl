{MENUCOLL}
<div class="cadre1">	
<h2>Changement du mot de passe de <font color="#0000FF">{UTILISATEUR}</font></h2>
<form name="form1" method="post" action="../membres/profil.php?act=newpass&status=ok&user_id={USERID}">
  <table border="0" cellspacing="1" cellpadding="5" align="center">
    <tr> 
      <td align="right">Nouveau mot de passe :</td>
      <td ><input name="txtpass1" type="text" id="txtpass1"></td>
    </tr>
    <tr> 
      <td align="right">Confirmation :</td>
      <td><input name="txtpass2" type="text" id="txtpass2"></td>
    </tr>
    <tr> 
      <td colspan="2" align="center">
          <input type="submit" name="Submit" value="Valider">
          <input type="button" name="return" value="Annuler" onclick="window.location.href='{URLSITE}membres/profil.php'">
        </td>
    </tr>
  </table>
</form>
</div>