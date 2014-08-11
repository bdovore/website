<html>
<head>
<title>News</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>

<body>
<form name="form1" method="post" action="{ACTION}">
  <table width="61%" border="0" cellpadding="15" bgcolor="#99CCFF">
    <tr> 
      <td bordercolor="#FFFFFF">&nbsp;</td>
      <td>News post&eacute;e par <strong>{AUTEUR}</strong></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
        <td bordercolor="#FFFFFF">&nbsp;</td>
        <td>
            <select name="cmblevel" size="1">{LEVEL}</select>
        </td>
        <td>&nbsp;</td>
    </tr>
    <tr> 
      <td width="12%" bordercolor="#FFFFFF">&nbsp;</td>
      <td width="88%"><input name="txttitre" type="text" id="txttitre" value="{TITRE}" size="50"></td>
      <td width="88%">&nbsp;</td>
    </tr>
    <tr> 
      <td height="170" bordercolor="#FFFFFF">&nbsp;</td>
      <td><textarea name="txtcontent" cols="45" rows="8" id="txtcontent">{NEWSCONTENT}</textarea></td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td bordercolor="#FFFFFF">&nbsp;</td>
      <td><div align="center"> 
          <input type="submit" name="Submit" value="{BUTTON}">
        </div></td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td height="51" bordercolor="#FFFFFF">&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
  </table>
</form>
</body>
</html>
