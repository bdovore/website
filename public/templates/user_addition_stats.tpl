<link href="{URLSITE}style/addition.css" rel="stylesheet" type="text/css">

{MENUCOLL}
<div class="cadre1">
<a href="{URLSITE}membres/detailAddition.php"><! a href="javascript:alert('Fermer pour travaux !')">Detail 
de la valorisation</a>
</div>

<form name="addForm" method="get" action="">
<div class="cadre1">

  <div class="addTitle">Achats depuis le début de votre collection</div>
  <div class="addFreeBlock"> 
    <table class="addTable">
      <tr> 
        <td class="addTitle">Annee</td>
        <td class="addTitleMiddle">Qté</td>
        <td class="addTitle">Coût</td>
      </tr>
      <!-- BEGIN YearBlock -->
      <tr> 
        <td class="addData"><a href="{URLANNEE}">{ANNEE}</a></td>
        <td class="addData">{QUANTITE}</td>
        <td class="addDataRight">{COUT}</td>
      </tr>
      <!-- END YearBlock -->
    </table>
  </div>

  <div align="center">
    Sélectionner l'information à afficher 
    <input name="annee" type="hidden" value="{ANNEE}">
      <label><input type="radio" name="info" value="0" {TYPE0} onClick="submit();">Coût</label>
      <label><input type="radio" name="info" value="1" {TYPE1} onClick="submit();">Nombre</label>
	</div>
<!--        <div class="addFreeBlock"> <img src="{IMGANNEE}" class="addImg"> </div>-->
    <div class="addFreeBlock" style="width:601px;height:281px"> <img src="{IMGANNEE}" class="addImg"> </div>
</div>

<div class="cadre1"> 
    <div class="addTitle">Achats pour l'année {ANNEE}</div>
    <div class="addFreeBlock"> 
      <select name="mois" size="12" onChange="submit();" class="addSelect">{OPTMOIS}
      </select>
    </div>
        <table class="addTable">
          <tr> 
            <td class="addTitle">Serie</td>
            <td colspan="2" class="addTitleMiddle">Titre</td>
            <td class="addTitle">Date</td>
            <td class="addTitle">Coût</td>
            <td width="10">&nbsp;</td>
          </tr>
          <!-- BEGIN MonthBlock -->
          <tr> 
            <td class="addDataSmall"><a href="{URLALBSERIE}" class="addLiens">{ALBSERIE}</a></td>
            <td class="addDataSmall">{ALBTOME}</td>
            <td class="addDataSmall"><a href="#" onclick="window.open('{URLSITE}membres/useralbum.php?id_tome={IDTOME}&id_edition={IDEDITION}','Album','width=580,height=530,scrollbars=1')"  class="addLiens">{ALBTITRE}</a></td>
            <td class="addDataSmall">{ALBDATEACHAT}</td>
            <td class="addDataSmall">{ALBCOUT}</td>
            <td class="addDataSmall">&nbsp;</td>
          </tr>
          <!-- END MonthBlock -->
        </table>

      <div class="addFreeBlock" style="width:601px;height:281px"> <img src="{IMGMOIS}" class="addImg"> </div>
</div>
</form>





