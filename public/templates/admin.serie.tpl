<SCRIPT type="text/javascript"><!--
function valider(){
    // si la valeur du champ Série est non vide
    if((document.forms['input_form'].elements['txtSerie'].value != "") && (document.forms['input_form'].elements['txtGenreId'].value != "")) {
        // les données sont ok, on peut envoyer le formulaire    
        return true;
    }else {
        // sinon on affiche un message
        alert("Saisissez les champs requis");
        // et on indique de ne pas envoyer le formulaire
        return false;
    } 
}
//--></SCRIPT>

<!--<body onLoad="{BODYONLOAD}">-->

<form action="{URLACTION}" method="post" name="input_form" onSubmit="return valider()">
    <table width="100%" class="fond_admin">
        <tr>
            <!-- colonne de gauche -->
            <td width="73%"> 
                {ADMINSERIEDETAIL}

                <div class="submit"> 
                    <input type="submit" name="Submit" value="{ACTIONNAME}" />
                </div>
                
            </td>
            
            <!-- colonne de droite -->
            <td width="27%" valign="top" class="cadre_texte-admin">
                <div class="b">{NBALBUMS} albums dans la s&eacute;rie :</div>
                <!-- BEGIN AlbBlock -->
                <a href="{ALB_LIEN}">{ALB_TITRE}</a><br />
                <!-- END AlbBlock -->
                <p><div class="b"><a href="{URLAJOUTALB}">Ajouter un album dans la série</a></div></p>
            </td>
        </tr>
        <tr class="cadre_texte-admin"> 
            <td>
                <p>
                    {WARNING_NOTE}
                    <a href="{URLDELETE}">Supprimer cette s&eacute;rie</a><br />
                    Fusionner cette s&eacute;rie
                </p>
                <p>
                    {NBAUTEURS} auteurs ont travaill&eacute; sur la s&eacute;rie : 
                    <br />
                    <!-- BEGIN AutBlock -->
                    <a href="{AUT_LIEN}">{AUT_TITRE}</a> - 
                    <!-- END AutBlock -->
                </p>
                <p>&nbsp;</p>
            </td>
            <td>
                <div class="b">Menu Express</div>
                <p>
                    <ul>
                        <a href="{URLMASSDETAIL}"><li>Affichage rapide des albums</li></a>
                        <a href="{URLMASSUPDATE}"><li>Mise à jour multiples</li></a>
                        <a href="{URLMASSRENAME}"><li>Renommer les albums de la série</li></a>
                        <a href="{URLMASSCOUV}"><li>Controler les couvertures de cette série</li></a>
                    </ul>
                </p>
            </td>
        </tr>
    </table>
</form>