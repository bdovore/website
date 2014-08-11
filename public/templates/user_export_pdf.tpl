{MENUCOLL}
<link href="{URLSITE}style/export.css" rel="stylesheet" type="text/css">

<div class="cadre1">
<form action="export_pdf.php?act=export" method="post" name="export"
id="export">
<div class="expTitle">Génération de fichier PDF</div>


<div class="expRadioBlock">
<div align="center">
<div class="expTitle2">Information &agrave; exporter</div>
</div>
<p><label><input type="checkbox" name="expCollec" value=
"O">Collection</label> <label><input type="checkbox" name="expAchFut"
value="O">Achats Futurs</label> <label><input type="checkbox" name=
"expAlbManq" value="O">Albums manquants</label></p>
</div>
<hr class="expSep">
<p><input type="submit" name="Submit2" value="Exporter"><br></p>
</form>
</div>
