<?php




minAccessLevel(2);


// Tableau pour les choix d'options
$opt_type[0][0] = 0;
$opt_type[0][1] = 'Album';
$opt_type[1][0] = 1;
$opt_type[1][1] = 'Coffret';
// D�finie les choix possibles apres insertion dans la base
$action[0][0] = 0;
$action[0][1] = "Ins�rer dans ma collection";
$action[1][0] = 1;
$action[1][1] = "Ins�rer dans mes achats futurs";
$action[2][0] = 2;
$action[2][1] = "Ne rien faire";

// INSERE LA PROPOSITION DANS LA TABLE USER_ALBUM_PROP
if($act=="append"){

$query = "
INSERT INTO `users_alb_prop` (
    `USER_ID`,`PROP_DTE`, `PROP_TYPE`, `ACTION`, `NOTIF_MAIL`,
    `TITRE`, `NUM_TOME`, `ID_SERIE`, `SERIE`, `DTE_PARUTION`, `ID_GENRE`, `GENRE`, `ID_EDITEUR`,
    `EDITEUR`, `ID_SCENAR`, `SCENAR`, `ID_DESSIN`, `DESSIN`, `ID_COLOR`, `COLOR`, `ID_SCENAR_ALT`,
    `SCENAR_ALT`, `ID_DESSIN_ALT`, `DESSIN_ALT`, `ID_COLOR_ALT`, `COLOR_ALT`, `ID_COLLECTION`,
    `COLLECTION`, `HISTOIRE`, `EAN`, `ISBN`, `FLG_INT`, `FLG_TYPE`, `PRIX`, `DESCRIB_EDITION`
) VALUES (
    '".$DB->escape($_SESSION["UserId"])."',
    NOW(),
    'AJOUT',
    '".$DB->escape($_POST['cmbAction'])."',
    ".(($_POST['chkNotEmail'] == "checked") ? "'1'" : "'0'").",
    ".sqlise($_POST['txtTitre'],'text').",
    ".($_POST['txtNumTome']=='' ? "NULL" :  "'".$DB->escape($_POST['txtNumTome']). "'").",
    ".sqlise($_POST['txtSerieId'],'text').",
    ".sqlise($_POST['txtSerie'],'text').",
    '".$DB->escape($_POST['txtDateParution']). "',
    ".sqlise($_POST['txtGenreId'],'text').",
    ".sqlise($_POST['txtGenre'],'text').",
    ".sqlise($_POST['txtEditeurId'],'text').",
    ".sqlise($_POST['txtEditeur'],'text').",
    ".sqlise($_POST['txtScenarId'],'text').",
    ".sqlise($_POST['txtScenar'],'text').",
    ".sqlise($_POST['txtDessiId'],'text').",
    ".sqlise($_POST['txtDessi'],'text').",
    ".sqlise($_POST['txtColorId'],'text').",
    ".sqlise($_POST['txtColor'],'text').",
    ".sqlise($_POST['txtScenarAltId'],'text').",
    ".sqlise($_POST['txtScenarAlt'],'text').",
    ".sqlise($_POST['txtDessiAltId'],'text').",
    ".sqlise($_POST['txtDessiAlt'],'text').",
    ".sqlise($_POST['txtColoAltId'],'text').",
    ".sqlise($_POST['txtColoAlt'],'text').",
    ".sqlise($_POST['txtCollecId'],'text').",
    ".sqlise($_POST['txtCollec'],'text').",
    '".$DB->escape($_POST['txtHistoire'])."',
    ".sqlise($_POST['txtEAN'],'text').",
    ".sqlise($_POST['txtISBN'],'text').",
    ".(($_POST['chkIntegrale'] == "checkbox") ? "'O'" : "'N'").",
    ".$DB->escape($_POST['lstType']).",
    ".($_POST['txtPrixVente']=='' ? "NULL" :  "'".$DB->escape($_POST['txtPrixVente']). "'").",
    '".$DB->escape($_POST['txtCommentEdition'])."'
)";
$DB->query ($query);

    // r�cup�re la valeur de la derni�re insertion
    $lid= mysql_insert_id();

    // Verifie la pr�sence d'une image � t�l�charger
    if (is_file($txtFileLoc) | (preg_match('/^(http:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/', $_POST['txtFileURL'], $url_ary))){
        if (is_file($txtFileLoc)){ // un fichier � uploader
            $imageproperties = getimagesize($txtFileLoc);
            $imagetype = $imageproperties[2];
            $imagelargeur = $imageproperties[0];
            $imagehauteur = $imageproperties[1];
            // v�rifie le type d'image
            if (($imagetype != 1) and ($imagetype != 2)){
                echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Seul des fichiers JPEG ou GIF peuvent �tre charg�s. Vous allez �tre redirig�.';
                exit();
            }
            $uploaddir = BDO_DIR."images/tmp/";
            $newfilename = sprintf("tmpCV-%06d-01",$lid);
            if (($imagetype == 1)){
                $newfilename .=".gif";
            }else{
                $newfilename .=".jpg";
            }
            if(!copy($txtFileLoc,$uploaddir.$newfilename)){
                echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Erreur lors de l\'envoi de l\'image au serveur. Vous allez �tre redirig�.';
                exit();
            }else{
                $img_couv=$newfilename;
            }
        }else if (preg_match('/^(http:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/', $_POST['txtFileURL'], $url_ary)){ // un fichier � t�l�charger
            if ( empty($url_ary[4]) ){
                echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">URL image incompl�te. Vous allez �tre redirig�.';
                exit();
            }
            $base_get = '/' . $url_ary[4];
            $port = ( !empty($url_ary[3]) ) ? $url_ary[3] : 80;
            // Connection au serveur h�bergeant l'image
            if ( !($fsock = @fsockopen($url_ary[2], $port, $errno, $errstr)) ){
                $error = true;
                echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">URL image innacessible. Vous allez �tre redirig�.';
                exit();
            }

            // R�cup�re l'image
            @fputs($fsock, "GET $base_get HTTP/1.1\r\n");
            @fputs($fsock, "HOST: " . $url_ary[2] . "\r\n");
            @fputs($fsock, "Connection: close\r\n\r\n");
            unset($avatar_data);
            while( !@feof($fsock) ){
                $avatar_data .= @fread($fsock, 102400);
            }
            @fclose($fsock);

            // Check la validit� de l'image
            if (!preg_match('#Content-Length\: ([0-9]+)[^ /][\s]+#i', $avatar_data, $file_data1) || !preg_match('#Content-Type\: image/[x\-]*([a-z]+)[\s]+#i', $avatar_data, $file_data2)){
                $error = true;
                echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Erreur lors du t�l�chargement de l\'image. Vous allez �tre redirig�.';
                exit();
            }
            $avatar_filesize = $file_data1[1];
            $avatar_filetype = $file_data2[1];
            $avatar_data = substr($avatar_data, strlen($avatar_data) - $avatar_filesize, $avatar_filesize);
            $tmp_path = BDO_DIR.'images/tmp';
            $tmp_filename = tempnam($tmp_path, uniqid(rand()) . '-');
            $fptr = @fopen($tmp_filename, 'wb');
            $bytes_written = @fwrite($fptr, $avatar_data, $avatar_filesize);
            @fclose($fptr);
            if ( $bytes_written != $avatar_filesize ){
                @unlink($tmp_filename);
                echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Could not write avatar file to local storage. Please contact the board administrator with this message. Vous allez �tre redirig�.';
                exit();
            }

            // newfilemname
            if ( !($imgtype = check_image_type($avatar_filetype, $error)) ){
                exit;
            }
            $new_filename = sprintf("tmpCV-%06d-01",$lid).$imgtype;

            // si le fichier existe, on l'efface
            if (file_exists(BDO_DIR."images/tmp/$new_filename")){
                @unlink(BDO_DIR."images/tmp/$new_filename");
            }

            // copie le fichier temporaire dans le repertoire image
            @copy($tmp_filename, BDO_DIR."images/tmp/$new_filename");
            @unlink($tmp_filename);
            $img_couv=$new_filename;
        }else{
            $img_couv='';
        }

        // met � jours la r�f�rence au fichier image dans la base
        $query = "UPDATE users_alb_prop SET";
        $query .= " `img_couv` = '".$DB->escape($img_couv)."'";
        $query .=" WHERE (`id_proposal`=".$DB->escape($lid).");";
        $DB->query($query);
    }
    // message de confirmation
    echo GetMetaTag(2,"Votre proposition a �t� ajout�e � la liste des demandes. Merci de votre contribution � la base BDovore.",(BDO_URL."membres/suiviproposals.php?cle=1&sort=DESC"));
}

// AFFICHE UN FORMULAIRE VIDE
elseif($act==""){
    $url_image = BDO_URL_IMAGE."couv/default.png";

    // Creation d'un nouveau Template
    $t = new Template(BDO_DIR."public/templates");

    // fichier � utiliser
    $t->set_file(array(
    "tpMenuColl" => "menu_coll.tpl",
    "tpBody" => "user.prop.album.tpl",
    "tpMenu" => "user.menu.tpl",
    "tpBase" => "body.tpl"
    ));
    //echo $query;
    //echo htmlspecialchars($DB->f("id_color"));
    $t->set_var (array(
    "OPTIONS" => GetOptionValue($action,0),
    "OPTTYPE" => GetOptionValue($opt_type,0),
    "ACTIONNAME" => "Enregistrer",
    "URLACTION" => BDO_URL."membres/prop_album.php?act=append",
    "PAGETITLE" => "BDOVORE.com : proposer un album"
    ));
    // assigne la barre de login
    $t->set_var (array(
    "LOGINBARRE" => GetIdentificationBar(),
    "URLSITE" => BDO_URL,
    "URLSITEIMAGE" => BDO_URL_IMAGE,
    ));
    $t->parse("MENUCOLL","tpMenuColl");
    $t->parse("BODY","tpBody");
    $t->parse("MENUBARRE","tpMenu");
    $t->pparse("MyFinalOutput","tpBase");
}
