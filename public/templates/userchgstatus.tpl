<html>
<head>
<title>Document sans titre</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>

<body>
<form name="form1" method="post" action="../admin/usermanagement.php?act=viewstatus&status=ok&userid={USERID}">
  <table width="75%" border="1" cellspacing="0" cellpadding="5">
    <tr>
      <td width="45%"><div align="right"><font size="2" face="Arial, Helvetica, sans-serif">Status
          courant de {UTILISATEUR} :</font></div></td>
      <td width="55%"><font size="2" face="Arial, Helvetica, sans-serif">{CURRENTSTATUS}</font></td>
    </tr>
    <tr>
      <td><div align="right"><font size="2" face="Arial, Helvetica, sans-serif">Nouveau
          status :</font></div></td>
      <td><font size="2" face="Arial, Helvetica, sans-serif">
        <select name="cmblevel" size="1" id="cmblevel">
            {OPTIONS}
        </select>
        </font></td>
    </tr>
    <tr>
      <td><div align="right"><font size="2" face="Arial, Helvetica, sans-serif">Pr&eacute;venir
          l'utilisateur par e-mail:</font></div></td>
      <td><font size="2" face="Arial, Helvetica, sans-serif">
        <select name="cmbemail" size="1" id="cmbemail">
          <option value="1">Oui</option>
          <option value="0" selected>Non</option>
        </select>
        </font></td>
    </tr>
    <tr>
      <td colspan="2"><div align="center">
          <input type="submit" name="Submit" value="Envoyer">
        </div></td>
    </tr>
  </table>
</form>
</body>
</html>
