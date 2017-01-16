<script type="text/javascript">
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

function valider(){
  // si la valeur du champ prenom est non vide
  if((document.forms['form1'].elements['txtTome1Id'].value != "") && (document.forms['form2'].elements['txtTome2Id'].value != "")) {
    // les données sont ok, on peut envoyer le formulaire
    return true;
  }
  else {
    // sinon on affiche un message
    alert("Saisissez les champs requis");
    // et on indique de ne pas envoyer le formulaire
    return false;
  }
}

function urlsource(){
    document.forms['form1'].action = "{URLREFRESH}?source_id="+document.forms['form1'].elements['txtTome1Id'].value+"&dest_id={DESTID}";
    //alert("Saisissez les champs requis");
    return true;
}

function urldest(){
    document.forms['form2'].action = "{URLREFRESH}?source_id={SOURCEID}&dest_id="+document.forms['form2'].elements['txtTome2Id'].value;
    //alert("Saisissez les champs requis");
    return true;
}

</script>
<table width="100%" border="1">
  <tr>
    <td colspan="2" align="center"><font color="#FF0000" face="Arial, Helvetica, sans-serif"><strong>{ERRORMESSAGE}</strong></font></td>
  </tr>
  <tr bgcolor="#FFFFCC">
    <td width="50%" bordercolor="#CCCCCC" bgcolor="#CCCCCC"> <p><strong>ALBUM
        A SUPPRIMER</strong></p>
      <form name="form1" method="post" action="" onSubmit="return urlsource()">
        <input name="txtTome1Id" type="text" id="txtTome1Id" value="{TOMEID1}" size="5">
        <input name="txtTome1" type="text" id="txtTome1" value="{TITRE1}" size="40">
        <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('album',document.form1.txtTome1.value,'form1','txtTome1',true)"><br>
        <input name="Submit1" type="submit" id="Submit1" value="Rafraichir">
      </form></td>
    <td width="50%"> <p><strong>ALBUM A GARDER</strong></p>
      <form name="form2" method="post" action="" onSubmit="return urldest()">
        <input name="txtTome2Id" type="text" id="txtTome2Id" value="{TOMEID2}" size="5">
        <input name="txtTome2" type="text" id="txtTome2" value="{TITRE2}" size="40">
        <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('album',document.form2.txtTome2.value,'form2','txtTome2',true)"><br>
        <input name="Submit1" type="submit" id="Submit1" value="Rafraichir">
      </form></td>
  </tr>
  <tr>
    <td valign=top><table width="100%" border="1" cellspacing="0">
        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC">
          <td width="22%">Serie</td>
          <td width="43%">{IDSERIE1}- {SERIE1}</td>
          <td width="35%" rowspan="12" align="center" valign="middle"><img src="{URLIMAGE1}" width="150" height="210"></td>
        </tr>
        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC">
          <td>Tome</td>
          <td>{TOME1}</td>
        </tr>
        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC">
          <td>Genre</td>
          <td>{IDGENRE1} - {GENRE1}</td>
        </tr>
        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC">
          <td>Scenariste</td>
          <td>{IDSCENAR1} - {SCENAR1}</td>
        </tr>
        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC">
          <td>Scenariste #2</td>
          <td>{IDSCENARALT1} - {SCENARALT1}</td>
        </tr>
        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC">
          <td>Dessinateur</td>
          <td>{IDDESS1} - {DESS1}</td>
        </tr>
        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC">
          <td>Dessinateur #2</td>
          <td>{IDDESSALT1} - {DESSALT1}</td>
        </tr>
        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC">
          <td>Coloriste</td>
          <td>{IDCOLOR1} - {COLOR1}</td>
        </tr>
        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC">
          <td>Coloriste #2</td>
          <td>{IDCOLORALT1} - {COLORALT1}</td>
        </tr>
        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC">
          <td>Editeur</td>
          <td>{IDEDIT1} - {EDIT1}</td>
        </tr>
        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC">
          <td>Collection</td>
          <td>{IDCOLL1} - {COLL1}</td>
        </tr>
        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC">
          <td>Date Parution</td>
          <td>{DTEPAR1}</td>
        </tr>
        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC">
          <td>Aper&ccedil;u histoire</td>
          <td colspan="2">{HISTOIRE1}</td>
        </tr>
      </table></td>
    <td valign=top><table width="100%" border="1" cellspacing="0">
        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC">
          <td width="22%">Serie</td>
          <td width="43%">{IDSERIE2}- {SERIE2}</td>
          <td width="35%" rowspan="12" align="center" valign="middle"><img src="{URLIMAGE2}" width="150" height="210"></td>
        </tr>
        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC">
          <td>Tome</td>
          <td>{TOME2}</td>
        </tr>
        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC">
          <td>Genre</td>
          <td>{IDGENRE2} - {GENRE2}</td>
        </tr>
        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC">
          <td>Scenariste</td>
          <td>{IDSCENAR2} - {SCENAR2}</td>
        </tr>
        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC">
          <td>Scenariste #2</td>
          <td>{IDSCENARALT2} - {SCENARALT2}</td>
        </tr>
        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC">
          <td>Dessinateur</td>
          <td>{IDDESS2} - {DESS2}</td>
        </tr>
        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC">
          <td>Dessinateur #2</td>
          <td>{IDDESSALT2} - {DESSALT2}</td>
        </tr>
        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC">
          <td>Coloriste</td>
          <td>{IDCOLOR2} - {COLOR2}</td>
        </tr>
        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC">
          <td>Coloriste #2</td>
          <td>{IDCOLORALT2} - {COLORALT2}</td>
        </tr>
        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC">
          <td>Editeur</td>
          <td>{IDEDIT2} - {EDIT2}</td>
        </tr>
        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC">
          <td>Collection</td>
          <td>{IDCOLL2} - {COLL2}</td>
        </tr>
        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC">
          <td>Date Parution</td>
          <td>{DTEPAR2}</td>
        </tr>
        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC">
          <td>Aper&ccedil;u histoire</td>
          <td colspan="2">{HISTOIRE2}</td>
        </tr>
      </table></td>
  </tr>
  <tr>
    <td bgcolor="#CCCCCC"> <p><strong>Statistiques</strong></p>
      <p>{NBUSERS1} utilisateur(s) ont cet album dans leur collection.<br>
        Il existe {NBCOMMENT1} commentaire(s) sur cet album.</p></td>
    <td bordercolor="#FFFFCC" bgcolor="#FFFFCC"> <p><strong>Statistiques</strong></p>
      <p>{NBUSERS2} utilisateur(s) ont cet album dans leur collection.<br>
        Il existe {NBCOMMENT2} commentaire(s) sur cet album.</p></td>
  </tr>
  <tr>
    <td colspan="2" align="center"><p><a href="{URLECHANGE}">Inverser la séléction</a><br>
        <a href="{URLFUSION}" onClick="return valider()">Fusionner les deux albums</a></p></td>
  </tr>
</table>
