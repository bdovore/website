<form action="{URLACTION}" method="post" enctype="multipart/form-data" name="myform" target="_self" id="myform">
    <table class="fond_admin">
        <!--DWLayoutTable-->
        <tr>
            <td width="75%" rowspan="4" valign="top">
                {ADMINSERIEDETAIL}

                {ADMINALBUMDETAIL}

                <table class="tableaux_admin" width="100%">
                    <tr>
                        <td class="label">
                            <p>Différentes éditions : {NBEDITIONS}</p>
                            <table>
                                <tr>
                                    <td width="8%" class="entete">Defaut</td>
                                    <td width="23%" class="entete">Editeur</td>
                                    <td width="23%" class="entete">Collection</td>
                                    <td width="14%" class="entete">Parution</td>
                                    <td width="12%" class="entete">Attributs</td>
                                    <td width="13%" class="entete">Couverture</td>
                                    <td width="7%" class="entete">Editer</td>
                                </tr>
                              <!-- BEGIN EditionBlock -->
                                <tr>
                                    <td align="center" class="data"{BGCOLOR}><p>
                                        <label>
                                        <input name="btnDefEdit" type="radio" value="{BTNVALUE}" {ISCHECKED} />
                                        </label>
                                        <br />
                                    </p></td>
                                    <td class="data"{BGCOLOR}>{EDITEUR}</td>
                                    <td class="data"{BGCOLOR}>{COLLECTION}</td>
                                    <td class="data"{BGCOLOR}>{DTPAR}</td>
                                    <td valign="middle" class="data" align="center"{BGCOLOR}><img src="{IMGTT}" width="15" height="20" /></td>
                                    <td align="center" class="data"{BGCOLOR}><a href="{URLIMAGE}" target="_blank"><img src="{URLIMAGE}" width="50" height="60" border="0" align="absmiddle" /></a></td>
                                    <td align="center" class="data"{BGCOLOR}><a href="{URLEDITEDITION}"><img src="{URLSITEIMAGE}edit.gif" width="18" height="13" border="0" /></a></td>
                                </tr>
                            <!-- END EditionBlock -->
                            </table>
                            <p><a href="{URLAJOUTEDITION}" class="b">Ajouter une nouvelle édition</a></p>
                        </td>
                    </tr>
                </table>
                <div class="submit">
                    <input type="submit" name="Submit" value="{ACTIONNAME}" />
                </div>
            </td>
            <td width="287" height="47" bordercolor="#FFFFFF"></td>
        </tr>

        <!-- colonne de droite -->
        <tr width="100%">
            <td class="cadre_texte-admin">
                <p><strong>Statistiques</strong></p>
                <p>
                    <ul>
                        <li>{NBUSERS} utilisateur(s) ont cet album dans leur collection</li>
                        <li>{NBUSERS2} utilisateur(s) ont un commentaire sur cet album</li>
                    </ul>
                </p>
                <p><strong>Menu Express</strong></p>
                <p>
                    <ul>
                        <a href="{URLDELETE}"><li>Supprimer cet album</li></a>
                        <a href="{URLFUSIONDELETE}"><li>Fusionner en conservant certaines éditions</li></a>
                        <a href="{URLSPLIT}"><li>Créer un nouvel album à partir des éditions actuelles</li></a>
                        <a href="{URLFUSION}"><li>Fusionner cet album</li></a>
                        <li><a href=# onclick="javascript:window.open('addserie.php','myserie','scrollbars=no,resizable=yes,width=440,height=140');return(false)"><li>
                            Ajout rapide d'une série
                        </li></a>
                        <a href=# onclick="javascript:window.open('addauteur.php','myauteur','scrollbars=no,resizable=yes,width=400,height=150');return(false)"><li>
                            Ajout rapide d'un auteur
                        </li></a>
                    </ul>
                </p>
            </td>
        </tr>
  </table>
</form>