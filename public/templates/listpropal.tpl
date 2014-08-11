<div class="cadre1">
<h3>Proposition {TYPEENCOURS} en cours... {NBTOTAL}</h3>
      <!-- BEGIN LinksBlock -->
      <a href="listpropal.php?type={TYPEAUTRE}">{AJOUTCORRECTION}</a><br>
      <!-- END LinksBlock -->
<br>
      <table width="100%" height="29" class="donnees">
        <tr> 
          <td width="20%" class="entete"><div align="center"><font color="#0000FF"><strong>
		  <a href="{URLSITE}listpropal.php?type={TYPE}&cle=1&sort=asc">
		  	<img src="{URLSITEIMAGE}flechehaut.gif" width="12" height="12" hspace="5" border="0"></a>S&eacute;rie
		 <a href="{URLSITE}listpropal.php?cle=1&sort=DESC&type={TYPE}">
		 	<img src="{URLSITEIMAGE}flechebas.gif" width="12" height="12" hspace="5" border="0"></a></strong></font></div></td>
          <td width="5%" class="entete"><div align="center"><strong>Tome</strong></div></td>
          <td width="25%" class="entete"><div align="center"><strong>Titre</strong></div></td>
          <td width="20%"  class="entete"><div align="center"><font color="#0000FF"><strong><a href="{URLSITE}listpropal.php?type={TYPE}&cle=2&sort=asc"><img src="{URLSITEIMAGE}flechehaut.gif" width="12" height="12" hspace="5" border="0"></a>Demand&eacute;e 
              Par<a href="{URLSITE}listpropal.php?type={TYPE}&cle=2&sort=desc"><img src="{URLSITEIMAGE}flechebas.gif" width="12" height="12" hspace="5" border="0"></a></strong></font></div></td>
          <td width="15%" class="entete"> <div align="center"><font color="#0000FF"><strong><a href="{URLSITE}listpropal.php?cle=3&sort=asc&type={TYPE}"><img src="{URLSITEIMAGE}flechehaut.gif" width="12" height="12" hspace="5" border="0"></a>Demand&eacute; 
              le <a href="{URLSITE}listpropal.php?type={TYPE}&cle=3&sort=desc"><img src="{URLSITEIMAGE}flechebas.gif" width="12" height="12" hspace="5" border="0"></a></strong></font></div></td>
        </tr>
        <!-- BEGIN DetailBlock -->
        <tr> 
          <td width="20%" class="data"><div align="center">{SERIE}</div></td>
          <td width="5%" class="data"><div align="center">{TOME}</div></td>
          <td width="25%" class="data">{TITRE}</td>
          <td width="20%"  class="data"><div align="center">{AUTEUR}</div></td>
          <td width="15%" class="data"> <div align="center">{DTEPAR}</div></td>
        </tr>
        <!-- END DetailBlock -->
      </table>
<p class="texte">{BARRENAVIGATION}</p>
</div>