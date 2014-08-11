<SCRIPT language="Javascript"> 
<!-- 
  
function Chargement() { 
 d = document.add_collect;
 for (i = 0; i < d.elements.length; i++) {
   if (d.elements[i].name == "sel_alb[]") {
     d.elements[i].checked = true;
   }
 }
} 
  
//--> 
</SCRIPT>

<div class="cadre1">
    <div style="float: right;">
        <!-- AddThis Button BEGIN -->
        <script type="text/javascript">var addthis_pub="zeas";var addthis_brand="BDovore";var addthis_header_color="#990000";var addthis_header_background="#DAC1B3";</script><a href="http://www.addthis.com/bookmark.php?v=20" onmouseover="return addthis_open(this, '', '[URL]', '[TITLE]')" onmouseout="addthis_close()" onclick="return addthis_sendto()"><img src="{URLSITEIMAGE}site/lg-addthis-fr.gif" width="125" height="16" alt="Partager cette page" style="border:0"/></a>
        <noscript>
            <span class="petite_police"><br />Javascript absent/désactivé dans votre navigateur : bouton inactif</span>
        </noscript>
        <script type="text/javascript" src="http://s7.addthis.com/js/200/addthis_widget.js"></script>
        <!-- AddThis Button END -->
    </div>
    <font color="#990000" size="-1" face="Verdana, Arial, Helvetica, sans-serif">
        <strong>Série : </strong>
    </font>
    {SERTITLE} {EDITSERIE}
    <br />
    <font color="#990000" size="-1" face="Verdana, Arial, Helvetica, sans-serif"> 
        <strong>Genre : </strong>
    </font>
    {SERGENRE}
    <br />
    <font color="#990000" size="-1" face="Verdana, Arial, Helvetica, sans-serif"> 
        <strong>Nombre de tome: </strong>
    </font>
    {NBTOME}
    <br />
    <font color="#990000" size="-1" face="Verdana, Arial, Helvetica, sans-serif"> 
        <strong>Albums dans la base: </strong>
    </font>
    {NBALB}
    <br />
    <font color="#990000" size="-1" face="Verdana, Arial, Helvetica, sans-serif"> 
        <strong>Avancement : </strong>
    </font>
    {SERFIN}
    <br />
    <font color="#990000" size="-1" face="Verdana, Arial, Helvetica, sans-serif">
        <strong>Synopsis : </strong>
    </font>
    {HISTOIRE}
</p>
<p>
    {SERNOTE}
    <br />
    Dernier(s) commentaire(s) :
    <!-- BEGIN CommentBlock -->
    <table width="100%" border="1" align="center" cellpadding="3" cellspacing="0" bordercolor="#B64444">
        <tbody>
            <tr bordercolor="#FFFFFF"> 
                <td height="25" bordercolor="#B64444" bgcolor="#C8857C">
                    <b><font color="#990000" face="Arial, Helvetica, sans-serif" size="2"> 
                        Note : {NOTE}
                    </font></b>
                </td>
                <td bordercolor="#B64444" bgcolor="#C8857C">
                    <div align="right"><font face="Arial, Helvetica, sans-serif" size="1">
                        <font color="#0000ff" face="Arial, Helvetica, sans-serif" size="2">
                            <font color="#333333">
                                Postée par <strong>{USERNAME}</strong>
                        </font></font>
                         le {CMTDATE}
                    </font></div>
                </td>
            </tr>
            <tr bordercolor="#B64444"> 
                <td colspan="2"><font size="2" face="Arial, Helvetica, sans-serif">{CMTTEXTE}</font></td>
            </tr>
        </tbody>
    </table>
    <!-- END CommentBlock -->
    
    <a href="#" onClick='open("./membres/commentserie.php?id_serie={SERID}&lib_tome={SERTITLE}","Comment","width=500,height=400,scrollbars=1")'>
        Ajouter/Voir tous les commentaires sur la série
    </a>
</div>

<form name="add_collect" method="post" action="./membres/addalbum.php">
<div class="cadre1">
    <div align="right">
        <a name="ici" href="#ici" onClick="Chargement();">Tout sélectionner</a>
        <input type="hidden" name="id_serie" value="{SERID}" />
        <input type="hidden" name="page" value="{NUMPAGE}" />
        <input type="hidden" name="src" value="serie" />
    </div>
        <!-- BEGIN AlbBlock -->
    <table width="100%" border="0" bordercolor="#000000" bgcolor="#EED8BF" dwcopytype="CopyTableRow">
        <tr> 
            <td width="25%" valign="top">
                <img src="{URLSITEIMAGE}/couv/{COUVALBUM}" width="180" border="1" />
                <div class="petite_police">{COPYRIGHT}</div>
                <a href="{URLAMAZON}" target="_blank">Commander sur Amazon !</a> 
            </td>
            <td width="75%" align="left" valign="top">
                <table width="100%" height="75%" border="1" cellpadding="3" cellspacing="0" bordercolor="#000000" frame="border" rules="all">
                    <tbody>
                        <tr> 
                            <td width="25%" bgcolor="#C8857C"><font color="#990000" size="-1" face="Verdana, Arial, Helvetica, sans-serif">
                                <strong>Titre :</strong>
                            </font></td>
                            <td width="75%" bgcolor="#DAC1B3"><font size="-1" face="Verdana, Arial, Helvetica, sans-serif"> 
                                <a href={ALBURL}>
                                    {ALBTITLE}
                                </a>
                            </font>{EDITALB}</td>
                        </tr>
                        <tr> 
                            <td bgcolor="#C8857C"><font color="#990000" size="-1" face="Verdana, Arial, Helvetica, sans-serif">
                                <strong>Tome :</strong>
                            </font></td>
                            <td bgcolor="#DAC1B3"><font size="-1" face="Verdana, Arial, Helvetica, sans-serif"> 
                                {NUMTOME}
                            </font></td>
                        </tr>
                        <tr> 
                          <td bgcolor="#C8857C"><font color="#990000" size="-1" face="Verdana, Arial, Helvetica, sans-serif"><strong>Genre 
                            :</strong></font></td>
                          <td bgcolor="#DAC1B3"><font size="-1" face="Verdana, Arial, Helvetica, sans-serif"> 
                            {ALBGENRE}</font></td>
                        </tr>
                        <tr> 
                          <td bgcolor="#C8857C"><font color="#990000" size="-1" face="Verdana, Arial, Helvetica, sans-serif"><strong>Scénario 
                            :</strong></font></td>
                          <td bgcolor="#DAC1B3"><font size="-1" face="Verdana, Arial, Helvetica, sans-serif"> 
                            {ALBSCENAR}</font></td>
                        </tr>
                        <tr> 
                          <td bgcolor="#C8857C"><font color="#990000" size="-1" face="Verdana, Arial, Helvetica, sans-serif"><strong>Dessin 
                            :</strong></font></td>
                          <td bgcolor="#DAC1B3"><font size="-1" face="Verdana, Arial, Helvetica, sans-serif"> 
                            {ALBDESSIN}</font></td>
                        </tr>
                        <tr> 
                          <td height="30" bgcolor="#C8857C"><font color="#990000" size="-1" face="Verdana, Arial, Helvetica, sans-serif"><strong>Editeur 
                            : </strong></font></td>
                          <td bgcolor="#DAC1B3"><font size="-1">{ALBEDITEUR}</font> - Edition : {DTEPARUTION}</td>
                        </tr>
                        <tr> 
                          <td height="30" bgcolor="#C8857C"><font color="#990000" size="-1" face="Verdana, Arial, Helvetica, sans-serif"><strong>Synopsis 
                            : </strong></font></td>
                          <td bgcolor="#DAC1B3">{ALBSTORY}</td>
                        </tr>
                        <tr> 
                          <td height="30" bgcolor="#C8857C"><strong><font color="#900000" size="-1" face="Verdana, Arial, Helvetica, sans-serif">Identifiant 
                            :</font></strong></td>
                          <td bgcolor="#DAC1B3">
							ISBN-13 (EAN) : {EAN}
							<br/ >
							ISBN-10 : {ISBN}
							<br />
                            ID-BDovore : {TOMEID}</td>
                        </tr>
                    </tbody>
                </table> 
                <br />
                <table width="80%" border="1" align="center" cellpadding="0" cellspacing="3" bordercolor="#000000" frame="border" rules="all">
                    <tr> 
                        <td width="50%"><div align="center">
                            <a href="#" onClick="window.open('{URLSITE}membres/add_fiche_album.php?act=add&id_tome={TOMEID}&id_edition={EDITIONID}&flg_achat=N','Album','width=200,height=80,scrollbars=1');return false">
                                <font color="#900000" size="-1">
                                    <strong>Ajouter &agrave; ma collection</strong>
                                </font>
                            </a>
                        </div></td>
                        <td width="47%"><div align="center">
                            <a href="#" onClick="window.open('{URLSITE}membres/add_fiche_album.php?act=add&id_tome={TOMEID}&id_edition={EDITIONID}&flg_achat=O','Album','width=200,height=80,scrollbars=1');return false">
                                <font color="#90000" size="-1">
                                    <strong>Achat futur</strong>
                                </font>
                            </a>
                        </div></td>
                        <td width="3%">
                            <div align="center">
                                <input name="sel_alb[]" type="checkbox" id="sel_alb[]" value="{TOMEID}" />
                            </div>
                        </td>
                    </tr>
                </table>
                <p>{ALBNOTE} </p>
                <p>
                    <a href="#" onClick='open("./membres/comment.php?id_tome={TOMEID}&lib_tome={ALBTITLE}&aut=({NSCENAR},{NDESSIN})&src=serie","Comment","width=500,height=400,scrollbars=1")'>
                        Ajouter/Voir les commentaires
                    </a>
                    <br />
                    <a href="{URLEDIT}" target="_blank">Proposer une correction</a> 
                </p>
            </td>
        </tr>
    </table>
    <!-- END AlbBlock -->

    <div align="center">
        <table width="20%" border="0">
            <tr> 
                <td>
                    <div align="center">
                        Achat futur : 
                        <input name="cb_achat" type="checkbox" id="cb_achat2" value="checkbox" />
                    </div>
                </td>
            </tr>
            <tr> 
                <td>
                    <input name="B_Add" type="submit" id="B_Add2" value="Ajouter &agrave; ma collection" />
                </td>
            </tr>
        </table>
    </div>
    <div align="center"> </div>
    <div align="right">
        <a name="ici" href="#ici" onClick="Chargement();">Tout sélectionner</a>
    </div>
    <div align="center">{NAVPAGE} </div>
    <div align="center"></div>
    <div align="center"> </div>
</div>
</form>