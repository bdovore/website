<!-- Fin du formulaire de recherche -->
{MENUCOLL}
<div class="cadre1">
  <div align="center">
  <form name="filter" method="post" action="usersearch.php">
  <table width="80%" border="0" align="center">
                <tr>
                <td width="20%"><div align="left">N'afficher que les albums :</div></td>
                <td width="20%"> <div align="left">
                    <input name="cb_pret" type="checkbox" value="1" {PRETCHECK}>
                    prêtés <br>
                    <input name="cb_dedi" type="checkbox" value="1" {DEDICHECK}>
                    dédicacés </div></td>
                <td width="25%"> <div align="left">
                    <input name="cb_note" type="checkbox" value="1" {NOTCHECK}>
                    non notés <br>
                    <input name="cb_comment" type="checkbox" value="1" {CMTCHECK}>
                    non commentés </div></td>
                    <td width="20%">
                    <input name="rb_mode" type="radio" value="1" {MODE1CHECKED}>
                  Mes BD <br>
                  <input type="radio" name="rb_mode" value="3" {MODE3CHECKED}>
                  Futurs Achats
                    </td>
                <td> <input type="submit" name="send_filter" value="Ok"> </td>
                </tr>
    </table>
    Recherche :
          <input name="str_search" type="text" id="str_search" value={DEFSEARCH} size="20" maxlength="50">
            <br>
         dans Albums:
          <input name="cb_album" type="checkbox" id="cb_album" value="cb_album" {ALBCHECK}>
          S&eacute;ries:
          <input name="cb_serie" type="checkbox" id="cb_serie" value="cb_serie" {SERCHECK}>
          Auteurs:
          <input name="cb_aut" type="checkbox" id="cb_aut" value="cb_aut" {AUTCHECK}>



                                         <br />trier par <select name="ch_tri" size="1" id="ch_tri">
                                {OPTIONS}
                                 </select>




    <input name="search_mode" type="hidden" value="1">
    <! input name="cb_serie" type="hidden" value="1">
    <p align="center">
  <a href="#" onclick="document.forms.filter.str_search.value='';document.forms.filter.submit();">Tous</a>&nbsp;
  <a href="#" onclick="document.forms.filter.str_search.value='A';document.forms.filter.search_mode.value='2';document.forms.filter.submit();">A</a>&nbsp;
  <a href="#" onclick="document.forms.filter.str_search.value='B';document.forms.filter.search_mode.value='2';document.forms.filter.submit();">B</a>&nbsp;
  <a href="#" onclick="document.forms.filter.str_search.value='C';document.forms.filter.search_mode.value='2';document.forms.filter.submit();">C</a>&nbsp;
  <a href="#" onclick="document.forms.filter.str_search.value='D';document.forms.filter.search_mode.value='2';document.forms.filter.submit();">D</a>&nbsp;
  <a href="#" onclick="document.forms.filter.str_search.value='E';document.forms.filter.search_mode.value='2';document.forms.filter.submit();">E</a>&nbsp;
  <a href="#" onclick="document.forms.filter.str_search.value='F';document.forms.filter.search_mode.value='2';document.forms.filter.submit();">F</a>&nbsp;
  <a href="#" onclick="document.forms.filter.str_search.value='G';document.forms.filter.search_mode.value='2';document.forms.filter.submit();">G</a>&nbsp;
  <a href="#" onclick="document.forms.filter.str_search.value='H';document.forms.filter.search_mode.value='2';document.forms.filter.submit();">H</a>&nbsp;
  <a href="#" onclick="document.forms.filter.str_search.value='I';document.forms.filter.search_mode.value='2';document.forms.filter.submit();">I</a>&nbsp;
  <a href="#" onclick="document.forms.filter.str_search.value='J';document.forms.filter.search_mode.value='2';document.forms.filter.submit();">J</a>&nbsp;
  <a href="#" onclick="document.forms.filter.str_search.value='K';document.forms.filter.search_mode.value='2';document.forms.filter.submit();">K</a>&nbsp;
  <a href="#" onclick="document.forms.filter.str_search.value='L';document.forms.filter.search_mode.value='2';document.forms.filter.submit();">L</a>&nbsp;
  <a href="#" onclick="document.forms.filter.str_search.value='M';document.forms.filter.search_mode.value='2';document.forms.filter.submit();">M</a>&nbsp;
  <a href="#" onclick="document.forms.filter.str_search.value='N';document.forms.filter.search_mode.value='2';document.forms.filter.submit();">N</a>&nbsp;
  <a href="#" onclick="document.forms.filter.str_search.value='O';document.forms.filter.search_mode.value='2';document.forms.filter.submit();">O</a>&nbsp;
  <a href="#" onclick="document.forms.filter.str_search.value='P';document.forms.filter.search_mode.value='2';document.forms.filter.submit();">P</a>&nbsp;
  <a href="#" onclick="document.forms.filter.str_search.value='Q';document.forms.filter.search_mode.value='2';document.forms.filter.submit();">Q</a>&nbsp;
  <a href="#" onclick="document.forms.filter.str_search.value='R';document.forms.filter.search_mode.value='2';document.forms.filter.submit();">R</a>&nbsp;
  <a href="#" onclick="document.forms.filter.str_search.value='S';document.forms.filter.search_mode.value='2';document.forms.filter.submit();">S</a>&nbsp;
  <a href="#" onclick="document.forms.filter.str_search.value='T';document.forms.filter.search_mode.value='2';document.forms.filter.submit();">T</a>&nbsp;
  <a href="#" onclick="document.forms.filter.str_search.value='U';document.forms.filter.search_mode.value='2';document.forms.filter.submit();">U</a>&nbsp;
  <a href="#" onclick="document.forms.filter.str_search.value='V';document.forms.filter.search_mode.value='2';document.forms.filter.submit();">V</a>&nbsp;
  <a href="#" onclick="document.forms.filter.str_search.value='W';document.forms.filter.search_mode.value='2';document.forms.filter.submit();">W</a>&nbsp;
  <a href="#" onclick="document.forms.filter.str_search.value='X';document.forms.filter.search_mode.value='2';document.forms.filter.submit();">X</a>&nbsp;
  <a href="#" onclick="document.forms.filter.str_search.value='Y';document.forms.filter.search_mode.value='2';document.forms.filter.submit();">Y</a>&nbsp;
  <a href="#" onclick="document.forms.filter.str_search.value='Z';document.forms.filter.search_mode.value='2';document.forms.filter.submit();">Z</a>&nbsp;
  </p>
    </form>
</div>
</div>
  <div class="cadre1" align="center">
    <p>{NBREPONSES}</p>
     <table width="90%" border="1" align="center" cellpadding="5" cellspacing="0" bordercolor="#000000" frame="border" rules="all">
  <!-- BEGIN ResultBlock -->
    <tr bgcolor="EED8BF">
      <td width="75%"><a href="{URLSERIE}"><font color="#990000" size="-1" face="Verdana, Arial, Helvetica, sans-serif"><strong>{SERIE}</strong></font></a><br/>
        Titre : <a href={URLALBUM}><font size="-1" face="Verdana, Arial, Helvetica, sans-serif" color="#990000">{TITRE}</font></a><br/>
        Editeur : {EDITEUR} - Edition : {DTEPARUTION} </td>
      <td width="15%"><a href="{URLSCEN}">{SCEN}</a>{SEP}<a href="{URLDESS}">{DESS}</a></td>
      <td width="5%"><a href={URLALBUM}><img src="{URLSITEIMAGE}edit.gif" width="18" height="13" border="0"></a></td>
      <td width="5%"><a href="{URLDELETE}"><img src="{URLSITEIMAGE}poubelle.gif" width="25" height="24" border="0"></a></td>
    </tr>

  <!-- END ResultBlock -->
   </table>
  <!-- BEGIN ButtonBlock -->
  <p>{NAVPAGE}&nbsp; </p>

    <p>&nbsp; </p>
<!-- END ButtonBlock -->
   <p align="center">Exporter la liste compl&egrave;te : <a href="{URLSITE}membres/export.php"><img src="{URLSITEIMAGE}site/excel.jpg" width="25" height="25" border="0" alt="Excel"></a>
    ou <a href="export_pdf.php"><img src="{URLSITEIMAGE}site/pdf.jpg" width="25" height="25" border="0" alt="Adobe pdf"></a></p>

</div>