<html>
<head>
<title>{WNDTITLE}</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="{URLSITE}style/style1.css" rel="stylesheet" type="text/css" />
</head>

<body>

<form name="form1" method="post" action="{URLACTION}">
<table width="100%" border="1" cellpadding="5" cellspacing="0" bordercolor="#FFFFCC" bgcolor="#DAC1B3"   frame="box" rules="groups">
<tr>
<td><p><strong>Attention:</strong> Vous êtes sur le point de rajouter un album dans votre collection. Si vous ne possédez qu'un album et que vous souhaitez simplement choisir l'édition que vous possédez, retournez à l'écran précédent et choisissez votre édition dans la liste déroulante prévue à cet effet.</p>
<p>Vous ne pouvez rajouter qu'une seule édition à la fois.</p>
</td>
</tr>
<tr>
    <td>
            <table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#000000" frame="border" rules="all">
            <tr bgcolor="#C8857C">
              <td><font color="#990000" size="-1" face="Verdana, Arial, Helvetica, sans-serif"><strong></strong></font></td>
              <td><font color="#990000" size="-1" face="Verdana, Arial, Helvetica, sans-serif"><strong>Editeur</strong></font></td>
              <td><font color="#990000" size="-1" face="Verdana, Arial, Helvetica, sans-serif"><strong>Collection</strong></font></td>
              <td><font color="#990000" size="-1" face="Verdana, Arial, Helvetica, sans-serif"><strong>Date de Parution</strong></font></td>
              <td><font color="#990000" size="-1" face="Verdana, Arial, Helvetica, sans-serif"><strong>Couverture</strong></font></td>
		</tr>
		<!-- BEGIN EditionBlock -->
		<tr>
              <td bgcolor="#DAC1B3"><font size="-1" face="Verdana, Arial, Helvetica, sans-serif"><div align="center"><input name="btnDefEdit" type="radio" value="{BTNVALUE}" {ISCHECKED}></div></font></td>
              <td bgcolor="#DAC1B3"><font size="-1" face="Verdana, Arial, Helvetica, sans-serif">{EDITEUR}</font></td>
              <td bgcolor="#DAC1B3"><font size="-1" face="Verdana, Arial, Helvetica, sans-serif">{COLLECTION}</font></td>
              <td bgcolor="#DAC1B3"><font size="-1" face="Verdana, Arial, Helvetica, sans-serif">{DTPAR}</font></td>
              <td bgcolor="#DAC1B3"><font size="-1" face="Verdana, Arial, Helvetica, sans-serif"><img src="{URLIMAGE}" width="50" height="60" border="0" align="absmiddle" /></font></td>
		</tr>
		<!-- END EditionBlock -->
        </table>
            </td>
  </tr>
<tr><td>
<input type="submit" name="Submit" value="{ACTIONNAME}">
</tr></td>
</table>

</form>


</body>
</html>
