{MENUCOLL}


<div class="cadre1">
<form name="form_browse" method="post" action="{ACTBROWSER}">
        <div align="center">
	 		 <input name="rb_browse" type="radio" value="ser" {SERCHECK}>
            Par série
            <input name="rb_browse" type="radio" value="aut" {AUTCHECK}>
            Par auteur
            <input type="radio" name="rb_browse" value="genr" {GENRCHECK}>
            Par genre
            <input type="radio" name="rb_browse" value="edit" {EDITCHECK}>
            Par Editeur
            <input type="submit" name="Submit" value="Envoyer">
			<table width="60%" border="0">
				<tr>
				<td width="20%">N'afficher que les albums :</td>
				<td width="40%"><input name="cb_pret" type="checkbox" value="1" {PRETCHECK}>prêtés <br>
					<input name="cb_dedi" type="checkbox" value="1" {DEDICHECK}>dédicacés
				</td>
				<td width="40%">
				<input name="cb_note" type="checkbox" value="1" {NOTCHECK}>non notés <br>
				<input name="cb_comment" type="checkbox" value="1" {CMTCHECK}>non commentés	</td>
				</tr>
			</table>
          </div>
<table width="100%" border="0">

    <tr>
      <td>
        <div align="center"><!-- BEGIN NavBlock --><a href="#" onclick="document.forms.form_browse.let.value='{VLETTER}';document.forms.form_browse.submit();">{LETTER}&nbsp;</a>  <!-- END NavBlock -->
        </div>
		 </td>
    </tr>
    <tr>
      <td>
          <div align="center"> Ou commence par :
            <input name="let" type="text" id="let">

          </div>
        </td>
    </tr>
  </table>
</form>
</div>

<div class="cadre1">
      <!-- BEGIN DataBlock -->
<p id="{LEVSIGN}"><img src="{URLSITEIMAGE}site/spacer.gif" width={WSPACER} height={HSPACER}><img src="{URLSITEIMAGE}site/{IMGNAVIG}">
          <a href="{URLEVEL}#{LEVSIGN}" {ACTLEVEL}>{NAMELEVEL}</a> {URLEDIT}</p>
	  <!-- END DataBlock -->
</div
<div class="cadre1">
<p align="center"><a href="{URLPREVPAGE}">Pr&eacute;c&eacute;dent</a> <a href="{URLNEXTPAGE}">Suivant</a> </p>
</div>



