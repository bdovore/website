<?php

/*
 * Controller pour l'ajout et la gestion des propositions
 * reprie en partie du code Latruffe originale + usage du model
 * @author : Tom
 *
 */

class Proposition extends Bdo_Controller {

    public function Index () {
        /*
         * Affichage du modele de saisie d'une proposition
         * => si un album est passé en paramètre, on est dans le cas de la correction, sinon création
         */
        $action_user[0][0] = 0;
        $action_user[0][1] = "Insérer dans ma collection";
        $action_user[1][0] = 1;
        $action_user[1][1] = "Insérer dans mes achats futurs";
        $action_user[2][0] = 2;
        $action_user[2][1] = "Ne rien faire";
        $type = getVal("type","ALBUM");
        if (User::minAccesslevel(2)) {
            $id_edition = getValInteger("id_edition",0);

            $action = postVal("action","");

            if ($action == "append") {
                $er = $this->addProposition();
                if (issetNotEmpty($er))  {
                    var_dump($er);
                    die("Erreur lors de l'insertion, contactez l'équipe !");
                }
                else {
                    $this->view->addAlertPage("Votre demande est bien enregistrée !");
                    $this->view->addPhtmlFile('alert', 'BODY');
                    
                }
            }
            else {
                if ($type == "EDITION") {
                    $this->loadModel("Edition");
                    $this->Edition->load();
                    $this->view->set_var("edition",$this->Edition);
                    $this->view->set_var("ID_TOME",getValInteger("id_tome"));
                    $this->view->set_var('PAGETITLE',"Proposer l'ajout d'une édition ");
                    $this->view->set_var("TYPE","EDITION");
                }
                else if ($id_edition <> 0) {
                    $this->loadModel("Edition");
                    $this->Edition->load();
                    $this->Edition->set_dataPaste(array(
                    "ID_EDITION" => $id_edition
                    ));
                    $this->Edition->load();
                    $this->view->set_var("edition",$this->Edition);
                    $this->view->set_var('PAGETITLE',"Proposer une correction pour ".$this->Edition->TITRE_TOME);
                    $this->view->set_var("TYPE","CORRECTION");
                }
                else {
                    $this->view->set_var('PAGETITLE',"Proposer l'ajout d'un album");
                    $this->view->set_var("OPTIONS",GetOptionValue($action_user,0));
                    $this->view->set_var("TYPE","AJOUT");
                }
            }
        }

        else {
            $this->view->addAlertPage("Vous devez vous authentifier pour accéder à cette page !");
            $this->view->addPhtmlFile('alert', 'BODY');
        }

        $this->view->layout = "iframe";
        $this->view->render();
    }

    private function addProposition() {

        $type = postVal("type","ALBUM");
        if ($type == "EDITION") {
            $this->loadModel("Edition");
            $this->Edition->set_dataPaste((array(
                "ID_TOME" => postValInteger("txtTomeId"),
                "ID_EDITEUR" => postValInteger("txtEditeurId"),
                "ID_COLLECTION" => postValInteger("txtCollecId"),
                //"DTE_PARUTION" => postVal("txtDateParution"),
                "DTE_PARUTION" => completeDate(postVal("txtDateParution")),
                "COMMENT" => postVal("txtCommentaire"),
                "USER_ID" => $_SESSION['userConnect']->user_id,
                "PROP_DTE" => date('d/m/Y H:i:s'),
                "PROP_STATUS" => "0",
                "ISBN" => postVal("txtISBN"),
                "EAN" => postVal("txtEAN")
            )));
            $this->Edition->update();
            $lid = $this->Edition->ID_EDITION;
        }
        else {
            $this->loadModel("User_album_prop");
            $id_edition = getValInteger("id_edition",0);
            // on enregistre la proposition
            // TODO vérifier pourquoi il n'y a de Db_Escape_String que sur certain string ...
            $this->User_album_prop->set_dataPaste(array(
                "USER_ID" => $_SESSION['userConnect']->user_id,
                "PROP_DTE" => date('d/m/Y H:i:s'),
                "PROP_TYPE" => ($id_edition == 0) ? 'AJOUT' : 'CORRECTION',
                "ID_TOME" => postValInteger("txtTomeId"),
                "ID_EDITION" => postValInteger("txtEditionId"),
                "TITRE" => postVal("txtTitre"),
                "NUM_TOME" => postValInteger("txtNumTome"),
                "FLG_INT" => ((postVal("chkIntegrale") == "checkbox") ? "O" : "N"),
                "FLG_TYPE" => postVal("lstType"),
                "ID_SERIE" => postValInteger("txtSerieId"),
                "SERIE" => postVal("txtSerie"),
                "FLG_FINI" => postVal("lstAchevee"),
                "DTE_PARUTION" => completeDate(postVal("txtDateParution")),
                "ID_GENRE" => postValInteger("txtGenreId"),
                "GENRE" => postVal("txtGenre"),
                "ID_EDITEUR" => postValInteger("txtEditeurId"),
                "EDITEUR" => postVal("txtEditeur"),
                "ID_SCENAR" => postValInteger("txtScenarId"),
                "SCENAR" => postVal("txtScenar"),
                "ID_SCENAR_ALT" => postValInteger("txtScenarAltId"),
                "SCENAR_ALT" => postVal("txtScenarAlt"),
                "ID_DESSIN" => postValInteger("txtDessiId"),
                "DESSIN" => postVal("txtDessi"),
                "ID_DESSIN_ALT" => postValInteger("txtDessiAltId"),
                "DESSIN_ALT" => postVal("txtDessiAlt"),
                "ID_COLOR" => postValInteger("txtColorId"),
                "COLOR" => postVal("txtColor"),
                "ID_COLOR_ALT" => postValInteger("txtColorAltId"),
                "COLOR_ALT" => postVal("txtColorAlt"),
                "ID_COLLECTION" => postValInteger("txtCollecId"),
                "COLLECTION" => postVal("txtCollec"),
                "EAN" => postVal("txtEAN"),
                "ISBN" => postVal("txtISBN"),
                "HISTOIRE" => postVal("txtHistoire"),
                "COMMENTAIRE" => postVal("txtCommentaire"),
                "ACTION" => postVal("cmbAction"),
                "NOTIF_MAIL" =>((postVal("chkNotEmail") == "checked") ? "1" : "0")
            ));

            $this->User_album_prop->update();
            if (issetNotEmpty($this->User_album_prop->error)) {
                return $this->User_album_prop->error;
            }
            $lid = $this->User_album_prop->ID_PROPOSAL;
        }

        // Verifie la présence d'une image à télécharger
        // on vérifie aussi la taille maximale d'un upload (2MB pour le moment).
        // Normalement si le serveur est configuré correctement, on ne doit pas
        // vraiment s'en préoccuper, il y a une limite dans la config du serveur.
        if ( $_FILES['txtFileLoc']['size'] > 0 && $_FILES['txtFileLoc']['size'] < 2000000 )
        //if (is_file(postVal("txtFileLoc")))
        { // un fichier à uploader
            $imageproperties = getimagesize($_FILES['txtFileLoc']['tmp_name']);
            $imagetype = $imageproperties[2];
            //$imagelargeur = $imageproperties[0];
            //$imagehauteur = $imageproperties[1];

            $new_filename = sprintf("tmpCV-%06d-01",$lid);

            // vérifie le type d'image
            switch ($imagetype)
            {
                case IMAGETYPE_GIF:
                    $new_filename .=".gif";
                    break;
                case IMAGETYPE_JPEG:
                    $new_filename .=".jpg";
                    break;
                case IMAGETYPE_PNG:
                    $new_filename .=".png";
                    break;
                default:
                    echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Seul des fichiers PNG, JPEG ou GIF peuvent être chargés. Vous allez être redirigé.';
                    exit();
                    break;
            }

            //move_uploaded_file fait un copy(), mais en plus il vérifie que le fichier est bien un upload
            //et pas un fichier local (genre constante.php, au hasard)
            if(!move_uploaded_file($_FILES['txtFileLoc']['tmp_name'], BDO_DIR_UPLOAD.$new_filename)) {
                echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Erreur lors de l\'envoi de l\'image au serveur. Vous allez être redirigé.';
                exit();
            } else {
                $img_couv=$new_filename;
            }
        }
        else if (preg_match('/^(http:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/', postVal("txtFileURL"), $url_ary))
        { // un fichier à télécharger
            // TODO en php5 copy() gère tout ça, plus besoin de passer directement par les sockets
            // mais le serveur bodovore est en php 4.4
            // !!! le serveur pour la beta est en php 5 !!! --> TODO
            if ( empty($url_ary[4]) )
            {
                echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">URL image incomplète. Vous allez être redirigé.';
                exit();
            }
            $myurl = postVal("txtFileURL");

            $tmp_filename = tempnam(BDO_DIR_UPLOAD, uniqid(rand()) . '-');

            $content = file_get_contents($myurl);
            $save = file_put_contents($tmp_filename,$content);
            $mime_type = mime_content_type($tmp_filename);
             // Check la validité de l'image
            if (!preg_match('#image.([a-z]+)#i', $mime_type, $file_data2)) {
                $error = true;
                echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Erreur lors du t&eacute;l&eacute;chargement de l\'image. Vous allez &ecirc;tre redirig&eacute;.';
                exit();
            }

            // règle n°1 de l'upload: ne pas faire confiance au type MIME du header HTTP
            // new file name
            //if ( !($imgtype = check_image_type($avatar_filetype, $error)) )
            //{
            //    exit;
            //}

            //$new_filename = sprintf("tmpCV-%06d-01",$lid).$imgtype;

            $imageproperties = getimagesize($tmp_filename);
            $imagetype = $imageproperties[2];

            $new_filename = sprintf("tmpCV-%06d-01",$lid);

            // vérifie le type d'image
            switch ($imagetype)
            {
                case IMAGETYPE_GIF:
                    $new_filename .=".gif";
                    break;
                case IMAGETYPE_JPEG:
                    $new_filename .=".jpg";
                    break;
                case IMAGETYPE_PNG:
                    $new_filename .=".png";
                    break;
                default:
                    echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Seul des fichiers PNG, JPEG ou GIF peuvent être chargés. Vous allez être redirigé.';
                    exit();
                    break;
            }

            // si le fichier existe, on l'efface.
            // NB: D'après la doc, copy écrase le fichier existant automatiquement
            //if (file_exists(BDO_DIR_UPLOAD . $new_filename))
            //{
            //    @unlink(BDO_DIR_UPLOAD . $new_filename);
            //}

            // copie le fichier temporaire dans le repertoire image
            if (!@copy($tmp_filename, BDO_DIR_UPLOAD . $new_filename)) {
                @unlink($tmp_filename);
                echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Erreur lors de la copie de l\'image sur le serveur. Vous allez être redirigé.';
                exit();
            }

            @unlink($tmp_filename);

            $img_couv=$new_filename;
        }
        else
        {
            $img_couv='';
        }

        $id_edition = postValInteger("txtEditionId",0);

        if ($type=="EDITION") {
            $this->Edition->set_dataPaste(array("IMG_COUV" => $img_couv));
            $this->Edition->update();
            return $this->Edition->error;
        }
        else {
            $this->User_album_prop->set_dataPaste(array("IMG_COUV" => $img_couv));
            $this->User_album_prop->update();

            return $this->User_album_prop->error;
        }
    }
    public function Listpropal(){
        /* fonction pour lister les propositons en cours et informer les utilisateurs
         *
         */
        $type = getVal("type","AJOUT");
        if ($type == "EDITION") {
            $this->loadModel("Edition");
            $dbs_edition = $this->Edition->load("c"," WHERE PROP_STATUS in (0,2,3,4)");

            $this->view->set_var("dbs_edition",$dbs_edition);
        }
        else {
            $this->loadModel("User_album_prop");
            //echo $this->User_album_prop->select()." WHERE STATUS in (0,2,3,4) and PROP_TYPE = '".  Db_Escape_String($type)."'";
            $dbs_prop = $this->User_album_prop->load("c"," WHERE STATUS in (0,2,3,4) and PROP_TYPE = '".  Db_Escape_String($type)."'");
            $this->view->set_var("dbs_prop", $dbs_prop);
        }

        $this->loadModel('Statistique');
        $this->Statistique->editionAttente();
        $this->Statistique->ajoutCorrection();

        $this->view->set_var(
            array(
                'NBEDITION' => $this->Statistique->nbEditionAttente,
                "NBAJOUT" => $this->Statistique->nbajout,
                "NBCORRECTION" => $this->Statistique->nbcorrect,
            )
        );

        //$this->view->set_var($this->User_album_prop->getAllStat());
        $this->view->set_var("type",$type);
        $this->view->set_var("PAGETITLE","Liste des proposition : ".Db_Escape_String($type));
        $this->view->render();

    }
}

?>
