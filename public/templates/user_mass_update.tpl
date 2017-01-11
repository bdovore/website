{MENUCOLL}
<div class="cadre1">
	  <form action="{URLSITE}membres/mass_update.php" method="post" name="Filter">
        <table width="495" border="1" cellspacing="0" bordercolor="#990000" frame="box" rules="groups">
          <tr bordercolor="#EEDED6">
            <td width="139"><strong>Champs à éditer</strong></td>
            <td width="225"><strong>Filtres</strong></td>
            <td width="117"> </td>
          </tr>
          <tr bordercolor="#EEDED6">
            <td>
              <select name="lstCle0">{OPTIONCLE0}

              </select></td>
            <td>
              <input type="checkbox" name="chkFiltre1" value="checkbox" {ISCHECKED1}>
              Futurs achats</td>
            <td>
              <input type="checkbox" name="chkFiltre2" value="checkbox" {ISCHECKED2}>
              Non noté</td>
          </tr>
          <tr bordercolor="#EEDED6">
            <td>
              <select name="lstCle1">{OPTIONCLE1}
                </select></td>
            <td>
              <input type="checkbox" name="chkFiltre3" value="checkbox" {ISCHECKED3}>
              Albums prêtés</td>
            <td>
              <input type="checkbox" name="chkFiltre4" value="checkbox" {ISCHECKED4}>
              Non commenté</td>
          </tr>
          <tr bordercolor="#EEDED6">
            <td>
              <select name="lstCle2">{OPTIONCLE2}
                </select></td>
            <td>Recherche :
              <input name="str_search" type="text" id="str_search" value="{STRSEARCH}" size="15" maxlength="50">
			  <br>
			  Albums:
          <input name="cb_album" type="checkbox" id="cb_album" value="cb_album" {ALBCHECK}>
          Séries:
          <input name="cb_serie" type="checkbox" id="cb_serie" value="cb_serie" {SERCHECK}>
          Auteurs:
          <input name="cb_aut" type="checkbox" id="cb_aut" value="cb_aut" {AUTCHECK}>
			  </td>
            <td> </td>
          </tr>
          <tr bordercolor="#EEDED6">
            <td>
              <select name="lstCle3">{OPTIONCLE3}
                </select></td>
            <td colspan="2" align="center">
              <input type="submit" name="Submit2" value="Rafraichir"></td>
          </tr>
          <tr bordercolor="#EEDED6">
            <td>
              <select name="lstCle4">{OPTIONCLE4}
                </select></td>
            <td> </td>
            <td> </td>
          </tr>
        </table>
	  </form> <br>
	  <form action="{URLFORM}" method="post" name="Values">
		<table width="100%" height="55" border="1" cellpadding="1" cellspacing="1" class="Tableur" rules="all">
          <tr>
            <td width="182" height="25" class="EnteteTableur">Serie</td>
            <td width="36" class="EnteteTableur">Tome</td>
            <td width="208" class="EnteteTableur">Titre</td>

            <!-- BEGIN TitleBlock -->
            <td class="EnteteTableur"><input name="{KEYNAME}" type="hidden" value="{KEYVALUE}">
              {TITRE}</td>
            <!-- END TitleBlock -->
			<td width="5%" class="EnteteTableur"></td>
          </tr>
          <!-- BEGIN RowBlock -->
          <tr>
            <td class="DataTableur"><a href="{URLSERIE}">{ALBSERIE}</a></td>
            <td class="DataTableur" >{ALBTOME}</td>
            <td class="DataTableur"><input name="idedition[]" type="hidden" value="{IDEDITION}">
              <a href="#" onclick="window.open('{URLTOME}','Album','width=580,height=530,scrollbars=1')">{ALBTITRE}</a></td>
            <!-- BEGIN ColumnBlock -->
            <td class="DataTableur">{CHAMP}</td>
            <!-- END ColumnBlock -->
			<td width="5%"><a href="{URLDELETE}"><img src="{URLSITEIMAGE}poubelle.gif" width="25" height="24" border="0"></a></td>
          </tr>
          <!-- END RowBlock -->

        </table>
        <br>

        <div align="right">
          <input type="submit" name="Submit3" value="Valider">
        </div>
<div align="left">{BARRENAVIGATION}</div>
	  </form>
	  </div>



