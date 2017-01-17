<?php




minAccessLevel(2);


// Tableau pour les choix d'options
// Différents stades d'une série
$opt_status[0][0] = 0;
$opt_status[0][1] = 'Finie';
$opt_status[1][0] = 1;
$opt_status[1][1] = 'En cours';
$opt_status[2][0] = 2;
$opt_status[2][1] = 'One Shot';
$opt_status[3][0] = 3;
$opt_status[3][1] = 'Interrompue/Abandonnée';

// différents types d'articles
$opt_type[0][0] = 0;
$opt_type[0][1] = 'Album';
$opt_type[1][0] = 1;
$opt_type[1][1] = 'Coffret';

// INSERE UN NOUVEL ALBUM DANS LA BASE
if($act=="append")
{
    if ($_POST['lstEdition'] =='')
    {
        $id_edition = $_POST['txtEditionId'];
    }else{
        $id_edition = $_POST['lstEdition'];
    }

    $query_el = array("USER_ID" => $_SESSION["UserId"],
    "PROP_DTE" => 'NOW()',
    "PROP_TYPE" => sqlise('CORRECTION','text'),
    "ID_TOME" => $DB->escape($_POST['txtTomeId']),
    "ID_EDITION" => $DB->escape($id_edition),
    "TITRE" => sqlise($_POST['txtTitre'],'text'),
    "NUM_TOME" => sqlise($_POST['txtNumTome'],'text'),
    "FLG_INT" => (($_POST['chkIntegrale'] == "checkbox") ? "'O'" : "'N'"),
    "FLG_TYPE" => $DB->escape($_POST['lstType']),
    "ID_SERIE" => sqlise($_POST['txtSerieId'],'text'),
    "SERIE" => sqlise($_POST['txtSerie'],'text'),
    "FLG_FINI" => sqlise($_POST['lstAchevee'],'text'),
    "DTE_PARUTION" => sqlise($_POST['txtDateParution'],'text'),
    "ID_GENRE" => sqlise($_POST['txtGenreId'],'text'),
    "GENRE" => sqlise($_POST['txtGenre'],'text'),
    "ID_EDITEUR" => sqlise($_POST['txtEditeurId'],'text'),
    "EDITEUR" => sqlise($_POST['txtEditeur'],'text'),
    "ID_SCENAR" => sqlise($_POST['txtScenarId'],'text'),
    "SCENAR" => sqlise($_POST['txtScenar'],'text'),
    "ID_SCENAR_ALT" => sqlise($_POST['txtScenarAltId'],'text'),
    "SCENAR_ALT" => sqlise($_POST['txtScenarAlt'],'text'),
    "ID_DESSIN" => sqlise($_POST['txtDessiId'],'text'),
    "DESSIN" => sqlise($_POST['txtDessi'],'text'),
    "ID_DESSIN_ALT" => sqlise($_POST['txtDessiAltId'],'text'),
    "DESSIN_ALT" => sqlise($_POST['txtDessiAlt'],'text'),
    "ID_COLOR" => sqlise($_POST['txtColorId'],'text'),
    "COLOR" => sqlise($_POST['txtColor'],'text'),
    "ID_COLLECTION" => sqlise($_POST['txtCollecId'],'text'),
    "EAN" => sqlise($_POST['txtEAN'],'text'),
    "ISBN" => sqlise($_POST['txtISBN'],'text'),
    "COLLECTION" => sqlise($_POST['txtCollec'],'text'),
    "HISTOIRE" => sqlise($_POST['txtHistoire'],'text'),
    "COMMENTAIRE" => sqlise($_POST['txtCommentaire'],'text')
    );

    $query = insert_query("users_alb_prop",$query_el);
    //echo $query;
    $DB->query ($query);
    // récupère la valeur de la dernière insertion
    $lid= mysql_insert_id();

    // Verifie la présence d'une image à télécharger
    if (is_file($txtFileLoc) | (preg_match('/^(http:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/', $_POST['txtFileURL'], $url_ary)))
    {
        if (is_file($txtFileLoc))
        { // un fichier à uploader
            $imageproperties = getimagesize($txtFileLoc);
            $imagetype = $imageproperties[2];
            $imagelargeur = $imageproperties[0];
            $imagehauteur = $imageproperties[1];
            // vérifie le type d'image
            if (($imagetype != 1) and ($imagetype != 2))
            {
                echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Seul des fichiers JPEG ou GIF peuvent être chargés. Vous allez être redirigé.';
                exit();
            }
            $uploaddir = BDO_DIR."images/tmp/";
            $newfilename = sprintf("tmpCV-%06d-01",$lid);
            if (($imagetype == 1))
            {
                $newfilename .=".gif";
            }else{
                $newfilename .=".jpg";
            }
            if(!copy($txtFileLoc,$uploaddir.$newfilename))
            {
                echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Erreur lors de l\'envoi de l\'image au serveur. Vous allez être redirigé.';
                exit();
            }else{
                $img_couv=$newfilename;
            }
        }
        else if (preg_match('/^(http:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/', $_POST['txtFileURL'], $url_ary))
        { // un fichier à télécharger
            if ( empty($url_ary[4]) )
            {
                echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">URL image incomplète. Vous allez être redirigé.';
                exit();
            }
            $base_get = '/' . $url_ary[4];
            $port = ( !empty($url_ary[3]) ) ? $url_ary[3] : 80;
            // Connection au serveur hébergeant l'image
            if ( !($fsock = @fsockopen($url_ary[2], $port, $errno, $errstr)) )
            {
                $error = true;
                echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">URL image innacessible. Vous allez être redirigé.';
                exit();
            }

            // Récupère l'image
            @fputs($fsock, "GET $base_get HTTP/1.1\r\n");
            @fputs($fsock, "HOST: " . $url_ary[2] . "\r\n");
            @fputs($fsock, "Connection: close\r\n\r\n");

            unset($avatar_data);
            while( !@feof($fsock) )
            {
                $avatar_data .= @fread($fsock, 102400);
            }
            @fclose($fsock);

            // Check la validité de l'image
            if (!preg_match('#Content-Length\: ([0-9]+)[^ /][\s]+#i', $avatar_data, $file_data1) || !preg_match('#Content-Type\: image/[x\-]*([a-z]+)[\s]+#i', $avatar_data, $file_data2))
            {
                $error = true;
                echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Erreur lors du téléchargement de l\'image. Vous allez être redirigé.';
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

            if ( $bytes_written != $avatar_filesize )
            {
                @unlink($tmp_filename);
                echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Could not write avatar file to local storage. Please contact the board administrator with this message. Vous allez être redirigé.';
                exit();
            }
            // newfilemname
            if ( !($imgtype = check_image_type($avatar_filetype, $error)) )
            {
                exit;
            }

            $new_filename = sprintf("tmpCV-%06d-01",$lid).$imgtype;

            // si le fichier existe, on l'efface
            if (file_exists(BDO_DIR."images/tmp/$new_filename"))
            {
                @unlink(BDO_DIR."images/tmp/$new_filename");
            }

            // copie le fichier temporaire dans le repertoire image
            @copy($tmp_filename, BDO_DIR."images/tmp/$new_filename");
            @unlink($tmp_filename);

            $img_couv=$new_filename;
        }
        else
        {
            $img_couv='';
        }
        /* Annulation du redimensionnement

        //Redimensionnement
        //*****************
        $max_size = 180;
        $imageproperties = getimagesize(BDO_DIR."images/tmp/$img_couv");
        if ($imageproperties != false) {
        $imagetype = $imageproperties[2];
        $imagelargeur = $imageproperties[0];
        $imagehauteur = $imageproperties[1];

        //Détermine s'il y a lieu de redimensionner l'image
        if ((($imagelargeur > $imagehauteur) && ($imagehauteur > $maxsize)) || (($imagelargeur <= $imagehauteur) & ($imagelargeur > $max_size))) {

        if ($imagelargeur < $imagehauteur) {
        // image de type panorama : on limite la largeur à 128
        $new_w = $max_size;
        $new_h = round($imagehauteur * $max_size / $imagelargeur);
        }else {
        // imahe de type portrait : on limite la hauteur au maxi
        $new_h = $max_size;
        $new_w = round($imagelargeur * $max_size / $imagehauteur);
        }
        }else{
        $new_h = $imagehauteur;
        $new_w = $imagelargeur;
        }

        $new_image = imagecreatetruecolor($new_w, $new_h);
        switch ($imagetype) {
        case "1";
        $source = imagecreatefromgif(BDO_DIR."images/tmp/$img_couv");
        break;

        case "2";
        $source = imagecreatefromjpeg(BDO_DIR."images/tmp/$img_couv");
        break;

        case "3";
        $source = imagecreatefrompng(BDO_DIR."images/tmp/$img_couv");
        break;

        case "6";
        $source = imagecreatefrombmp(BDO_DIR."images/tmp/$img_couv");
        break;
        }

        imagecopyresampled($new_image, $source, 0, 0, 0, 0, $new_w, $new_h, $imagelargeur, $imagehauteur);
        unlink(BDO_DIR."images/tmp/$img_couv");
        $img_couv = substr($img_couv,0,strlen($img_couv)-3)."jpg";
        imagejpeg($new_image,BDO_DIR."images/tmp/$img_couv",100);
        }
        */



        // met à jours la référence au fichier dans la base
        $query = "UPDATE users_alb_prop SET";
        $query .= " `img_couv` = '".$DB->escape($img_couv)."'";
        $query .=" WHERE (`id_proposal`=".$DB->escape($lid).");";
        $DB->query($query);
    }
//  echo GetMetaTag(2,"Votre proposition a été prise en compte. Elle sera validée par un administrateur.",("javascript:window.close()"));
    echo GetMetaTag(2,"Votre proposition a été prise en compte. Elle sera traitée par un correcteur.",BDO_URL."membres/suiviproposals.php?cle=1&sort=DESC&lstSource=2");
}



// AFFICHE UN FORMULAIRE AVEC L'ALBUM A EDITER
elseif($act=="")
{
    // Vérifie si une édition a été passée
    if ($id_edition == '')
    {// Récupère l'édition par défaut
        $query = "SELECT id_edition FROM bd_tome WHERE id_tome = ".$DB->escape($alb_id);
        $DB->query ($query);
        $DB->next_record();
        $id_edition = $DB->f("id_edition");
    }

    // prepare la liste des editions disponibles
    $DB->query(q_AllEditionByIdTome($DB->escape($alb_id)),"ORDER BY en.dte_parution");
    $opt_edition[0][0] = 0;
    $opt_edition[0][1] = "Toutes les éditions";
    $i=1;

    while ($DB->next_record())
    {
        $opt_edition[$i][0] = $DB->f("id_edition");
        $opt_edition[$i][1] = $DB->f("enom")." - ".dateParution($DB->f("dte_parution"),$DB->f("flag_dte_parution"));
        $i++;
    }
    $nb_edition = $i-1;

    // récupère les données principales
    $query = q_edition("en.id_edition=".$DB->escape($id_edition));

    $DB->query ($query);
    $DB->next_record();
    // Determine l'URL image
    if (is_null($DB->f("img_couv")) | ($DB->f("img_couv")==''))
    {
        $url_image = BDO_URL_IMAGE."couv/default.png";
    }else{

        $url_image = BDO_URL_IMAGE."couv/".$DB->f("img_couv");

    }
    // Creation d'un nouveau Template
    $t = new Template(BDO_DIR."public/templates");
    // fichier à utiliser
//  $t->set_file(array(
//  "tpBody" => "user.correct.tpl"));

// fichier à utiliser
$t->set_file(array(
"tpForm_Recherche" => "form_recherche.tpl",
"tpBody" => "user.correct.tpl",
"tpMenu" => "menu.tpl",
"tpMenuUser" => "menu_user".(minAccessLevel(2,false) ? '' : '_logout').".tpl",
"tpBase" => "body.tpl"
));

    $t->set_var (array
    ("IDTOME" => $DB->f("id_tome"),
    "IDEDITION" => $DB->f("id_edition"),
    "TITRE" => stripslashes($DB->f("titre")),
    "IDSERIE" => $DB->f("id_serie"),
    "SERIE" => htmlentities(stripslashes($DB->f("s_nom"))),
    "TOME" => $DB->f("num_tome"),
    "IDGENRE" => $DB->f("id_genre"),
    "GENRE" => htmlentities($DB->f("libelle")),
    "IDSCEN" => $DB->f("id_scenar"),
    "SCENARISTE" => htmlentities($DB->f("scpseudo")),
    "IDSCENALT" => $DB->f("id_scenar_alt"),
    "SCENARISTEALT" => ($DB->f("id_scenar_alt")==0 )? "" : htmlentities($DB->f("scapseudo")),
    "IDEDIT" => $DB->f("id_editeur"),
    "EDITEUR" => htmlentities(stripslashes($DB->f("enom"))),
    "IDDESS" => $DB->f("id_dessin"),
    "DESSINATEUR" => htmlentities($DB->f("depseudo")),
    "IDDESSALT" => $DB->f("id_dessin_alt"),
    "DESSINATEURALT" => ($DB->f("id_dessin_alt")==0 )? "" : htmlentities($DB->f("deapseudo")),
    "IDCOLOR" => $DB->f("id_color"),
    "COLORISTE" => htmlentities($DB->f("copseudo")),
    "IDCOLLEC" => $DB->f("id_collection"),
    "COLLECTION" => htmlentities($DB->f("cnom")),
    "EAN" => $DB->f("ean"),
    "ISBN" => $DB->f("isbn"),
    "DTPAR" => $DB->f("dte_parution"),
    "ISINT" => (($DB->f("flg_int")=='O') ? 'checked' : ''),
    "OPTTYPE" => GetOptionValue($opt_type,$DB->f("flg_type")),
    "URLIMAGE" => $url_image,
    "HISTOIRE" => stripslashes($DB->f("histoire")),
    "IMGCOUV" => $DB->f("img_couv"),
    "OPTSTATUS" => GetOptionValue($opt_status,$DB->f("flg_fini")),
    "ACTIONNAME" => "Proposer les modifications",
    "URLACTION" => BDO_URL."membres/prop_correct.php?act=append",
    "URLSITE" => BDO_URL,
    "URLSITEIMAGE" => BDO_URL_IMAGE,
    "URLSITEFORUM" => BDO_URL_FORUM
    ));

    // affiche (ou non) l'info édition
    $t->set_block('tpBody','EditionBlock','EBlock');

    if ($nb_edition > 1)
    {
        $t->set_var (array
        ("OPTEDITION" => GetOptionValue($opt_edition,$DB->f("id_edition"))
        ));
        $t->parse ("EBlock", "EditionBlock",true);
    }

    // Affiche le form
    //$t->pparse("BODY","tpBody");
    // assigne la barre de login
$t->set_var (array(
"LOGINBARRE" => GetIdentificationBar(),
"URLSITE" => BDO_URL,
"URLSITEIMAGE" => BDO_URL_IMAGE,
"URLSITEFORUM" => BDO_URL_FORUM,
"PAGETITLE" => $pagetitle,
"DESCRIPTION" => htmlspecialchars($copyright." - ".$description1),
"PAGEKEYWORD" => $keyword
));

$t->parse("BODY","tpBody");
$t->parse("MENUBARRE","tpMenu");
$t->parse("FORM_RECHERCHE","tpForm_Recherche");
$t->parse("MENUBARREUSER","tpMenuUser");
$t->pparse("MyFinalOutput","tpBase");
}
