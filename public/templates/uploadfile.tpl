{MENUCOLL}
<div class="cadre1">
<h2>Chargement d'une image pour le compte <font color="#0000FF">{USERNAME}</font></h2>

    <form action="../membres/profil.php?act=image&status=ok&user_id={USERID}" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="MAX_FILE_SIZE" value="50000">
  <p>Indiquer l'emplacement de l'image sur votre ordinateur :<br />(fichier .jpg ou .gif de hauteur et largeur maximums : 150 x 120 pixels)<br />
    <input type="file" name="imgfile">
    <br />
    <br />
    <br />
    <input type="submit" value="Charger l'image dans votre profil BDovore">
    <br />
    <br />
</form>
</div>