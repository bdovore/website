<SCRIPT LANGAUGE="JavaScript"><!--
function newWindow(myCategory,myText,myForm,myElement,isMandatory,myEditeur) {
    if ((isMandatory == true) && (myText.length < 3)) {
        alert('Veuillez saisir au moins 3 caractères');
    }
    else {
        if (myCategory == "collection") {
            if (myEditeur == '') {
                alert("Veuillez saisir l'éditeur en premier");
            }
            else {
            mywindow=open("pickup.php?cat=" + myCategory + "&text=" + myText + "&ReturnDoc=" + myForm + "&ReturnElement=" + myElement + "&IdEditeur=" + myEditeur,'myname','scrollbars=yes,resizable=yes,width=350,height=270');
            }
        }
        else {
            mywindow=open('pickup.php?cat=' + myCategory + "&text=" + myText + "&ReturnDoc=" + myForm + "&ReturnElement=" + myElement,'myname','scrollbars=yes,resizable=yes,width=350,height=270');
        }
    }
}

function urlsource(){
    document.forms['form1'].action = "{URLREFRESH}?serie="+document.forms['form1'].elements['txtSerieId'].value;
    //alert("Saisissez les champs requis");
    return true;
}

function Chargement() {
 for (i = 0; i < document.myform.elements.length; i++) {
   if (document.myform.elements[i].name == "alb_id[]") {
     document.myform.elements[i].checked = true;
   }
 }
}
//--></SCRIPT>
<table width="100%" border="1">
  <tr>
    <!-- Debut colonne Gauche-->
    <td width="50%" valign="top"> <form name="form1" method="post" action="" onSubmit="return urlsource()">
        <table width="100%" border="1" cellpadding="5" cellspacing="0" bordercolor="#333333" bgcolor="#FFFFCC">
          <tr bordercolor="#FFFFCC">
            <td width="10%" class="label"> <div align="center">Serie</div></td>
            <td width="10%"> <div align="left">
                <input name="txtSerieId" type="text" id="txtSerieId" value="{IDSERIE}" size="5">
              </div></td>
            <td width="27%"> <input name="txtSerie" type="text" id="txtSerie" value="{SERIE}" size="40"></td>
            <td width="28%"><img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('serie',document.form1.txtSerie.value,'form1','txtSerie',true)"></td>
            <td width="51"> <input name="Submit1" type="submit" id="Submit1" value="Rafraichir"></td>
          </tr>
        </table>
      </form>
      <br> <form action="{URLACTION}" method="post" enctype="multipart/form-data" name="myform" target="_self" id="myform">
        <table width="100%" border="1" cellpadding="5" cellspacing="0" bordercolor="#333333" bgcolor="#FFFFCC">
          <tr bordercolor="#FFFFCC">
            <td width="24%">Nouveau titre</td>
            <td width="76%">
              <input name="txtNouvTitre" type="text" value="{NOUVTITRE}" size="50"></td>
          </tr>
        </table>
        <br>
        <table width="100%" border="1" cellpadding="5" cellspacing="0" bordercolor="#333333" bgcolor="#FFFFCC">
          <tr bordercolor="#999999">
            <td width="11%" align="center">Tome</td>
            <td width="79%">Titre</td>
            <td width="10%">Traiter</td>
          </tr>
          <!-- BEGIN AlbumsBlock -->
          <tr bordercolor="#999999">
            <td align="center">{TOME}</td>
            <td>{TITRE}</td>
            <td align="center">
              <input type="checkbox" name="alb_id[]" value="{IDTOME}">
              <input name="num_tome[{IDTOME}]" type="hidden" value="{TOME}"> </td>
          </tr>
          <!-- END AlbumsBlock -->
          <tr bordercolor="#FFFFCC">
            <td>&nbsp;</td>
            <td colspan="2" align="right"><a name="ici" href="#ici" onClick="Chargement();">Tout
              selectionner</a></td>
          </tr>
        </table>
        <br>
        <p>
        <div align="center"></p>
          <input type="submit" name="Submit" value="{ACTIONNAME}">
          <br>
        </div>
      </form></td>
    <!-- Debut colonne Gauche-->
    <td width="50%"> <table width="100%" border="1">
        <tr>
          <td bordercolor="#FFFFCC" bgcolor="#FFFFCC"><p><strong>Aide</strong><br>
              Ce form permet de renommer tous les albums d'une série.<br>
              L'expression #tome# sera automatiquement remplacée par le numéro
              de tome.<br>
              Seul les albums selectionnés seront traités.
            </p>
            <p><strong>Menu Express</strong><br>
              <a href="{URLEDITSERIE}">Retour à la fiche série</a><br>
            </p></td>
        </tr>
      </table></td>
  </tr>
</table>

