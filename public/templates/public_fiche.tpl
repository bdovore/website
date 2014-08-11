<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
    <title>{DOCTITRE}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    {CSSSTYLE}
</head>

<body>

<div id="fiche">
    <div id="couv">
        <img id="couv2" src="{URLCOUV}" alt="Couverture" />
        <br />
        &copy; {SHORTEDIT}
    </div>
    <table id="info">
        <tr>
            <td class="album" colspan="2"><a href="#" onclick="window.open('{URLTITRE}','Album','width=500,height=400,scrollbars=1')">{TITRE}</a></td>
        </tr>
        <tr>
            <td class="entete">{LBLSERIE}</td>
            <td class="contenu"><a href="{URLSERIE}" target="_blank">{SERIE}</a></td>
        </tr>
        <tr>
            <td class="entete">Scénario :</td>
            <td class="contenu"><a href="{URLSCENAR}" target="_blank">{SCENARISTE}</a></td>
        </tr>
        <tr>
            <td class="entete">Dessin :</td>
            <td class="contenu"><a href="{URLDESSIN}" target="_blank">{DESSINATEUR}</a></td>
        </tr>
        <tr>
            <td class="entete">Couleurs :</td>
            <td class="contenu"><a href="{URLCOLOR}" target="_blank">{COLORISTE}</a></td>
        </tr>
        <tr>
            <td class="entete">Editeur :</td>
            <td class="contenu">{EDITEUR}</td>
        </tr>
        <tr>
            <td class="entete">Parution :</td>
            <td class="contenu">{DTEPARUTION}</td>
        </tr>
        <tr>
            <td class="afutur" colspan="2"><a href="#" onclick="window.open('{URLACHATFUTUR}','Album','width=200,height=80,scrollbars=1')">Ajouter à mes achats futurs</a></td>
        </tr>
    </table>
    <div class="space">&nbsp;</div>
</div>
<div class="space">&nbsp;</div>
</body>
</html>