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

	document.forms['form1'].action = "{URLREFRESH}?source_id="+document.forms['form1'].elements['txtField1Id'].value+"&dest_id={DESTID}";

    //alert("Saisissez les champs requis");

	return true;

}

 

function urldest(){

	document.forms['form2'].action = "{URLREFRESH}?source_id={SOURCEID}&dest_id="+document.forms['form2'].elements['txtField2Id'].value;

    //alert("Saisissez les champs requis");

	return true;

}



</script>

<table width="100%" border="1">

  <tr> 

    <td colspan="2" align="center"><font color="#FF0000" face="Arial, Helvetica, sans-serif"><strong>{ERRORMESSAGE}</strong></font></td>

  </tr>

  <tr bgcolor="#FFFFCC"> 

    <td width="50%" bordercolor="#CCCCCC" bgcolor="#CCCCCC"> <p><strong>AUTEUR 

        A SUPPRIMER</strong></p>

      <form name="form1" method="post" action="" onSubmit="return urlsource()">

        <input name="txtField1Id" type="text" id="txtField1Id" value="{AUTEURID1}" size="5">

		<input name="txtField1" type="text" id="txtField1" value="{AUTEUR1}" size="40">		

        <a href="{URLEDITEDIT1}"><img src="{URLSITEIMAGE}edit.gif" width="18" height="13" border="0"></a> 

        <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('auteur',document.form1.txtField1.value,'form1','txtField1',true)"><br>

        <input name="Submit1" type="submit" id="Submit1" value="Rafraichir">

      </form></td>

    <td width="50%"> <p><strong>AUTEUR A GARDER</strong></p>

      <form name="form2" method="post" action="" onSubmit="return urldest()">

        <input name="txtField2Id" type="text" id="txtField2Id" value="{AUTEURID2}" size="5">

        <input name="txtField2" type="text" id="txtField2" value="{AUTEUR2}" size="40">

        <a href="{URLEDITEDIT2}"><img src="{URLSITEIMAGE}edit.gif" width="18" height="13" border="0"></a> 

        <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('auteur',document.form2.txtField2.value,'form2','txtField2',true)"><br>

        <input name="Submit1" type="submit" id="Submit1" value="Rafraichir">

      </form></td>

  </tr>

  <tr> 

    <td><table width="100%" border="1" cellspacing="0">

        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC"> 

          <td>Prenom</td>

          <td colspan="4">{PRENOM1}</td>

        </tr>

        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC"> 

          <td>Nom</td>

          <td colspan="4">{NOM1}</td>

        </tr>

        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC"> 

          <td>Date Naissance</td>

          <td colspan="4">{DTENAISS1}</td>

        </tr>

        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC"> 

          <td>Date Dec&egrave;s</td>

          <td colspan="4">{DTEDECES1}</td>

        </tr>

        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC"> 

          <td>Nationalit&eacute;</td>

          <td colspan="4">{NATION1}</td>

        </tr>

        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC"> 

          <td>&nbsp;</td>

          <td><div align="center">Sc&eacute;nariste</div></td>

          <td><div align="center">Dessinateur</div></td>

          <td><div align="center">Coloriste</div></td>

          <td>&nbsp;</td>

        </tr>

        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC"> 

          <td>&nbsp;</td>

          <td><div align="center"> 

              <input type="checkbox" name="checkbox" value="checkbox" {ISSCEN1} disabled>

            </div></td>

          <td><div align="center"> 

              <input name="checkbox2" type="checkbox" disabled value="checkbox" {ISDESS1}>

            </div></td>

          <td><div align="center"> 

              <input type="checkbox" name="checkbox3" value="checkbox" {ISCOLOR1} disabled>

            </div></td>

          <td>&nbsp;</td>

        </tr>

        <tr bordercolor="#CCCCCC" bgcolor="#CCCCCC"> 

          <td width="22%">Commentaire</td>

          <td width="78%" colspan="4">{COMMENT1}</td>

        </tr>

      </table></td>

    <td><table width="100%" border="1" cellspacing="0">

        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC"> 

          <td>Prenom</td>

          <td colspan="4">{PRENOM2}</td>

        </tr>

        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC"> 

          <td>Nom</td>

          <td colspan="4">{NOM2}</td>

        </tr>

        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC"> 

          <td>Date Naissance</td>

          <td colspan="4">{DTENAISS2}</td>

        </tr>

        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC"> 

          <td>Date Dec&egrave;s</td>

          <td colspan="4">{DTEDECES2}</td>

        </tr>

        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC"> 

          <td>Nationalit&eacute;</td>

          <td colspan="4">{NATION2}</td>

        </tr>

        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC"> 

          <td>&nbsp;</td>

          <td> 

            <div align="center">Sc&eacute;nariste</div></td>

          <td> 

            <div align="center">Dessinateur</div></td>

          <td> 

            <div align="center">Coloriste</div></td>

          <td>&nbsp;</td>

        </tr>

        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC"> 

          <td>&nbsp;</td>

          <td> 

            <div align="center"> 

              <input type="checkbox" name="checkbox" value="checkbox" {ISSCEN2} disabled>

            </div></td>

          <td> 

            <div align="center"> 

              <input type="checkbox" name="checkbox2" value="checkbox" {ISDESS2} disabled>

            </div></td>

          <td> 

            <div align="center"> 

              <input type="checkbox" name="checkbox3" value="checkbox" {ISCOLOR2} disabled>

            </div></td>

          <td>&nbsp;</td>

        </tr>

        <tr bordercolor="#FFFFCC" bgcolor="#FFFFCC"> 

          <td width="22%">Commentaire</td>

          <td width="78%" colspan="4">{COMMENT2}</td>

        </tr>

      </table></td>

  </tr>

  <tr> 

    <td bgcolor="#CCCCCC"> <p><strong>Statistiques</strong></p>

      <p>Cet auteur a travaill&eacute; sur {NBUSERS1} album(s).</p></td>

    <td bordercolor="#FFFFCC" bgcolor="#FFFFCC"> <p><strong>Statistiques</strong></p>

      <p>Cet auteur a travaill&eacute; sur {NBUSERS2} album(s).</p></td>

  </tr>

  <tr> 

    <td colspan="2" align="center"><p><a href="{URLECHANGE}">Inverser la séléction</a><br>

        <a href="{URLFUSION}" onClick="return valider()">Fusionner les deux auteurs</a></p></td>

  </tr>

</table>

