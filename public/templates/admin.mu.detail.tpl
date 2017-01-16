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

function CheckDate(d) {
      // Cette fonction vérifie le format JJ/MM/AAAA saisi et la validité de la date.
      // Le séparateur est défini dans la variable separateur
      var amin=1800; // année mini
      var amax=2050; // année maxi
      var separateur="/"; // separateur entre jour/mois/annee
      var j=(d.substring(0,2));
      var m=(d.substring(3,5));
      var a=(d.substring(6));
      var ok=1;
      if (d == '' ) {
    return ok;
      }
      if ( ((isNaN(j))||(j<0)||(j>31)) && (ok==1) ) {
         alert("Le jour d'une date n'est pas correct."); ok=0;
      }
      if ( ((isNaN(m))||(m<0)||(m>12)) && (ok==1) ) {
         alert("Le mois d'une date n'est pas correct."); ok=0;
      }
      if ( ((isNaN(a))||(a<amin)||(a>amax)) && (ok==1) ) {
         alert("L'année d'une date n'est pas correcte."); ok=0;
      }
      if ( ((d.substring(2,3)!=separateur)||(d.substring(5,6)!=separateur)) && (ok==1) ) {
         alert("Les séparateurs doivent être des "+separateur); ok=0;
      }
      if (ok==1) {
         var d2=new Date(a,m-1,j);
         j2=d2.getDate();
         m2=d2.getMonth()+1;
         a2=d2.getFullYear();
         if (a2<=100) {a2=1900+a2}
         if ( (j!=j2)||(m!=m2)||(a!=a2) ) {
            alert("La date "+d+" n'existe pas !"+ j + " " + j2 + " " + m + " " + m2 + " " + a + " "+ a2);
            ok=0;
         }
      }
      return ok;
   }

function valid_form() {
 for (i = 0; i < document.myform.elements.length; i++) {
    //alert(String(document.myform.elements[i].name).substring(0,7));
     if(String(document.myform.elements[i].name).substring(0,7) == "txtDate")
    {
        if (CheckDate(document.myform.elements[i].value) != 1)
        {
            return false;
        }
    }
 }
}

//--></SCRIPT>
<table width="100%" border="1">
  <tr>
    <!-- Debut colonne Gauche-->
    <td width="85%" valign="top"> <form name="form1" method="post" action="" onSubmit="return urlsource()">
        <table width="50%" border="1" cellpadding="5" cellspacing="0" bordercolor="#333333" bgcolor="#FFFFCC">
          <tr bordercolor="#FFFFCC">
            <td width="10%" class="label"> <div align="center">Serie</div></td>
            <td width="10%"> <div align="left">
                <input name="txtSerieId" type="text" id="txtSerieId" value="{IDSERIE}" size="5" />
              </div></td>
            <td width="27%"> <input name="txtSerie" type="text" id="txtSerie" value="{SERIE}" size="40"></td>
            <td width="28%"><img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('serie',document.form1.txtSerie.value,'form1','txtSerie',true)"></td>
            <td width="51"> <input name="Submit1" type="submit" id="Submit1" value="Rafraichir"></td>
          </tr>
        </table>
      </form>
      <br /> <form action="{URLACTION}" method="post" enctype="multipart/form-data" onsubmit='return valid_form()' name="myform" target="_self" id="myform">
        <br />
        <table width="100%" border="1" cellpadding="5" cellspacing="0" bordercolor="#333333" bgcolor="#FFFFCC">
          <tr bordercolor="#999999">
            <td width="5%" align="center">Tome</td>
            <td width="31%">Titre</td>
            <td width="6%">Type</td>
            <td width="7%">Int&eacute;grale</td>
            <td width="10%">ISBN</td>
            <td width="10%">EAN</td>
            <td width="11%">Date Parution</td>
            <td width="6%">Prix</td>
            <td width="14%">&nbsp;</td>
          </tr>
          <!-- BEGIN AlbumsBlock -->
          <tr bordercolor="#999999" bgcolor="{BGCOLOR}">
            <td align="center"><input type="hidden" name="id_tome[]" value="{IDTOME}" />
              <input name="txtNumTome[{IDTOME}]" type="text" id="txtNumTome" value="{NUMTOME}" size="2" /></td>
            <td align="center"><input name="txtTitre[{IDTOME}]" type="text" id="txtTitre" value="{TITRE}" size="45" /></td>
            <td align="center"><select name="lstType[{IDTOME}]" size="1">{OPTTYPE}</select></td>
            <td align="center"><input type="checkbox" name="chkIntegrale[{IDTOME}]" value="checkbox" {ISINT} /></td>
            <td align="center"><input name="txtISBN[{IDTOME}]" type="text" value="{ISBN}" size="10" maxlength="10" /></td>
            <td align="center"><input name="txtEAN[{IDTOME}]" type="text" value="{EAN}" size="13" maxlength="13" /></td>
            <td align="center"><input name="txtDateParution[{IDTOME}]" type="text" id="txtDateParution" value="{DTPAR}" size="15" /></td>
            <td align="center"><input name="txtPrix[{IDTOME}]" type="text" id="txtPrix" value="{PRIX}" size="4" /></td>
            <td align="center"><a href="{URLEDITEDIT}"><img src="{URLSITEIMAGE}edit.gif" width="18" height="13" border="0" /></a></td>
          </tr>
          <!-- END AlbumsBlock -->
          <tr bordercolor="#FFFFCC">
            <td>&nbsp;</td>
            <td colspan="8" align="right">&nbsp;</td>
          </tr>
        </table>
        <br />
        <p>
        <div align="center"></p>
          <input type="submit" name="Submit" value="{ACTIONNAME}">
          <br />
        </div>
      </form></td>
    <!-- Debut colonne Gauche-->
    <td width="15%"> <table width="100%" border="1">
        <tr>
          <td bordercolor="#FFFFCC" bgcolor="#FFFFCC"><p><strong>Aide</strong><br />
              Les albums ayant plusieurs éditions apparaissent en rouge<br />
              La décimale du prix doit être indiquée par un point et non une virgule<br />
            </p>
            <p><strong>Menu Express</strong><br />
              <a href="{URLEDITSERIE}">Retour à la fiche série</a><br />
            </p></td>
        </tr>
      </table></td>
  </tr>
</table>