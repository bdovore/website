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
  if((document.forms['form1'].elements['txtEdition1Id'].value != "") && (document.forms['form2'].elements['txtEdition2Id'].value != "")) {
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
    document.forms['form1'].action = "{URLREFRESH}?source_id="+document.forms['form1'].elements['txtEdition1Id'].value+"&dest_id={DESTID}";
    //alert("Saisissez les champs requis");
    return true;
}

function urldest(){
    document.forms['form2'].action = "{URLREFRESH}?source_id={SOURCEID}&dest_id="+document.forms['form2'].elements['txtEdition2Id'].value;
    //alert("Saisissez les champs requis");
    return true;
}

</script>

<table width="100%" border="1">
    <tr>
        <td colspan="2" align="center">
            <font color="#FF0000" face="Arial, Helvetica, sans-serif"><strong>{ERRORMESSAGE}</strong></font>
        </td>
    </tr>
    <tr bgcolor="#FFFFCC">
        <td width="50%" bordercolor="#CCCCCC" bgcolor="#CCCCCC">
            <p><strong>EDITION A SUPPRIMER</strong></p>
            <form name="form1" method="post" action="" onSubmit="return urlsource()">
                <input name="txtEdition1Id" type="text" id="txtEdition1Id" value="{EDITIONID1}" size="5" />
                <input name="Submit1" type="submit" id="Submit1" value="Rafraichir" />
            </form>
            <a href="{URLEDITION1}">{URLEDITION1}</a>
        </td>
        <td width="50%">
            <p><strong>EDITION A GARDER</strong></p>
            <form name="form2" method="post" action="" onSubmit="return urldest()">
                <input name="txtEdition2Id" type="text" id="txtEdition2Id" value="{EDITIONID2}" size="5" />
                <input name="Submit1" type="submit" id="Submit1" value="Rafraichir" />
            </form>
            <a href="{URLEDITION2}">{URLEDITION2}</a>
        </td>
    </tr>
    <!-- Affichage de l'édition à supprimer -->
    <tr>
        <td>
            <table width="100%" border="1" cellspacing="0">

                <!-- Série -->
                <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC">
                     <td width="22%">Serie</td>
                     <td width="43%">{IDSERIE1}- {SERIE1}</td>
                     <td width="35%" rowspan="12" align="center" valign="middle">
                        <img src="{URLIMAGE1}" width="150" height="210" />
                    </td>
                </tr>

                <!-- Album -->
                <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC">
                      <td>Tome (<a href="{URL_SITE}adminalbums.php?alb_id={TOMEID1}">album</a>)</td>
                      <td>{TOME1}</td>
                </tr>
                <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC">
                      <td>Genre</td>
                      <td>{IDGENRE1} - {GENRE1}</td>
                </tr>
                <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC">
                      <td>Aper&ccedil;u histoire</td>
                      <td>{HISTOIRE1}</td>
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

                <!-- Edition -->
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
                      <td colspan="2">{DTEPAR1}</td>
                </tr>
                <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC">
                      <td>EAN</td>
                      <td colspan="2">{EAN1}</td>
                </tr>
                <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC">
                      <td>ISBN</td>
                      <td colspan="2">{ISBN1}</td>
                </tr>
                <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC">
                      <td>Description de l'édition</td>
                      <td colspan="2">{DESCRIPTED1}</td>
                </tr>
            </table>
        </td>

        <!-- Affichage de l'édition à garder -->
        <td>
            <table width="100%" border="1" cellspacing="0">

                <!-- Série -->
                <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC">
                    <td width="22%">Serie</td>
                    <td width="43%">{IDSERIE2}- {SERIE2}</td>
                    <td width="35%" rowspan="12" align="center" valign="middle">
                        <img src="{URLIMAGE2}" width="150" height="210" />
                    </td>
                </tr>

                <!-- Album -->
                <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC">
                      <td>Tome (<a href="{URL_SITE}adminalbums.php?alb_id={TOMEID2}">album</a>)</td>
                      <td><b><a href="{URL_SITE}adminalbums.php?alb_id={TOMEID2}">{TOME2}</a></b></td>
                </tr>
                <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC">
                      <td>Genre</td>
                      <td>{IDGENRE2} - {GENRE2}</td>
                </tr>
                 <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC">
                      <td>Aper&ccedil;u histoire</td>
                      <td>{HISTOIRE2}</td>
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

                <!-- Edition -->
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
                      <td colspan="2">{DTEPAR2}</td>
                </tr>
                <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC">
                      <td>EAN</td>
                      <td colspan="2">{EAN2}</td>
                </tr>
                <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC">
                      <td>ISBN</td>
                      <td colspan="2">{ISBN2}</td>
                </tr>
                <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC">
                      <td>Description de l'édition</td>
                      <td colspan="2">{DESCRIPTED2}</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td bgcolor="#CCCCCC">
            <p><b>Statistiques</b></p>
            <p>{NBUSERS1} utilisateur(s) ont cette édition dans leur collection.</p>
        </td>
        <td bordercolor="#FFFFCC" bgcolor="#FFFFCC">
            <p><b>Statistiques</b></p>
            <p>{NBUSERS2} utilisateur(s) ont cette édition dans leur collection.
        </td>
    </tr>
    <tr>
        <td colspan="2" align="center">
            <p>
                <a href="{URLECHANGE}">Inverser la séléction</a>
                <br />
                <a href="{URLFUSION}" onClick="return valider()">Fusionner les deux éditions</a>
            </p>
        </td>
    </tr>
</table>