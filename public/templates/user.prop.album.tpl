<script type="text/javascript"><!--
function newWindow(myCategory,myText,myForm,myElement,isMandatory,myEditeur) {
    if ((isMandatory == true) && (myText.length < 2)) {
        alert('Veuillez saisir au moins 2 caractères');
    }else {
        if (myCategory == "collection") {
            if (myEditeur == '') {
                alert("Veuillez saisir l'éditeur en premier");
            }else {
                mywindow=open("{URLSITE}admin/pickup.php?cat=" + myCategory + "&text=" + myText + "&ReturnDoc=" + myForm + "&ReturnElement=" + myElement + "&IdEditeur=" + myEditeur,'myname','scrollbars=yes,resizable=yes,width=350,height=270');
            }
        }else {
            mywindow=open('{URLSITE}admin/pickup.php?cat=' + myCategory + "&text=" + myText + "&ReturnDoc=" + myForm + "&ReturnElement=" + myElement,'myname','scrollbars=yes,resizable=yes,width=350,height=270');
        }
    }
}
//--></script>

{MENUCOLL}
<form action="{URLACTION}" method="post" enctype="multipart/form-data" name="myform" target="_self" id="myform">
<div class="cadre1">
    <table width="100%">
        <!--DWLayoutTable-->
        <tr bordercolor="#FFFFFF">
            <td width="907" rowspan="4" valign="top">
                <div id="titre_prop_album">Vous êtes ici sur la page de <b>proposition de nouvel album</b></div>
                <div style="margin-top: 7px;">
                    - Pour une proposition de <b>nouvelle édition</b> concernant un album existant, vous pouvez également utiliser
                    ce formulaire mais un formulaire plus pratique pour vous est à votre disposition via la fiche album de votre collection
                    (ajoutez l'album à votre collection puis faites une demande de nouvelle édition)
                    <br />
                    - Pour une demande de <b>correction</b> sur un album existant, utilisez le lien indiqué sur les fiches albums ou sur les pages série
                </div>
                <p>Afin de proposer un nouvel album, merci de prendre tout d'abord le temps de prendre connaissance des guides adaptés à votre album :
                    <div align="center">
                    <ul>
                        <li><a href="{URLSITEFORUM}viewtopic.php?t=352" target="_blank">Tutoriel : comment faire une demande d'ajout ou de correction</a>
                        <li><a href="{URLSITEFORUM}viewtopic.php?t=617" target="_blank">Tutoriel : les mangas V.O.</a>
                        <li><a href="{URLSITEFORUM}viewtopic.php?t=392" target="_blank">Tutoriel : les BD et Comics V.O.</a>
                        <li><a href="{URLSITEFORUM}viewtopic.php?t=618" target="_blank">Tutoriel : les séries françaises traduites en d'autres langues</a>
                        <li><a href="{URLSITEFORUM}viewtopic.php?t=391" target="_blank">Tutoriel : les artbooks</a>
                    </ul>
                    </div>
                </p>
                <p>
                    L'équipe travaille d'arrache-pied (et bénévolement) pour que la base de données soit la plus exacte et complète possible, aidez-nous en
                    <b><u>renseignant au mieux toutes les informations à votre disposition et en les validant avec les loupes !</u></b>
                    (permet d'associer le champ de texte que vous renseignez avec son identifiant dans la base de données)
                </p>
                <p>
                    Avant de commencer à remplir ce formulaire d'ajout, vous pouvez consulter la
                    <b><a href="{URLSITE}listpropal.php?type=AJOUT">liste des demandes d'ajout en cours de traitement</a></b>
                    afin de vous éviter de saisir une proposition déjà faite récemment (et évidemment de bien vérifier
                    que l'album n'existe pas déjà via le moteur de recherche ou le <a href="{URLSITE}browser.php">BDoBrowser</a>.
                </p>

                <div style="padding-top: 10px;"><span class="i">La meilleure source pour remplir ce formulaire est <span class="b">votre exemplaire</span> (si vous en possédez un)</span> - Proverbe bdovorien présocrato-cambrien</div>
                <table class="table_form_public">
                    <tr width="100%">
                        <td width="10%"></td>
                        <td width="28%"></td>
                        <td width="25%"></td>
                        <td width="37%"></td>
                    </tr>
                    <tr>
                        <td class="enonce_tr_form_public">Série<div class="petite_police">(ou one-shot)</div><input name="txtSerieId" id="txtSerieId" value="" type="hidden" /></td>
                        <td colspan="2">
                            <input name="txtSerie" id="txtSerie" value="{SERIE}" size="57%" class="flat" type="text" />
                            <img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('serie',document.myform.txtSerie.value,'myform','txtSerie',true)" title="Tapez une partie du titre puis cliquez pour rechercher et valider la série" height="14" width="24" />
                        </td>
                        <td><span class="petite_police">Majuscules uniquement pour la 1ère lettre et pour les noms propres, sauf typographie particulière voulue par l'auteur (précisez-le dans le champ Résumé)</td>
                    </tr>
                    <tr>
                        <td class="enonce_tr_form_public">Genre<input name="txtGenreId" id="txtGenreId" value="" type="hidden" /></td>
                        <td>
                            <input name="txtGenre" value="{GENRE}" size="23%" class="flat" type="text" />
                            <img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('genre',document.myform.txtGenre.value,'myform','txtGenre',false)" height="14" width="24" />
                        </td>
                        <td colspan="2"><span class="petite_police">(La loupe vous donne ici accès à tous les genres disponibles)</span></td>
                    </tr>
                </table>
                <br />
                <table class="table_form_public">
                    <!--DWLayoutTable-->
                    <tr width="100%">
                        <td width="15%"></td>
                        <td width="2%"></td>
                        <td width="49%"></td>
                        <td width="32%"></td>
                    </tr>
                    <tr>
                        <td class="enonce_tr_form_public">Titre de l'album</td>
                        <!--<div class="cadre_survol">
                            <img src="{URLSITEIMAGE}site/help.png" />
                            <div>Titre spécifique de l'album ou "Titre série, Tome X"</div>
                        </div>-->
                        <td colspan="2"><input name="txtTitre" id="txtTitre" value="{TITRE}" size="65" class="flat" type="text" /></td>
                        <td><span class="petite_police">
                            En l'absence de titre distinctif : "<i>Titre de la Série</i>, Tome X" ; "<i>Titre de la Série</i>" pour un one-shot
                        </span></td>
                    </tr>
                    <tr>
                        <td class="enonce_tr_form_public">Tome nº</td>
                        <td><input name="txtNumTome" id="txtNumTome" value="{TOME}" size="1" class="flat" type="text" /></td>
                        <td colspan="2"><span class="petite_police">vide si le volume contient toute la "série" ou pour les albums one-shot ; "HS" pour un hors-série</td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <table width="100%">
                                <tr>
                                    <td width="20%">&nbsp;</td>
                                    <td width="18%" class="enonce_tr_form_public">Type</td>
                                    <td width="18%" class="enonce_tr_form_public">Int&eacute;grale</td>
                                    <!--<td width="1%" class="enonce_tr_form_public">Prix éditeur</td>-->
                                    <td width="44%">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td align="center">
                                        <select name="lstType" size="1">{OPTTYPE}</select>
                                    </td>
                                    <td align="center" title="pas pris en compte lors d'une simple mise à jour"><input type="checkbox" name="chkIntegrale" value="checkbox" {ISINT} /></td>
                                    <!--<td align="center"><input name="txtPrixVente" type="text" id="txtPrixVente" value="{PRIX_VENTE}" size="3" style="text-align: center;" /> <span class="petite_police">euros</span></td>-->
                                    <td>&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" class="label">
                            <div style="font-weight: bold; color: #990000;">Histoire</div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" class="label">
                            <textarea name="txtHistoire" class="textarea_description_edition" style="width: 100%; height: 9em;" id="txtHistoire">{HISTOIRE}</textarea>
                        </td>
                        <td class="label">
                            <span class="petite_police">Utiliser ce champ pour indiquer le "résumé" de l'album. <u>Indiquez votre source !</u> (votre pseudo si vous en êtes l'auteur, le nom de l'éditeur,...).
                            <br /><br />
                            Vous pouvez également utiliser ce champ pour <u>laisser un commentaire</u> à vos correcteurs adorés et aimants</span>
                        </td>
                    </tr>
                </table>

                <table class="table_form_public">
                    <tr>
                        <td colspan="4">
                            <span class="enonce_tr_form_public">Auteurs </span><span class="petite_police"> (un seul par champ, à valider à la loupe si présent dans la base.
                            <br />Vous pouvez sélectionner "Collectif" comme 1er ou 2nd auteur si plus de 2)</span>
                        </td>
                        <td>&nbsp;</td>
                        <td><div class="petite_police" align="center">Champs ci-dessous optionnels<br />(2nd auteur de chaque catégorie)</div></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td width="14%" class="enonce_tr_form_public">Scenariste(s)</td>
                        <td width="0%"><input name="txtScenarId" value="" size="5" type="hidden" /></td>
                        <td width="28%"><input name="txtScenar" value="{SCENARISTE}" size="40" class="flat" type="text" /></td>
                        <td width="10%"><img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('auteur',document.myform.txtScenar.value,'myform','txtScenar',true)" height="14" width="24" /></td>
                        <td width="3%"><input name="txtScenarAltId" value="" size="5" type="hidden" /></td>
                        <td width="25%"><input name="txtScenarAlt" value="{SCENARISTEALT}" size="40" class="flat" type="text" /></td>
                        <td width="17%"><img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('auteur',document.myform.txtScenarAlt.value,'myform','txtScenarAlt',true)" height="14" width="24" /></td>
                    </tr>
                    <tr>
                      <td class="enonce_tr_form_public">Dessinateur(s)</td>
                      <td><input name="txtDessiId" value="" size="5" type="hidden" /></td>
                      <td><input name="txtDessi" value="{DESSINATEUR}" size="40" class="flat" type="text" /></td>
                      <td><img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('auteur',document.myform.txtDessi.value,'myform','txtDessi',true)" height="14" width="24" /></td>
                      <td><input name="txtDessiAltId" value="" size="5" type="hidden" /></td>
                      <td><input name="txtDessiAlt" value="{DESSINATEURALT}" size="40" class="flat" type="text" /></td>
                      <td><img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('auteur',document.myform.txtDessiAlt.value,'myform','txtDessiAlt',true)" height="14" width="24" /></td>
                    </tr>
                    <tr>
                        <td class="enonce_tr_form_public">Coloriste(s)<div class="petite_police">Indiquez si noir et blanc</div></td>
                        <td><input name="txtColorId" value="" size="5" type="hidden" /></td>
                        <td><input name="txtColor" value="{COLORISTE}" size="40" class="flat" type="text" /></td>
                        <td><img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('auteur',document.myform.txtColor.value,'myform','txtColor',true)" height="14" width="24" /></td>
                        <td><input name="txtColoAltId" value="" size="5" type="hidden" /></td>
                        <td><input name="txtColoAlt" value="{COLORISTEALT}" size="40" class="flat" type="text" /></td>
                        <td><img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('auteur',document.myform.txtColoAlt.value,'myform','txtColoAlt',true)" height="14" width="24" /></td>
                    </tr>
                </table>

                <br />

                <table class="table_form_public">
                    <tr width="100%">
                        <td width="13%"></td>
                        <td width="7%"></td>
                        <td width="25%"></td>
                        <td width="55%"></td>
                    </tr>
                    <tr>
                        <td class="enonce_tr_form_public">Editeur<input name="txtEditeurId" value="" size="7" type="hidden" /></td>
                        <td colspan="3"><input name="txtEditeur" value="{EDITEUR}" size="40" class="flat" type="text" /> <img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('editeur',document.myform.txtEditeur.value,'myform','txtEditeur',true)" height="14" width="24" /></td>
                    </tr>
                    <tr>
                        <td class="enonce_tr_form_public">Collection<input name="txtCollecId" value="" size="7" type="hidden" /></td>
                        <td colspan="2">
                            <input name="txtCollec" value="{COLLECTION}" size="40" class="flat" type="text" />
                            <img src="{URLSITEIMAGE}site/loupe.gif" onclick="newWindow('collection',document.myform.txtCollec.value,'myform','txtCollec',false,document.myform.txtEditeurId.value)" height="14" width="24" />
                        </td>
                        <td><span class="petite_police">Une fois l'éditeur sélectionné, cliquez sur cette loupe pour voir les collections existantes</span></td>
                    </tr>
                    <tr>
                        <td align="center"><span class="enonce_tr_form_public">Date de sortie</span> <span class="petite_police">(aaaa-mm-jj)</span></td>
                        <td><input name="txtDateParution" id="txtDateParution3" value="0000-00-00" size="9" class="flat" type="text" /></td>
                        <td colspan="2">
                            <span class="petite_police">
                                BDovore ne gère pas les réimpressions, uniquement les rééditions
                                (changements de caractéristiques autres que la seule date d'impression)
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><div class="enonce_tr_form_public">ISBN-13</div><div class="petite_police" style="text-align: center;">(>2006-01-00)</div></td>
                        <td colspan="3">
                            <input name="txtEAN" id="txtEAN" value="{EAN}" size="14" class="flat" type="text" />
                            <span class="petite_police">13 chiffres sans trait d'union ni espace (obligatoire depuis janvier 2006 - aussi appelé <span class="b">EAN</span> ou <span class="b">code barre</span>)</span>
                        </td>
                    </tr>
                    <tr>
                        <td><div class="enonce_tr_form_public">ISBN-10</div><div class="petite_police" style="text-align: center;">(<2007-01-00)</div></td>
                        <td colspan="3">
                            <input name="txtISBN" id="txtISBN" value="{ISBN}" size="10" class="flat" type="text" />
                            <span class="petite_police">10 chiffres sans trait d'union ni espace (à partir de 1972 - généralisé dans les années 80 - obsolète depuis janvier 2007)</span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="enonce_tr_form_public">Description de l'édition</td>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <textarea name="txtCommentEdition" class="textarea_description_edition" style="width: 100%; height: 7em;">{COMMENT}</textarea>
                        </td>
                        <td>
                            <span class="petite_police">
                                (Edition originale, tirage de tête, tirage limité, présence de pages bonus, esquisses, interview, dossier,
                                nombre de pages, etc...)
                            </span>
                        </td>
                    </tr>
                </table>

                <br />

                <table class="table_form_public">
                    <tr>
                        <td class="enonce_tr_form_public">Couverture</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td width="26%" class="label"><font size="-1">Envoyer depuis un fichier local</font></td>
                        <td width="44%"><input name="txtFileLoc" id="txtFileLoc" size="37" class="flat" type="file" /></td>
                        <td width="30%">&nbsp;</td>
                    </tr>
                    <tr>
                      <td class="label"><font size="-1">Envoyer depuis une adresse URL</font></td>
                      <td><input name="txtFileURL" id="txtFileURL" size="50" class="flat" type="text" /></td>
                      <td><span class="petite_police">L'adresse doit se terminer par <b>".jpg" ou ".gif"</b> (ex: http://blablabla/image.jpg).</span></td>
                    </tr>
                </table>
                <br />
                <table class="table_form_public">
                    <tr>
                        <td class="label">
                            Après ajout dans la base
                            <select name="cmbAction" size="1" id="cmbAction">{OPTIONS}</select>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">
                            <input name="chkNotEmail" value="checked" type="checkbox" checked />
                            <font size="-1">recevoir un email d'information/confirmation après traitement de cette proposition par l'équipe</font>
                        </td>
                    </tr>
                </table>
                <br />
                <div align="center">
                    <input name="Submit" value="{ACTIONNAME}" type="submit" />
                    <br />
                </div>
            </td>
        </tr>
    </table>
</div>
</form>
