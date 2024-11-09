<?php


use Wikidata\Wikidata;
/**
 * @author Tom
 *
 */
class Admin extends Bdo_Controller {

    /**
     */
    function __construct() {
       parent::__construct();
       if (User::minAccesslevel(1)) {
           
            Bdo_Cfg::setVar('explicit', 1);
           
       }
    }
    public function Index() {

        if (User::minAccesslevel(1)) {
            $this->loadModel("User_album_prop");

            $this->view->set_var($this->User_album_prop->getAllStat());

            $this->loadModel("Edition");
            $dbs_tome = $this->Edition->load("c", " WHERE bd_edition.VALID_DTE > DATE_ADD(CURRENT_DATE(), INTERVAL -1 YEAR) ORDER BY bd_edition.VALID_DTE desc limit 0,100");
            $this->view->set_var("dbs_tome", $dbs_tome);
            $this->view->set_var("PAGETITLE", "Administration Bdovore - Accueil");
            $this->view->render();
        } else {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
    }

    public function Ajout() {
        if (User::minAccesslevel(1)) {
            $list = getVal("list", "");
            if ($list == "genre") {
                /*
                 * On charge la liste des genres pour permettre leur édition
                 */
                $this->loadModel("Genre");
                $this->view->set_var(array(
                    "listGenreBD" => $this->Genre->BD(),
                    "listGenreMangas" => $this->Genre->Mangas(),
                    "listGenreComics" => $this->Genre->Comics()));
            }


            $this->view->set_var("PAGETITLE", "Administration Bdovore - Ajout");
            $this->view->render();
        } else {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
    }

    public function User() {
        if (!User::minAccesslevel(1)) {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }

        $searchvalue = getVal("username", "");
        $this->view->set_var("users", null);

        if ($searchvalue != "") {
            $this->loadModel("User");
            $this->view->set_var("users", $this->User->getUserList($searchvalue));
        }

        $this->view->set_var("PAGETITLE", "Administration Bdovore - Utilisateurs");
        $this->view->set_var("searchvalue", $searchvalue);
        $this->view->render();
    }

    public function addSerie() {
        /*
         * Ajout rapide d'une série
         */
        $this->view->set_var(array("PAGETITLE" => "Ajout rapide : serie"));
        if (User::minAccesslevel(1)) {
            $act = getVal("act");
            // Mettre à jour les informations
            if ($act == "insert") {
                $tri = substr(trim(clean_article(postVal('txtNomSerie'))), 0, 3);
                $this->loadModel("Serie");
                $this->Serie->set_dataPaste(array(
                    "NOM" => postVal('txtNomSerie'),
                    "ID_GENRE" => postValInteger("txtGenreId"),
                    "NOTE" => "0",
                    "FLG_FINI" => "1",
                    "TRI" => $tri
                ));
                $this->Serie->update();

                // fichier à utiliser
                $this->view->set_var(array(
                    "script" => "parent.$.fancybox.close();"
                ));
            }

            // Afficher le formulaire pré - remplis
            elseif ($act == "") {

                $this->view->set_var(array(
                    "URLACTION" => BDO_URL . "admin/addserie?act=insert"
                ));
            }
            $this->view->layout = "iframe";
            $this->view->render();
        }
    }

    public function addAuteur() {
        $this->view->set_var(array("PAGETITLE" => "Ajout rapide : auteur"));
        if (User::minAccesslevel(1)) {
            $act = getVal("act", "");
            if ($act == "insert") {
                if (postVal('txtPrenom') != '')
                    $long_name = postVal('txtNom') . ", " . postVal('txtPrenom');
                else
                    $long_name = postVal('txtNom');
                $pseudo = postVal('txtPseudo');
                $pseudo = notIssetOrEmpty($pseudo) ? $long_name : $pseudo;
                $this->loadModel("Auteur");
                $this->Auteur->set_dataPaste(array(
                    "PSEUDO" => $pseudo,
                    "NOM" => postVal('txtNom'),
                    "PRENOM" => postVal('txtPrenom')
                ));
                $this->Auteur->update();
                $this->view->set_var(array
                    (
                    "BODYONLOAD" => "parent.$.fancybox.close();"
                ));
            }

            // Afficher le formulaire pré-remplis
            elseif ($act == "") {
                $this->view->set_var(array
                    (
                    "URLACTION" => BDO_URL . "admin/addauteur?act=insert"
                ));
            }
            $this->view->layout = "iframe";
            $this->view->render();
        }
    }

    public function editEdition() {
        /*
         * Gestion de éditions
         */
        $this->view->set_var(array("PAGETITLE" => "Admin : Edition"));
        if (User::minAccesslevel(1)) {
            $act = getVal("act");
            $edition_id = getValInteger("edition_id");
            $conf = getVal("conf", "");
            $this->loadModel("Edition");
            $this->loadModel("Tome");
            $this->view->set_var(array("PAGETITLE" => "Administration des Editions"));
            $explicit = postVal("chkExplicite") == "checked" ? 1 : 0 ;
            // Mettre à jour les informations
            if ($act == "update") {
                $tome_id = postValInteger("txtTomeId");
                $edition_id = postValInteger("txtEditionId");
                if (is_file($_FILES["txtFileLoc"]["tmp_name"])) {// un fichier à uploader
                    $img_couv = imgCouvFromForm($tome_id, $edition_id);
                } else if (preg_match('/^(https?:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/', postVal('txtFileURL'), $url_ary)) { // un fichier à télécharger
                    $img_couv = imgCouvFromUrl2($url_ary[0], $tome_id, $edition_id);
                } else {
                    $img_couv = '';
                }
                // vérifie si l'image et temporaire et que la proposition est validée => on copie l'image
                if (postVal('FLAG_DTE_PARUTION') != "1")
                    $txtDateParution = completeDate(postVal('txtDateParution'));
                else
                    $txtDateParution = '';
               
                $this->Edition->set_dataPaste(array(
                    "ID_EDITION" => $edition_id,
                    'DTE_PARUTION' => $txtDateParution,
                    'FLAG_DTE_PARUTION' => ((postVal('FLAG_DTE_PARUTION') == "1") ? "1" : ""),
                    'ID_EDITEUR' => postValInteger('txtEditeurId'),
                    'ID_COLLECTION' => postValInteger('txtCollecId'),
                    'EAN' => trim(postVal('txtEAN')),
                    'ISBN' => trim(postVal("txtISBN")),
                    'COMMENT' => postVal("txtComment"),
                    "FLG_TT" => ((postVal('chkTT') == "checkbox") ? "O" : "N"),
                    "VALIDATOR" => $_SESSION["userConnect"]->user_id,
                    "VALID_DTE" => date('d/m/Y H:i:s'),
                    "FLG_EXPLICIT" => $explicit
                ));


                // vérifie si la couverture a été changée
                if ($img_couv != '') {
                    $this->Edition->set_dataPaste(array("IMG_COUV" => $img_couv));
                }
                $this->Edition->update();
                if (issetNotEmpty($this->Edition->error)) {
                    var_dump($this->Edition->error);
                    exit();
                }

                echo 'Mise &agrave; jour effectu&eacute;e dans la table bd_edition<br />';


                // On rajoute un redimensionnement si le correcteur l'a voulu
                if (postVal("chkResize") == "checked") {
                    $id_edition = intval(postVal("txtEditionId"));
                    $this->resize_edition_image($id_edition, BDO_DIR_COUV);
                }

                $redirection = BDO_URL . "admin/editalbum?alb_id=" . postVal("txtTomeId");
                echo GetMetaTag(1, "L'&eacute;dition a &eacute;t&eacute; mise &agrave; jour", $redirection);
                exit();
            } elseif ($act == "delete") {// EFFACEMENT D'UNE EDITION
                if ($conf == "ok") {

                    // Determine s'il y a lieu d'effacer l'image
                    $this->Edition->set_dataPaste(array("ID_EDITION" => $edition_id));
                    $this->Edition->load();
                    $url_img = $this->Edition->IMG_COUV;
                    $id_tome = $this->Edition->ID_TOME;
                    if ($url_img != '') {
                        $filename = $url_img;
                        if (file_exists(BDO_DIR_COUV . "$filename")) {
                            unlink(BDO_DIR_COUV . "$filename");
                            echo "Couverture effac&eactue;e<br />";
                        }
                    }

                    // Efface l'édition de la base
                    $this->Edition->delete();
                    $redirection = BDO_URL . "admin/editalbum?alb_id=" . $id_tome;
                    echo GetMetaTag(1, "L'&eacute;dition a &eacute;t&eacute; &eacute;ffac&eacute;e de la base", $redirection);
                    exit();
                } else {// Affiche la demande de confirmation
                    echo 'Etes-vous s&ucirc;r de vouloir effacer l\'&eacute;dition n. ' . $edition_id . ' ? <a href="' . BDO_URL . 'admin/editedition?act=delete&conf=ok&edition_id=' . $edition_id . '">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';
                    exit();
                }
            } elseif ($act == "autorize") {// ACTIVATION D'UNE EDITION
                // Commence par activer l'édition dans la base
                $this->Edition->set_dataPaste(array(
                    "ID_EDITION" => $edition_id));
                $this->Edition->load();
                $prop_img = $this->Edition->IMG_COUV;
                $this->Edition->set_dataPaste(array(
                    "PROP_STATUS" => "1",
                    "VALIDATOR" => $_SESSION["userConnect"]->user_id,
                    "VALID_DTE" => date('d/m/Y H:i:s')
                ));
                $this->Edition->update();
                // vérifit l'image
                if (substr($prop_img, 0, 3) == "tmp") {
                    $newfilename = "CV-" . sprintf("%06d", $this->Edition->ID_TOME) . "-" . sprintf("%06d", $this->Edition->ID_EDITION);
                    $strLen = strlen($prop_img);
                    $newfilename .= substr($prop_img, $strLen - 4, $strLen);
                    @copy(BDO_DIR_UPLOAD . $prop_img, BDO_DIR_COUV . $newfilename);
                    @unlink(BDO_DIR_UPLOAD . $prop_img);

                    // met à jour la référence au fichier dans la table bd_edition
                    $this->Edition->set_dataPaste(array("IMG_COUV" => $newfilename));
                    $this->Edition->update();
                }
                echo GetMetaTag(1, "L'&eacute;dition a &eacute;t&eacute; activ&eacute;e", BDO_URL . "admin/editalbum?alb_id=" . $this->Edition->ID_TOME);
                exit();
            }
// AFFICHE UN FORMULAIRE VIDE
            elseif ($act == "new") {
                // determine si une référence d'album a été passée
                 
                if (getVal("alb_id", "") <> "") {
                    $alb_id = getValInteger("alb_id");
                    $this->Tome->set_dataPaste(array("ID_TOME" => $alb_id));
                    $this->Tome->load();
                    $alb_titre = $this->Tome->TITRE_TOME;
                } else {
                    $alb_titre = '';
                    $alb_id = '';
                }

                $url_image = BDO_URL_COUV . "default.png";
                // Creation d'un nouveau Template
                $this->view->set_var(array(
                    "URLIMAGE" => $url_image,
                    "NBUSERS" => "0",
                    "IDTOME" => $alb_id,
                    "TITRE" => $alb_titre,
                    "URLDELETE" => "javascript:alert('D&eactue;sactiv&eacute;');",
                    "ACTIONNAME" => "Enregistrer",
                    "URLACTION" => BDO_URL . "admin/editedition?act=append"
                ));
                // assigne la barre de login
                $this->view->render();
            }

// INSERE UNE NOUVELLE EDITION DANS LA BASE
            elseif ($act == "append") {
                $id_tome = postValInteger('txtTomeId');

                $txtDateParution = completeDate(postVal('txtDateParution'));
                if (postVal('txtDateParution') == "")
                    $flag_dte_par = 1;
                else
                    $flag_dte_par = ((postVal('FLAG_DTE_PARUTION') == "1") ? "1" : "");

                $this->Edition->set_dataPaste(array(
                    "ID_TOME" => $id_tome,
                    "ID_EDITEUR" => postValInteger('txtEditeurId'),
                    "ID_COLLECTION" => postValInteger('txtCollecId'),
                    "DTE_PARUTION" => $txtDateParution,
                    "FLAG_DTE_PARUTION" => $flag_dte_par,
                    "EAN" => trim(postVal("txtEAN")),
                    'ISBN' => trim(postVal("txtISBN")),
                    'COMMENT' => postVal("txtComment"),
                    "FLG_TT" => ((postVal('chkTT') == "checkbox") ? "O" : "N"),
                    "VALIDATOR" => $_SESSION["userConnect"]->user_id,
                    "VALID_DTE" => date('d/m/Y H:i:s'),
                    "FLG_EXPLICIT" => $explicit
                ));
                $this->Edition->update();
                if (issetNotEmpty($this->Edition->error)) {
                    var_dump($this->Edition->error);
                    exit();
                }
                // récupère la valeur de la dernière insertion
                $lid = $this->Edition->ID_EDITION;

                // Verifie la présence d'une image à télécharger
                if (is_file($_FILES["txtFileLoc"]["tmp_name"])) { // un fichier à uploader
                    $img_couv = imgCouvFromForm($id_tome, $lid);
                } else if (preg_match('/^(https?:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/', postVal('txtFileURL'), $url_ary)) { // un fichier à télécharger
                    $img_couv = imgCouvFromUrl2($url_ary[0], $id_tome, $lid);
                } else {
                    $img_couv = '';
                }

                if ($img_couv != '') {
                    // met à jour la référence au fichier dans la table bd_edition
                    $this->Edition->set_dataPaste(array("IMG_COUV" => $img_couv));
                    $this->Edition->update();
                }

                // On rajoute un redimensionnement si le correcteur l'a voulu
                if (postVal("chkResize") == "checked") {
                    $this->resize_edition_image($lid, BDO_DIR_COUV);
                }

                echo GetMetaTag(2, "L'&eacute;dition a &eacute;t&eacute; ajout&eacute;e", (BDO_URL . "admin/editalbum?alb_id=" . $id_tome));
            }

// AFFICHER UNE EDITION
            elseif ($act == "") {

                // récupérer le nombres dutilisateurs avec cette edition dans leur collection
                $this->Edition->set_dataPaste(array("ID_EDITION" => $edition_id));

                $this->Edition->load();
                $id_edition = $this->Edition->ID_EDITION ?? -1;
                if ($id_edition == -1) {
                    // on sort : impossible de charger l'édition
                    $this->view->set_var(array(
                        "IDTOME" => 0,
                        "IDEDITION" => $edition_id,
                        "TITRE" => "",
                        "IDEDIT" => 0,
                        "EDITEUR" => "",
                        "IDCOLLEC" => 0,
                        "COLLECTION" => "",
                        "DTPAR" => "",
                        "CHKFLAG_DTE_PARUTION" => "",
                        "COMMENT" => "",
                        "ISTT" => "",
                        "FLGDEF" => "",
                        "EAN" => "",
                        "URLEAN" => "",
                        "ISBN" => "",
                        "URLISBN" => "",
                        "URLIMAGE" => "",
                        "DIMIMAGE" => "",
                        "NBUSERS" => 0,
                        "VIEWUSEREDITION" => "<a href='" . BDO_URL . "admin/viewUserEdition.php?id_edition=" . $edition_id . "'>(voir les utilisateurs)</a>",
                        "ACTIONAUTORIZE" => "",
                        "CONTACTUSER" => "",
                        "URLDELETE" => BDO_URL . "admin/editedition?act=delete&edition_id=" . $edition_id,
                        "URLFUSION" => "",
                        "URLFUSIONEDITION" => BDO_URL . "admin/mergeeditions?source_id=" . $edition_id,
                        "URLEDITEDIT" => "",
                        "URLEDITCOLL" => "",
                        "ACTIONNAME" => "Valider les Modifications",
                        "URLACTION" => BDO_URL . "admin/editedition?act=update",
                        "EXPLICIT_CHECKED" => ""
                    ));
                } else {
                     $nbusers = intval($this->Edition->NBR_USER_ID ?? 0);

                    // Récupère l'adresse mail de l'utilisateur

                    $mail_adress = $this->Edition->EMAIL ?? Null;
                    $mailsubject = "Votre proposition de nouvelle &eacute;dition pour l'album : " . $this->Edition->TITRE_TOME ?? "";
                    $pseudo = $this->Edition->USERNAME ?? "";




                    // Determine l'URL image
                    $img = $this->Edition->IMG_COUV ?? Null;
                    if (is_null($img) | ($img)) {
                        $url_image = BDO_URL_COUV . "default.png";
                        $dim_image = "";
                    } else {
                        if (substr($this->Edition->IMG_COUV, 0, 3) == "tmp") { // image temporaire dans le repertoire upload
                            $url_image = BDO_URL_IMAGE . "tmp/" . $this->Edition->IMG_COUV;
                            $dim_image = imgdim(BDO_DIR_UPLOAD . $this->Edition->IMG_COUV);
                        } else {
                            $url_image = BDO_URL_COUV . $this->Edition->IMG_COUV;
                            $dim_image = imgdim(BDO_DIR_COUV . $this->Edition->IMG_COUV);
                        }
                    }

                    // détermine s'il est possible d'effacer cet album
                    if (($this->Edition->ID_EDITION == $this->Edition->ID_EDITION_DEFAULT) | ($nbusers > 0)) {
                        $url_delete = "javascript:alert('Impossible d\'effacer cette &eacute;dition');";
                    } else {
                        $url_delete = BDO_URL . "admin/editedition?act=delete&edition_id=" . $edition_id;
                    }
                    // Activation de l'edition
                    if ($this->Edition->PROP_STATUS == 0) {
                        $actionautorise = "<a href=\"" . BDO_URL . "admin/editedition?act=autorize&edition_id=" . $edition_id . "\">Activer cette &eacute;dition</a>";
                        $contactuser = "propos&eacute;e par <a href=\"mailto:" . $mail_adress . "?subject=" . $mailsubject . "\" style=\"font-weight: bold;\">" . $pseudo . "</a> (" . $mail_adress . ")<br />";
                    } else {
                        $actionautorise = "";
                        $contactuser = "";

                    }

                    $this->view->set_var(array(
                        "IDTOME" => $this->Edition->ID_TOME,
                        "IDEDITION" => $edition_id,
                        "TITRE" => stripslashes($this->Edition->TITRE_TOME),
                        "IDEDIT" => $this->Edition->ID_EDITEUR,
                        "EDITEUR" => $this->Edition->NOM_EDITEUR,
                        "IDCOLLEC" => $this->Edition->ID_COLLECTION,
                        "COLLECTION" => $this->Edition->NOM_COLLECTION,
                        "DTPAR" => $this->Edition->DATE_PARUTION_EDITION,
                        "CHKFLAG_DTE_PARUTION" => (($this->Edition->FLAG_DTE_PARUTION == 1) ? 'CHECKED' : ''),
                        "COMMENT" => stripslashes($this->Edition->COMMENT_EDITION),
                        "ISTT" => isset($this->Edition->FLG_TT) ? "" : (($this->Edition->FLG_TT == 'O') ? 'checked' : ''),
                        "FLGDEF" => (($this->Edition->ID_EDITION == $this->Edition->ID_EDITION_DEFAULT ? 'O' : '')),
                        "EAN" => $this->Edition->EAN_EDITION,
                        "URLEAN" => "http://www.bdnet.com/" . $this->Edition->EAN_EDITION . "/alb.htm",
                        "ISBN" => $this->Edition->ISBN_EDITION,
                        "URLISBN" => BDO_PROTOCOL . "://www.amazon.fr/exec/obidos/ASIN/" . $this->Edition->ISBN_EDITION,
                        "URLIMAGE" => $url_image,
                        "DIMIMAGE" => $dim_image,
                        "NBUSERS" => $nbusers,
                        "VIEWUSEREDITION" => "<a href='" . BDO_URL . "admin/viewUserEdition.php?id_edition=" . $edition_id . "'>(voir les utilisateurs)</a>",
                        "ACTIONAUTORIZE" => $actionautorise,
                        "CONTACTUSER" => $contactuser,
                        "URLDELETE" => $url_delete,
                        "URLFUSION" => BDO_URL . "admin/mergealbums?source_id=" . $this->Edition->ID_TOME,
                        "URLFUSIONEDITION" => BDO_URL . "admin/mergeeditions?source_id=" . $edition_id,
                        "URLEDITEDIT" => BDO_URL . "admin/editediteur?editeur_id=" . $this->Edition->ID_EDITEUR,
                        "URLEDITCOLL" => BDO_URL . "admin/editcollection?collec_id=" . $this->Edition->ID_COLLECTION,
                        "ACTIONNAME" => "Valider les Modifications",
                        "URLACTION" => BDO_URL . "admin/editedition?act=update",
                        "EXPLICIT_CHECKED" => $this->Edition->FLG_EXPLICIT ? "checked" : ""
                    ));
                    if ($this->Edition->DATE_PARUTION_EDITION == "0000-00-00") {
                        $this->view->set_var("PARUTION_0", "to_be_corrected");
                    }

                }
               
                $this->view->render();
            }
        } else {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
    }

    public function editAlbum() {
        /*
         * Methode d'édition / modification / suppression d'un album
         *
         */
        $this->view->set_var(array("PAGETITLE" => "Admin : Album"));
        if (User::minAccesslevel(1)) {
            $this->loadModel("Tome");
            $this->loadModel("Serie");
            // Tableau pour les choix d'options du status des series
            $opt_status[0][0] = 0;
            $opt_status[0][1] = 'Finie';
            $opt_status[1][0] = 1;
            $opt_status[1][1] = 'En cours';
            $opt_status[2][0] = 2;
            $opt_status[2][1] = 'One Shot';
            $opt_status[3][0] = 3;
            $opt_status[3][1] = 'Interrompue/Abandonn&eacute;e';

// Tableau pour les choix d'options
            $opt_type[0][0] = 0;
            $opt_type[0][1] = 'Album';
            $opt_type[1][0] = 1;
            $opt_type[1][1] = 'Coffret';


            $act = getVal("act");
            $conf = getVal("conf");
            $idtome = getValInteger("idtome");
// Mettre à jour les informations
            if ($act == "update") {
                $this->Serie->set_dataPaste(array("ID_SERIE" => postValInteger("txtSerieId")));
                $this->Serie->load(); // chargement de la série pour récupérer le genre de l'album

                $this->Tome->set_dataPaste(array(
                    "ID_TOME" => postValInteger("txtTomeId"),
                    "TITRE" => postVal("txtTitre"),
                    "NUM_TOME" => postVal("txtNumTome", ""),
                    "ID_SERIE" => postValInteger("txtSerieId"),
                    "PRIX_BDNET" => floatval(postVal("txtPrixVente")),
                    "ID_SCENAR" => postValInteger("txtScenarId"),
                    "ID_DESSIN" => postValInteger("txtDessiId"),
                    "ID_DESSIN_ALT" => postValInteger('txtDessiAltId') ? postValInteger('txtDessiAltId') : '0',
                    "ID_SCENAR_ALT" => postValInteger('txtScenarAltId') ? postValInteger('txtScenarAltId') : '0',
                    "ID_COLOR" => postValInteger("txtColorId", "0"),
                    "ID_COLOR_ALT" => postValInteger("txtColorAltId") ? postValInteger("txtColorAltId") : "0",
                    "FLG_INT" => (postVal("chkIntegrale") == "checkbox") ? "O" : "N",
                    "FLG_TYPE" => postVal("lstType"),
                    "HISTOIRE" => postVal("txtHistoire"),
                    "ID_GENRE" => $this->Serie->ID_GENRE,
                    "ID_EDITION" => postVal("btnDefEdit")
                ));

                $this->Tome->update();
                if (issetNotEmpty($this->Tome->error)) {
                    var_dump($this->Tome->error);
                    exit();
                }
                echo GetMetaTag(2, "Mise &agrave; jour effectu&eacute;e", (BDO_URL . "admin/editalbum?alb_id=" . postValInteger("txtTomeId")));
            }


// EFFACEMENT D'UN ALBUM
            elseif ($act == "delete") {
                if ($conf == "ok") {
                    //Revérifie que c'est bien l'administrateur qui travaille
                    if (User::minAccesslevel(1)) {
                        // Efface les éditions et les couvertures correspondantes
                        $this->loadModel("Edition");
                        $dbs_edition = $this->Edition->load("c", "where bd_tome.id_tome =" . $idtome);
                        foreach ($dbs_edition->a_dataQuery as $edition) {
                            if ($edition->IMG_COUV != '') {
                                $filename = $edition->IMG_COUV;
                                if (file_exists(BDO_DIR_COUV . "$filename")) {
                                    @unlink(BDO_DIR_COUV . "$filename");
                                    echo "Couverture effac&eacute;e pour l'eacute;dition N" . $edition->ID_EDITION . "<br />";
                                }
                            }
                        }
                        // vide la table bd_edition
                        $this->Tome->deleteEditionForAlbum($idtome);
                        echo 'R&eacute;f&eacute;rence(s) &agrave; l\'album supprim&eacute;e(s) dans la table bd_edition<br />';

                        $this->Tome->set_dataPaste(array("ID_TOME" => $idtome));
                        $this->Tome->delete();

                        $redirection = BDO_URL . "admin";
                        echo '<META http-equiv="refresh" content="1; URL=' . $redirection . '">L\'album a &eacute;t&eacute; effac&eacute; de la table bd_tome.';
                        exit();
                    }
                } else {
                    // Affiche la demande de confirmation
                    echo 'Etes-vous sur de vouloir effacer l\'album n. ' . $idtome . ' ? <a href="' . BDO_URL . 'admin/editalbum?act=delete&conf=ok&idtome=' . $idtome . '">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';
                    exit();
                }
            } elseif ($act == "new") {
                // AFFICHE UN FORMULAIRE VIDE
                $url_image = BDO_URL_COUV . "default.png";
                $champ_form_style = 'champ_form_desactive';



                $this->view->set_var(array(
                    "CHAMPFORMSTYLE" => $champ_form_style,
                    "URLIMAGE" => $url_image,
                    "OPTTYPE" => GetOptionValue($opt_type, 0),
                    "NBUSERS" => "0",
                    "NBUSERS2" => "0",
                    "URLSERIE" => "javascript:alert('D&eacute;sactiv&eacute;');",
                    "URLDELETE" => "javascript:alert('D&eacute;sactiv&eacute;');",
                    "URLFUSION" => "javascript:alert('D&eacute;sactiv&eacute;');",
                    "ACTIONNAME" => "Enregistrer",
                    "URLEDITSERIE" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
                    "URLEDITGENRE" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
                    "URLEDITSCEN" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
                    "URLEDITDESS" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
                    "URLEDITDESSALT" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
                    "URLEDITCOLOR" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
                    "URLEDITCOLORALT" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
                    "URLEDITEDIT" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
                    "URLEDITCOLL" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
                    "URLEDITCOLLALT" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
                    "URLACTION" => BDO_URL . "admin/editalbum?act=append",
                    
                    "IDTOME" => "",
                    "TITRE" => "",
                    "IDSERIE" => "",
                    "SERIE" => "",
                    "TRI" => "",
                    "IDGENRE" => "",
                    "GENRE" => "",
                    "NBTOME" => "",
                    "HISTOIRE_SERIE" => "",
                    "TOME" => "",
                    "PRIX_VENTE" => "",
                    "IDSCEN" => "",
                    "SCENARISTE" => "",
                    "IDSCENALT" => "",
                    "SCENARISTEALT" => "",
                    "IDDESS" => "",
                    "DESSINATEUR" => "",
                    "IDDESSALT" => "",
                    "DESSINATEURALT" => "",
                   
                     "IDCOLOR" => "",
                    "COLORISTE" => "",
                    "IDCOLORALT" => "",
                    "COLORISTEALT" => "",
                    "IDEDIT" => "",
                    "EDITEUR" => "",
                   "ISINT" => "",
                    "HISTOIRE" => "",
                    "DIMIMAGE" => "",
                    "EXPLICIT_CHECKED" => "",
                    "COLLECTION" => "",
                    "IDCOLLEC" => NULL,
                    "CHKFLAG_DTE_PARUTION" => NULL,
                    "ISTT" => "",
                    "EAN" => "",
                    "ISBN" => "",
                    "COMMENT" => "",
                    "OPTSTATUS" => GetOptionValue($opt_status, 0),
                   
                    
                ));
                $this->view->render();
            }


// AFFICHE UN FORMULAIRE prérempli
            elseif ($act == "newfserie") {
                $url_image = BDO_URL . "images/couv/default.png";
                $champ_form_style = 'champ_form_desactive';
                $champ_form_style_newfserie = 'champ_form_desactive_newfserie';
                // Creation d'un nouveau Template


                $id_serie = getValInteger("id_serie");
                $this->Tome->load("c", " WHERE bd_tome.id_serie =" . $id_serie . " ORDER BY bd_tome.num_tome DESC LIMIT 1");
                
                // on met me flag explicit automaitquement si la série est dnas un genre erotique
                $listExplicit = array(17, 79, 55);
                if (in_array ($this->Tome->ID_GENRE , $listExplicit)) {
                    $explicit = "checked";
                } else {
                    $explicit = "";
                }

                $this->view->set_var(array(
                    "CHAMPFORMSTYLE" => $champ_form_style,
                    "CHAMPFORMSTYLE_NEWFSERIE" => $champ_form_style_newfserie,
                    "URLIMAGE" => $url_image,
                    "OPTTYPE" => GetOptionValue($opt_type, 0),
                    "NBUSERS" => "0",
                    "NBUSERS2" => "0",
                    "TOME" => $this->Tome->NUM_TOME + 1,
                    "IDSERIE" => $id_serie,
                    "SERIE" => $this->Tome->NOM_SERIE,
                    "IDSCEN" => $this->Tome->ID_SCENAR,
                    "SCENARISTE" => $this->Tome->scpseudo,
                    "IDSCENALT" => $this->Tome->ID_SCENAR_ALT,
                    "SCENARISTEALT" => ($this->Tome->ID_SCENAR_ALT == 0 ) ? "" : $this->Tome->scapseudo,
                    "IDDESS" => $this->Tome->ID_DESSIN,
                    "DESSINATEUR" => $this->Tome->depseudo,
                    "IDDESSALT" => $this->Tome->ID_DESSIN_ALT,
                    "DESSINATEURALT" => ($this->Tome->ID_DESSIN_ALT == 0 ) ? "" : $this->Tome->deapseudo,
                    "IDCOLOR" => $this->Tome->ID_COLOR,
                    "COLORISTE" => $this->Tome->copseudo,
                    "IDCOLORALT" => $this->Tome->ID_COLOR_ALT,
                    "COLORISTEALT" => ($this->Tome->ID_COLOR_ALT == 0 ) ? "" : $this->Tome->coapseudo,
                    "IDEDIT" => $this->Tome->ID_EDITEUR,
                    "EDITEUR" => $this->Tome->NOM_EDITEUR,
                    "IDCOLLEC" => $this->Tome->ID_COLLECTION,
                    "COLLECTION" => $this->Tome->NOM_COLLECTION,
                    "URLSERIE" => "javascript:alert('D&eacute;sactiv&eacute;');",
                    "URLDELETE" => "javascript:alert('D&eacute;sactiv&eacute;');",
                    "URLFUSION" => "javascript:alert('D&eacute;sactiv&eacute;');",
                    "ACTIONNAME" => "Enregistrer",
                    "URLEDITSERIE" => BDO_URL . "admin/editserie?serie_id=" . $this->Tome->ID_SERIE,
                    "URLEDITGENRE" => BDO_URL . "admin/editgenre?genre_id=" . $this->Tome->ID_GENRE,
                    "URLEDITSCEN" => BDO_URL . "admin/editauteur?auteur_id=" . $this->Tome->ID_SCENAR,
                    "URLEDITDESS" => BDO_URL . "admin/editauteur?auteur_id=" . $this->Tome->ID_DESSIN,
                    "URLEDITCOLOR" => BDO_URL . "admin/editauteur?auteur_id=" . $this->Tome->ID_COLOR,
                    "URLEDITSCENALT" => BDO_URL . "admin/editauteur?auteur_id=" . $this->Tome->ID_SCENAR_ALT,
                    "URLEDITDESSALT" => BDO_URL . "admin/editauteur?auteur_id=" . $this->Tome->ID_DESSIN_ALT,
                    "URLEDITCOLORALT" => BDO_URL . "admin/editauteur?auteur_id=" . $this->Tome->ID_COLOR_ALT,
                    "URLEDITEDIT" => BDO_URL . "admin/editediteur?editeur_id=" . $this->Tome->ID_EDITEUR,
                    "URLEDITCOLL" => BDO_URL . "admin/editcollection?collec_id=" . $this->Tome->ID_COLLECTION,
                    "URLEDITCOLLALT" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
                    "URLACTION" => BDO_URL . "admin/editalbum?act=append",
                    "EXPLICIT_CHECKED" => $explicit
                ));

                $this->view->render();
            }


// INSERE UN NOUVEL ALBUM DANS LA BASE
            elseif ($act == "append") {

                // on vérifie d'abord la date
                $txtDateParution = completeDate(postVal('txtDateParution'));
                $flgDteParution = postVal('FLAG_DTE_PARUTION');
                $explicit = postVal("chkExplicite") == "checked" ? 1 : 0 ;
                if (!postVal("txtTitre")) {
                    echo "Erreur : il semble que vous ayez oublié le titre :D";
                    exit();
                }
                if (!validateDate($txtDateParution) AND $flgDteParution <> "1") {
                    echo "Erreur : la date fournie est invalide et risque de générer un album fantôme :D";
                    exit();
                }
                $this->Serie->set_dataPaste(array("ID_SERIE" => postValInteger("txtSerieId")));
                $this->Serie->load(); // chargement de la série pour récupérer le genre de l'album

                $this->Tome->set_dataPaste(array(
                    "TITRE" => postVal("txtTitre"),
                    "NUM_TOME" => postVal("txtNumTome", ""),
                    "ID_SERIE" => postValInteger("txtSerieId"),
                    "PRIX_BDNET" => floatval(postVal("txtPrixVente")),
                    "ID_SCENAR" => postValInteger("txtScenarId"),
                    "ID_DESSIN" => postValInteger("txtDessiId"),
                    "ID_DESSIN_ALT" => postValInteger('txtDessiAltId') ? postValInteger('txtDessiAltId') : '0',
                    "ID_SCENAR_ALT" => postValInteger('txtScenarAltId') ? postValInteger('txtScenarAltId') : '0',
                    "ID_COLOR" => postValInteger("txtColorId", "0"),
                    "ID_COLOR_ALT" => postValInteger("txtColorAltId") ? postValInteger("txtColorAltId") : "0",
                    "FLG_INT" => (postVal("chkIntegrale") == "checkbox") ? "O" : "N",
                    "FLG_TYPE" => postVal("lstType"),
                    "HISTOIRE" => postVal("txtHistoire"),
                    "ID_GENRE" => $this->Serie->ID_GENRE
                ));
                $this->Tome->update();
                if (issetNotEmpty($this->Tome->error)) {
                    var_dump($this->Tome->error);
                    exit();
                }
                // récupère la valeur de la dernière insertion
                $lid_tome = $this->Tome->ID_TOME;


                // insère un champ dans la table id_edition
                $this->loadModel("Edition");


                $this->Edition->set_dataPaste(array(
                    'DTE_PARUTION' => $txtDateParution,
                    'FLAG_DTE_PARUTION' => ((postVal('FLAG_DTE_PARUTION') == "1") ? "1" : ""),
                    'ID_EDITEUR' => postValInteger('txtEditeurId'),
                    'ID_COLLECTION' => postValInteger('txtCollecId'),
                    'EAN' => trim(postVal('txtEAN')),
                    'ISBN' => trim(postVal("txtISBN")),
                    'COMMENT' => postVal("txtComment"),
                    "FLG_TT" => ((postVal('chkTT') == "checkbox") ? "O" : "N"),
                    "VALIDATOR" => $_SESSION["userConnect"]->user_id,
                    "ID_TOME" => $lid_tome,
                    "VALID_DTE" => date('d/m/Y H:i:s'),
                    "PROP_DTE" => date('d/m/Y H:i:s'),
                    "FLG_EXPLICIT" => $explicit
                ));
                $this->Edition->update();
                if (issetNotEmpty($this->Edition->error)) {
                    echo "Erreur lors de la création de l'édition !";
                    var_dump($this->Edition->error);
                    exit();
                }

                // récupère la valeur de la dernière insertion
                $lid_edition = $this->Edition->ID_EDITION;

                // renseigne cette edition comme defaut pour bd_tome
                $this->Tome->set_dataPaste(array("ID_EDITION" => $lid_edition));
                $this->Tome->update();
                if (issetNotEmpty($this->Tome->error)) {
                    var_dump($this->Tome->error);
                    exit();
                }



                // Verifie la présence d'une image à télécharger
                if (is_file($_FILES["txtFileLoc"]["tmp_name"]) | (preg_match('/^(http:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/', postval('txtFileURL'), $url_ary))) {
                    if (is_file($_FILES["txtFileLoc"]["tmp_name"])) { // un fichier à uploader
                        $img_couv = imgCouvFromForm($lid_tome, $lid_edition);
                    } else if (preg_match('/^(https?:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/', $_POST['txtFileURL'], $url_ary)) { // un fichier à télécharger
                        if (empty($url_ary[4])) {
                            echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">URL image incomplete. Vous allez etre redirige.';
                            exit();
                        }
                       
                        $img_couv = imgCouvFromUrl2($url_ary[0], $lid_tome, $lid_edition);
                        
                    } else {
                        $img_couv = '';
                    }
                    

                    // met à jours la référence au fichier dans la table bd_edition
                    $this->Edition->set_dataPaste(array("IMG_COUV" => $img_couv));
                    $this->Edition->update();
                    if (postVal("chkResize") == "checked" && $img_couv != '') {
                       $this->resize_edition_image($lid_edition, BDO_DIR_COUV);
                    }
                }
                echo GetMetaTag(2, "L'album a &eacute;t&eacute; ajout&eacute;", (BDO_URL . "admin/editalbum?alb_id=" . $lid_tome));
            }


// AFFICHER UN ALBUM
            elseif ($act == "") {
                $alb_id = getValInteger("alb_id");
                $this->Tome->set_dataPaste(array("ID_TOME" => $alb_id));
                $this->Tome->load();
                // récupère le nombre d'utilisateurs
                if (!isset($this->Tome->ID_TOME)) {
                    echo GetMetaTag(2, "L'album n'existe pas", (BDO_URL . "admin"));
                } else {
                    
               
                    $nb_users = $this->Tome->NBR_USER_ID_TOME;


                    $nb_comments = $this->Tome->NB_NOTE_TOME;

                    $id_edition_default = $this->Tome->ID_EDITION;

                    $champ_form_style = 'champ_form_desactive';

                    // détermine s'il est possible d'effacer cet album
                    if (($nb_users == 0) & ($nb_comments == 0)) {
                        $url_delete = BDO_URL . "admin/editalbum?act=delete&idtome=" . $this->Tome->ID_TOME;
                    } else {
                        $url_delete = "javascript:alert('Impossible');";
                    }

                    $this->view->set_var(array(
                        "CHAMPFORMSTYLE" => $champ_form_style,
                        "IDTOME" => $this->Tome->ID_TOME,
                        "TITRE" => $this->Tome->TITRE_TOME,
                        "IDSERIE" => $this->Tome->ID_SERIE,
                        "SERIE" => $this->Tome->NOM_SERIE,
                        "TRI" => $this->Tome->TRI,
                        "IDGENRE" => $this->Tome->ID_GENRE,
                        "GENRE" => $this->Tome->NOM_GENRE,
                        "OPTSTATUS" => GetOptionValue($opt_status, $this->Tome->FLG_FINI),
                        "NBTOME" => $this->Tome->NB_TOME,
                        "HISTOIRE_SERIE" => $this->Tome->HISTOIRE_SERIE,
                        "TOME" => $this->Tome->NUM_TOME,
                        "PRIX_VENTE" => $this->Tome->PRIX_BDNET,
                        "IDSCEN" => $this->Tome->ID_SCENAR,
                        "SCENARISTE" => $this->Tome->scpseudo,
                        "IDSCENALT" => $this->Tome->ID_SCENAR_ALT,
                        "SCENARISTEALT" => ($this->Tome->ID_SCENAR_ALT == 0 ) ? "" : $this->Tome->scapseudo,
                        "IDDESS" => $this->Tome->ID_DESSIN,
                        "DESSINATEUR" => $this->Tome->depseudo,
                        "IDDESSALT" => $this->Tome->ID_DESSIN_ALT,
                        "DESSINATEURALT" => ($this->Tome->ID_DESSIN_ALT == 0 ) ? "" : $this->Tome->deapseudo,
                        "IDCOLOR" => $this->Tome->ID_COLOR,
                        "COLORISTE" => $this->Tome->copseudo,
                        "IDCOLORALT" => $this->Tome->ID_COLOR_ALT,
                        "COLORISTEALT" => ($this->Tome->ID_COLOR_ALT == 0 ) ? "" : $this->Tome->coapseudo,
                        "IDEDIT" => $this->Tome->ID_EDITEUR,
                        "EDITEUR" => $this->Tome->NOM_EDITEUR,
                        "IDCOLLEC" => $this->Tome->ID_COLLECTION,
                        "COLLECTION" => $this->Tome->NOM_COLLECTION,
                        "HISTOIRE" => $this->Tome->HISTOIRE_TOME,
                        "ID_EDITION" => $this->Tome->ID_EDITION,
                        "ISINT" => (($this->Tome->FLG_INT_TOME == 'O') ? 'checked' : ''),
                        "OPTTYPE" => GetOptionValue($opt_type, $this->Tome->FLG_TYPE_TOME),
                        "NBUSERS" => $nb_users,
                        "NBUSERS2" => $nb_comments,
                        "URLDELETE" => $url_delete,
                        "URLFUSION" => BDO_URL . "admin/mergealbums?source_id=" . $this->Tome->ID_TOME,
                        "URLSPLIT" => BDO_URL . "admin/splitedition?alb_id=" . $this->Tome->ID_TOME,
                        "URLFUSIONDELETE" => BDO_URL . "admin/fusiondelete?alb_id=" . $this->Tome->ID_TOME,
                        "URLEDITSERIE" => BDO_URL . "admin/editserie?serie_id=" . $this->Tome->ID_SERIE,
                        "URLEDITGENRE" => BDO_URL . "admin/editgenre?genre_id=" . $this->Tome->ID_GENRE,
                        "URLEDITSCEN" => BDO_URL . "admin/editauteur?auteur_id=" . $this->Tome->ID_SCENAR,
                        "URLEDITDESS" => BDO_URL . "admin/editauteur?auteur_id=" . $this->Tome->ID_DESSIN,
                        "URLEDITCOLOR" => BDO_URL . "admin/editauteur?auteur_id=" . $this->Tome->ID_COLOR,
                        "URLEDITSCENALT" => BDO_URL . "admin/editauteur?auteur_id=" . $this->Tome->ID_SCENAR_ALT,
                        "URLEDITDESSALT" => BDO_URL . "admin/editauteur?auteur_id=" . $this->Tome->ID_DESSIN_ALT,
                        "URLEDITCOLORALT" => BDO_URL . "admin/editauteur?auteur_id=" . $this->Tome->ID_COLOR_ALT,
                        "URLEDITEDIT" => BDO_URL . "admin/editediteur?editeur_id=" . $this->Tome->ID_EDITEUR,
                        "URLEDITCOLL" => BDO_URL . "admin/editcollection?collec_id=" . $this->Tome->ID_COLLECTION,
                        "ACTIONNAME" => "Valider les Modifications",
                        "URLACTION" => BDO_URL . "admin/editalbum?act=update"
                    ));

                    // Affiche les informations relatives aux différentes éditions
                    $this->loadModel("Edition");
                    $dbs_edition = $this->Edition->load("c", "where bd_tome.id_tome =" . $this->Tome->ID_TOME ." ORDER BY DATE_PARUTION_EDITION");



                    $this->view->set_var(array(
                        "NBEDITIONS" => count($dbs_edition->a_dataQuery),
                        "dbs_edition" => $dbs_edition,
                        "URLAJOUTEDITION" => BDO_URL . "admin/editedition?act=new&alb_id=" . $alb_id
                    ));

                    $this->view->render();
                 }
            }
        } else {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
    }

    public function fusionDelete() {
        /*
         * Fusion d'album en conservant certaines éditions
         */
        // Fusionne les albums et transfère les éditions cochées
        if (!User::minAccesslevel(1)) {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }

        $act = getVal("act", "");
        if ($act == "update") {
            $old_idtome = postValInteger("txtTomeId");
            $new_idtome = postValInteger("txtTome2Id");
            if (postValInteger("txtTome2Id") == "") {
                echo GetMetaTag(2, "L'ID de l'album &agrave; conserver n'a pas &eacute;t&eacute; pr&eacute;cis&eacute;.", (BDO_URL . "admin/editalbum?alb_id=" . $old_idtome));
                exit();
            }

            $this->loadModel("Tome");
            $this->Tome->add_dataPaste("ID_TOME", $new_idtome);
            $this->Tome->load();
            // on récupère l'id edition à remplacer pour les utilisateurs qui auraient encore l'ancien id_tome dans leur collection
            $new_idedition = $this->Tome->ID_EDITION;
            $this->loadModel("Edition");

            $chkEdition = postVal("chkEdition");
            $txtCouv = postVal("txtCouv");
            foreach ($chkEdition as $idedition) {
                // Modifie les couvertures
                $old_filename = $txtCouv[$idedition];
                if ($old_filename == "") {
                    $new_filename = "";
                } else {
                    $new_filename = "CV-" . sprintf("%06d", $new_idtome) . "-" . sprintf("%06d", $idedition) . substr($old_filename, -4);
                    if (rename(BDO_DIR_COUV . $old_filename, BDO_DIR_COUV . $new_filename) === false) {
                        //TODO une fonction echo_utf8() qui fait tout ça :
                        $text = "Le fichier " . BDO_DIR_COUV . $old_filename . " ne peut pas être renommé.";
                        echo htmlentities($text, ENT_COMPAT, 'UTF-8') . "<br/>";
                        $text = "Le fichier " . BDO_DIR_COUV . $new_filename . " n'a donc pas été être créé.";
                        echo htmlentities($text, ENT_COMPAT, 'UTF-8') . "<br/>";
                    }
                }
                // on met à jour l'édition
                //$nb =  $this->Edition->updateTome($idedition,$new_idtome,$new_filename);
                $this->Edition->set_dataPaste(array(
                    "ID_EDITION" => $idedition,
                    "ID_TOME" => $new_idtome,
                    "IMG_COUV" => $new_filename
                ));
                $this->Edition->update();
                echo "Nombre de records modifi&eacute;es dans la table bd_edition : " . $this->Edition->affected_rows . " <br />";
            }

            // Met à jour les commentaires
            $this->loadModel("Comment");
            $nb = $this->Comment->replaceIdTome($old_idtome, $new_idtome);

            echo "Nombre de records modifi&eacute;es dans la table users_comment : " . $nb . "<br />";

            // Met à jour les carres
            $this->loadModel("Users_list_carre");
            $nb = $this->Users_list_carre->replaceIdTome($old_idtome, $new_idtome);
            echo "Nombre de records modifi&eacute;es dans la table users_list_carre : " . $nb . "<br />";

            // Met à jour les exclusions
            $this->loadModel("Users_exclusions");
            $nb = $this->Users_exclusions->replaceIdTome($old_idtome, $new_idtome);
            echo "Nombre de records modifi&eacute;es dans la table users_exclusions : " . $nb . "<br />";

            $this->loadModel("Useralbum");
            $nb = $this->Useralbum->replaceEditionFromTome($old_idtome, $new_idedition);
            echo "Nombre de records modifi&eacute;es dans la table users_album : " . $nb . "<br />";
            // Efface les éditions et les couvertures correspondantes qui peuvent rester
            // on charge les éditions retantes de l'ancien tome
            $this->Edition->load("c", " WHERE bd_tome.id_tome = " . intval($old_idtome));
            if (issetNotEmpty($this->Edition->a_dataQuery)) {
                foreach ($this->Edition->a_dataQuery as $edition) {

                    if ($edition->IMG_COUV != '') {
                        $filename = $edition->IMG_COUV;
                        if (file_exists(BDO_DIR_COUV . $filename)) {
                            @unlink(BDO_DIR_COUV . $filename);
                            echo "Couverture effac&eacute;e pour l'&eacute;dition N°" . $edition->ID_EDITION . "<br />";
                        }
                    }
                }
            }

            // vide la table bd_edition
            $this->Edition->deleteTome($old_idtome);
            echo 'R&eacute;f&eacute;rence(s) &agrave; l\'album supprim&eacute;e(s) dans la table bd_edition<br />';

            $this->Tome->add_dataPaste("ID_TOME", $old_idtome);
            $this->Tome->delete();

            echo 'R&eacute;f&eacute;rence(s) &eagrave; l\'album supprim&eacute;e(s) dans la table bd_tome<br />';



            $nb = $this->Useralbum->deleteTome($old_idtome);
            echo "Nombre de records supprim&eacute;s dans la table users_album : " . $nb . "<br />";

            echo GetMetaTag(2, "Fusion effectu&eacute;e.", (BDO_URL . "admin/editalbum?alb_id=" . intval($new_idtome)));
        }

// AFFICHER UN ALBUM
        elseif ($act == "") {

            // récupère les données principales
            $alb_id = getValInteger("alb_id");
            $report_id = getValInteger("report_id");
            $this->loadModel("Tome");
            $this->Tome->add_dataPaste("ID_TOME", $alb_id);
            $this->Tome->load();

            // Détermine l'affichage des infos
            $scenaristes1 = ($this->Tome->ID_SCENAR_ALT == 0) ? $this->Tome->scpseudo : $this->Tome->scpseudo . " / " . $this->Tome->scapseudo;
            $dessinateurs1 = ($this->Tome->ID_DESSIN_ALT == 0) ? $this->Tome->depseudo : $this->Tome->scdeeudo . " / " . $this->Tome->deapseudo;
            $coloristes1 = ($this->Tome->ID_COLOR_ALT == 0) ? $this->Tome->copseudo : $this->Tome->codeeudo . " / " . $this->Tome->coapseudo;
            $edcollec1 = ($this->Tome->NOM_COLLECTION == "<N/A>") ? $this->Tome->NOM_EDITEUR : $this->Tome->NOM_EDITEUR . " / " . $this->Tome->NOM_COLLECTION;

            $this->view->set_var(array(
                "IDTOME" => $this->Tome->ID_TOME,
                "TITRE" => $this->Tome->TITRE_TOME,
                "TOME" => $this->Tome->NUM_TOME,
                "SERIE" => $this->Tome->NOM_SERIE,
                "SCENARISTES" => $scenaristes1,
                "DESSINATEURS" => $dessinateurs1,
                "COLORISTES" => $coloristes1,
                "EDCOLLEC" => $edcollec1,
            ));

            if ($report_id != "") {
                // récupère les données sur le nouveau tome
                $this->Tome->add_dataPaste("ID_TOME", $report_id);
                $this->Tome->load();

                // Détermine l'affichage des infos
                $scenaristes2 = ($this->Tome->ID_SCENAR_ALT == 0) ? $this->Tome->scpseudo : $this->Tome->scpseudo . " / " . $this->Tome->scapseudo;
                $dessinateurs2 = ($this->Tome->ID_DESSIN_ALT == 0) ? $this->Tome->depseudo : $this->Tome->scdeeudo . " / " . $this->Tome->deapseudo;
                $coloristes2 = ($this->Tome->ID_COLOR_ALT == 0) ? $this->Tome->copseudo : $this->Tome->codeeudo . " / " . $this->Tome->coapseudo;
                $edcollec2 = ($this->Tome->NOM_COLLECTION == "<N/A>") ? $this->Tome->NOM_EDITEUR : $this->Tome->NOM_EDITEUR . " / " . $this->Tome->NOM_COLLECTION;

                $this->view->set_var(array(
                    "IDTOME2" => $this->Tome->ID_TOME,
                    "TITRE2" => $this->Tome->TITRE_TOME,
                    "TOME2" => $this->Tome->NUM_TOME,
                    "SERIE2" => $this->Tome->NOM_SERIE,
                    "SCENARISTES2" => $scenaristes2,
                    "DESSINATEURS2" => $dessinateurs2,
                    "COLORISTES2" => $coloristes2,
                    "EDCOLLEC2" => $edcollec2,
                ));

                // Affiche les informations relatives aux différentes éditions
                $this->loadModel("Edition");
                $dbs_edition2 = $this->Edition->load("c", " WHERE bd_tome.id_tome = " . intval($report_id));

                $nb_editions2 = count($dbs_edition2->a_dataQuery);
            }

            // Affiche les informations relatives aux différentes éditions
            $this->loadModel("Edition");
            $dbs_edition = $this->Edition->load("c", " WHERE bd_tome.id_tome = " . intval($alb_id));

            // on déclare le block à utiliser

            $this->view->set_var(array(
                "dbs_edition" => $dbs_edition,
                "dbs_edition2" => $dbs_edition2,
                "NBEDITIONS" => count($dbs_edition->a_dataQuery),
                "NBEDITIONS2" => $nb_editions2,
                "REFRESHPAGE" => "fusiondelete?alb_id=" . $alb_id,
                "URLRETOURFICHE" => BDO_URL . "admin/editalbum?alb_id=" . $alb_id,
                "ACTIONNAME" => "Transf&eacute;rer les &eacute;ditions et effacer l'album",
                "URLACTION" => BDO_URL . "admin/fusiondelete?act=update",
                "PAGETITLE" => "Admin : delete / fusion"
            ));

            $this->view->render();
        }
    }

    public function mergeAlbums() {
        if (!User::minAccesslevel(1)) {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
        $error_msg[0] = "Album &agrave; supprimer non d&eacute;fini";
        $error_msg[1] = "Album &agrave; garder non d&eacute;fini";
        $error_msg[2] = "Album &agrave; garder et album &agrave; supprimer identiques";
        $act = getVal("act");
        $conf = getVal("conf");
        $dest_id = getValInteger("dest_id", 0);
        $source_id = getValInteger("source_id", 0);
// Fusionne les albums
        if ($act == "merge") {
            // vérifie que source_id et dest_id ont été definis
            if ((is_null($dest_id)) | ($dest_id == 0)) {
                header("Location:" . BDO_URL . "admin/mergealbums?source_id=$source_id&error=1");
            }
            if ((is_null($source_id)) | ($source_id == "")) {
                header("Location:" . BDO_URL . "admin/mergealbums?dest_id=$dest_id&error=0");
            }
            if ($source_id == $dest_id) {
                header("Location:" . BDO_URL . "admin/mergealbums?source_id=$source_id&dest_id=$dest_id&error=2");
            }
            if ($conf == "ok") {

                // Récupère la valeur de l'album à mettre à jour
                // Met à jour les commentaires
                $this->loadModel("Comment");
                $nb = $this->Comment->replaceIdTome($source_id, $dest_id);
                echo "Nombre de records modifi&eacute;s dans la table users_comment : " . $nb . "<br />";

                // Met à jour les carres
                $this->loadModel("Users_list_carre");
                $nb = $this->Users_list_carre->replaceIdTome($source_id, $dest_id);
                echo "Nombre de records modifi&eacute;s dans la table users_list_carre : " . $nb . "<br />";

                // Met à jour les exclusions
                $this->loadModel("Users_exclusions");
                $nb = $this->Users_exclusions->replaceIdTome($source_id, $dest_id);
                echo "Nombre de records modifi&eacute;es dans la table users_exclusions : " . $nb . "<br />";

                // Fusionne les albums (restera ensuite à fusionner les éditions redondantes, cf. mergeeditions)
                $this->loadModel("Edition");
                $nb = $this->Edition->replaceIdTome($source_id, $dest_id);
                echo "Nombre de records modifi&eacute;es dans la table bd_edition : " . $nb . "<br />";

                $this->loadModel("Tome");
                $this->Tome->add_dataPaste("ID_TOME", $source_id);
                $this->Tome->delete();
                echo 'R&eacute;f&eacute;rence(s) &agrave; l\'album supprim&eacute;e(s) dans la table bd_tome<br />';

                echo GetMetaTag(4, "Fusion effectu&eacute;e.", (BDO_URL . "admin/editalbum?alb_id=" . $dest_id));
            } else {
                // Demande de confirmation
                echo 'Etes-vous s&ucirc;r de vouloir fusionner les albums n ' . $source_id . ' et ' . $dest_id . '? <a href="' . BDO_URL . 'admin/mergealbums?act=merge&conf=ok&source_id=' . $source_id . '&dest_id=' . $dest_id . '">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';
                exit();
            }
        }

// AFFICHER UN ALBUM
        elseif ($act == "") {

            // REMPLISSAGE PARTIE GAUCHE
            if ((!is_null($source_id)) & ($source_id != '')) {
                $this->loadModel("Tome");
                $this->Tome->add_dataPaste("ID_TOME", $source_id);
                $this->Tome->load();
                // récupère le nombre d'utilisateurs
                $nb_comments1 = $this->Tome->NB_NOTE_TOME;

                // Determine l'URL image
                if (is_null($this->Tome->IMG_COUV) | ($this->Tome->IMG_COUV == '')) {
                    $url_image1 = BDO_URL_COUV . "default.png";
                } else {
                    $url_image1 = BDO_URL_COUV . $this->Tome->IMG_COUV;
                }
                $this->view->set_var(array(
                    "TOMEID1" => $this->Tome->ID_TOME,
                    "TITRE1" => $this->Tome->TITRE_TOME,
                    "IDSERIE1" => $this->Tome->ID_SERIE,
                    "SERIE1" => $this->Tome->NOM_SERIE,
                    "TOME1" => $this->Tome->NUM_TOME,
                    "IDGENRE1" => $this->Tome->ID_GENRE,
                    "GENRE1" => $this->Tome->NOM_GENRE,
                    "IDSCENAR1" => $this->Tome->ID_SCENAR,
                    "SCENAR1" => $this->Tome->scpseudo,
                    "IDSCENARALT1" => $this->Tome->ID_SCENAR_ALT,
                    "SCENARALT1" => $this->Tome->scapseudo,
                    "IDEDIT1" => $this->Tome->ID_EDITEUR,
                    "EDIT1" => $this->Tome->NOM_EDITEUR,
                    "IDDESS1" => $this->Tome->ID_DESSIN,
                    "DESS1" => $this->Tome->depseudo,
                    "IDDESSALT1" => $this->Tome->ID_DESSIN_ALT,
                    "DESSALT1" => $this->Tome->deapseudo,
                    "IDCOLOR1" => $this->Tome->ID_COLOR,
                    "COLOR1" => $this->Tome->copseudo,
                    "IDCOLORALT1" => $this->Tome->ID_COLOR_ALT,
                    "COLORALT1" => $this->Tome->coapseudo,
                    "IDCOLL1" => $this->Tome->ID_COLLECTION,
                    "COLL1" => $this->Tome->NOM_COLLECTION,
                    "DTEPAR1" => $this->Tome->DTE_PARUTION,
                    "URLIMAGE1" => $url_image1,
                    "HISTOIRE1" => $this->Tome->HISTOIRE_TOME,
                    "SOURCEID" => $this->Tome->ID_TOME,
                    "NBUSERS1" => $this->Tome->NBR_USER_ID_TOME,
                    "NBCOMMENT1" => $nb_comments1
                ));
            } else {
                $this->view->set_var(array(
                    "NBUSERS1" => "0",
                    "NBCOMMENT1" => "0"
                ));
            }

            //REMPLISSAGE DE LA PARTIE DROITE
            if ((!is_null($dest_id)) & ($dest_id != '')) {
                $this->loadModel("Tome");
                $this->Tome->add_dataPaste("ID_TOME", $dest_id);
                $this->Tome->load();


                // Determine l'URL image
                if (is_null($this->Tome->IMG_COUV) | ($this->Tome->IMG_COUV == '')) {
                    $url_image2 = BDO_URL_COUV . "default.png";
                } else {
                    $url_image2 = BDO_URL_COUV . $this->Tome->IMG_COUV;
                }
                $this->view->set_var(array(
                    "TOMEID2" => $this->Tome->ID_TOME,
                    "TITRE2" => $this->Tome->TITRE_TOME,
                    "IDSERIE2" => $this->Tome->ID_SERIE,
                    "SERIE2" => $this->Tome->NOM_SERIE,
                    "TOME2" => $this->Tome->NUM_TOME,
                    "IDGENRE2" => $this->Tome->ID_GENRE,
                    "GENRE2" => $this->Tome->NOM_GENRE,
                    "IDSCENAR2" => $this->Tome->ID_SCENAR,
                    "SCENAR2" => $this->Tome->scpseudo,
                    "IDSCENARALT2" => $this->Tome->ID_SCENAR_ALT,
                    "SCENARALT2" => $this->Tome->scapseudo,
                    "IDEDIT2" => $this->Tome->ID_EDITEUR,
                    "EDIT2" => $this->Tome->NOM_EDITEUR,
                    "IDDESS2" => $this->Tome->ID_DESSIN,
                    "DESS2" => $this->Tome->depseudo,
                    "IDDESSALT2" => $this->Tome->ID_DESSIN_ALT,
                    "DESSALT2" => $this->Tome->deapseudo,
                    "IDCOLOR2" => $this->Tome->ID_COLOR,
                    "COLOR2" => $this->Tome->copseudo,
                    "IDCOLORALT2" => $this->Tome->ID_COLOR_ALT,
                    "COLORALT2" => $this->Tome->coapseudo,
                    "IDCOLL2" => $this->Tome->ID_COLLECTION,
                    "COLL2" => $this->Tome->NOM_COLLECTION,
                    "DTEPAR2" => $this->Tome->DTE_PARUTION,
                    "URLIMAGE2" => $url_image2,
                    "HISTOIRE2" => $this->Tome->HISTOIRE_TOME,
                    "DESTID" => $this->Tome->ID_TOME,
                    "NBUSERS2" => $this->Tome->NBR_USER_ID_TOME,
                    "NBCOMMENT2" => $this->Tome->NB_NOTE_TOME
                ));
            } else {
                $this->view->set_var(array(
                    "NBUSERS2" => "0",
                    "NBCOMMENT2" => "0"
                ));
            }
            $error = getVal("error", "");
            // Message d'erreur
            if ($error) {
                $this->view->set_var("ERRORMESSAGE", $error_msg[$error]);
            }
            // variables mises à jour dans tous les cas
            $this->view->set_var(array(
                "URLREFRESH" => BDO_URL . "admin/mergealbums",
                "URLECHANGE" => BDO_URL . "admin/mergealbums?source_id=$dest_id&dest_id=$source_id",
                "URLFUSION" => BDO_URL . "admin/mergealbums?act=merge&source_id=$source_id&dest_id=$dest_id"
            ));
            $this->view->render();
        }
    }

    public function mergeEditions() {
        if (!User::minAccesslevel(1)) {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
        $this->view->set_var("PAGETITLE", "Fusion Edition");
        $error_msg[0] = "Edition &agrave; supprimer non d&eacute;finie";
        $error_msg[1] = "Edition &agrave; garder non d&eacute;finie";
        $error_msg[2] = "Edition &agrave; garder et &agrave; supprimer identiques";
        $act = getVal("act", "");
        $dest_id = getValInteger("dest_id");
        $source_id = getValInteger("source_id");
        $conf = getVal("conf");
        $this->loadModel("Edition");
// Fusionne les editions
        if ($act == "merge") {
            // vérifie que source_id et dest_id ont été definis
            if ((is_null($dest_id)) | ($dest_id == "")) {
                header("Location:" . BDO_URL . "admin/mergeeditions?source_id=$source_id&error=1");
            }
            if ((is_null($source_id)) | ($source_id == "")) {
                header("Location:" . BDO_URL . "admin/mergeeditions?dest_id=$dest_id&error=0");
            }
            if ($source_id == $dest_id) {
                header("Location:" . BDO_URL . "admin/mergeeditions?source_id=$source_id&dest_id=$dest_id&error=2");
            }
            if ($conf == "ok") {

                // Récupère les données de l'edition à mettre à jour (de destination)
                $this->Edition->add_dataPaste("ID_EDITION", $source_id);
                $this->Edition->load();
                // Efface les éditions et les couvertures correspondantes
                if ($this->Edition->IMG_COUV != '') {
                    $filename = $this->Edition->IMG_COUV;
                    if (file_exists(BDO_DIR_COUV . $filename)) {
                        @unlink(BDO_DIR_COUV . $filename);
                        echo "Couverture effac&eacute;e pour l'&eacute;dition N" . $this->Edition->ID_EDITION . "<br />";
                    }
                }

                $this->loadModel("Useralbum");
                $this->Useralbum->replaceEditionFromEdition($source_id, $dest_id);
                echo 'R&eacute;f&eacute;rence(s) &agrave; l\'&eacute;dition modifi&eacute;e(s) dans la table users_album<br />';


                // vide la table bd_edition
                $this->Edition->delete();
                echo 'R&eacute;f&eacute;rence(s) &agrave; l\'album supprim&eacute;e(s) dans la table bd_edition<br />';


                $redirection = BDO_URL . "admin/editedition?edition_id=" . $dest_id;
                echo '<META http-equiv="refresh" content="4; URL=' . $redirection . '">Les &eacute;ditions ont &eacute;t&eacute; fusionn&eacute;es.';
            } else {
                // Demande de confirmation
                echo 'Etes-vous s&ucirc;r de vouloir fusionner les &eacute;ditions n' . $source_id . ' et ' . $dest_id . '? <a href="' . BDO_URL . 'admin/mergeeditions?act=merge&conf=ok&source_id=' . $source_id . '&dest_id=' . $dest_id . '">Oui</a> - <a href="javascript:history.go(-1)">Non</a><br />Si l\'&eacute;dition &agrave; supprimer est l\'&eacute;dition par d&eacute;faut, n\'oubliez pas de red&eacute;finir une &eacute;dition par d&eacute;faut pour l\'album en question.';
                exit();
            }
        }

// AFFICHER L'INTERFACE DE FUSION DES EDITIONS
        elseif ($act == "") {


            // REMPLISSAGE PARTIE GAUCHE
            if ((!is_null($source_id)) & ($source_id != '')) {

                $this->Edition->add_dataPaste("ID_EDITION", $source_id);
                $this->Edition->load();
                // r�cup�re le nombre d'utilisateurs
                $nb_users1 = $this->Edition->NBR_USER_ID;

                // Determine l'URL image
                if (!$this->Edition->IMG_COUV) {
                    $url_image1 = BDO_URL_IMAGE . "couv/default.png";
                } else {
                    $url_image1 = BDO_URL_IMAGE . "couv/" . $this->Edition->IMG_COUV;
                }
                $this->view->set_var(array(
                    "EDITIONID1" => $this->Edition->ID_EDITION,
                    "EAN1" => $this->Edition->EAN_EDITION,
                    "ISBN1" => $this->Edition->ISBN_EDITION,
                    "TOMEID1" => $this->Edition->ID_TOME,
                    "TITRE1" => $this->Edition->TITRE_TOME,
                    "IDSERIE1" => $this->Edition->ID_SERIE,
                    "SERIE1" => $this->Edition->NOM_SERIE,
                    "TOME1" => $this->Edition->NUM_TOME,
                    "IDGENRE1" => $this->Edition->ID_GENRE,
                    "GENRE1" => $this->Edition->NOM_GENRE,
                    "IDSCENAR1" => $this->Edition->ID_SCENAR,
                    "SCENAR1" => $this->Edition->scpseudo,
                    "IDSCENARALT1" => $this->Edition->ID_SCENAR_ALT,
                    "SCENARALT1" => $this->Edition->scapseudo,
                    "IDEDIT1" => $this->Edition->ID_EDITEUR,
                    "EDIT1" => $this->Edition->NOM_EDITEUR,
                    "IDDESS1" => $this->Edition->ID_DESSIN,
                    "DESS1" => $this->Edition->depseudo,
                    "IDDESSALT1" => $this->Edition->ID_DESSIN_ALT,
                    "DESSALT1" => $this->Edition->deapseudo,
                    "IDCOLOR1" => $this->Edition->ID_COLOR,
                    "COLOR1" => $this->Edition->copseudo,
                    "IDCOLORALT1" => $this->Edition->ID_COLOR_ALT,
                    "COLORALT1" => $this->Edition->coapseudo,
                    "IDCOLL1" => $this->Edition->ID_COLLECTION,
                    "COLL1" => $this->Edition->NOM_COLLECTION,
                    "DTEPAR1" => $this->Edition->DATE_PARUTION_EDITION,
                    "URLIMAGE1" => $url_image1,
                    "HISTOIRE1" => $this->Edition->HISTOIRE,
                    "DESCRIPTED1" => $this->Edition->COMMENT_EDITION,
                    "SOURCEID" => $this->Edition->ID_EDITION,
                    "NBUSERS1" => $nb_users1,
                ));
            } else {
                $this->view->set_var("NBUSERS1", "0");
            }

            //REMPLISSAGE DE LA PARTIE DROITE
            if ((!is_null($dest_id)) & ($dest_id != '')) {
                // r�cup�re le nombre d'utilisateurs

                $this->Edition->add_dataPaste("ID_EDITION", $dest_id);
                $this->Edition->load();
                $nb_users2 = $this->Edition->NBR_USER_ID;


                // Determine l'URL image
                if (!$this->Edition->IMG_COUV) {
                    $url_image2 = BDO_URL_IMAGE . "couv/default.png";
                } else {
                    $url_image2 = BDO_URL_IMAGE . "couv/" . $this->Edition->IMG_COUV;
                }
                $this->view->set_var(array(
                    "EDITIONID2" => $this->Edition->ID_EDITION,
                    "EAN2" => $this->Edition->EAN_EDITION,
                    "ISBN2" => $this->Edition->ISBN_EDITION,
                    "TOMEID2" => $this->Edition->ID_TOME,
                    "TITRE2" => $this->Edition->TITRE_TOME,
                    "IDSERIE2" => $this->Edition->ID_SERIE,
                    "SERIE2" => $this->Edition->NOM_SERIE,
                    "TOME2" => $this->Edition->NUM_TOME,
                    "IDGENRE2" => $this->Edition->ID_GENRE,
                    "GENRE2" => $this->Edition->NOM_GENRE,
                    "IDSCENAR2" => $this->Edition->ID_SCENAR,
                    "SCENAR2" => $this->Edition->scpseudo,
                    "IDSCENARALT2" => $this->Edition->ID_SCENAR_ALT,
                    "SCENARALT2" => $this->Edition->scapseudo,
                    "IDEDIT2" => $this->Edition->ID_EDITEUR,
                    "EDIT2" => $this->Edition->NOM_EDITEUR,
                    "IDDESS2" => $this->Edition->ID_DESSIN,
                    "DESS2" => $this->Edition->depseudo,
                    "IDDESSALT2" => $this->Edition->ID_DESSIN_ALT,
                    "DESSALT2" => $this->Edition->deapseudo,
                    "IDCOLOR2" => $this->Edition->ID_COLOR,
                    "COLOR2" => $this->Edition->copseudo,
                    "IDCOLORALT2" => $this->Edition->ID_COLOR_ALT,
                    "COLORALT2" => $this->Edition->coapseudo,
                    "IDCOLL2" => $this->Edition->ID_COLLECTION,
                    "COLL2" => $this->Edition->NOM_COLLECTION,
                    "DTEPAR2" => $this->Edition->DATE_PARUTION_EDITION,
                    "URLIMAGE2" => $url_image2,
                    "HISTOIRE2" => $this->Edition->HISTOIRE,
                    "DESCRIPTED2" => $this->Edition->COMMENT_EDITION,
                    "DESTID" => $this->Edition->ID_EDITION,
                    "NBUSERS2" => $nb_users2,
                ));
            } else {
                $this->view->set_var("NBUSERS2", "0");
            }
            // Message d'erreur
            if (!is_null($error)) {
                $this->view->set_var("ERRORMESSAGE", $error_msg[$error]);
            }
            // variables mises � jour dans tous les cas
            $this->view->set_var(array(
                "URLEDITION1" => BDO_URL . "admin/editedition?edition_id=" . $source_id,
                "URLEDITION2" => BDO_URL . "admin/editedition?edition_id=" . $dest_id,
                "URLREFRESH" => BDO_URL . "admin/mergeeditions",
                "URLECHANGE" => BDO_URL . "admin/mergeeditions?source_id=$dest_id&dest_id=$source_id",
                "URLFUSION" => BDO_URL . "admin/mergeeditions?act=merge&source_id=$source_id&dest_id=$dest_id"
            ));
            // assigne la barre de login
            $this->view->render();
        }
    }

    public function editAuteur() {
        if (User::minAccesslevel(1)) {
            $act = getVal("act");
            $conf = getVal("conf");
            $auteur_id = getVal("auteur_id");
            $this->view->layout = "iframe";
            $this->loadModel("Auteur");
            // Mettre à jour les informations
            if ($act == "update") {
                $nom = postVal('txtNomAuteur');
                $prenom = postVal('txtPrenomAuteur');
                $pseudo = (postVal('txtPseudoAuteur') == '' ? postVal('txtNomAuteur') . ", " .
                                postVal('txtPrenomAuteur') : postVal('txtPseudoAuteur') );
                $auteur_id = postVal("txtIdAuteur");
                $this->Auteur->set_dataPaste(array("ID_AUTEUR" => $auteur_id));
                $this->Auteur->load();
                //var_dump($_FILES['txtFileLoc']);
                if ($_FILES['txtFileLoc']['size'] > 0) {// un fichier à uploader
                    $img_aut = imgAutFromForm($auteur_id);
                } else if (preg_match('/^(http:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/', postVal('txtFileURL'), $url_ary)) { // un fichier à télécharger
                    $img_aut = imgAutFromUrl($url_ary, $auteur_id);
                } else {
                    $img_aut = '';
                }
                if (issetNotEmpty($this->Auteur->IMG_AUT) and $img_aut == "")
                    $img_aut = $this->Auteur->IMG_AUT;
                $this->Auteur->set_dataPaste(array(
                    "ID_AUTEUR" => postVal("txtIdAuteur"),
                    "PRENOM" => $prenom,
                    "NOM" => $nom,
                    "PSEUDO" => $pseudo,
                    "FLG_SCENAR" => postVal('chkScen') == 'checked' ? 1 : 0,
                    "FLG_DESSIN" => postVal('chkDess') == 'checked' ? 1 : 0,
                    "FLG_COLOR" => (postVal('chkColor') == 'checked' ? 1 : 0),
                    "COMMENT" => postVal('txtCommentaire'),
                    "DTE_NAIS" => postVal('txtDateNaiss'),
                    "DTE_DECES" => postVal('txtDateDeces'),
                    "NATIONALITE" => postVal('txtNation'),
                    "VALIDATOR" => $_SESSION["userConnect"]->user_id,
                    "VALID_DTE" => date('d/m/Y H:i:s'),
                    "IMG_AUT" => $img_aut
                ));
                $this->Auteur->update();
                if (issetNotEmpty($this->Auteur->error)) {
                    var_dump($this->Auteur->error);
                    exit();
                }
                echo GetMetaTag(2, "Mise &agrave; jour effectu&eacute;e", (BDO_URL . "admin/editauteur?auteur_id=" . postVal("txtIdAuteur")));
            }

// effacement d'un auteur
            elseif ($act == "delete") {
                if ($conf == "ok") {
                    if (User::minAccesslevel(1)) {//Revérifie que c'est bien l'administrateur qui travaille
                        $this->Auteur->set_dataPaste(array("ID_AUTEUR" => $auteur_id));
                        $this->Auteur->delete();
                        echo 'L\'auteur a &eacute;t&eacute; effac&eacute;e de la base.';
                        exit();
                    }
                } else {// Affiche la demande de confirmation
                    echo 'Etes-vous s&ucirc;r de vouloir effacer l\'auteur n. ' . $auteur_id . ' ? <a href="' . BDO_URL . 'admin/editauteur?act=delete&conf=ok&auteur_id=' . $auteur_id . '">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';
                    exit();
                }
            }
// AFFICHE UN FORMULAIRE VIDE
            elseif ($act == "new") {

                $this->view->set_var(array
                    ("NBALBUMS" => "0",
                    "URLDELETE" => "javascript:alert('D&eacute;sactiv&eacute;');",
                    "URLFUSION" => "javascript:alert('D&eacute;sactiv&eacute;');",
                    "ACTIONNAME" => "Enregistrer",
                    "URLACTION" => BDO_URL . "admin/editauteur?act=append"
                ));

                $this->view->render();
            }

// INSERE UN NOUVEL ALBUM DANS LA BASE
            elseif ($act == "append") {
                $nom = postVal('txtNomAuteur');
                $prenom = postVal('txtPrenomAuteur');
                $pseudo = (postVal('txtPseudoAuteur') == '' ? postVal('txtNomAuteur') . ", " .
                                postVal('txtPrenomAuteur') . "'" : "'" . postVal('txtPseudoAuteur') );

                $this->Auteur->set_dataPaste(array(
                    "PRENOM" => $prenom,
                    "NOM" => $nom,
                    "FLG_SCENAR" => postVal('chkScen') == 'checked' ? 1 : 0,
                    "FLG_DESSIN" => postVal('chkDess') == 'checked' ? 1 : 0,
                    "FLG_COLOR" => (postVal('chkColor') == 'checked' ? 1 : 0),
                    "COMMENT" => postVal('txtCommentaire'),
                    "DTE_NAIS" => postVal('txtDateNaiss'),
                    "DTE_DECES" => postVal('txtDateDeces'),
                    "NATIONALITE" => postVal('txtNation'),
                    "VALIDATOR" => $_SESSION["userConnect"]->user_id,
                    "VALID_DTE" => date('d/m/Y H:i:s')
                ));
                $this->Auteur->update();
                $lid = $this->Auteur->ID_AUTEUR;
                //echo GetMetaTag(2, "L'auteur a &eacute;t&eacute; ajout&eacute;", (BDO_URL . "admin/editauteur?auteur_id=" . $lid));
            }

// AFFICHER UN AUTEUR
            elseif ($act == "") {

                // Compte les albums pour lesquels les auteurs ont travaillé
                $this->Auteur->set_dataPaste(array("ID_AUTEUR" => $auteur_id));
                $this->Auteur->load();
                $nb_auteur = intval($this->Auteur->getNbAlbumForAuteur($auteur_id));
                
                $wikidata = new Wikidata();
                if ($this->Auteur->PSEUDO == $this->Auteur->NOM.", ".$this->Auteur->PRENOM) {
                    $search_wiki = $this->Auteur->PRENOM." ".$this->Auteur->NOM;
                } else {
                    $search_wiki = $this->Auteur->PSEUDO;
                }
                $result = $wikidata->search($search_wiki);
                if(!$result->isEmpty()) {
                       $singleResult = $result->first();
                       $entityId = $singleResult->getEntityId();
                       $entities = $wikidata->entities($entityId, 'fr');
                       $entity = $entities->first();
                       $wikilabel = $entity->getLabel(); // Steve Jobs
                       $wikidescription = $entity->getDescription('fr'); // US-amerikanischer Unternehmer, Mitbegründer von Apple Computer
                       $wikiimage = $entity->getPropertyValues('P18');
                       
                       $wikiwebsite = $entity->getPropertyValues('P856');
                       $wikibirth = $entity->getPropertyValues('P569');
                       $wikideath = $entity->getPropertyValues('P570');
                       $wikinationality = $entity->getPropertyValues('P27');
                       
                } else {
                    $wikilabel = null;
                    $wikidescription = null;
                    $wikiimage =  null;
                    $wikiwebsite = null;
                    $wikibirth = null;
                    $wikideath = null;
                    $wikinationality =  null;
                    $entityId = null;
                }
                $this->view->set_var(array
                    ("IDAUTEUR" => $this->Auteur->ID_AUTEUR,
                    "PSEUDO" => stripslashes($this->Auteur->PSEUDO),
                    "NOM" => (stripslashes($this->Auteur->NOM)),
                    "PRENOM" => (stripslashes($this->Auteur->PRENOM)),
                    "ISSCENAR" => $this->Auteur->FLG_SCENAR == 1 ? "checked" : '',
                    "ISDESSIN" => $this->Auteur->FLG_DESSIN == 1 ? "checked" : '',
                    "ISCOLOR" => $this->Auteur->FLG_COLOR == 1 ? "checked" : '',
                    "COMMENT" => (stripslashes(if_null_quote($this->Auteur->COMMENT))),
                    "DTNAIS" => $this->Auteur->DTE_NAIS,
                    "DTDECES" => $this->Auteur->DTE_DECES,
                    "DTNATION" => $this->Auteur->NATIONALITE,
                    "NBALBUMS" => $nb_auteur,
                    "URLDELETE" => BDO_URL . "admin/editauteur?act=delete&auteur_id=" . $this->Auteur->ID_AUTEUR,
                    "URLFUSION" => BDO_URL . "admin/mergeauteurs?source_id=" . $this->Auteur->ID_AUTEUR,
                    "ACTIONNAME" => "Valider les Modifications",
                    "URLACTION" => BDO_URL . "admin/editauteur?act=update",
                    "IMG_AUT" => ($this->Auteur->IMG_AUT ? $this->Auteur->IMG_AUT : "default_auteur.png"),
                    "WIKISEARCH" => $search_wiki,
                    "WIKILABEL" => $wikilabel,
                    "WIKIDESCRIPTION" => $wikidescription,
                    "WIKIIMAGE" => $wikiimage,
                    "WIKIBIRTH" => $wikibirth,
                    "WIKIWEBSITE" => $wikiwebsite,
                    "WIKIDEATH" => $wikideath,
                    "WIKINATIONALITY" => $wikinationality,
                    "WIKIENTITY" => $entityId));
                $this->view->render();
            }
        } else {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
    }

    public function editSerie() {
        if (User::minAccesslevel(1)) {

// Tableau pour les choix d'options du status des series
            $opt_status[0][0] = 0;
            $opt_status[0][1] = 'Finie';
            $opt_status[1][0] = 1;
            $opt_status[1][1] = 'En cours';
            $opt_status[2][0] = 2;
            $opt_status[2][1] = 'One Shot';
            $opt_status[3][0] = 3;
            $opt_status[3][1] = 'Interrompue/Abandonn&eacute;e';

            $act = getVal("act");
            $conf = getVal("conf");
            $idserie = getVal("idserie");
            $this->loadModel("Serie");
            $this->loadModel("Tome");
// Mettre à jour les informations
            if ($act == "update") {
                $this->Serie->set_dataPaste(array(
                    "ID_SERIE" => postVal("txtSerieId"),
                    "NOM" => postVal("txtSerie"),
                    "ID_GENRE" => postVal('txtGenreId'),
                    "FLG_FINI" => postVal('chkFini'),
                    "NB_TOME" => postVal('txtNbTome'),
                    "TRI" => postVal('txtTri'),
                    "HISTOIRE" => postVal("txtSerieHist")
                ));
                $this->Serie->update();
                if (issetNotEmpty($this->Serie->error)) {
                    var_dump($this->Serie->error);
                    exit();
                }
                $this->Tome->updateGenreForSerie(postValInteger("txtSerieId"), postValInteger('txtGenreId'));
                
                // mise à jour des liens entre séries
                $this->loadModel("Groupeserie");
                $this->Groupeserie->deleteLiens(postVal("txtSerieId"));
                $listSerieLiee = postVal("idSerie",[]);
                if (count($listSerieLiee) > 0 ) {
                    $this->Groupeserie->addLiens(postVal("txtSerieId"),$listSerieLiee);
                }

                echo '<META http-equiv="refresh" content="1; URL=editserie?serie_id=' . postVal("txtSerieId") . '">' . "Mise &agrave; jour effectu&eacute;e";
            }

// EFFACEMENT D'UN ALBUM
            elseif ($act == "delete") {
                if ($conf == "ok") {
                    $this->Tome->load("c", " WHERE BD_TOME.ID_SERIE = " . $idserie);

                    $nb_tome = $this->Tome->dbSelect->nbLineResult;
                    if ($nb_tome > 0)
                        exit('La s&eacute;rie contient encore ' . $nb_tome . ' album(s). Suppression interdite.');
                    $this->Serie->set_dataPaste(array("ID_SERIE" => $idserie));
                    $this->Serie->delete();
                    if (issetNotEmpty($this->Serie->error)) {
                        var_dump($this->Serie->error);
                        exit();
                    }
                    echo 'La serie a &eacute;t&eacute; effac&eacute;e de la base.';
                    exit();
                } else {// Affiche la demande de confirmation
                    echo 'Etes-vous sur de vouloir effacer la s&eacute;rie n. ' . $idserie . ' ? <a href="' . BDO_URL . 'admin/editserie?act=delete&conf=ok&idserie=' . $idserie . '">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';

                    exit();
                }
            }
// AFFICHE UN FORMULAIRE VIDE
            elseif ($act == "new") {

                $this->view->set_var(array(
                    "NBALBUMS" => "0",
                    "NBAUTEURS" => "0",
                    "NBNOTES" => "0",
                    "NBCOMMENTS" => "0",
                    "STYLE_NOTATION" => "",
                    "OPTSTATUS" => GetOptionValue($opt_status, 1),
                    "URLDELETE" => "javascript:alert('Déeactue;sactiv&eactue;');",
                    "ACTIONNAME" => "Enregistrer",
                    "URLEDITGENRE" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
                    "URLACTION" => BDO_URL . "admin/editserie?act=append",
                    "IDSERIE" => "",
                    "SERIE" => "",
                    "TRI" => "",
                    "IDGENRE" => "",
                    "GENRE" => "",
                    "NOTE" => "",
                    "WARNING_NOTE" => "",
                    "HISTOIRE_SERIE" => "",
                    
                    "NBTOME" => "",
                    
                    
                    "URLMASSDETAIL" => "javascript:alert('Déeactue;sactiv&eactue;');",
                    "URLMASSUPDATE" => "javascript:alert('Déeactue;sactiv&eactue;');",
                    "URLMASSRENAME" => "javascript:alert('Déeactue;sactiv&eactue;');",
                    "URLMASSCOUV" => "javascript:alert('Déeactue;sactiv&eactue;');",
                    "URLAJOUTALB" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
                    "dbs_serie_liee" => array()
                ));
                // assigne la barre de login
                $this->view->layout = "iframe";
                $this->view->render();
            }

// INSERE UNE NOUVELLE SERIE DANS LA BASE
            elseif ($act == "append") {
                if (postVal('txtTri') == '') {
                    $tri = substr(trim(clean_article(stripslashes(postVal('txtSerie')))), 0, 3);
                } else {
                    $tri = postVal('txtTri');
                }
                $this->Serie->set_dataPaste(array(
                    "NOM" => postVal("txtSerie"),
                    "ID_GENRE" => postVal('txtGenreId'),
                    "FLG_FINI" => postVal('chkFini'),
                    "NB_TOME" => postVal('txtNbTome'),
                    "TRI" => $tri,
                    "HISTOIRE" => postVal("txtSerieHist")
                ));
                $this->Serie->update();
                $lid = $this->Serie->ID_SERIE;
                echo GetMetaTag(2, "La s&eacute;rie a &eacute;t&eacute; ajout&eacute;e", (BDO_URL . "admin/editserie?serie_id=" . $lid));
            }

// AFFICHER UNE SERIE
            elseif ($act == "") {
                $champ_form_style = 'champ_form_desactive';
                $serie_id = getVal("serie_id");
                // Selectionne les albums présents dans la série
                $dbs_tome = $this->Tome->load("c", " WHERE bd_tome.ID_SERIE=" . $serie_id);

                $nb_tome = $this->Tome->dbSelect->nbLineResult;


                // Selectionne les auteurs ayant travaillé pour la série
                $this->loadModel("Auteur");
                $dbs_auteur = $this->Auteur->getAuteurForSerie($serie_id);
                $nb_auteur = count($dbs_auteur);


                //récupère les données dans la base
                $this->Serie->set_dataPaste(array("ID_SERIE" => $serie_id));
                $this->Serie->load();

                //affichage du message de notification de note/commentaire de membre sur la serie
                $warning_note = "";
                if ($this->Serie->NB_NOTE_SERIE == '0') {
                    $warning_note = '<div>Aucun membre n\'a not&eacute;/comment&eacute; la s&eacute;rie.</div>';
                } else {
                    $warning_note = '<div class="b">Des membres ont not&eacute;/comment&eacute; la s&eacute;rie.</div>';
                }
                $this->loadModel("Groupeserie");
                $listSerieLiee = $this->Groupeserie->getSerieLiee( $this->Serie->ID_SERIE);
                
                $this->view->set_var(array(
                    "IDSERIE" => $this->Serie->ID_SERIE,
                    "SERIE" => stripslashes($this->Serie->NOM_SERIE),
                    "TRI" => $this->Serie->TRI_SERIE,
                    "IDGENRE" => $this->Serie->ID_GENRE,
                    "GENRE" => $this->Serie->NOM_GENRE,
                    "NOTE" => $this->Serie->NB_NOTE_SERIE,
                    "WARNING_NOTE" => $warning_note,
                    "HISTOIRE_SERIE" => $this->Serie->HISTOIRE_SERIE,
                    "OPTSTATUS" => GetOptionValue($opt_status, $this->Serie->FLG_FINI_SERIE),
                    "NBTOME" => $this->Serie->NB_TOME_FINAL,
                    "NBALBUMS" => $this->Serie->NB_ALBUM,
                    "NBAUTEURS" => $nb_auteur,
                    "URLDELETE" => BDO_URL . "admin/editserie?act=delete&idserie=" . $this->Serie->ID_SERIE,
                    "ACTIONNAME" => "Valider les Modifications",
                    "URLEDITGENRE" => BDO_URL . "admin/editgenre?genre_id=" . $this->Serie->ID_GENRE,
                    "URLMASSDETAIL" => BDO_URL . "admin/mu_detail.php?serie=" . $this->Serie->ID_SERIE,
                    "URLMASSUPDATE" => BDO_URL . "admin/mu_serie.php?serie=" . $this->Serie->ID_SERIE,
                    "URLMASSRENAME" => BDO_URL . "admin/murenameserie?serie=" . $this->Serie->ID_SERIE,
                    "URLMASSCOUV" => BDO_URL . "admin/mucouvserie?serie=" . $this->Serie->ID_SERIE,
                    "URLAJOUTALB" => BDO_URL . "admin/editalbum?act=newfserie&id_serie=" . $this->Serie->ID_SERIE,
                    "URLACTION" => BDO_URL . "admin/editserie?act=update",
                    "dbs_tome" => $dbs_tome,
                    "dbs_auteur" => $dbs_auteur,
                    "dbs_serie_liee" => $listSerieLiee
                ));

                $this->view->layout = "iframe";
                $this->view->render();
            }
        } else {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
    }

    public function editGenre() {
        if (User::minAccesslevel(1)) {
            $this->loadModel("Genre");
            $act = getVal("act", "");
            $genre_id = getValInteger("genre_id");
            if ($act == "update") {
                $this->Genre->set_dataPaste(array("ID_GENRE" => postValInteger("txtIdGenre")));
                $this->Genre->load();
                $this->Genre->set_dataPaste(array("LIBELLE" => postVal('txtGenre'), "ORIGINE" => postVal('origine')));
                $this->Genre->update();
                if (issetNotEmpty($this->Genre->error)) {
                    /*
                     * Erreur : on envoit les infos
                     */
                    var_dump($this->Genre->error);
                    exit;
                }

                $this->view->addAlertPage("Mise &agrave; jour effectu&eacute;e");
                $this->view->addPhtmlFile('alert', 'BODY');
                $this->view->set_var("BODYONLOAD", "history.go(-1);");
                $this->view->layout = "iframe";
                $this->view->render();
            }
            // EFFACEMENT D'UN GENRE
            elseif ($act == "delete") {
                $conf = getVal("conf");
                if ($conf == "ok") {


                    $this->Genre->set_dataPaste(array("ID_GENRE" => $genre_id));
                    $this->Genre->load();
                    $this->Genre->delete();
                    $redirection = BDO_URL . "admin";
                    echo 'Le genre a &eacute;t&eacute; effac&eacute; de la base.<script>window.close();</script>';
                    exit();
                } else {// Affiche la demande de confirmation
                    echo 'Etes-vous s&ucirc;r de vouloir effacer le genre n. ' . $genre_id . ' ? <a href="' . BDO_URL . 'admin/editgenre?act=delete&conf=ok&genre_id=' . $genre_id . '">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';
                    exit();
                }
            }
            // AFFICHE UN FORMULAIRE VIDE
            elseif ($act == "new") {
                // Creation d'un nouveau Template

                $this->view->set_var(array(
                    "NBSERIES" => "0",
                    "URLDELETE" => "javascript:alert('D&eacute;sactiv&eacute;');",
                    "URLFUSION" => "javascript:alert('D&eacute;sactiv&eacute;');",
                    "ACTIONNAME" => "Enregistrer",
                    "URLACTION" => BDO_URL . "admin/editgenre?act=append",
                    "IDGENRE" => "",
                    "GENRE" => "",
                    "ORIGINE" => ""
                ));
                $this->view->layout = "iframe";
                $this->view->render();
            }

// INSERE UN NOUVEAU GENRE DANS LA BASE
            elseif ($act == "append") {
                $this->Genre->set_dataPaste(array("LIBELLE" => postVal('txtGenre'), "ORIGINE" => postVal('origine')));
                $this->Genre->update();
                $lid = $this->Genre->ID_GENRE;
                echo GetMetaTag(2, "Le nouveau genre a &eacute;t&eacute; ajout&eacute;", (BDO_URL . "admin/editgenre?genre_id=" . $lid));
            }

// AFFICHER UN GENRE
            elseif ($act == "") {

                // Compte les albums pour lesquels les auteurs ont travaillé
                $genre_id = getValInteger("genre_id");
                $nb_serie = $this->Genre->getNbSerieForGenre($genre_id);

                //récupère les données utilisateur dans la base de données
                $this->Genre->set_dataPaste(array("ID_GENRE" => $genre_id));
                $this->Genre->load();
                $this->view->set_var(array(
                    "IDGENRE" => $this->Genre->ID_GENRE,
                    "GENRE" => stripslashes($this->Genre->LIBELLE),
                    "ORIGINE" => $this->Genre->ORIGINE,
                    "NBSERIES" => $nb_serie,
                    "URLDELETE" => BDO_URL . "admin/editgenre?act=delete&genre_id=" . $genre_id,
                    "URLFUSION" => BDO_URL . "admin/mergegenre?source_id=" . $genre_id,
                    "ACTIONNAME" => "Valider les Modifications",
                    "URLACTION" => BDO_URL . "admin/editgenre?act=update"
                ));
                $this->view->layout = "iframe";
                $this->view->render();
            }
        } else {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
    }

    public function editEditeur() {
        if (User::minAccesslevel(1)) {
            $this->loadModel("Editeur");
            $act = getVal("act", "");
            $editeur_id = getValInteger("editeur_id");
            // Mettre à jour les informations
            if ($act == "update") {
                $this->Editeur->set_dataPaste(array(
                    "ID_EDITEUR" => postValInteger("txtIdEditeur"),
                    "NOM" => postVal('txtNomEditeur'),
                    "URL_SITE" => postVal('txtUrlSite')
                ));
                $this->Editeur->update();
                if (notIssetOrEmpty($this->Editeur->error)) {
                    var_dump($this->Editeur->error);
                    exit;
                }
                $this->view->addAlertPage("Mise &agrave; jour effectu&eacute;e");
                $this->view->addPhtmlFile('alert', 'BODY');
                //$this->view->set_var("BODYONLOAD", "history.go(-1);");
                $this->view->layout = "iframe";
                $this->view->render();
            }

// EFFACEMENT D'UN ALBUM
            elseif ($act == "delete") {
                $conf = getVal("conf", "");
                if ($conf == "ok") {
                    $this->Editeur->set_dataPaste(array("ID_EDITEUR" => $editeur_id));
                    $this->Editeur->load();
                    $this->Editeur->delete();

                    echo 'L\'&eacutediteur a &eacutet&eacute effac&eacute de la base.';
                    exit();
                } else {// Affiche la demande de confirmation
                    echo 'Etes-vous sur de vouloir effacer l\'&eacutediteur n. ' . $editeur_id . ' ? <a href="' . BDO_URL . 'admin/editediteur?act=delete&conf=ok&editeur_id=' . $editeur_id . '">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';
                    exit();
                }
            }
// AFFICHE UN FORMULAIRE VIDE
            elseif ($act == "new") {

                $this->view->set_var(array
                    ("NBCOLLEC" => "0",
                    "URLDELETE" => "javascript:alert('D&eacutesactiv&eacute');",
                    "URLADDCOLLEC" => "javascript:alert('D&eacutesactiv&eacute');",
                    "URLFUSION" => "javascript:alert('D&eacutesactiv&eacute');",
                    "ACTIONNAME" => "Enregistrer",
                    "URLACTION" => BDO_URL . "admin/editediteur?act=append",
                     "ID_EDITEUR" => "",
                    "NOM" => "",
                    "URLWEBSITE" => ""
                ));
                $this->view->layout = "iframe";
                $this->view->render();
            }
// INSERE UN NOUVEL EDITEUR DANS LA BASE
            elseif ($act == "append") {
                $this->Editeur->set_dataPaste(array(
                    "NOM" => postVal("txtNomEditeur"),
                    "URL_SITE" => postVal("txtUrlSite")
                ));
                $this->Editeur->update();
                $lid = $this->Editeur->ID_EDITEUR;

                // on ajpute la collection par défaut
                // Insère un collection <N/A> pour cet éditeur
                $this->loadModel("Collection");
                $this->Collection->set_dataPaste(array(
                    "ID_EDITEUR" => $lid,
                    "NOM" => "<N/A>"
                ));
                $this->Collection->update();

                echo GetMetaTag(2, "L'&eacute;diteur a &eacute;t&eacute; ajout&eacute;", (BDO_URL . "admin/editediteur?editeur_id=" . $lid));
            }

// AFFICHER UN EDITEUR
            elseif ($act == "") {

                $this->loadModel("Collection");
                $dbs_collection = $this->Collection->load("c", " WHERE bd_editeur.id_editeur=" . $editeur_id);


                $nb_collec = $this->Collection->dbSelect->nbLineResult;

                //récupère les données editeur dans la base de données
                $this->Editeur->set_dataPaste(array("ID_EDITEUR" => $editeur_id));
                $this->Editeur->load();
                $this->view->set_var(array
                    ("IDEDITEUR" => $this->Editeur->ID_EDITEUR,
                    "NOM" => $this->Editeur->NOM,
                    "URLWEBSITE" => $this->Editeur->URL_SITE,
                    "NBCOLLEC" => $nb_collec,
                    "URLDELETE" => BDO_URL . "admin/editediteur?act=delete&editeur_id=" . $editeur_id,
                    "URLFUSION" => BDO_URL . "admin/mergeediteurs?source_id=" . $editeur_id,
                    "URLADDCOLLEC" => BDO_URL . "admin/editcollection?act=new&editeur_id=" . $editeur_id,
                    "ACTIONNAME" => "Valider les Modifications",
                    "dbs_collection" => $dbs_collection,
                    "URLACTION" => BDO_URL . "admin/editediteur?act=update"));
                $this->view->layout = "iframe";
                $this->view->render();
            }
        } else {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
    }

    public function mergeEditeurs() {
        if (!User::minAccesslevel(1))
            die("Vous n'avez pas accès à cette page.");


        $error_msg[0] = "Editeur à supprimer non défini";
        $error_msg[1] = "Editeur à garder non défini";
        $error_msg[2] = "Editeur à garder et album à supprimer identiques";
        $act = getVal("act");
        $conf = getVal("conf");
        $dest_id = getValInteger("dest_id", 0);
        $source_id = getValInteger("source_id", 0);
        $this->loadModel("Editeur");
        if ($act == "merge") {
            // vérifie que source_id et dest_id ont été defini
            if ($dest_id == 0) {
                header("Location:" . BDO_URL . "admin/mergeediteurs?source_id=$source_id&error=1");
            }
            if ($source_id == 0) {
                header("Location:" . BDO_URL . "admin/mergeediteurs?dest_id=$dest_id&error=0");
            }

            if ($source_id == $dest_id) {
                header("Location:" . BDO_URL . "admin/mergeediteurs.php?source_id=$source_id&dest_id=$dest_id&error=2");
            }

            if ($conf == "ok") {

                $this->loadModel("Edition");
                $this->loadModel("Collection");
                // Met � jour l'information contenue dans la base de donn�es
                $nb = $this->Edition->replaceIdEditeur($source_id, $dest_id);
                echo "Nombre de records modifi&eacute;es dans la table bd_edition : " . $nb . "<br />";

                // Met � jour la table collection
                $nb = $this->Collection->replaceIdEditeur($source_id, $dest_id);
                echo "Nombre de records modifi&eacute;es dans la table bd_collection : " . $nb . "<br />";

                // Supprime l'ancien editeur
                $this->Editeur->add_dataPaste("ID_EDITEUR", $source_id);
                $this->Editeur->delete();
                echo "Nombre de records modifi&eacute;es dans la table bd_editeur : " . 1 . "<br />";

                $redirection = BDO_URL . "admin";
                echo '<META http-equiv="refresh" content="4; URL=' . $redirection . '">Les &eacute;diteurs ont &eacute;t&eacute; fusionn&eacute;s.';
            } else {
                // Demande de confirmation

                echo 'Etes-vous sur de vouloir fusionner les editeurs n' . $source_id . ' et ' . $dest_id . '? <a href="' . BDO_URL . 'admin/mergeediteurs?act=merge&conf=ok&source_id=' . $source_id . '&dest_id=' . $dest_id . '">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';
                exit();
            }
        }



// AFFICHER
        elseif ($act == "") {
            $this->loadModel("User");

            // REMPLISSAGE PARTIE GAUCHE
            if ((!is_null($source_id)) & ($source_id != '')) {
                // r�cup�re le nombre d'utilisateurs
                $nb_users1 = $this->User->countUserBy("editeur", $source_id);

                // r�cup�re les donn�es principales
                $this->Editeur->add_dataPaste("ID_EDITEUR", intval($source_id));
                $this->Editeur->load();
                $this->view->set_var(array
                    ("EDITEURID1" => $this->Editeur->ID_EDITEUR,
                    "URLEDITEDIT1" => BDO_URL . "admin/adminediteurs?editeur_id=" . $this->Editeur->ID_EDITEUR,
                    "EDITEUR1" => $this->Editeur->NOM,
                    "URLSITE1" => $this->Editeur->URL_SITE,
                    "SOURCEID" => $this->Editeur->ID_EDITEUR,
                    "NBUSERS1" => $nb_users1
                ));
            } else {
                $t->set_var(array
                    ("NBUSERS1" => "0",
                    "URLEDITEDIT1" => "javascript:alert('D&eacute;sactiv&eacute;')"
                ));
            }

            //REMPLISSAGE DE LA PARTIE DROITE
            if ($dest_id) {
                // r�cup�re le nombre d'utilisateurs
                $nb_users2 = $this->User->countUserBy("editeur", $dest_id);

                // r�cup�re les donn�es principales
                $this->Editeur->add_dataPaste("ID_EDITEUR", intval($dest_id));
                $this->Editeur->load();

                $this->view->set_var(array
                    ("EDITEURID2" => $this->Editeur->ID_EDITEUR,
                    "URLEDITEDIT2" => BDO_URL . "admin/adminediteurs?editeur_id=" . $this->Editeur->ID_EDITEUR,
                    "EDITEUR2" => $this->Editeur->NOM,
                    "URLSITE2" => $this->Editeur->URL_SITE,
                    "DESTID" => $this->Editeur->ID_EDITEUR,
                    "NBUSERS2" => $nb_users2
                ));
            } else {
                $this->view->set_var(array
                    ("NBUSERS2" => "0",
                    "URLEDITEDIT2" => "javascript:alert('D&eacute;sactiv&eacute;')"
                ));
            }
            // Message d'erreur
            if (!is_null($error)) {
                $this->view->set_var("ERRORMESSAGE", $error_msg[$error]);
            }


            // variables mises� jour dans tous les cas
            $this->view->set_var(array
                ("URLREFRESH" => BDO_URL . "admin/mergeediteurs",
                "URLECHANGE" => BDO_URL . "admin/mergeediteurs?source_id=$dest_id&dest_id=$source_id",
                "URLFUSION" => BDO_URL . "admin/mergeediteurs?act=merge&source_id=$source_id&dest_id=$dest_id"
            ));

            $this->view->render();
        }
    }

    public function mergeAuteurs() {
        $error_msg[0] = "Auteur à supprimer non défini";
        $error_msg[1] = "Auteur à garder non défini";
        $error_msg[2] = "Auteur à garder et auteur à fusionner identiques";
        if (!User::minAccesslevel(1))
            die("Vous n'avez pas accès à cette page.");

        $source_id = getValInteger("source_id");
        $dest_id = getValInteger("dest_id");
        $act = getVal("act","");
        $conf = getVal("conf");
        $this->loadModel("Auteur");

        if ($act == "merge") {
            // vérifie que source_id et dest_id ont été defini
            if ((is_null($dest_id)) | ($dest_id == "")) {
                header("Location:" . BDO_URL . "admin/mergeauteurs?source_id=$source_id&error=1");
            }
            if ((is_null($source_id)) | ($source_id == "")) {
                header("Location:" . BDO_URL . "admin/mergeauteurs?dest_id=$dest_id&error=0");
            }

            if ($source_id == $dest_id) {
                header("Location:" . BDO_URL . "admin/mergeauteurs?source_id=$source_id&dest_id=$dest_id&error=2");
            }

            if ($conf == "ok") {

                $modif = $this->Auteur->replaceAuteur($source_id, $dest_id);

                echo "Nombre de records modifiées dans la table bd_tome : " . $modif . "<br>";

                // Supprime l'ancien auteur
                $this->Auteur->set_dataPaste(array("ID_AUTEUR" => $source_id));
                 $this->Auteur->delete();

                echo "Auteur supprim&eacute; <br>";

                $redirection = BDO_URL . "admin";
                echo '<META http-equiv="refresh" content="4; URL=' . $redirection . '">Les auteurs ont été fusionnés.';
            } else {
                // Demande de confirmation

                echo 'Etes-vous s&ucirc;r de vouloir fusionner les auteurs n°' . $source_id . ' et ' . $dest_id . '? <a href="' . BDO_URL . 'admin/mergeauteurs?act=merge&conf=ok&source_id=' . $source_id . '&dest_id=' . $dest_id . '">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';
                exit();
            }
        }

        // AFFICHER
        elseif ($act == "") {

            // REMPLISSAGE PARTIE GAUCHE
            if ((!is_null($source_id)) & ($source_id != '')) {
                // récupère le nombre d'utilisateurs
                $nb_album = $this->Auteur->getNbAlbumForAuteur($source_id);

                // récupère les données principales
                $this->Auteur->set_dataPaste(array(
                    "ID_AUTEUR" => $source_id
                ));
                $auteur1 = $this->Auteur->load();

                $this->view->set_var(array
                    (
                    "AUTEUR1" => $auteur1->a_dataQuery[0],
                    "NBALBUM1" => $nb_album,
                    "URLEDIT1" => BDO_URL."admin/editauteur?auteur_id=".$source_id
                ));
            } else {
                $this->view->set_var(array
                    ("NBUSERS1" => "0",
                    "URLEDIT1" => "javascript:alert('Désactivé')"
                ));
            }

            //REMPLISSAGE DE LA PARTIE DROITE
            if ((!is_null($dest_id)) & ($dest_id != '')) {
                // récupère le nombre d'utilisateurs

                $nb_album2 = $this->Auteur->getNbAlbumForAuteur($dest_id);

                // récupère les données principales
                // récupère les données principales
                $this->Auteur->set_dataPaste(array(
                    "ID_AUTEUR" => $dest_id
                ));
                $this->Auteur->load();

                $this->view->set_var(array
                    (
                    "AUTEUR2" => $this->Auteur,
                    "NBALBUM2" => $nb_album2,
                    "URLEDIT2" => BDO_URL."admin/editauteur?auteur_id=".$dest_id
                ));
            } else {
                $this->view->set_var(array
                    ("NBALBUM2" => "0",
                    "URLEDITEDIT2" => "javascript:alert('Désactivé')"
                ));
            }
            // Message d'erreur
            if (!is_null($error)) {
                $this->view->set_var("ERRORMESSAGE", $error_msg[$error]);
            }


            // variables misesà jour dans tous les cas
            $this->view->set_var(array
                ("URLREFRESH" => BDO_URL . "admin/mergeauteurs",
                "URLECHANGE" => BDO_URL . "admin/mergeauteurs?source_id=$dest_id&dest_id=$source_id",
                "URLFUSION" => BDO_URL . "admin/mergeauteurs?act=merge&source_id=$source_id&dest_id=$dest_id"
            ));

            // assigne la barre de login

        }
        $this->view->render();
    }

    public function editCollection() {
        /*
         * Methode d'ajout / suppression / modification d'une collection
         */
        if (User::minAccesslevel(1)) {
            $act = getVal("act", "");
            $conf = getVal("conf", "");
            $collec_id = getValInteger("collec_id");
            $this->loadModel("Collection");
            // Mettre à jour les informations
            if ($act == "update") {
                $this->Collection->set_dataPaste(array(
                    "NOM" => postVal('txtNomColl'),
                    "ID_EDITEUR" => postVal('txtEditeurId'),
                    "ID_COLLECTION" => postValInteger("txtIdColl")
                ));
                $this->Collection->update();
                echo '<META http-equiv="refresh" content="2; URL=javascript:history.go(-1)">' . "Mise &agrave; jour effectu&eacute;e";
            }

// EFFACEMENT D'UNE COLLECTION
            elseif ($act == "delete") {
                if ($conf == "ok") {
                    $this->Collection->set_dataPaste(array("ID_COLLECTION" => $collec_id));
                    $this->Collection->delete();

                    echo 'La collection a &eacute;t&eacute; effac&eacute;e de la base.';
                    exit();
                } else {
                    // Affiche la demande de confirmation
                    echo 'Etes-vous s&ucirc;r de vouloir effacer la collection  n. ' . $collec_id . ' ? <a href="' . BDO_URL . 'admin/editcollection?act=delete&conf=ok&collec_id=' . $collec_id . '">Oui</a> - <a href="javascript:history.go(-1)">Non</a>';
                    exit();
                }
            }
// AFFICHE UN FORMULAIRE VIDE
            elseif ($act == "new") {
                $editeur_id = getValInteger("editeur_id", 0);
                if ($editeur_id) {// Un éditeur a été passé dans l'URL
                    $this->loadModel("Editeur");
                    $this->Editeur->set_dataPaste(array("ID_EDITEUR" => $editeur_id));
                    $this->Editeur->load();

                    $this->view->set_var(array(
                        "IDEDITEUR" => $this->Editeur->ID_EDITEUR,
                        "EDITEUR" => htmlentities(stripslashes($this->Editeur->NOM)),
                    ));
                }

                $this->view->set_var(array(
                    "NBCOLALB" => "0",
                    "URLDELETE" => "javascript:alert('D&eacute;sactiv&eacute;');",
                    "ACTIONNAME" => "Enregistrer",
                    "URLEDITEDIT" => "javascript:alert('Veuillez d\'abord enregistrer vos modifications');",
                    "URLACTION" => BDO_URL . "admin/editcollection?act=append",
                    "NOM" => "",
                    "EDITEUR" => ""
                ));
                $this->view->layout = "iframe";
                $this->view->render();
            }

// INSERE UNE NOUVELLE COLLECTION DANS LA BASE
            elseif ($act == "append") {
                $this->Collection->set_dataPaste(array(
                    'NOM' => postVal('txtNomColl'),
                    'ID_EDITEUR' => postValInteger("txtEditeurId")
                ));
                $this->Collection->update();
                $lid = $this->Collection->ID_COLLECTION;
                echo GetMetaTag(2, "La collection a &eacute;t&eacute; ajout&eacute", (BDO_URL . "admin/editcollection?collec_id=" . $lid));
                exit();
            }

// AFFICHER UNE COLLECTION
            elseif ($act == "") {
                // on compte le nombre d'albums dans la collection
                $nb_albums = $this->Collection->getNbAlbumForCollection($collec_id);
                $this->Collection->set_dataPaste(array("ID_COLLECTION" => $collec_id));
                $this->Collection->load();
                //récupère les données



                $this->view->set_var(array(
                    "IDCOLL" => $this->Collection->ID_COLLECTION,
                    "NOM" => htmlentities(stripslashes($this->Collection->NOM)),
                    "IDEDITEUR" => $this->Collection->ID_EDITEUR,
                    "EDITEUR" => htmlentities(stripslashes($this->Collection->NOM_EDITEUR)),
                    "NBCOLALB" => $nb_albums,
                    "URLDELETE" => BDO_URL . "admin/editcollection?act=delete&collec_id=" . $this->Collection->ID_COLLECTION,
                    "ACTIONNAME" => "Valider les Modifications",
                    "URLEDITEDIT" => BDO_URL . "admin/editediteur?editeur_id=" . $this->Collection->ID_EDITEUR,
                    "URLACTION" => BDO_URL . "admin/editcollection?act=update"
                ));
                $this->view->layout = "iframe";
                $this->view->render();
            }
        } else {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
    }

    private function resize_edition_image($id_edition, $imagedir) {
        //Redimensionnement : à revoir
        //*****************
        // cherche les infos de cette édition
        $this->loadModel("Edition");
        $this->Edition->set_dataPaste(array("ID_EDITION" => $id_edition));
        $this->Edition->load();


        $id_tome = $this->Edition->ID_TOME;
        $url_img = $this->Edition->IMG_COUV;


        if ($url_img == '') {
            echo "error : no image in database<br/>";
        } else {
            $newfilename = $url_img;

            $max_size = 360;

            //if ($_SERVER["SERVER_NAME"] != 'localhost')
            $imageproperties = getimagesize($imagedir . $newfilename);
            //else $imageproperties = false;

            if ($imageproperties != false) {
                $imagetype = $imageproperties[2];
                $imagelargeur = $imageproperties[0];
                $imagehauteur = $imageproperties[1];

                //Détermine s'il y a lieu de redimensionner l'image
                if ((($imagelargeur > $imagehauteur) && ($imagehauteur > $max_size)) || (($imagelargeur <= $imagehauteur) & ($imagelargeur > $max_size))) {

                    if ($imagelargeur < $imagehauteur) {
                        // image de type panorama : on limite la largeur à 128
                        $new_w = $max_size;
                        $new_h = round($imagehauteur * $max_size / $imagelargeur);
                    } else {
                        // imahe de type portrait : on limite la hauteur au maxi
                        $new_h = $max_size;
                        $new_w = round($imagelargeur * $max_size / $imagehauteur);
                    }
                } else {
                    $new_h = $imagehauteur;
                    $new_w = $imagelargeur;
                }

                $new_image = imagecreatetruecolor($new_w, $new_h);

                switch ($imagetype) {
                    case "1":
                        $source = imagecreatefromgif($imagedir . $newfilename);
                        break;

                    case "2":
                        $source = imagecreatefromjpeg($imagedir . $newfilename);
                        break;

                    case "3":
                        $source = imagecreatefrompng($imagedir . $newfilename);
                        break;

                    case "6":
                        $source = imagecreatefrombmp($imagedir . $newfilename);
                        break;
                    
                    case IMAGETYPE_WEBP:
                        $source = imagecreatefromwebp(BDO_DIR_COUV. $img_couv);
                        break;
                    
                }

                imagecopyresampled($new_image, $source, 0, 0, 0, 0, $new_w, $new_h, $imagelargeur, $imagehauteur);

                switch ($imagetype) {
                    case "2":
                        unlink($imagedir . $newfilename);
                        imagejpeg($new_image, $imagedir . $newfilename, 100);
                        break;

                    case "1":
                    case "3":
                    case "6":
                        unlink($imagedir . $newfilename);
                        $img_couv = substr($newfilename, 0, strlen($newfilename) - 3) . "jpg";
                        imagejpeg($new_image, $imagedir . $img_couv, 100);
                }
            } else {
                echo "error : no image properties <br/>";
            }

            echo "$new_w, $new_h, $imagelargeur, $imagehauteur<br />";
            echo "Image redimensionnée<br />";
        }
    }

    public function muRenameSerie() {
        if (!User::minAccesslevel(1)) {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
        $this->loadModel("Serie");
        $this->loadModel("Tome");
        $act = getVal("act", "");
        $serie = getVal("serie", "");
        // Mettre à jour les informations
        if ($act == "update") {
            $nb = 0;
            $num_tome = postVal("num_tome");
            $alb_id = postVal("alb_id");
            $newtitre = postVal("txtNouvTitre");
            foreach ($alb_id as $idtome) {
                $nouv_titre = ereg_replace("#tome#", $num_tome[$idtome], $newtitre);
                $this->Tome->renameAlbum($idtome, $nouv_titre);
                $nb++;
            }
            echo GetMetaTag(2, "$nb albums ont été traités.", (BDO_URL . "admin/murenameserie?serie=" . $serie));
        }

        // AFFICHER UNE FICHE SERIE
        elseif ($act == "") {
            if ($serie != "") {
                $this->Serie->set_dataPaste(array(
                    "ID_SERIE" => $serie
                ));
                $this->Serie->load();

                $this->view->set_var(array(
                    "SERIE" => stripslashes($this->Serie->NOM_SERIE),
                    "IDSERIE" => $serie,
                    "NOUVTITRE" => stripslashes($this->Serie->NOM_SERIE) . ", Tome #tome#"
                ));

                $dbs_tome = $this->Tome->load('c', "
                            WHERE bd_tome.id_serie=
                            " . $serie . "
                             ORDER BY bd_tome.id_tome");
                // selection des albums
                $this->view->set_var('dbs_tome', $dbs_tome);
            }

            $this->view->set_var(array(
                "ACTIONNAME" => "Mettre à Jour",
                "URLACTION" => BDO_URL . "admin/murenameserie?act=update&serie=" . $serie,
                "URLREFRESH" => BDO_URL . "admin/murenameserie",
                "URLEDITSERIE" => BDO_URL . "admin/editserie?serie_id=" . $serie
            ));
            $this->view->layout = "iframe";
            $this->view->render();
        }
    }

    public function muCouvSerie() {
        if (!User::minAccesslevel(1)) {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
        $this->loadModel("Serie");
        $this->loadModel("Tome");
        $this->loadModel("Edition");
        $act = getVal("act", "");
        $serie = getVal("serie", "");
        // Mettre à jour les informations
        if ($act == "update") {
            $nb = 0;
            $alb_id = postVal("alb_id");
            $url_amz = postVal("url_amz");
            foreach ($alb_id as $idtome) {
                $this->Tome->set_dataPaste(array(
                    "ID_TOME" => $idtome
                ));
                $this->Tome->load();
                // Selection le numéro de l'edition en cours


                $idedition = $this->Tome->ID_EDITION;

                // Efface la couverture actuelle
                $oldfile = $this->Tome->IMG_COUV;
                @unlink(BDO_DIR_COUV . $oldfile);

                // détermine le nouveau nom
                $newfilename = "CV-" . sprintf("%06d", $idtome) . "-" . sprintf("%06d", $idedition);

                // Copie le fichier dans le répertoire temporaire
                $new_filename = get_img_from_url($url_amz[$idtome], BDO_DIR_UPLOAD, $newfilename);

                // Déplace le fichier dans le répertoire couv
                rename(BDO_DIR_UPLOAD . $new_filename, BDO_DIR_COUV . $new_filename);

                // Met à jour bd_edition
                $this->Edition->set_dataPaste(array(
                    "ID_EDITION" => $idedition,
                    "IMG_COUV" => $new_filename
                ));
                $this->Edition->update();


                $nb++;
            }
            echo GetMetaTag(2, "$nb albums ont été traités.", (BDO_URL . "admin/mucouvserie?serie=" . $serie));
        }

        // AFFICHER UNE FICHE SERIE
        elseif ($act == "") {

            if ($serie != "") {

                // récupère le infos liées à la série
                $this->Serie->set_dataPaste(array(
                    "ID_SERIE" => $serie
                ));
                $this->Serie->load();
                $this->view->set_var(array(
                    "SERIE" => stripslashes($this->Serie->NOM_SERIE),
                    "IDSERIE" => $serie,
                    "NOUVTITRE" => stripslashes($this->Serie->NOM_SERIE) . ", Tome #tome#"
                ));

                // Affiche les couvertures
                $dbs_tome = $this->Tome->load('c', "
                            WHERE bd_tome.id_serie=
                            " . $serie . "
                             ORDER BY bd_tome.id_tome");
                // selection des albums
                $this->view->set_var('dbs_tome', $dbs_tome);
            }
            $this->view->set_var(array(
                "ACTIONNAME" => "Mettre à Jour",
                "URLACTION" => BDO_URL . "admin/mucouvserie?act=update&serie=" . $serie,
                "URLREFRESH" => BDO_URL . "admin/mucouvserie",
                "URLEDITSERIE" => BDO_URL . "admin/editserie?serie_id=" . $serie
            ));

            $this->view->layout = "iframe";
            $this->view->render();
        }
    }

    public function splitEdition() {

        if (!User::minAccesslevel(1)) {
            die("Vous n'avez pas acc&egrave;s &agrave; cette page.");
        }
        $act = getVal("act");
        $alb_id= getValInteger("alb_id");
        // Split les éditions dans un nouvel album
        if($act=="update")
        {
                // vérifie si une série a été passé
                $new_serie_id = postValInteger("txtNewSerieId");
                $old_tome_id = postValInteger("txtTomeId");
                $chkEdition = postVal("chkEdition");
                $txtCouv = postVal("txtCouv");
                // teste si des editions ont été cochée
                if (count($chkEdition) == 0) {
                        echo GetMetaTag(2,"Aucune édition à transférer.",(BDO_URL."admin/splitedition?alb_id=".$old_tome_id));
                        exit();
                }

                // Récupère le genre de la nouvelle série
                $this->loadModel("Serie");
                $this->Serie->set_dataPaste(array("ID_SERIE" =>$new_serie_id ));
                $this->Serie->load();

                $id_genre = $this->Serie->ID_GENRE;

                // création du nouvel album dans la base bd_tome
                $this->loadModel("Tome");
                $this->Tome->set_dataPaste(array("ID_TOME" => $old_tome_id));
                $this->Tome->load();
                $newTome = new Tome();
                $newTome->set_dataPaste(array(
                    "TITRE" => $this->Tome->TITRE_TOME,
                    "NUM_TOME" => $this->Tome->NUM_TOME,
                    "ID_GENRE" => $id_genre,
                    "ID_SERIE" =>  $new_serie_id,
                    "ID_SCENAR" => $this->Tome->ID_SCENAR,
                    "ID_SCENAR_ALT"  => $this->Tome->ID_SCENAR_ALT,
                    "ID_DESSIN"  => $this->Tome->ID_DESSIN,
                    "ID_DESSIN_ALT"  => $this->Tome->ID_DESSIN_ALT,
                    "ID_COLOR"  => $this->Tome->ID_COLOR,
                    "ID_COLOR_ALT"=> $this->Tome->ID_COLOR_ALT,
                    "FLG_INT" => $this->Tome->FLG_INT_TOME,
                    "FLG_TYPE" => $this->Tome->FLG_TYPE_TOME,
                    "PRIX_BDNET" => $this->Tome->PRIX_BDNET,
                    "HISTOIRE" => $this->Tome->HISTOIRE_TOME
                ));


                $newTome->update();


                // récupère la valeur du dernier album inséré
                $new_tome_id = $newTome->ID_TOME;

                echo "new tome:".$new_tome_id."<br>";

                // transfère les éditions à transférer sur le nouvel album
                // et prend la première édition comme édition par défaut
                $flg_edition = "O";
                $this->loadModel("Edition");
                foreach ($chkEdition as $idedition) {

                        // si une couverture existe, son nom est modifié
                        $old_filename = $txtCouv[$idedition];
                        if ($old_filename == "")
                        {
                                $new_filename = "";
                        }else{
                                $new_filename = "CV-".sprintf("%06d",$new_tome_id)."-".sprintf("%06d",$idedition).substr($old_filename,-4);
                                echo "renomme $old_filename en $new_filename<br>";
                                rename(BDO_DIR_COUV.$old_filename,BDO_DIR_COUV.$new_filename);
                        }

                        if ($flg_edition == "O") //première édition comme édition par défaut
                        {
                                // renseigne cette edition comme defaut pour bd_tome
                                $newTome->add_dataPaste("ID_EDITION" , $idedition);
                                $newTome->update();
                                $flg_edition = "N";

                        }
                        $this->Edition->set_dataPaste(array("ID_EDITION" => $idedition));
                        $this->Edition->load();
                        // Transfère les éditions sélectionnées sous le nouvel albums
                        $this->Edition->set_dataPaste(array(
                           "ID_TOME" => $new_tome_id,
                            "IMG_COUV" => $new_filename
                        ));

                        $this->Edition->update();
                        echo "Nombre de records modifi&eactue;es dans la table bd_edition : ".$this->Edition->affected_rows."<br>";
                }

                echo GetMetaTag(2,"Split effectu&eacute;.",(BDO_URL."admin/editalbum?alb_id=".$new_tome_id));
                exit();
        }

        // AFFICHER UN ALBUM
        elseif($act=="")
        {
                $this->loadModel("Tome");
                $this->Tome->set_dataPaste(array("ID_TOME" =>$alb_id ));
                $this->Tome->load();

                $id_edition = $this->Tome->ID_EDITION;

                // Détermine l'affichage des infos
                $scenaristes1 = ($this->Tome->ID_SCENAR_ALT == 0) ? stripslashes($this->Tome->scpseudoscpseudo) : stripslashes($this->Tome->scpseudo)." / ".stripslashes($this->Tome->scapseudo);
                $dessinateurs1 = ($this->Tome->ID_DESSIN_ALT  == 0) ? stripslashes($this->Tome->depseudo) : stripslashes($this->Tome->depseudo)." / ".stripslashes($this->Tome->deapseudo);
                $coloristes1 = ($this->Tome->ID_COLOR_ALT == 0) ? stripslashes($this->Tome->copseudo) : stripslashes($this->Tome->copseudo)." / ".stripslashes($this->Tome->coapseudo);
                $edcollec1 = ($this->Tome->NOM_COLLECTION == "<N/A>") ? stripslashes($this->Tome->NOM_EDITION) : stripslashes($this->Tome->NOM_EDITION)." / ".stripslashes($this->Tome->NOM_COLLECTION);
                // Creation d'un nouveau Template


                $this->view->set_var (array
                ("IDTOME" => $this->Tome->ID_TOME,
                "TITRE" => stripslashes($this->Tome->TITRE_TOME),
                "SERIEID" => $this->Tome->ID_SERIE,
                "SERIE" => stripslashes($this->Tome->NOM_SERIE),
                "TOME" => $this->Tome->NUM_TOME,
                "SCENARISTES" => $scenaristes1,
                "DESSINATEURS" => $dessinateurs1,
                "COLORISTES" => $coloristes1,
                "EDCOLLEC" => $edcollec1,
                ));

                // Affiche les informations relatives aux différentes éditions sauf celle par defaut
                $this->loadModel('Edition');
                $dbs_edition = $this->Edition->load("c", "where PROP_STATUS not in ('0','99','98') and bd_tome.id_tome =" . $alb_id);



                $this->view->set_var (array
                ("dbs_edition" => $dbs_edition,
                 "DEFAULT_EDITION" => $id_edition,
                "URLRETOURFICHE" => BDO_URL."admin/editalbum?alb_id=".$alb_id,
                "ACTIONNAME" => "Effectuer les modifications",
                "URLACTION" => BDO_URL."admin/splitedition?act=update"
                ));
                $this->view->render();


        }
    }

}
