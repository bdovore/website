<h3>{TITREPAGE}</h3>
<form action="{URLACTION}" method="post">
<table width="745" height="57" class="donnees">
  <tr> 
    <td width="127" height="25" class="entete">Genre</td>
    <td width="216" class="entete">Serie</td>
    <td width="229" class="entete">Titre</td>
    <td width="59" class="entete"><div align="center">Tome</div></td>
    <td width="90" class="entete"><div align="center">Oter de la liste</div></td>
  </tr>
  <!-- BEGIN SelBlock -->
  <tr> 
    <td width="127" height="24" class="data">{GENRE}</td>
    <td width="216" class="data">{SERIE}</td>
      <td width="229" class="data"><a href="{URLTITRE}" target="_blank">{TITRE}</a></td>
    <td width="59" class="data"><div align="center">{TOME}</div></td>
    <td width="90" class="data"><div align="center"><input name="AlbSelect[]" type="checkbox" value="{NOUVID}"></div></td>
  </tr>
  <!-- END SelBlock -->
</table>
  <br>
  <table width="70%" border="0">
    <tr> 
      <td width="47%"><span class="texte">{BARRENAVIGATION}</span></td>
      <td width="31%"><a href="{URLRETOURMENU}" class="texte">Retour au menu</a></td>
      <td width="22%"><input type="submit" name="Submit" value="Valider les modifications"></td>
    </tr>
  </table>
</form>

