<SCRIPT type="text/javascript"><!--
function newWindow(myCategory,myText,myForm,myElement,isMandatory,myEditeur) {
	if ((isMandatory == true) && (myText.length < 2)) {
		alert('Veuillez saisir au moins 2 caractères');
	}else {
		if (myCategory == "collection") {
			if (myEditeur == '') {
				alert("Veuillez saisir l'éditeur en premier");
			}else {
                mywindow=open("pickup.php?cat=" + myCategory + "&text=" + myText + "&ReturnDoc=" + myForm + "&ReturnElement=" + myElement + "&IdEditeur=" + myEditeur,'myname','scrollbars=yes,resizable=yes,width=350,height=270');
			}
		}else {
			mywindow=open('pickup.php?cat=' + myCategory + "&text=" + myText + "&ReturnDoc=" + myForm + "&ReturnElement=" + myElement,'myname','scrollbars=yes,resizable=yes,width=350,height=270');
		}
	}
}
function verifFieldNull(myField) {
    if(myField == "0") {
        alert('Veuillez attribuer une série à l\'album rogntudju !');
        return false;
    }else{
        return true;
    }
}
function verifAddExisting(myIdTome,myAction) {
    if(myAction == "2") {
        alert("Le membre ne semble pas avoir demandé qu'un album soit ajouté à sa collection ou futurs achats.");
        return false;
    }else if(myIdTome == "") {
        alert("Veuillez indiquer l'identifiant de l'album rogntudju !");
        return false;
    }else{
        return true;
    }
}
//--></SCRIPT>

<div class="fond_admin">
	<div class="titre_admin">Traitement des albums en attente</div>
	<div class="date_before_valid">{DATEBEFOREVALID}</div>
    <table width="100%">
        <tr>
            <form action="{URLACTION}" method="post" enctype="multipart/form-data" name="myform" target="_self" id="myform" onsubmit="return verifFieldNull(document.myform.txtSerieId.value)">
                <!--Debut colonne gauche -->
                <td width="65%" valign="top">
                    <table class="tableaux_admin">
                        <tr> 
                            <td width="15%" class="label" align="center">Album</td>
                            <input name="txtPropId" type="hidden" id="txtPropId" value="{PROPID}" />
                            <td width="85%">
                                <input name="txtTitre" type="text" id="txtTitre" value="{TITRE}" size="65%" class="{CLTITRE}" />
                            </td>
                        </tr>
                        <tr bordercolor="#FFFFCC"> 
                            <td><!--DWLayoutEmptyCell-->&nbsp;</td>
                            <td class="original">{ORITITRE}&nbsp;</td>
                        </tr>
                        <tr bordercolor="#FFFFCC"> 
                            <td colspan="2" class="label">
                                <table width="100%" border="0">
                                    <tr>
                                        <td width="25%" align="center">Type</td>
                                        <td width="25%" align="center">Tome</td>
                                        <td width="25%" align="center">Int&eacute;grale</td>
                                        <td width="25%" align="center">Prix</td>
                                    </tr>
                                    <tr>
                                        <td align="center">
                                            <select name="lstType" size="1">{OPTTYPE}</select>
                                        </td>
                                        <td align="center"><input name="txtNumTome" type="text" id="txtNumTome" value="{TOME}" size="2" class="{CLTOME}" /></td>
                                        <td align="center"><input type="checkbox" name="chkIntegrale" value="checkbox" {ISINT} /></td> 
                                        <td align="center"><input name="txtPrixVente" type="text" id="txtPrixVente" value="{PRIX_VENTE}" size="3" style="text-align: center;" /></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr bordercolor="#FFFFCC"> 
                            <td class="label">Intro Tome</td>
                            <td valign="top">
                                <textarea name="txtHistoire" class="champ_commentaire">{HISTOIRE}</textarea>
                            </td>
                        </tr>
                    </table>
                    
                    <table class="tableaux_admin">
                        <tr> 
                            <td class="label" align="center">Serie</td>
                            <td>
                                <div align="center"> 
                                    <input name="txtSerieId" type="text" id="txtSerieId2" value="{IDSERIE}" size="3" class="{CLIDSERIE}" />
                                </div>
                            </td>
                            <td>
                                <input name="txtSerie" type="text" id="txtSerie3" value="{SERIE}" size="40" class="{CLSERIE}" /> {LIENEDITSERIE}
                            </td>
                            <td>
                                <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('serie',document.myform.txtSerie.value,'myform','txtSerie',true)" />
                            </td>
                        </tr>
                        <tr> 
                            <td colspan="2">&nbsp;</td>
                            <td class="original">{ORISERIE}</td>
                            <td><!--DWLayoutEmptyCell-->&nbsp;</td>
                        </tr>
                        <tr> 
                            <td width="15%" class="label" align="center">Genre</td>
                            <td width="14%"> 
                                <div align="center"> 
                                    <input name="txtGenreId" type="text" value="{IDGENRE}" size="1" class="{CLIDGENRE}" />
                                </div>
                            </td>
                            <td width="65%">
                                <input name="txtGenre" type="text" value="{GENRE}" size="30"class="{CLGENRE}" />
                            </td>
                            <td width="6%">
                                <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('genre',document.myform.txtGenre.value,'myform','txtGenre',false)" />
                            </td>
                        </tr>
                        <tr> 
                            <td colspan="2">&nbsp;</td>
                            <td class="original">{ORIGENRE}</td>
                            <td>&nbsp;</td>
                        </tr>
                    </table>
                    
                    <table class="tableaux_admin">
                        <tr> 
                            <td width="18%" class="label">
                                Scenariste
                            </td>
                            <td width="10%">
                                <input name="txtScenarId" type="text" value="{IDSCEN}" size="4" class="{CLIDSCEN}" />
                            </td>
                            <td width="59%">
                                <input name="txtScenar" type="text" value="{SCENARISTE}" size="33"class="{CLSCENARISTE}" />
                            </td>
                            <td width="13%">
                                <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('auteur',document.myform.txtScenar.value,'myform','txtScenar',true)" />
                            </td>
                        </tr>
                        <tr> 
                            <td colspan="2">&nbsp;</td>
                            <td class="original">{ORISCENARISTE}</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr> 
                            <td width="18%" class="label">
                                Scenariste #2
                            </td>
                            <td width="10%">
                                <input name="txtScenarAltId" type="text" value="{IDSCENALT}" size="4" class="{CLIDSCENALT}" />
                            </td>
                            <td width="59%">
                                <input name="txtScenarAlt" type="text" value="{SCENARISTEALT}" size="33"class="{CLSCENARISTEALT}" />
                            </td>
                            <td width="13%">
                                <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('auteur',document.myform.txtScenarAlt.value,'myform','txtScenarAlt',true)" />
                            </td>
                        </tr>
                        <tr> 
                            <td colspan="2">&nbsp;</td>
                            <td class="original">{ORISCENARISTEALT}</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr> 
                            <td class="label">Dessinateur</td>
                            <td> <input name="txtDessiId" type="text" value="{IDDESS}" size="4" class="{CLIDDESS}"></td>
                            <td> <input name="txtDessi" type="text" value="{DESSINATEUR}" size="33" class="{CLDESSINATEUR}"></td>
                            <td> <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('auteur',document.myform.txtDessi.value,'myform','txtDessi',true)"></td>
                        </tr>
                        <tr> 
                            <td colspan="2">&nbsp;</td>
                            <td class="original">{ORIDESSINATEUR}</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr> 
                            <td class="label">Dessinateur #2</td>
                            <td> <input name="txtDessiAltId" type="text" value="{IDDESSALT}" size="4" class="{CLIDDESSALT}"></td>
                            <td> <input name="txtDessiAlt" type="text" value="{DESSINATEURALT}" size="33" class="{CLDESSINATEURALT}"></td>
                            <td> <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('auteur',document.myform.txtDessiAlt.value,'myform','txtDessiAlt',true)"></td>
                        </tr>
                        <tr> 
                            <td colspan="2">&nbsp;</td>
                            <td class="original">{ORIDESSINATEURALT}</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr> 
                            <td class="label">Coloriste</td>
                            <td> <input name="txtColorId" type="text" value="{IDCOLOR}" size="4" class="{CLIDCOLOR}"></td>
                            <td> <input name="txtColor" type="text" value="{COLORISTE}" size="33" class="{CLCOLORISTE}"></td>
                            <td> <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('auteur',document.myform.txtColor.value,'myform','txtColor',true)"></td>
                        </tr>
                        <tr> 
                            <td colspan="2">&nbsp;</td>
                            <td class="original">{ORICOLORISTE}</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr> 
                            <td class="label">Coloriste #2</td>
                            <td> <input name="txtColorAltId" type="text" value="{IDCOLORALT}" size="4" class="{CLIDCOLORALT}"></td>
                            <td> <input name="txtColorAlt" type="text" value="{COLORISTEALT}" size="33" class="{CLCOLORISTEALT}"></td>
                            <td> <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('auteur',document.myform.txtColorAlt.value,'myform','txtColorAlt',true)"></td>
                        </tr>
                        <tr> 
                            <td colspan="2">&nbsp;</td>
                            <td class="original">{ORICOLORISTEALT}</td>
                            <td>&nbsp;</td>
                        </tr>
                    </table>

                    <table class="tableaux_admin">
                        <tr> 
                            <td width="16%" class="label">Editeur</td>
                            <td width="12%" align="center"><input name="txtEditeurId" type="text" value="{IDEDIT}" size="3" class="{CLIDEDIT}" /></td>
                            <td width="58%"><input name="txtEditeur" type="text" value="{EDITEUR}" size="33"class="{CLEDITEUR}" /></td>
                            <td width="14%"><img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('editeur',document.myform.txtEditeur.value,'myform','txtEditeur',true)"></td>
                        </tr>
                        <tr> 
                            <td colspan="2">&nbsp;</td>
                            <td class="original">{ORIEDITEUR}</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr> 
                            <td class="label">Collection</td>
                            <td align="center"><input name="txtCollecId" type="text" value="{IDCOLLEC}" size="3" class="{CLIDCOLLEC}" /></td>
                            <td><input name="txtCollec" type="text" value="{COLLECTION}" size="33" class="{CLCOLLECTION}" /></td>
                            <td><img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('collection',document.myform.txtCollec.value,'myform','txtCollec',false,document.myform.txtEditeurId.value)"></td>
                        </tr>
                        <tr> 
                            <td colspan="2">&nbsp;</td>
                            <td class="original">{ORICOLLECTION}</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr> 
                            <td colspan="4" class="label">
                                <table class="tableaux_admin_sans-bord">
                                    <tr> 
                                        <td align="center">
                                            <a href="{URLEAN}" title="Teste le code EAN sur BDNet" target="_blank">EAN</a> : 
                                            <input name="txtEAN" type="text" value="{EAN}" size="14" />{ISEAN}
                                        </td>
                                        <td align="center">
                                            <a href="{URLISBN}" title="Teste le code ISBN sur Amazon" target="_blank">ISBN</a> : 
                                            <input name="txtISBN" type="text" value="{ISBN}" size="10" />{ISISBN}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr> 
                            <td colspan="4" class="label">
                                <table class="tableaux_admin_sans-bord">
                                    <tr> 
                                        <td width="22%" align="center">
                                            Date Parution
                                            <div class="petite_police" style="margin-bottom: 10px;">(aaaa-mm-jj)</div>
                                            <input name="txtDateParution" type="text" id="txtDateParution" value="{DTPAR}" size="10" />
                                        </td>
                                        <td width="1%" align="center"></td>
                                        <td width="8%" align="center">
                                            <div>Tirage de t&ecirc;te</div>
                                            <input type="checkbox" name="chkTT" value="checkbox" {ISTT} />
                                        </td>
                                        <td width="1%" align="center"></td>
                                        <td width="68%" align="center">
                                            Description de l'édition<br />
                                            <textarea name="txtCommentEdition" class="textarea_description_edition">{COMMENT}</textarea>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <br />
                    <table class="tableaux_admin">
                        <tr> 
                            <td colspan="4" bordercolor="#FFFFCC" class="label">
                                <p>
                                    <b>Ajouter comme nouvelle édition d'un album d&eacute;j&agrave; existant<br /></b>
                                </p>
                            </td>
                        </tr>
                        <tr> 
                            <td width="14%" class="label"><div align="center">Album</div></td>
                            <td width="13%">
                                <div align="left"> 
                                    <input name="txtExistingTomeId" type="text" id="txtExistingTomeId" value="" size="5" />
                                </div>
                            </td>
                            <td width="62%"><input name="txtExistingTome" type="text" id="txtExistingTome" value="" size="40" /></td>
                            <td width="11%"><img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('album',document.myform.txtExistingTome.value,'myform','txtExistingTome',true)"></td>
                        </tr>
                    </table>
                    <div align="center"> 
                        <p></p>
                        <div style="float: left;">Simple mise à jour de la proposition :</div>
                        <div align="center" style="width: 20px; margin: auto; text-align: center;"><input type="checkbox" name="chkUpdate" value="O" /></div>
                        <br />
                        <input type="submit" name="Submit" value="{ACTIONNAME}" />
                        <br />
                        <br />
                        <br />
                        <table width="100%">
                            <tr align="center">
                                <td width="35%">
                                    {BOUTONPRECEDENT}
                                </td>
                                <td width="30%">
                                    &nbsp;
                                </td>
                                <td width="35%">
                                    {BOUTONSUIVANT}
                                </td>
                            </tr>
                            <tr><td colspan="3">&nbsp;</td></tr>
                            
                        </table>
                    </div>
                <!--Fin colonne gauche -->
                </td>
                
                
                <!--Debut colonne droite -->
                <td width="35%" valign="top">
                    <table width="100%" cellpadding="5" bgcolor="#D35E5E" style="font-size: 0.9em;">
                        <tr> 
                            <td width="25%" align="center" class="to_be_corrected">
                                rouge
                            </td>
                            <td width="75%" align="center">
                                Absence d'Information ou de Validation
                            </td>
                        </tr>
                        <tr> 
                            <td align="center" class="has_changed">jaune</td>
                            <td align="center">Champ non validé ou Validé puis modifié</td>
                        </tr>
                        <tr>
                            <td align="center" class="original">gris</td>
                            <td align="center">Information actuellement dans la base</td>
                        </tr>
                    </table>
                    <table class="tableaux_admin">
                        <tr> 
                            <td align="center">
                                <img src="{URLIMAGE}" width="180" border="0" align="absmiddle" />
                                <br />
                                {DIMIMAGE}
                            </td>
                        </tr>
                        <tr> 
                            <td class="label">
                                Redimensionner la couverture :
                                <input type="checkbox" name="chkResize" value="checked" />
                            </td>
                        </tr>
                        <tr> 
                            <td class="label">
                                Envoyer depuis un fichier local :
                                <br /><input name="txtFileLoc" type="file" id="txtFileLoc2" style="width: 100%;" class="flat" />
                            </td>
                        </tr>
                        <tr> 
                            <td class="label">
                                Envoyer depuis une URL :
                                <br /><input name="txtFileURL" type="text" id="txtFileURL2" style="width: 100%;" class="flat" />
                            </td>
                        </tr>
                        <tr> 
                            <td class="label">
                                Effacer cette couverture :
                                <input type="checkbox" name="chkDelete" value="checked" />
                            </td>
                        </tr>
                    </table>
            </form>
                    <table class="cadre_texte-admin">
                        <tr>
                            <td>
                                <div class="b">Bla bla des correcteurs</div>
                                <form action="{URLCOMMENTCORR}" method="post" name="formcorr">
                                    <p class="texte">
                                        Statut courant de la proposition :
                                        <select name="cmbStatus" size="1" style="background-color: {COLOR_STATUS};">{OPTIONSTATUS}</select>
                                    </p>
                                    <span class="texte">Commentaires des correcteurs<br /></span>
                                    <textarea name="txtCommentCorr" class="textarea_description_edition" style="background-color: {COLOR_STATUS}; border: 1px dotted #FFFFFF;">{CORRCOMMENT}</textarea>
                                    <br />
                                    {CURRENTSTATUS}
                                    <input name="txtComBut" value="Valider" type="submit" />
                                </form>
                            </td>
                        </tr>
                    </table>	
                    <table class="cadre_texte-admin">
                        <tr>
                            <td>
                                <p class="texte"><b>Albums Approchants</b></p>
                                <div class="texte">
                                    <!-- BEGIN CloseBlock -->
                                      <a href="{URLCLOSELINKS}" target="_blank">{CLOSELINKS}</a><br />
                                    <!-- END CloseBlock -->
                                </div>
                            </td>
                        </tr>
                    </table>
                    <table class="cadre_texte-admin">
                        <form action="{URLUTILVALID}" method="post" name="subform" id="subform" onsubmit="return verifAddExisting(document.subform.txtFutAlbId.value, document.subform.PropAction.value)">
                            <tr>
                                <td>
                                    <p class="texte"><b>Ajout d'un album existant</b></p>
                                    <p class="texte">
                                        Action sur la collection de l'utilisateur : <span class="b">{ACTIONUTIL}</span>
                                    </p>
                                    <p>
                                        <input name="PropAction" type="hidden" id="PropAction" value="{PROPACTION}" />
                                        <input name="txtFutAlbId" type="text" id="txtFutAlbId" size="5" /> 
                                        <input name="txtFutAlb" type="text" id="txtFutAlb" size="23" /> 
                                        <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('album',document.subform.txtFutAlb.value,'subform','txtFutAlb',true)">
                                    </p>
                                    <p>
                                        <input name="txtFutButton" value="Valider" type="submit" />
                                    </p>
                                    <p class="texte">
                                        Aide : Si l'album propos&eacute; existe d&eacute;j&agrave; 
                                        et que l'utilisateur a demand&eacute; l'ajout de cet album dans 
                                        sa collection, sélectionner l'album 
                                        existant via le formulaire et valider.
                                    </p>
                                </td>
                            </tr>
                        </form>
                    </table>
                    <table class="cadre_texte-admin">
                        <tr>
                            <td>
                                <p class="texte">
                                    <div class="b">Contact</div>
                                    <a href="mailto:{ADRESSEMAIL}?subject={MAILSUBJECT}" class="b">
                                        Envoyer un email à {MEMBRE}
                                    </a>
                                    ({ADRESSEMAIL})
                                </p>
                            </td>
                        </tr>
                    </table>
                    <table class="cadre_texte-admin">
                        <tr>
                            <td>
                                <p class="texte">
                                    <b>Menu Express</b>
                                </p>
                                <div class="texte">
                                    <a href=# onclick="javascript:window.open('addserie.php','myserie','scrollbars=no,resizable=yes,width=440,height=140');return(false)">
                                        Ajout rapide d'une série
                                    </a>
                                    <br />
                                    <a href=# onclick="javascript:window.open('addauteur.php','myauteur','scrollbars=no,resizable=yes,width=400,height=150');return(false)">
                                        Ajout rapide d'un auteur
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                <!--Fin colonne droite -->
            <!--Fermeture du form plus haut -->
        </tr>
    </table>
    <table width="64%">
        <form action="{URLDELETE}" method="post" name="subform2">
            <tr align="center">
                <td class="cadre_texte-admin">
                    <span class="texte">En cas de <span class="b">suppression</span>, le contenu (à modifier) du champ suivant sera envoyé par email à l'utilisateur :</span>
                    <br />
                    <div class="b">Sujet : {SUJET_EMAIL}</div>
                    <textarea name="txtMailRefus" class="textarea_description_edition" style="height: 12em;">{CORPS_EMAIL}</textarea>
                    <br />
                    <input type="submit" value="Supprimer" name="subform2" />
                </td>
            </tr>
        </form>
    </table>
</div>
