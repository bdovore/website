<?php



minAccessLevel(1);

// Tableau pour les choix d'options
// Avancement de la série
$opt_status[0][0] = 0;
$opt_status[0][1] = 'Finie';
$opt_status[1][0] = 1;
$opt_status[1][1] = 'En cours';
$opt_status[2][0] = 2;
$opt_status[2][1] = 'One Shot';
$opt_status[3][0] = 3;
$opt_status[3][1] = 'Interrompue/Abandonnée';

// Type d'album
$opt_type[0][0] = 0;
$opt_type[0][1] = 'Album';
$opt_type[1][0] = 1;
$opt_type[1][1] = 'Coffret';


// LISTE LES PROPOSALS
if ($act==""){
    $titre_admin = "Corrections en attente";
    if ($cle == "") {
        $cle=1;
    }
    if ($sort == "DESC") {
        $sort = " DESC";
    }else{
        $sort="";
    }

    // Selection des champs à afficher
    $clerep[1] = "id_proposal";
    $clerep[2] = "prop_dte";
    $clerep[3] = "id_user";
    $clerep[4] = "titre";
    $clerep[5] = "serie";

    $query = "
    SELECT
        p.id_proposal,
        p.user_id,
        u.username,
        p.prop_dte,
        p.titre,
        p.serie
    FROM
        users_alb_prop p
        INNER JOIN users u ON p.user_id = u.user_id
    WHERE
        p.status = 0
        AND p.PROP_TYPE='CORRECTION'
    ORDER BY ".$clerep[$cle].$sort ;

    $DB->query ($query);

    // Creation d'une nouvelle instance Fast Template
    $t = new Template(BDO_DIR."public/templates");
    // fichier à utiliser
    $t->set_file(array(
    "tpBody" => "admin.proposals.tpl",
    "tpBase" => "body.tpl"));
    // on déclare le block à utiliser
    $t->set_block('tpBody','PropBlock','PBlock');

    //Liste les corrections
    while ($DB->next_record()){
        $t->set_var (array(
        "TITRE_ADMIN" => $titre_admin,
        "ID" => $DB->f ("id_proposal"),
        "DATE" => $DB->f ("prop_dte"),
        "USER" => $DB->f ("username"),
        "TITRE" => stripslashes($DB->f ("titre")),
        "SERIE" => stripslashes($DB->f ("serie")),
        "URLEDIT" => BDO_URL."admin/admincorrections.php?act=valid&propid=".$DB->f ("id_proposal"),
        "URLDELETE" => BDO_URL."admin/admincorrections.php?act=supprim&propid=".$DB->f ("id_proposal"))
        );
        $t->parse ("PBlock", "PropBlock",true);
    }

    // assigne la barre de login
    $t->set_var (array(
    "LOGINBARRE" => GetIdentificationBar(),
    "MENUBARRE" => admin_menu(),
    "URLSITE" => BDO_URL,
    "URLSITEIMAGE" => BDO_URL_IMAGE,
    "URLSITEFORUM" => BDO_URL_FORUM
    ));
    $t->parse("BODY","tpBody");
    $t->pparse("MyFinalOutput","tpBase");
}

//SUPPRESSION DE PROPOSAL

elseif($act=="supprim")
{
    if($status=="ok")
    {//suppression de la proposition

        // Vérifie l'existence d'une couverture

        $query = "SELECT user_id, img_couv, titre FROM users_alb_prop WHERE id_proposal = ".$DB->escape($propid);
        $DB->query ($query);
        $DB->next_record();
        $prop_user = $DB->f("user_id");
        $prop_img = $DB->f("img_couv");
        $prop_action = $DB->f("action");
        $prop_titre = $DB->f("titre");
        $notif_mail = $DB->f("notif_mail");

        if ($prop_img != '')
        {
            $filename = $prop_img;
            if (file_exists(BDO_DIR."images/tmp/$filename"))
            {
                @unlink(BDO_DIR."images/tmp/$filename");
            }
        }
        //Effacement virtuel de l'album
        $query = "UPDATE users_alb_prop SET `STATUS` = 99, `VALIDATOR` =" . $DB->escape($_SESSION["UserId"]) . " , `VALID_DTE` = NOW() WHERE id_proposal=".$DB->escape($propid);
        $DB->query ($query);

        //rouvre la page
        echo GetMetaTag(2,"La proposition a été effacée",(BDO_URL."admin/admincorrections.php"));
        exit;
    }else{
        // affiche la confirmation de la demande d'éffacement
        echo 'Etes-vous s&ucirc;r de vouloir effacer la proposition n&deg;'.$propid.'  ?   <a href="admincorrections.php?act=supprim&propid='.$propid.'&status=ok">oui</a>
      - <a href="javascript:history.go(-1)">non</a>';
        exit();}
}

// AFFICHAGE D'UN PROPOSAL
elseif($act=="valid"){

    $query = "
    SELECT
        users_alb_prop.ID_PROPOSAL,
        users_alb_prop.USER_ID,
        users_alb_prop.ID_TOME,
        users_alb_prop.ID_EDITION,
        users_alb_prop.COMMENTAIRE,
        users_alb_prop.TITRE,
        users_alb_prop.NUM_TOME,
        users_alb_prop.ID_SERIE,
        users_alb_prop.SERIE AS ORISERIE,
        users_alb_prop.FLG_FINI AS FLG_FINI,
        users_alb_prop.FLG_INT AS FLG_INT,
        users_alb_prop.FLG_TYPE AS FLG_TYPE,
        bd_serie.NOM AS SERIE,
        users_alb_prop.DTE_PARUTION,
        users_alb_prop.ID_GENRE,
        users_alb_prop.GENRE AS ORIGENRE,
        bd_genre.LIBELLE AS GENRE,
        users_alb_prop.ID_EDITEUR,
        users_alb_prop.EDITEUR AS ORIEDITEUR,
        bd_editeur.NOM AS EDITEUR_NOM,
        users_alb_prop.ID_SCENAR,
        users_alb_prop.SCENAR AS ORISCENAR,
        bd_auteur.PSEUDO AS PSEUDO_SCENAR,
        users_alb_prop.ID_SCENAR_ALT,
        users_alb_prop.SCENAR_ALT AS ORISCENARALT,
        bd_auteur_3.PSEUDO AS PSEUDO_SCENAR_ALT,
        users_alb_prop.ID_DESSIN,
        users_alb_prop.DESSIN AS ORIDESSIN,
        users_alb_prop.EAN,
        users_alb_prop.ISBN,
        bd_auteur_1.PSEUDO AS PSEUDO_DESSIN,
        users_alb_prop.ID_DESSIN_ALT,
        users_alb_prop.DESSIN_ALT AS ORIDESSINALT,
        bd_auteur_4.PSEUDO AS PSEUDO_DESSIN_ALT,
        users_alb_prop.ID_COLOR,
        users_alb_prop.COLOR AS ORICOLOR,
        bd_auteur_2.PSEUDO AS PSEUDO_COLOR,
        users_alb_prop.DESCRIB_EDITION,
        users_alb_prop.ID_COLLECTION,
        users_alb_prop.COLLECTION AS ORICOLLECTION,
        bd_collection.NOM AS COLLECTION,
        users_alb_prop.HISTOIRE,
        users_alb_prop.IMG_COUV
    FROM
        users_alb_prop
        LEFT JOIN bd_serie ON users_alb_prop.ID_SERIE = bd_serie.ID_SERIE
        LEFT JOIN bd_genre ON users_alb_prop.ID_GENRE = bd_genre.ID_GENRE
        LEFT JOIN bd_editeur ON users_alb_prop.ID_EDITEUR = bd_editeur.ID_EDITEUR
        LEFT JOIN bd_auteur ON users_alb_prop.ID_SCENAR = bd_auteur.ID_AUTEUR
        LEFT JOIN bd_auteur AS bd_auteur_1 ON users_alb_prop.ID_DESSIN = bd_auteur_1.ID_AUTEUR
        LEFT JOIN bd_auteur AS bd_auteur_2 ON users_alb_prop.ID_COLOR = bd_auteur_2.ID_AUTEUR
        LEFT JOIN bd_collection ON users_alb_prop.ID_COLLECTION = bd_collection.ID_COLLECTION
        LEFT JOIN bd_auteur as bd_auteur_3 ON users_alb_prop.ID_SCENAR_ALT = bd_auteur_3.ID_AUTEUR
        LEFT JOIN bd_auteur as bd_auteur_4 ON users_alb_prop.ID_DESSIN_ALT = bd_auteur_4.ID_AUTEUR
    WHERE
        users_alb_prop.ID_PROPOSAL = ".$DB->escape($propid);

    $DB->query ($query);
    $DB->next_record();

    // Creation d'un nouveau Template
    $t = new Template(BDO_DIR."public/templates");
    // fichier à utiliser
    $t->set_file(array(
    "tpBody" => "admin.valid.corr.tpl",
    "tpBase" => "body.tpl")
    );

    $t->set_var (array(
    "PROPID" => stripslashes($DB->f("ID_PROPOSAL")),
    "TITRE" => stripslashes($DB->f("TITRE")),
    "CLTITRE" => ($DB->f("TITRE")!='' ? "flat" : "to_be_corrected"),
    "IDSERIE" => $DB->f("ID_SERIE"),
    "CLIDSERIE" => (is_numeric($DB->f("ID_SERIE")) & ($DB->f("SERIE")==$DB->f("ORISERIE")) ? "flat" : "to_be_corrected"),
    "TOME" => $DB->f("NUM_TOME"),
    "IDGENRE" => $DB->f("ID_GENRE"),
    "CLIDGENRE" => (is_numeric($DB->f("ID_GENRE")) & ($DB->f("GENRE")==$DB->f("ORIGENRE")) ? "flat" : "to_be_corrected"),
    "IDSCEN" => $DB->f("ID_SCENAR"),
    "CLIDSCEN" => (is_numeric($DB->f("ID_SCENAR")) & ($DB->f("PSEUDO_SCENAR")==$DB->f("ORISCENAR")) ? "flat" : "to_be_corrected"),
    "IDSCENALT" => $DB->f("ID_SCENAR_ALT"),
    "CLIDSCENALT" => (is_numeric($DB->f("ID_SCENAR_ALT")) & ($DB->f("PSEUDO_SCENAR_ALT")==$DB->f("ORISCENARALT")) ? "flat" : "to_be_corrected"),
    "IDEDIT" => $DB->f("ID_EDITEUR"),
    "CLIDEDIT" => (is_numeric($DB->f("ID_EDITEUR")) & ($DB->f("EDITEUR_NOM")==$DB->f("ORIEDITEUR"))? "flat" : "to_be_corrected"),
    "IDDESS" => $DB->f("ID_DESSIN"),
    "CLIDDESS" => (is_numeric($DB->f("ID_DESSIN")) & ($DB->f("PSEUDO_DESSIN")==$DB->f("ORIDESSIN")) ? "flat" : "to_be_corrected"),
    "IDDESSALT" => $DB->f("ID_DESSIN_ALT"),
    "CLIDDESSALT" => (is_numeric($DB->f("ID_DESSIN_ALT")) & ($DB->f("PSEUDO_DESSIN_ALT")==$DB->f("ORIDESSINALT")) ? "flat" : "to_be_corrected"),
    "IDCOLOR" => $DB->f("ID_COLOR"),
    "CLIDCOLOR" => (is_numeric($DB->f("ID_COLOR")) & ($DB->f("PSEUDO_COLOR")==$DB->f("ORICOLOR")) ? "flat" : "to_be_corrected"),
    "IDCOLLEC" => $DB->f("ID_COLLECTION"),
    "CLIDCOLLEC" => (is_numeric($DB->f("ID_COLLECTION")) & ($DB->f("COLLECTION")==$DB->f("ORICOLLECTION")) ? "flat" : "to_be_corrected"),
    "EAN" => $DB->f("EAN"),
    "ISBN" => $DB->f("ISBN"),
    "DTPAR" => $DB->f("DTE_PARUTION"),
    "HISTOIRE" => stripslashes($DB->f("HISTOIRE")),
    "USERCOMMENT" => stripslashes($DB->f("COMMENTAIRE")),
    "SERIE" => htmlentities(stripslashes($DB->f("ORISERIE"))),
    "GENRE" => htmlentities($DB->f("ORIGENRE")),
    "SCENARISTE" => htmlentities(stripslashes($DB->f("ORISCENAR"))),
    "SCENARISTEALT" => htmlentities(stripslashes($DB->f("ORISCENARALT"))),
    "DESSINATEUR" => htmlentities(stripslashes($DB->f("ORIDESSIN"))),
    "DESSINATEURALT" => htmlentities(stripslashes($DB->f("ORIDESSINALT"))),
    "COLORISTE" => htmlentities(stripslashes($DB->f("ORICOLOR"))),
    "EDITEUR" => htmlentities(stripslashes($DB->f("ORIEDITEUR"))),
    "COLLECTION" => htmlentities($DB->f("ORICOLLECTION")),
    "OPTSTATUS" => GetOptionValue($opt_status,$DB->f("FLG_FINI")),
    "OPTTYPE" => GetOptionValue($opt_type,$DB->f("FLG_TYPE")),
    "ISINT" => (($DB->f("FLG_INT")=='O') ? 'checked' : ''),
    "ACTIONNAME" => "Valider",
    "URLACTION" => BDO_URL."admin/admincorrections.php?act=update&propid=$propid",
    "URLDELETE" => BDO_URL."admin/adminproposals.php?act=supprim&propid=".$DB->f ("ID_PROPOSAL")
    ));
    if ($DB->f("ID_SERIE") != 0){
        $t->set_var (
        "LIENEDITNEWSERIE" , "<a href='".BDO_URL."admin/adminseries.php?serie_id=".stripslashes($DB->f("ID_SERIE"))."'><img src='".BDO_URL_IMAGE."edit.gif' width='18' height='13' border='0'></a>"
        );
    }

    $alb_id = $DB->f("ID_TOME");
    $edition_id = $DB->f("ID_EDITION");
    $user_id = $DB->f("USER_ID");

    $DB2 = new DB_Sql;

    // Determine le statut de l'utilisateur par rapport à l'album qu'il corrige
    if ($edition_id == 0){
        $query = "SELECT id_edition FROM users_album WHERE user_id=".$DB->escape($user_id);
    }else{
        $query = "SELECT id_edition FROM users_album WHERE user_id=".$DB->escape($user_id)." AND id_edition=".$DB->escape($edition_id);
    }
    $DB2->query ($query);

    if ($DB2->num_rows() == 0){
        $user_owns = 'L\'utilisateur <strong>ne poss&egrave;de pas</strong> cet album.';
    }else{
        $user_owns = 'L\'utilisateur <strong>poss&egrave;de</strong> cet album dans sa collection.';
        $DB2->next_record();
        $user_edition = $DB2->f("id_edition");
    }

    // Récupère l'édition définie par défaut
    $query = "SELECT id_edition FROM bd_tome WHERE id_tome =".$DB->escape($alb_id);
    $DB2->query ($query);
    $DB2->next_record();
    $def_edition = $DB2->f("id_edition");

    // Récupère l'info actuelle
    if ($edition_id == 0){
        // édition par défaut
        $query = q_tome("t.id_tome = ".$DB->escape($alb_id));

    }else{
        // force l'édition
        $query = q_edition("en.id_edition = ".$DB->escape($edition_id));
    }
    $DB2->query ($query);
    $DB2->next_record();

    // Determine l'URL image courante
    if (is_null($DB2->f("img_couv")) | ($DB2->f("img_couv")=='')){
        $ori_url_image = BDO_URL_COUV."default.png";
    }else{
        $ori_url_image = BDO_URL_COUV.$DB2->f("img_couv");
        $ori_dim_image = imgdim("$ori_url_image");
    }

    // Determine l'URL image modifiée
    if (is_null($DB->f("IMG_COUV")) | ($DB->f("IMG_COUV")=='')){
        $url_image = $ori_url_image;
    }else{
        $url_image = BDO_URL_IMAGE."tmp/".$DB->f("IMG_COUV");
        $dim_image = imgdim("$url_image");
    }

    // Détermine la nature de la correction
    if ($edition_id == 0){
        $has_edition = 'La correction porte sur <strong>toutes</strong> les &eacute;ditions.';
    }
    elseif ($edition_id == $user_edition){
        $has_edition = 'La correction porte sur l\'&eacute;dition qu\'il poss&egrave;de.';
    }else{
        $has_edition = 'La correction porte sur une &eacute;dition qu\'il <b>ne poss&egrave;de pas</b>.';
    }

    // Détermine s'il s'agit de l'édition par défaut
    if (($edition_id == $def_edition) | ($edition_id == 0)){
        $is_def_edition = '<b>L\'&eacute;dition utilis&eacute;e par d&eacute;faut va &ecirc;tre modifi&eacute;e.</b>';
    }else{
        $is_def_edition = 'L\'&eacute;dition utilis&eacute;e par d&eacute;faut ne sera pas modifi&eacute;e.';
    }

    // Récupère les données actuelles
    $t->set_var (array(
    "ORITITRE" => stripslashes($DB2->f("titre")),
    "CLTITRE" => ($DB->f("TITRE")==$DB2->f("titre") ? "flat" : "has_changed"),
    "ORISERIE" => htmlentities(stripslashes($DB2->f("s_nom"))),
    "CLSERIE" => ($DB->f("SERIE")==$DB2->f("s_nom") ? "flat" : "has_changed"),
    "ORISERIEFINI" => ($DB2->f("flg_fini") != '') ? $opt_status[$DB2->f("flg_fini")][1]:'',
    "NEW_FLG_FINI" => ($DB->f("FLG_FINI")==$DB2->f("flg_fini") ? "" : "*"),
    "ORITOME" => $DB2->f("num_tome"),
    "CLTOME" => ($DB->f("NUM_TOME")==$DB2->f("num_tome") ? "flat" : "has_changed"),
    "NEW_FLG_INT" => ($DB->f("FLG_INT")==$DB2->f("flg_int") ? "" : "*"),
    "NEW_FLG_TYPE" => ($DB->f("FLG_TYPE")==$DB2->f("flg_type") ? "" : "*"),
    "ORIGENRE" => htmlentities($DB2->f("libelle")),
    "CLGENRE" => ($DB->f("GENRE")==$DB2->f("libelle") ? "flat" : "has_changed"),
    "ORISCENARISTE" => htmlentities(stripslashes($DB2->f("scpseudo"))),
    "CLSCENARISTE" => ($DB->f("PSEUDO_SCENAR")==$DB2->f("scpseudo") ? "flat" : "has_changed"),
    "ORISCENARISTEALT" => stripslashes(htmlentities($DB2->f("scapseudo"))),
    "CLSCENARISTEALT" => ($DB->f("PSEUDO_SCENAR_ALT")==$DB2->f("scapseudo") ? "flat" : "has_changed"),
    "ORIEDITEUR" => stripslashes(htmlentities($DB2->f("enom"))),
    "CLEDITEUR" => ($DB->f("EDITEUR_NOM")==$DB2->f("enom") ? "flat" : "has_changed"),
    "ORIDESSINATEUR" => stripslashes(htmlentities($DB2->f("depseudo"))),
    "CLDESSINATEUR" => ($DB->f("PSEUDO_DESSIN")==$DB2->f("depseudo") ? "flat" : "has_changed"),
    "ORIDESSINATEURALT" => stripslashes(htmlentities($DB2->f("deapseudo"))),
    "CLDESSINATEURALT" => ($DB->f("PSEUDO_DESSIN_ALT")==$DB2->f("deapseudo") ? "flat" : "has_changed"),
    "ORICOLORISTE" => stripslashes(htmlentities($DB2->f("copseudo"))),
    "CLCOLORISTE" => ($DB->f("PSEUDO_COLOR")==$DB2->f("copseudo") ? "flat" : "has_changed"),
    "ORICOLLECTION" => htmlentities($DB2->f("cnom")),
    "CLCOLLECTION" => ($DB->f("COLLECTION")==$DB2->f("cnom") ? "flat" : "has_changed"),
    "ORIEAN" => ($DB2->f("ean")=="") ? "&nbsp;" : $DB2->f("ean"),
    "CLEAN" => ($DB->f("EAN")==$DB2->f("ean") ? "flat" : "has_changed"),
    "ORIISBN" => ($DB2->f("isbn")=="") ? "&nbsp;" : $DB2->f("isbn"),
    "CLISBN" => ($DB->f("ISBN")==$DB2->f("isbn") ? "flat" : "has_changed"),
    "ORIDTPAR" => $DB2->f("dte_parution"),
    "CLDTPAR" => ($DB->f("DTE_PARUTION")==$DB2->f("dte_parution") ? "flat" : "has_changed"),
    "ORIHISTOIRE" => stripslashes($DB2->f("histoire")),
    "CLHISTOIRE" => ($DB->f("HISTOIRE")==$DB2->f("histoire") ? "flat" : "has_changed"),
    "URLIMAGE" => $url_image,
    "URLORIIMAGE" => $ori_url_image,
    "DIMIMAGE" => $dim_image,
    "ORIDIMIMAGE" => $ori_dim_image,
    "USERHASEDITION" => $has_edition,
    "MODIFONDEFAULT" => $is_def_edition,
    "DEFEDITIONID" => $def_edition,
    "USEROWN" => $user_owns
    ));

    // Récupère l'adresse mail de l'utilisateur
    $query = "SELECT u.email, u.username, p.titre FROM users u, users_alb_prop p WHERE u.user_id =".$DB->escape($user_id)." and p.id_proposal=".$DB->escape($propid);
    $DB->query ($query);
    $DB->next_record();
    $mail_adress = $DB->f("email");
    $pseudo = $DB->f("username");
    $nom_album = $DB->f("titre");

    $t->set_var (array(
    "ADRESSEMAIL" => $mail_adress,
    "MAILSUBJECT" => "Votre proposition BDovore : ".$nom_album,
    "MEMBRE" => $pseudo
    ));

    // url suivant et précédent
    $query = "SELECT id_proposal FROM users_alb_prop WHERE id_proposal < ".$DB->escape($propid)." AND status = 0 AND prop_type = 'CORRECTION' ORDER BY id_proposal desc;";
    $DB->query ($query);

    if ($DB->num_rows() > 0){
        $DB->next_record();
        $prev_url = BDO_URL."admin/admincorrections.php?act=valid&propid=".$DB-> f('id_proposal');

        $t->set_var (
        "BOUTONPRECEDENT" , "<a href='".$prev_url."'><input type='submit' value='Précédent'></a>"
        );
    }else{
        $t->set_var (
        "BOUTONPRECEDENT" , "<del>Précédent</del>"
        );
    }

    $query = "SELECT id_proposal FROM users_alb_prop WHERE id_proposal>".$DB->escape($propid)." AND status = 0 AND prop_type = 'CORRECTION' ORDER BY id_proposal;";
    $DB->query ($query);

    if ($DB->num_rows() > 0){
        $DB->next_record();
        $next_url = BDO_URL."admin/admincorrections.php?act=valid&propid=".$DB-> f('id_proposal');

        $t->set_var (
        "BOUTONSUIVANT" , "<a href='".$next_url."'><input type='submit' value='Suivant'></a>"
        );
    }else{
        $t->set_var (
        "BOUTONSUIVANT" , "<del>Suivant</del>"
        );
    }

    // assigne la barre de login
    $t->set_var (array(
    "LOGINBARRE" => GetIdentificationBar(),
    "MENUBARRE" => admin_menu(),
    "URLSITE" => BDO_URL,
    "URLSITEIMAGE" => BDO_URL_IMAGE,
    "URLSITEFORUM" => BDO_URL_FORUM
    ));
    $t->parse("BODY","tpBody");
    $t->pparse("MyFinalOutput","tpBase");
}

elseif($act=="update"){
    // Récupère l'utilisateur et l'image de couv

    $query = "SELECT img_couv, id_tome, id_edition FROM users_alb_prop WHERE id_proposal =".$DB->escape($propid);
    $DB->query ($query);
    $DB->next_record();
    $prop_img = $DB->f("img_couv");
    $lid = $DB->f("id_tome");
    $edition = $DB->f("id_edition");
    $def_edition = $_POST['txtDefEdition'];

    // Met à jour l'information propre à l'album
    // Dans la base bd_tome
    $query = "UPDATE bd_tome SET ";
    $query .= "`titre` = '".$DB->escape($_POST['txtTitre'])."', ";
    $query .= "`num_tome` = ".($_POST['txtNumTome']=='' ? "NULL" :  "'".$DB->escape($_POST['txtNumTome']). "'").", ";
    $query .= "`flg_int` = ".(($_POST['chkIntegrale'] == "checkbox") ? "'O'" : "'N'").", ";
    $query .= "`flg_type` = ".$DB->escape($_POST['lstType']).", ";
    $query .= "`id_serie` = ".$DB->escape($_POST['txtSerieId']).", ";
    $query .= "`id_genre` = ".$DB->escape($_POST['txtGenreId']).", ";

    $query .= "`id_scenar` = ".sqlise($_POST['txtScenarId'],'int').", ";
    $query .= "`id_scenar_alt` = ".sqlise($_POST['txtScenarAltId'],'int').", ";
    $query .= "`id_dessin` = ".sqlise($_POST['txtDessiId'],'int').", ";
    $query .= "`id_dessin_alt` = ".sqlise($_POST['txtDessiAltId'],'int').", ";
    $query .= "`id_color` = ".sqlise($_POST['txtColorId'],'int').", ";
    $query .= "`id_color_alt` = ".sqlise($_POST['txtColorAltId'],'int').", ";

    $query .= "`histoire` = '".$DB->escape($_POST['txtHistoire'])."'";
    $query .=" WHERE (`id_tome`=".$lid.");";

    $DB->query($query);
    echo 'Info album : base bd_tome mise à jour.<br />';


    // Met à jour les informations série dans la table bd_tome
    $query = "UPDATE bd_tome SET";
    $query .= " `id_genre` = ".$DB->escape($_POST['txtGenreId']);
    $query .=" WHERE (`id_serie`=".$DB->escape($_POST['txtSerieId']).");";
    $DB->query($query);
    echo 'Info série : base bd_tome mise à jour.<br>';


    // Enfin, met à jour la table série
    $query = "UPDATE bd_serie SET";
    $query .= " `nom` = '".$DB->escape($_POST['txtSerie'])."',";
    $query .= " `id_genre` = ".$DB->escape($_POST['txtGenreId']).",";
    $query .= " `flg_fini` = ".$DB->escape($_POST['lstStatus']);
    $query .=" WHERE (`id_serie`=".$DB->escape($_POST['txtSerieId']).");";
    $DB->query($query);
    echo 'Info série : base bd_serie mise à jour.<br />';

    // copie l'image dans les couvertures
    if (($prop_img != '') && ($_POST['chkDelete'] != 'checked') && $edition != 0){
        $newfilename = "CV-".sprintf("%06d",$lid)."-".sprintf("%06d",$edition);
        $strLen =strlen ($prop_img);
        $newfilename .= substr ($prop_img, $strLen - 4, $strLen);//file extension
        @copy(BDO_DIR."images/tmp/$prop_img", BDO_DIR."images/couv/$newfilename");
        @unlink(BDO_DIR."images/tmp/$prop_img");
    }

    if ($_POST['chkModifEdition'] != 'checked'){

        // Mise à jour de la table bd_edition
        if ($edition == 0){
            // Mise à jour de la table bd_edition
            $query = "UPDATE bd_edition SET ";
            $query .= "`id_editeur` = ".$DB->escape($_POST['txtEditeurId']).", ";
            $query .= "`id_collection` = ".$DB->escape($_POST['txtCollecId']).", ";
            $query .= "`ean` = ".($_POST['txtEAN']=='' ? "NULL" :  "'".$DB->escape($_POST['txtEAN']). "'").", ";
            $query .= "`isbn` = ".($_POST['txtISBN']=='' ? "NULL" :  "'".$DB->escape($_POST['txtISBN']). "'").", ";
            $query .=" WHERE (`id_tome`=".$lid.");";
            $DB->query($query);
            echo 'Info édition : base bd_edition mise à jour.<br />';

        }else{
            // Mise à jour de la table bd_edition
            $query = "UPDATE bd_edition SET ";
            $query .= "`id_editeur` = ".$DB->escape($_POST['txtEditeurId']).", ";
            $query .= "`id_collection` = ".$DB->escape($_POST['txtCollecId']).", ";
            $query .= "`ean` = ".($_POST['txtEAN']=='' ? "NULL" :  "'".$DB->escape($_POST['txtEAN']). "'").", ";
            $query .= "`isbn` = ".($_POST['txtISBN']=='' ? "NULL" :  "'".$DB->escape($_POST['txtISBN']). "'").", ";
            $query .= "`dte_parution` = ".sqlise($_POST['txtDateParution'],'text');
            // vérifie si une image a été proposée
            if (($prop_img != '') && ($_POST['chkDelete'] != 'checked'))
            {
                $query .= ", `img_couv` = '$newfilename'";
            }
            $query .=" WHERE (`id_edition`=".$edition.");";
            $DB->query($query);
            echo 'Info édition : base bd_edition mise à jour.<br>';
        }
    }

    //Efface le fichier de la base et passe le status de l'album à valider
    if ($prop_img != ''){
        $filename = $DB->f ("img_couv");
        if (file_exists(BDO_DIR."images/tmp/$filename")){
            @unlink(BDO_DIR."images/tmp/$filename");
        }
    }

    if ($_POST["chkResize"] == "checked" && $edition != 0) {

        //Redimensionnement
        //*****************

        $max_size = 180;
        $imageproperties = getimagesize(BDO_DIR."images/couv/$newfilename");
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
                case "1":
                    $source = imagecreatefromgif(BDO_DIR."images/couv/$newfilename");
                    break;

                case "2":
                    $source = imagecreatefromjpeg(BDO_DIR."images/couv/$newfilename");
                    break;

                case "3":
                    $source = imagecreatefrompng(BDO_DIR."images/couv/$newfilename");
                    break;

                case "6":
                    $source = imagecreatefrombmp(BDO_DIR."images/couv/$newfilename");
                    break;
            }

            imagecopyresampled($new_image, $source, 0, 0, 0, 0, $new_w, $new_h, $imagelargeur, $imagehauteur);

            switch ($imagetype) {
                case "2":
                    unlink(BDO_DIR."images/couv/$newfilename");
                    imagejpeg($new_image,BDO_DIR."images/couv/$newfilename",100);
                    break;

                case "1":
                case "3":
                case "6":
                    unlink(BDO_DIR."images/couv/$newfilename");
                    $img_couv = substr($newfilename,0,strlen($newfilename)-3)."jpg";
                    imagejpeg($new_image,BDO_DIR."images/couv/$img_couv",100);

                    // met à jours la référence au fichier dans la table bd_edition
                    $query = "UPDATE bd_edition SET";
                    $query .= " `img_couv` = '".$DB->escape($img_couv)."'";
                    $query .=" WHERE (`id_edition`=".$edition.");";
                    $DB->query($query);
            }

        }

        echo "$new_w, $new_h, $imagelargeur, $imagehauteur<br />";
        echo "Image redimensionnée<br />";
    }


    $query = "UPDATE users_alb_prop SET `STATUS` = 1, `VALIDATOR` =" . $DB->escape($_SESSION["UserId"]) . " , `VALID_DTE` = NOW() WHERE id_proposal=".$DB->escape($propid);
    $DB->query ($query);

    $query = "SELECT id_proposal FROM users_alb_prop WHERE id_proposal>".$DB->escape($propid)." AND status = 0 AND prop_type = 'CORRECTION' ORDER BY id_proposal;";
    $DB->query ($query);

    if ($DB->num_rows() > 0){
        $DB->next_record();
        $next_url = BDO_URL."admin/admincorrections.php?act=valid&propid=".$DB-> f('id_proposal');
    }else{
        $next_url = BDO_URL."admin/adminalbums.php?alb_id=".$lid;
    }
    echo GetMetaTag(2,"<br>L'album a été mis à jour",$next_url);
}
