{MENUCOLL}
<div class="cadre1">
	<form name="form1" method="post" action="../membres/profil.php?act=update&user_id={USERID}">
	  <h2>Profil de <font color="#990000">{UTILISATEUR}</font></h2>


	    <table align="center" border="0" cellpadding="5" cellspacing="0" bordercolor="#FFFFCC">
          <tr> 
		  <td align="right">Adresse e-mail :</td>
		  <td><input name="txtemail" type="text" id="txtemail" value="{EMAIL}" size="50"></td>
		  <td align="center" rowspan="4"><img name="Photo" src="{PICTURE}" alt=""></td>
		</tr>
		<tr> 
		  <td align="right">Ville :</td>
		  <td><input name="txtlocation" type="text" id="txtlocation" value="{LOCATION}"></td>
		</tr>
        <tr> 
		  <td align="right">Date de naissance :</td>
		  <td><input name="txtanniv" type="text" id="txtanniv" value="{BIRTHDAY}">(aaaa-mm-jj)</td>
		</tr>
		<tr> 
		  <td align="right">Mot de passe :</td>
		    <td><a href="../membres/profil.php?act=newpass&user_id={USERID}">
		    Changer le mot de passe</a></td>
		</tr>
		<tr> 
		  <td colspan=2>&nbsp;</td>
		  <td align="center"><a href="../membres/profil.php?act=image&user_id={USERID}">
		  Changer l'avatar du forum</a></td>
		</tr>
	  </table>
	  <br />
	  <h3>Personnalisation</h3>
	    <table border="0" cellpadding="5" cellspacing="0" bordercolor="#FFFFCC">
          <tr> 
            <td align="right">Nombres de réponses affichées lors des recherches :</td>
            <td><select name="lstRowDisplay" size="1">
				{OPTIONDISPLAY}
			  </select></td>
          </tr>
          <tr> 
            <td align="right">Nombre d'albums affich&eacute;s dans la fiche s&eacute;rie :</td>
            <td><select name="lstRowSerie" size="1" id="lstRowSerie">
				{OPTIONDISPLAYSERIE}
			  </select></td>
          </tr>
          <tr> 
            <td  align="right">Autoriser les autres utilisateurs &agrave; consulter ma collection :</td>
            <td><select name="lstOpenCollec" size="1">
                <option value="Y" {YESISSELECTED}>Oui</option>
                <option value="N" {NOISSELECTED}>Non</option>
              </select></td>
          </tr>
          <tr> 
            <td colspan="2">D'autres utilisateurs pourront acc&eacute;der &agrave; 
              votre collection via l'adresse suivante : <br /><a href="{URLCOLLEC}">{URLCOLLEC}</a></td>
          </tr>
          <tr> 
            <td align="right">Selection du carr&eacute; magique :</td>
            <td><select name="lstCarre" size="1">{OPTIONCARRE}
              </select></td>
          </tr>
          <tr> 
		  <td align="right">Je souhaite recevoir la newsletter de BDovore :</td>
		    <td><input type="checkbox" name="txtNewsletter" value="checked" {IS_NEWSLETTER}></td>
		</tr>

	  </table>
<br>

	<div align="center">
	<input type="submit" name="Submit2" value="Mettre a jour"><br /><br />
	</div>
</div>

<div class="cadre1">

      <h2>Suppression de compte BDoVORE</h2>
      <div align="center">Si vous voulez supprimer votre profil BDoVore définitivement, cette option est pour vous.
      Votre collection, les commentaires et notes que vous avez pu ajouter seront supprimés.
      Peut être souhaitez vous <a href="../membres/export.php">exporter votre collection</a> avant cela ?<br />
          <br />
          <input type="button" name="delete" value="Suppression du compte BDoVORE" onClick="
          if(confirm('Suppression définitive du compte sans retour arrière !')){
           if(confirm('Après cette dernière confirmation, votre compte sera supprimé !\nVoules vous vraiment supprimer votre compte et donc votre collection ?')){
           window.location.href='../membres/profil.php?act=delete&user_id={USERID}';
          }
          }
          "></div>

</form>
</div>
