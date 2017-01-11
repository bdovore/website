<div style="position:absolute;top:1em;left:60%">
  <form action="usersearch.php" method="post" name="search" target="_self">
<div class="cadre1">
Recherche :
          <input name="str_search" type="text" id="str_search" value="" size="20" maxlength="50">
          <input type="submit" name="Submit" value="Envoyer"> <br>
          Albums:
          <input name="cb_album" type="checkbox" id="cb_album" value="cb_album" checked>
          S&eacute;ries:
          <input name="cb_serie" type="checkbox" id="cb_serie" value="cb_serie">
          Auteurs:
          <input name="cb_aut" type="checkbox" id="cb_aut" value="cb_aut"> </td>
      <table width="100%" border="0">
            <tr>
              <td width="73%"><div align="left">
                  <input name="rb_mode" type="radio" value="1" checked>
                  Mes BD
                  <input type="radio" name="rb_mode" value="2">
                  Pr&ecirc;t
                  <input type="radio" name="rb_mode" value="3">
                  A pr&eacute;voir</div></td>
              <td width="27%"><div align="right"><a href="userbrowser.php">BD
                  Browser</a></div></td>
            </tr>
          </table>
          </div>
  </form>
</div>
<div class="cadre1">
<p>&nbsp;
      <table width="100%" border="1">
        <tr>
          <td colspan="2">Param&egrave;tres de valorisation</td>
        </tr>
        <tr>
          <td width="44%"><table width="400" border="1">
              <tr>
                <td colspan="3">Valorisation de la collection</td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td>Nombres</td>
                <td>Valeur</td>
              </tr>
              <tr>
                <td width="59%">Album valoris&eacute;s au prix d'achat</td>
                <td width="23%"><a href="{URLNBPA}" title="{TITRENBPA}">{NBPA}</a></td>
                <td width="18%">{VALPA}</td>
              </tr>
              <tr>
                <td>Album valoris&eacute;s au prix bdovore</td>
                <td><a href="{URLNBPB}" title="{TITRENBPB}">{NBPB}</a></td>
                <td>{VALPB}</td>
              </tr>
              <tr>
                <td>Album valoris&eacute;s au prix par d&eacute;faut</td>
                <td><a href="{URLNBPD}" title="{TITRENBPD}">{NBPD}</a></td>
                <td>{VALPD}</td>
              </tr>
              <tr>
                <td>Coffrets</td>
                <td><p><a href="{URLNBC}" title="{TITRENBC}">{NBC}</a></p></td>
                <td>{VALC}</td>
              </tr>
              <tr>
                <td>Albums non valoris&eacute;s</td>
                <td><a href="{URLNBNV}" title="{TITRENBNV}">{NBNV}</a></td>
                <td>{VALNV}</td>
              </tr>
              <tr>
                <td>Total</td>
                <td><a href="{URLNBTOT}" title="{TITRENBTOT}">{NBTOT}</a></td>
                <td>{VALTOT}</td>
              </tr>
            </table></td>
          <td width="56%"> <form name="form1" method="post" action="{URLSITE}membres/addition.php?action=refresh">
              <table width="100%" border="1">
                <tr>
                  <td width="38%">Param&egrave;tres de valorisations</td>
                  <td width="62%">&nbsp;</td>
                </tr>
                <tr>
                  <td>Prix par d&eacute;faut d'un album</td>
                  <td> <input name="txtPrixAlbum" type="text" value="{DEFALB}" size="6" class="flat">
                  </td>
                </tr>
                <tr>
                  <td>Prix par d&eacute;faut d'une int&eacute;grale</td>
                  <td><input name="txtPrixIntegrale" type="text" value="{DEFINT}" size="6" class="flat"></td>
                </tr>
                <tr>
                  <td>Surco&ucirc;t coffret</td>
                  <td><select name="lstCoffret" class="flat">{OPTVALOCOFFRET}
                    </select> <input name="txtPrixCoffret" type="text" value="{DEFCOFFRET}" size="6" class="flat"></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td><input type="submit" name="Submit2" value="Recalculer"></td>
                </tr>
              </table>
            </form></td>
        </tr>
      </table><br>
	  <form action="{URLACTION}" method="post" name="formMAJ">
<table width="100%" border="1">
        <tr>
          <td>D&eacute;tail de la valorisation</td>
        </tr>
        <tr>
          <td>

		  <table width="100%" height="55" border="1" cellpadding="1" cellspacing="1" class="Tableur">
              <tr>
                <td width="182" height="25" class="EnteteTableur">Serie</td>
                <td width="36" class="EnteteTableur">Tome</td>
                <td width="208" class="EnteteTableur">Titre</td>
                <td width="208" class="EnteteTableur">Prix du march&eacute;</td>
                <td width="208" class="EnteteTableur">Prix Achat</td>
              </tr>
              <!-- BEGIN RowBlock -->
              <tr>
                <td class="DataTableur"><a href="{URLSERIE}">{ALBSERIE}</a></td>
                <td class="DataTableur" >{ALBTOME}</td>
                <td class="DataTableur"><a href="#" onclick="window.open('{URLTOME}','Album','width=600,height=800,scrollbars=1')">{ALBTITRE}</a></td>
                <td class="DataTableur">{PRIX}</td>
                <td class="DataTableur"><input name="prix_achat[{IDTOME}]" type="text" id="txtPrixAchat" value="{PRIXACHAT}" size="6" class="flat"></td>
              </tr>
              <!-- END RowBlock -->
            </table>
		  </td>
        </tr>
      </table>
</form>
{BARRENAVIGATION}
</div>



