{MENUCOLL}
<link href="{URLSITE}style/export.css" rel="stylesheet" type="text/css">

<div class="cadre1">

<form action="export.php?act=export" method="post" name="export">

<div class="expTitle">Exportation de données</div>

<div class="expFieldBlock">
<div class="expTitle2">ALBUMS</div>
Titre<input type="checkbox" name="sel_field[]" value="1" checked /><br />
Série<input type="checkbox" name="sel_field[]" value="0" {SELFIELD0} /><br />
Tome<input type="checkbox" name="sel_field[]" value="2" {SELFIELD2} /><br />
ISBN<input type="checkbox" name="sel_field[]" value="3" {SELFIELD3} /><br />
Genre<input type="checkbox" name="sel_field[]" value="4" {SELFIELD4} /><br />
Scénariste<input type="checkbox" name="sel_field[]" value="5" {SELFIELD5} /><br />
Déssinateur<input type="checkbox" name="sel_field[]" value="6" {SELFIELD6} /><br />
Editeur<input type="checkbox" name="sel_field[]" value="7" {SELFIELD7} /><br />
Collection<input type="checkbox" name="sel_field[]" value="8" {SELFIELD8} /><br />
Date de parution<input type="checkbox" name="sel_field[]" value="9" {SELFIELD9} /><br />
</div>


<div class="expFieldBlock">
<div class="expTitle2">COLLECTION</div>
Date d'ajout<input type="checkbox" name="sel_field[]" value="10" {SELFIELD10} /><br />
Note<input type="checkbox" name="sel_field[]" value="11" {SELFIELD11} /><br />
Commentaire<input type="checkbox" name="sel_field[]" value="12" {SELFIELD12} /><br />
Album prêté<input type="checkbox" name="sel_field[]" value="13" {SELFIELD13} /><br />
Emprunteur<input type="checkbox" name="sel_field[]" value="14" {SELFIELD14} /><br />
Date d'achat<input type="checkbox" name="sel_field[]" value="15" {SELFIELD15} /><br />
Prix d'achat<input type="checkbox" name="sel_field[]" value="16" {SELFIELD16} /><br />
Cadeau<input type="checkbox" name="sel_field[]" value="17" {SELFIELD17} /><br />
Edition originale<input type="checkbox" name="sel_field[]" value="18" {SELFIELD18} /><br />
</div>



<div class="expRadioBlock">

      <div align="center">
       <div class="expTitle2">Format</div>
      </div>

        <p>

         <label><input type="radio" name="info" value="0" {TYPE0} />Excel</label><br/>
          <label><input type="radio" name="info" value="1" {TYPE1} />CSV</label><br/>
          <label><input type="radio" name="info" value="2" {TYPE2} />XML</label><br/>
          <label><input type="radio" name="info" value="3" {TYPE3} />HTML</label>
        </p>
</div>

<div class="expRadioBlock">

      <div align="center">
            <div class="expTitle2">Information à exporter</div>
      </div>

        <p>
          <label><input type="radio" name="contenu" value="0" {CONTENU0} />Collection</label><br/>
          <label><input type="radio" name="contenu" value="1" {CONTENU1} />Achats Futurs</label><br/>
          <label><input type="radio" name="contenu" value="2" {CONTENU2} />Albums manquants</label>
        </p>
</div>
<hr class="expSep" />
<div class="expRadioBlock">
Mémoriser la selection<input type="checkbox" name="memsel" value="checked" /><br />
</div>
<hr class="expSep" />
<p>
<input type="submit" name="Submit2" value="Exporter" /> <br />
</p>
</form>

</div>

      </form>





