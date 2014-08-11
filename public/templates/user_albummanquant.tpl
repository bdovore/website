<SCRIPT language="Javascript"> 
<!-- 
  
function Chargement() { 
 d = document.exclAlb;
 for (i = 0; i < d.elements.length; i++) {
   if (d.elements[i].name == "sel_tome[]") {
     d.elements[i].checked = true;
   }
 }
} 
  
//--> 
</SCRIPT>


{MENUCOLL}
<div class="cadre1">
<div class="genTitle">S&eacute;ries &agrave; compl&eacute;ter</div>
<form name ="selSerie" action="{ACTION1}" method="post">

<div class="genBlock">
<p>Rechercher les albums manquants dans :</p>
<p><select name="lstSerie" size="1" id="lstSerie" onChange="submit();">{OPTSOURCE}</select></p>
<p><a href="{URLEXCLUSERIE}">Exclure cette série de mes albums à compléter</a></p>
<p><a href="{RAZSERIE}">Annuler les exclusions pour cette série</a></p>
</div>

</form>
<form name ="exclAlb" action="{ACTION2}" method="post">
<div class="genBlock"> 
        <table class="genTable">
          <tr> 
            <td class="genTitle">Sel</td>
            <td class="genTitle"><a href="{URLSITE}membres/albmanquant.php?cle=1&sort=asc&id_serie={SERID}"><img src="{URLSITEIMAGE}flechehautr.gif" width="12" height="12" hspace="5" border="0"></a>Titre<a href="{URLSITE}membres/albmanquant.php?cle=1&sort=desc&id_serie={SERID}"><img src="{URLSITEIMAGE}flechebasr.gif" width="12" height="12" hspace="5" border="0"></a></td>
            <td class="genTitle"><a href="{URLSITE}membres/albmanquant.php?cle=2&sort=asc&id_serie={SERID}"><img src="{URLSITEIMAGE}flechehautr.gif" width="12" height="12" hspace="5" border="0"></a>Tome<a href="{URLSITE}membres/albmanquant.php?cle=2&sort=desc&id_serie={SERID}"><img src="{URLSITEIMAGE}flechebasr.gif" width="12" height="12" hspace="5" border="0"></a></td>
            <td class="genTitle"><a href="{URLSITE}membres/albmanquant.php?cle=3&sort=asc&id_serie={SERID}"><img src="{URLSITEIMAGE}flechehautr.gif" width="12" height="12" hspace="5" border="0"></a>Date 
              Parution <a href="{URLSITE}membres/albmanquant.php?cle=3&sort=desc&id_serie={SERID}"><img src="{URLSITEIMAGE}flechebasr.gif" width="12" height="12" hspace="5" border="0"></a></td>
          </tr>
          <!-- BEGIN DetailBlock -->
          <tr> 
            <td class="genData"><input type="checkbox" name="sel_tome[]" value="{IDTOME}"></td>
            <td class="genData"><a href="{URLTITRE}">{TITRE}</a></td>
            <td class="genData">{TOME}</td>
            <td class="genData">{DTEPAR}</td>
          </tr>
          <!-- END DetailBlock -->
        </table>
 <p><a name="ici" href="#ici" onClick="Chargement();">Tout sélectionner</a></p>
 <p><input name="B_Add" type="submit" id="B_Add2" value="Exclure des albums manquants"></p>
 </div>
 </form>


<hr class="genSep"/>
<form name ="selSerieExclu" action="{ACTIONEXCLU}" method="post">

<div class="genBlock">
<p>Liste des séries avec au moins un album exclu :</p>
<p><select name="id_serie" size="1" id="idSerieExclu" >{OPTSOURCEEXCLU}</select></p>
<p><input name="actAnnExclu" type="submit" id="B_annEclu" value="Annuler les exclusions pour cette série"></p>
</div>
<hr class="genSep"/>
</form>


</div>
