<link href="{URLSITE}style/export.css" rel="stylesheet" type="text/css">

{BILAN}

<div class="cadre1">
<form action="import.php" method="post" name="import" enctype="multipart/form-data" name="myform">
<input type="hidden" name="act" value="import">
<div class="expTitle">Importation d'éditions dans la collection</div>

<div class="expFieldBlock">
<div class="expTitle2">Première colonne</div>
<label for="chean">EAN <input id="chean" type="radio" name="ean_isbn" value="ean"  {SELEAN}></label><br />
<label for="chisbn">ISBN <input id="chisbn" type="radio" name="ean_isbn" value="isbn" {SELISBN}></label><br />

</div>


<div class="expFieldBlock">
<div class="expTitle2">Autres Colonnes</div>
Date d'achat<input type="checkbox" name="sel_field[]" value="date_achat" {SELdate_achat} /><br />
Prix d'achat/cote<input type="checkbox" name="sel_field[]" value="cote" {SELcote} /><br />
Futur achat<input type="checkbox" name="sel_field[]" value="flg_achat" {SELflg_achat} /><br />
Album prêté<input type="checkbox" name="sel_field[]" value="flg_pret" {SELflg_pret} /><br />
Cadeau<input type="checkbox" name="sel_field[]" value="flg_cadeau" {SELflg_cadeau} /><br />
Edition originale<input type="checkbox" name="sel_field[]" value="flg_tete" {SELflg_tete} /><br />
Dédicacée<input type="checkbox" name="sel_field[]" value="flg_dedicace" {SELflg_dedicace} /><br />
</div>

<hr class="expSep" />
La première colonne est la colonne discriminante. C'est elle qui sera utilisée pour reconnaitre une édition.<br />
Les autres colonnes sont des colonnes optionnelles. Elles vous permettent de remplir les options de chaque édition dans votre collection.<br />
Elles sont utilisées dans l'ordre d'affichage de la liste.<br />
<br />
"Date d'achat" est obligatoirement de la forme jj/mm/AAAA.<br />
"Prix d'achat" est un nombre entier ou à virgule (virgule ou point) avec 2 décimales.<br />
"Futur achat", "Album prêté", "Cadeau", "Edition originale" et "Dédicacée" prennent les valeurs "O" pour Oui ou "N" pour Non.<br />
<br />
Ce programme ne gère pas les incohérences. Si vous indiquez "Futur Achat" et une "date d'achat", ça ne lui posera aucun cas de conscience.<br />
Toute ligne incorrecte par rapport aux indications données sera rejetée.<br />
Toute ligne supérieure à 50 caractères sera rejetée.<br />
Seules 500 lignes seront traitées au maximum.<br />
Si vous essayez d'inserer une édition déjà présente dans votre collection aucune indication précise ne vous sera faite.<br />
<br />
Ce programme vous indiquera les éditions inconnues, les éditions déjà possédées, les lignes rejetées, les lignes traitées avec succès.<br />
<br />
<div align="center">
Fichier à importer <input name="importFile" size="25" class="flat" type="file"><br />

<input type="submit" name="Submit2" value="Importer" />
</div>
<u>Exemples de fichiers pour l'import :</u><br />


<table border=0 cellspacing="0" cellpadding="5">
<tr>

<td width="50%" valign="top">
Voici une liste de 10 EAN. 9782205066753, 9782800150260, 9782800148809, 9782800149745, 9782800150901, 9782800151212,
9782754800808, 9782754803564, 9782754806084 et 9782355922992.<br />
Pour l'importer, en faire un simple fichier texte en séparant chaque valeur par un retour à la ligne comme suit :
<pre>
9782205066753
9782800150260
9782800148809
9782800149745
9782800150901
9782800151212
9782754800808
9782754803564
9782754806084
9782355922992
</pre>
Sélectionnez "EAN" comme première colonne. Ajouter le fichier et appuyer sur le bouton import.
</td>



<td width="50%" valign="top">
Voici la même liste de EAN avec comme colonnes supplémentaires, "date d'achat", "Cadeau" et "Dédicacée" :
<pre>
9782205066753;10/10/2010;O;N
9782800150260;05/10/2009;O;N
9782800148809;14/12/2010;N;O
9782800149745;23/01/2008;O;N
9782800150901;31/12/2010;O;N
9782800151212;24/05/2008;O;O
9782754800808;10/10/2010;O;N
9782754803564;10/10/2009;N;N
9782754806084;01/11/2001;N;N
9782355922992;12/10/2010;N;N
</pre>
Sélectionnez "EAN" comme première colonne. Cochez les cases "date d'achat", "Cadeau" et "Dédicacée".<br />
Ajouter le fichier et appuyer sur le bouton import.
</td>

</tr>
</table>

</div>

</div>
</form>
