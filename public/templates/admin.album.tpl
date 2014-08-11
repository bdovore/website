<script type="text/javascript"><!--
function verifFieldNull(myIDserie,myTitreVolume,myIDscen,myIDdess,myIDcolo,myIDedit,myIDcoll) {
    if(myIDserie == "") {
        alert('Veuillez attribuer une série à l\'album rogntudju !');
        return false;
    }else if(myTitreVolume==""){
        alert('Veuillez indiquer un titre pour le volume ajouté');
        return false;
    }else if(myIDscen==""){
        alert('Veuillez valider un scénariste');
        return false;
    }else if(myIDdess==""){
        alert('Veuillez valider un dessinateur');
        return false;
    }else if(myIDcolo==""){
        alert('Veuillez valider un coloriste (Id=2 si inconnu, 3 si noir et blanc)');
        return false;
    }else if(myIDedit==""){
        alert('Veuillez valider un éditeur');
        return false;
    }else if(myIDcoll==""){
        alert('Veuillez valider une collection');
        return false;
    }else{
        return true;
    }
}
//--></script>

<form action="{URLACTION}" method="post" enctype="multipart/form-data" name="myform" target="_self" id="myform" onsubmit="return verifFieldNull(document.myform.txtSerieId.value,document.myform.txtTitre.value,document.myform.txtScenarId.value,document.myform.txtDessiId.value,document.myform.txtColorId.value,document.myform.txtEditeurId.value,document.myform.txtCollecId.value)">
    <table class="fond_admin">
        <!--DWLayoutTable-->
        <tr>
            <!-- colonne de gauche -->
            <td width="65%" rowspan="4" valign="top">
                {ADMINSERIEDETAIL}                                
                {ADMINALBUMDETAIL}                                          
                {ADMINEDITIONDETAIL}                
                <div class="submit"> 
                    <input type="submit" name="Submit" value="{ACTIONNAME}" />
                </div>
            </td>
            
            <!-- colonne de droite -->
            <td width="35%" valign="top"> 
                {ADMINEDITIONCOUV}
                <table class="cadre_texte-admin" width="100%">
                    <tr> 
                        <td>
                            <p><div class="b">Statistiques</div></p>
                            <ul>
                                <li>{NBUSERS} utilisateur(s) possèdent cet album</li>
                                <li>{NBUSERS2} commentaire(s) sur cet album</li>
                            </ul>
                            <p><div class="b">Menu Express</div></p>
                            <p>
                                <ul>
                                    <a href="{URLDELETE}"><li>Supprimer cet album</li></a>
                                    <a href="{URLFUSION}"><li>Fusionner cet album</li></a>
                                    <a href=# onclick="javascript:window.open('addserie.php','myserie','scrollbars=no,resizable=yes,width=440,height=140');return(false)">
                                        <li>
                                            Ajout rapide d'une série
                                        </li>
                                    </a>
                                    <a href=# onclick="javascript:window.open('addauteur.php','myauteur','scrollbars=no,resizable=yes,width=400,height=150');return(false)">
                                        <li>
                                            Ajout rapide d'un auteur
                                        </li>
                                    </a>
                                </ul>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</form>
