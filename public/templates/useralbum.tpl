<html>
<head>
<title>{WNDTITLE}</title>
{HEAD}

<link href="{URLSITE}style/calendar.css" rel="stylesheet" type="text/css" />
<script src="{URLSITE}script/calendar/calendar.js" type="text/javascript"></script>
<script src="{URLSITE}script/calendar/calendar-setup.js" type="text/javascript"></script>
<script src="{URLSITE}script/calendar/calendar_fr.js" type="text/javascript"></script>



<script language="JavaScript">
    <!--
    function CheckDate(d) {
    // Cette fonction vérifie le format JJ/MM/AAAA saisi et la validité de la date.
    // Le séparateur est défini dans la variable separateur
    var amin=1900;
    // année mini
    var amax=2200;
    // année maxi
    var separateur="/";
    // separateur entre jour/mois/annee
    var j=(d.substring(0,2));
    var m=(d.substring(3,5));
    var a=(d.substring(6));
    var ok=1;
    if (d == '' ) {
        return ok;
    }
    if ( ((isNaN(j))||(j<1)||(j>31)) && (ok==1) ) {
        alert("Le jour n'est pas correct.");
        ok=0;
    }
    if ( ((isNaN(m))||(m<1)||(m>12)) && (ok==1) ) {
        alert("Le mois n'est pas correct.");
        ok=0;
    }
    if ( ((isNaN(a))||(a<amin)||(a>amax)) && (ok==1) ) {
        alert("L'année n'est pas correcte.");
        ok=0;
    }
    if ( ((d.substring(2,3)!=separateur)||(d.substring(5,6)!=separateur)) && (ok==1) ) {
        alert("Les séparateurs doivent être des "+separateur); ok=0;
    }
    if (ok==1) {
        var d2=new Date(a,m-1,j);
        j2=d2.getDate();
        m2=d2.getMonth()+1;
        a2=d2.getFullYear();
        if (a2<=100) {
            a2=1900+a2
        }
        if ( (j!=j2)||(m!=m2)||(a!=a2) ) {
            alert("La date "+d+" n'existe pas !"+ j + " " + j2 + " " + m + " " + m2 + " " + a + " "+ a2);
            ok=0;
        }
    }
    return ok;
}
function valid_form() {
    if (CheckDate(document.forms["form1"].date_achat.value) == 1) {
        if (!(isNaN(document.forms["form1"].prix.value))) {
            return true;
        }
        else {
            alert("Le prix est mal renseigné !");
            return false;
        }
    }
    else {
        return false;
    }
}
function test_value(){
    /*
    if (document.form1.lstEdition.value == '0'){
        mywindow=open("addedition.php?id_tome={TOMEID}",'myEdition','scrollbars=yes,resizable=yes,width=630,height=460');
    }
    else if (document.form1.lstEdition.value == '1') {
        mywindow=open("userdupalb.php?id_tome={TOMEID}",'myEdition','scrollbars=yes,resizable=yes,width=630,height=380');
    }
    */
    document.form1.submit();
}
function add_element(elName, elValue){
    var newOpt  = new Option(elName, elValue);
    var selLength = document.form1.lstEdition.length;
    document.form1.lstEdition.options[selLength] = newOpt;
    mywindow.close();
}
function setDteAchat(){
    if (!document.form1.cb_achat.checked) {
        d = new Date();
        mm = d.getMonth() + 1;
        yyyy = d.getFullYear();
        dd = d.getDate();
        dte = "";
        if (dd < 10) {
            dte = "0" + dd;
        }
        else {
            dte += dd;
        }
        if (mm < 10) {
            dte = dte + "/0" + mm;
        }
        else {
            dte = dte + "/" + mm;
        }
        dte = dte + "/" + yyyy;
        document.forms["form1"].date_achat.value = dte;
    }
}
function openSerie(){
    window.opener.location="{URLSITE}/membres/userserie.php?id_serie={SERID}";
}
//-->
</script>
</head>
<body {JSCRIPT}>

<form name="form1" method="post" onsubmit='return valid_form()' action="useralbum.php">
    <table class="cadre_fiche">
        <tr>
            <td width="30%" valign="top">
                <img src="{URLSITEIMAGE}couv/{COUVALBUM}" width="180" alt="Couverture" />
                <a href="{URLAMAZON}" target="_blank" title="Commander cet album sur Amazon">
                    {LINKAMAZON}
                </a>
            </td>
            <td width="70%" align="left" valign="top">
                <table width="100%" height="75%" border="1" cellpadding="3" cellspacing="0" bordercolor="#000000" frame="border" rules="all">
                    <tbody>
                        <tr>
                            <td width="30%" class="case_entete_fonce">
                                Titre :
                            </td>
                            <td width="70%" class="case_fond_clair">
                                {ALBTITLE}
                            </td>
                        </tr>
                        <tr>
                            <td class="case_entete_fonce">
                                Série :
                            </td>
                            <td class="case_fond_clair">
                                <a href="#" onclick="openSerie();">
                                    {SERTITLE}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="case_entete_fonce">
                                Tome :
                            </td>
                            <td class="case_fond_clair">
                                {NUMTOME}
                            </td>
                        </tr>
                        <tr>
                            <td class="case_entete_fonce">
                                Genre :
                            </td>
                            <td class="case_fond_clair">
                                {ALBGENRE}
                            </td>
                        </tr>
                        <tr>
                            <td class="case_entete_fonce">
                                Scénario :
                            </td>
                            <td class="case_fond_clair">
                                {ALBSCENAR}
                            </td>
                        </tr>
                        <tr>
                            <td class="case_entete_fonce">
                                Dessin :
                            </td>
                            <td class="case_fond_clair">
                                {ALBDESSIN}
                            </td>
                        </tr>
                        <tr>
                            <td class="case_entete_fonce">
                                Couleur :
                            </td>
                            <td class="case_fond_clair">
                                {ALBCOLOR}
                            </td>
                        </tr>
                        <tr>
                            <td height="30" class="case_entete_fonce">
                                Mon édition :
                            </td>
                            <td class="case_fond_clair">
                                    {OPTEDITION}
                                {COLLECTION}
                            </td>
                        </tr>
                        <tr>
                            <td class="case_entete_fonce">
                                Identifiant :
                            </td>
                            <td class="case_fond_clair">
                                ISBN-13 (EAN) : {EAN}
                                <br/ >
                                ISBN-10 : {ISBN}
                                <br />
                                ID-BDovore : {TOMEID}
                            </td>
                        </tr>
                        {COMMENT_EDITION}
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
    <table class="cadre_fiche">
        <tr>
            <td align="center">
        {NEWEDIT}
        {OTHEREDIT}
            </td>
        </tr>
    </table>
    <table class="cadre_fiche">
        <tr>
            <td>
                <p>
                    <strong>
                        <font color="#990000" size="-1">
                            Synopsis :
                        </font>
                    </strong>
                    {ALBSTORY}
                </p>
                <p style="text-align: center;">
                    <a href="{URLEDIT}" target="_blank" style="font-weight: bold; text-decoration: underline; color: #990000;">
                        Proposer une correction
                    </a>
                    <br />
                    <span class="petite_police">
                        (pour demander l'ajout d'une nouvelle édition, cliquez sur le bouton "Proposer une nouvelle édition"
                         après avoir vérifié dans la case "mon édition" qu'elle n'y est pas déja présente)
                    </span>
                </p>
            </td>
        </tr>
        <tr>
            <td>
                <hr />
                <center>
                    <strong>Données partagées</strong>
                    <br />
                    <span class="petite_police">(visibles par tous)</span>
                </center>
                <hr style="align: center; width: 50%;" />
            </td>
        </tr>
        <tr>
            <td>
                <font color="#990000" size="-1">
                    <strong>Note</strong> (sur 10)
                </font>
                <select name="note" id="note">
                    {OPTIONNOTE}
                </select>
                <br />
                <font color="#990000" size="-1">
                    <strong>Impressions de lecture</strong> :
                </font>
                <!--<textarea name="comment" cols="95" rows="5" class="zonereque" id="comment" style="font-size: 90%;">{USERCOMMENT}</textarea>-->
                <textarea name="comment" class="champ_commentaire">{USERCOMMENT}</textarea>
            </td>
        </tr>
        <tr>
            <td>
                <hr />
                <center><strong>Données privées</strong><br /><span class="petite_police">(pour votre utilisation personnelle)</span></center>
                <hr style="align: center; width: 50%;" />
            </td>
        </tr>
        <tr>
            <td>
                <font color="#990000" size="-1">
                    <strong>Edition originale :</strong>
                </font>
                <input name="cb_tete" type="checkbox" id="cb_tete" value="checkbox" {TETECHECKED} />
                <strong>
                    <font color="#990000" size="-1">
                        Dédicace :
                    </font>
                </strong>
                <input name="cb_dedicace" type="checkbox" id="cb_dedicace" value="checkbox" {DEDICHECKED} />
                <font color="#990000" size="-1">
                    <strong>
                        A acheter prochainement :
                    </strong>
                </font>
                <input name="cb_achat" type="checkbox" id="cb_achat" value="checkbox" {ACHATCHECKED} onClick="setDteAchat()" />
            </td>
        </tr>
        <tr>
            <td>
                <font color="#990000" size="-1">
                    <strong>Cadeau :</strong>
                </font>
                <input name="cb_cadeau" type="checkbox" id="cb_cadeau" value="checkbox" {CADEAUCHECKED} />
                <font color="#990000" size="-1">
                    <strong>
                        Date d'achat <span class="texte">(jj/mm/aaaa)</span> :
                    </strong>
                </font>
                <input name="date_achat" type="text" id="date_achat" value="{DTEACHAT}" size="10" maxlength="10" /><img id='date_achat_d_bn' SRC='{URLSITEIMAGE}/site/ico_calendrier.gif' border=0 align='absmiddle' style='cursor:hand'>
<SCRIPT type=text/javascript>
Calendar.setup({
firstDay :    1,
showsTime : false ,
inputField     :    'date_achat',      // id of the input field
ifFormat       :    '%d/%m/%Y',       // format of the input field
button         :    'date_achat_d_bn',   // trigger for the calendar (button ID)
singleClick    :    true,           // double-click mode
step           :    1                // show all years in drop-down boxes (instead of every other year as default)
});
</SCRIPT>




















                <font color="#990000" size="-1">
                    <strong>Prix/cote : </strong>
                </font>
                <input name="prix" type="text" id="prix" value="{PRIX}" size="4" maxlength="8" />
            </td>
        </tr>
        <tr>
            <td>
                <font color="#990000" size="-1">
                    <strong>Prêt :</strong>
                </font>
                <input name="cb_pret" type="checkbox" id="cb_pret" value="checkbox" {PRETCHECKED} />
                <font color="#990000" size="-1">
                    <strong>Dernier emprunteur :</strong>
                </font>
                <input name="name_pret" type="text" id="name_pret" value="{NOMPRET}" size="15" maxlength="100" />
                <strong>
                    <font color="#990000" size="-1">
                        Email :
                    </font>
                </strong>
                <input name="email_pret" type="text" id="email_pret" value="{EMAILPRET}" size="15" maxlength="100" />
            </td>
        </tr>
        <tr>
            <td>
                <font color="#990000" size="-1">
                    <strong>Remarque personnelle (état, mémo, ...)</strong> :
                </font>
                <!--<textarea name="remarque" cols="50" rows="3" id="remarque" >{USERRQUE}
                   </textarea>
                   -->
                <textarea name="remarque" class="champ_commentaire">{USERRQUE}</textarea>
            </td>
        </tr>
    </table>
     Ajouté dans la collection le {DTEAJOUT}
    <p align="center">
        <input name="id_tome" type="hidden" id="id_tome" value="{TOMEID}" />
        <input name="id_edition" type="hidden" id="id_edition" value="{EDITIONID}" />
        <input name="Submit" id="Submit" type='submit' value="Envoyer" />
    </p>
</form>

</body>
</html>