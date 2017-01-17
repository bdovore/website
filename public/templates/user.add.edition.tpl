<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

<head>
    <title>{DOCTITRE}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link href="{URLSITE}style/style1.css" rel="stylesheet" type="text/css" />
    <script src="{URLSITE}script/script.js" type="text/javascript"></script>
    <SCRIPT type="text/javascript"><!--
        function valid(){
            if ((document.myform.txtEditeurId.value == '') | (document.myform.txtCollecId.value == '')){
                alert ("Validez l'éditeur et la collection à l'aide des loupes");
                return false;
            }

            if (!isValidDate(document.myform.txtDateParution.value)) {
                alert ("La date doit avoir le format aaaa-mm-jj");
                return false;
            } else {
                return true;
            }
        }
    //--></SCRIPT>
</head>

<body>

<form action="{URLACTION}" method="post" enctype="multipart/form-data" name="myform" target="_self" id="myform" OnSubmit="return valid()">
    <table border="0">
        <tr>
            <td width="610" valign="top">
                {ERRORMESSAGE}
                <table class="table_form_public">
                    <tr>
                        <td width="100%" colspan="5">
                            <div style="text-align: center;">
                                BDovore ne gère pas les réimpressions.
                            </div>
                            <div class="petite_police" style="text-align: justify;">- L'édition que vous proposez doit comporter un isbn différent des autres éditions déjà présentes
                                 OU une modification dans la couverture OU dans le couple Editeur/Collection.<br />
                                - Utilisez le champ "Description de l'édition" plus bas pour justifier la création de cette édition.<br />
                                - Si vous possédez une réimpression et voulez en tenir compte : après avoir ajouté l'édition correspondante dans votre collection,
                                 saisissez votre date d'impression dans le champ "note personnelle" de votre fiche album.
                            </div>

                        </td>
                    </tr>
                    <tr>
                        <td width="100%" colspan="5" align="center"><hr width="75%" />
                            <a href="{URLSITEFORUM}viewtopic.php?t=1298" target="_blank" title="Ce lien s'ouvre dans un nouvel onglet ou dans une nouvelle page">
                                <i>Consulter le tutoriel d'ajout d'édition</i>
                            </a>
                            <br />
                            <a href="{URLSITE}listpropal.php?type=EDITION" target="_blank" title="Ce lien s'ouvre dans un nouvel onglet ou dans une nouvelle page">
                                <i>Liste des demandes d'ajout d'édition en cours de traitement</i>
                            </a>
                            <br />
                            <a href="{URLSITE}membres/prop_album.php" target="_blank" title="Ce lien s'ouvre dans un nouvel onglet ou dans une nouvelle page">
                                <i>Proposer l'ajout d'un nouvel album</i>
                            </a>
                            <hr width="75%" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div align="left" class="enonce_tr_form_public">Editeur</div>
                        </td>
                        <td>
                            <input name="txtTomeId" type="hidden" value="{IDTOME}" />
                            <input name="txtEditeurId" type="hidden" value="{IDEDIT}" />
                        </td>
                        <td>
                            <input name="txtEditeur" type="text" value="{EDITEUR}" size="25" />
                            <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('editeur',document.myform.txtEditeur.value,'myform','txtEditeur',true)" />
                        </td>
                        <td rowspan="2" colspan="2" align="center" valign="middle">
                            <strong>Important</strong>
                            : editeur et collection <strong>doivent</strong> &ecirc;tre validés à l'aide des loupes
                        </td>
                    </tr>
                    <tr>
                        <td class="label">
                            <div align="left" class="enonce_tr_form_public">Collection</div>
                        </td>
                        <td>
                            <input name="txtCollecId" type="hidden" value="{IDCOLLEC}" />
                        </td>
                        <td>
                            <input name="txtCollec" type="text" value="{COLLECTION}" size="25" />
                            <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('collection',document.myform.txtCollec.value,'myform','txtCollec',false,document.myform.txtEditeurId.value)" />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center" title="a : année ; m : mois ; j : jour">
                            <div class="enonce_tr_form_public">Date de sortie</div>
                            (aaaa-mm-jj)
                        </td>
                        <td colspan="3">
                            <input name="txtDateParution" type="text" id="txtDateParution" size="9" value="0000-00-00" title="le dernier 00 doit être gardé si l'on ne connait pas le jour exact, sinon toute la date sera perdue" />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center">
                            <div class="enonce_tr_form_public">ISBN-13 :</div>
                            <span class="texte">(sans tirets)</span>
                        </td>
                        <td>
                            <input name="txtEAN" type="text" id="txtEAN" value="{EAN}" size="14" title="nécessaire pour les dates &gt; 2006-01-00" />
                        </td>
                        <td align="center">
                            <div align="left" class="enonce_tr_form_public">ISBN-10 :</div>
                            <span class="texte">(sans tirets)</span>
                        </td>
                        <td>
                            <input name="txtISBN" type="text" id="txtISBN" value="{ISBN}" size="10" title="nécessaire pour les dates entre 1972-00-00 et 2007-01-00" />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" class="label">
                            <table width="100%" border="0">
                                <tr>
                                      <td width="25%" align="center">&nbsp;</td>
                                      <td width="25%" align="center" class="enonce_tr_form_public">Edition Originale</td>
                                      <td width="25%" align="center" class="enonce_tr_form_public">Tirage de t&ecirc;te</td>
                                      <td width="25%" align="center">&nbsp;</td>
                                </tr>
                                <tr>
                                      <td align="center">&nbsp;</td>
                                      <td align="center"><input type="checkbox" name="chkEO" value="checked" {ISEO}></td>
                                      <td align="center"><input type="checkbox" name="chkTT" value="checked" {ISTT}></td>
                                      <td align="center">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" class="label">
                            <table width="100%" border="0">
                                <tr>
                                    <td class="enonce_tr_form_public">Couverture</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width="40%" align="center">Envoyer depuis un fichier local</td>
                                    <td width="60%">
                                        <input name="txtFileLoc" type="file" id="txtFileLoc2" size="25" />
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center">Envoyer depuis une URL</td>
                                    <td>
                                        <input name="txtFileURL" type="text" id="txtFileURL2" value="{ORIURL}" size="40" />
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="label">
                            <div style="text-align: center;">
                                <div class="enonce_tr_form_public">Description de l'édition</div>
                                <span class="petite_police">(différence avec les autres éditions présentes sur le site, présence de bonus, d'un dossier, d'une interview, etc...)</span>
                            </div>
                        </td>
                        <td colspan="4">
                            <textarea name="txtDescrib" class="textarea_description_edition"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td width="18%"></td>
                        <td width="0%"></td>
                        <td width="38%"></td>
                        <td width="14%"></td>
                        <td width="30%"></td>
                    </tr>
                </table>
                <div align="center">
                    <input type="submit" name="Submit" value="{ACTIONNAME}" />
                    <br />
                </div>
            </td>
        </tr>
    </table>
</form>

</body>
</html>
