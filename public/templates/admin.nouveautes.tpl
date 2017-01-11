<table width="71%" border="0">
  <tr>
    <td width="40%">
	 <table width="223" height="29" class="donnees">
        <tr>
          <td width="110" height="25" class="entete"> <div align="center"><font color="#0000FF"><strong><font size="2" face="Arial, Helvetica, sans-serif"><a href="../admin/recupbdnet.php?cle=1&sort=asc"><img src="{URLSITEIMAGE}flechehaut.gif" width="12" height="12" hspace="5" border="0"></a>Mois<a href="../admin/recupbdnet.php?cle=1&sort=DESC"><img src="{URLSITEIMAGE}flechebas.gif" width="12" height="12" hspace="5" border="0"></a></font></strong></font></div></td>
          <td width="97" class="entete"><div align="center"><font color="#0000FF"><strong><font size="2" face="Arial, Helvetica, sans-serif"><a href="../admin/recupbdnet.php?cle=2&sort=asc"><img src="{URLSITEIMAGE}flechehaut.gif" width="12" height="12" hspace="5" border="0"></a>Nombre<a href="../admin/recupbdnet.php?cle=2&sort=DESC"><img src="{URLSITEIMAGE}flechebas.gif" width="12" height="12" hspace="5" border="0"></a></font></strong></font></div></td>
        </tr>
        <!-- BEGIN PropBlock -->
        <tr>
          <td width="110" height="25" class="data"> <div align="center"><font color="#000000"><a href="{URLDETAILMOIS}">{MOIS}</a></font></div></td>
          <td width="97" class="data"><div align="center"><font color="#000000" size="2" face="Arial, Helvetica, sans-serif">{NBALBUMS}</font></div></td>
        </tr>
        <!-- END PropBlock -->
      </table></td>
    <td width="60%"> <form action="admin.scan.gen.php" method="post">
        <table width="100%" border="0">
          <tr>
            <td width="47%" class="texte">Scanner un mois (yymm)</td>
            <td width="26%"><input type="text" name="txtMois"></td>
            <td width="27%"><input type="submit" name="Submit" value="Lancer"></td>
          </tr>
        </table>
      </form></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td class="texte">Detail Pour le mois de {DETAILMOIS}</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>
	 <table width="223" height="29" class="donnees">
        <tr>
          <td width="110" height="25" class="entete"> <div align="center"><font color="#0000FF"><strong><font size="2" face="Arial, Helvetica, sans-serif">Etapes</font></strong></font></div></td>
          <td width="97" class="entete"><div align="center"><font color="#0000FF"><strong><font size="2" face="Arial, Helvetica, sans-serif">Nombre</font></strong></font></div></td>
        </tr>
        <!-- BEGIN DetailBlock -->
        <tr>
          <td width="110" height="25" class="data"> <div align="center"><font color="#000000"><a href="{URLFLAG}">{FLAG}</a></font></div></td>
          <td width="97" class="data"><div align="center"><font color="#000000" size="2" face="Arial, Helvetica, sans-serif">{NBFLAGS}</font></div></td>
        </tr>
        <!-- END DetailBlock -->
      </table>
	 </td>
    <td>
	 <table width="289" height="166" class="donnees">
        <tr>
          <td width="281" height="25" class="entete"> <div align="center"><font color="#0000FF"><strong><font size="2" face="Arial, Helvetica, sans-serif">Liste
              des &eacute;tapes</font></strong></font></div></td>
        </tr>
        <tr>
          <td width="281" height="25" class="data"> <div align="center">Identifier
              les nouveaut&eacute;s dans BDNET</div></td>
        </tr>
        <tr>
          <td height="25" class="data"><div align="center"><a href="{URLSCAN}">Récupérer
              les informations depuis BDNET</a></div></td>
        </tr>
        <tr>
          <td height="25" class="data"><div align="center"><a href="{URLMAPPING}">Mapper avec la base bdovore</a></div></td>
        </tr>
        <tr>
          <td height="25" class="data"><div align="center"><a href="{URLTRANSFER}">Transf&eacute;rer </a></div></td>
        </tr>
        <tr>
          <td height="25" class="data"><div align="center">Rejeter</div></td>
        </tr>
      </table>	</td>
  </tr>
</table>

