<?php

/**
 *
 * @author laurent
 *
 */
class User extends Bdo_Db_Line
{

    /**
     */
    public $table_name = 'users';

    public $CARRE_TYPE = 0;

    public $error = '';

    public $a_roleGlobal = array();

    // initialisation
    public function __construct ($id = null)
    {
        if (is_array($id)) {
            $a_data = $id;
        }
        else {
            $a_data = array(
                    'user_id' => $id
            );
        }
        parent::__construct($this->table_name, $a_data);
    }

    public function select ()
    {
        return "SELECT
                `user_id` ,
                `last_connect` ,
                `nb_connect` ,
                `username` ,
                `password` ,
                `level` ,
                `email` ,
                `birthday` ,
                `location` ,
                `image` ,
                `OPEN_COLLEC` ,
                `MSG_COLLEC` ,
                `CARRE_TYPE` ,
                `ROW_DISPLAY` ,
                `ABT_NEWS` ,
                `VAL_ALB` ,
                `VAL_INT` ,
                `VAL_COF` ,
                `VAL_COF_TYPE` ,
                `ROWSERIE` ,
                `PREF_EXPORT` , 
                API_TOKEN,
                DATE_TOKEN
        FROM `" . $this->table_name . "`
 ";
    }

    public function search ($a_data = array())
    {
        // --------------------------------------------------------------------
        // -------- Champs selectionnés par defaut --------
        if (empty($a_data)) $a_data = $_POST;
        if (! isset($a_data['validSubmitSearch'])) {
            $a_data['ch_user_id'] = "checked";
            $a_data['ch_username'] = "checked";
            $a_data['ch_email'] = "checked";
        }

        $dbSearch = new Bdo_Db_Search();

        $dbSearch->select = "
SELECT
                `user_id` ,
                `last_connect` ,
                `nb_connect` ,
                `username` ,
                `level` ,
                `email` ,
                `birthday` ,
                `location` ,
                `image` ,
                `OPEN_COLLEC` ,
                `MSG_COLLEC` ,
                `CARRE_TYPE` ,
                `ROW_DISPLAY` ,
                `ABT_NEWS` ,
                `VAL_ALB` ,
                `VAL_INT` ,
                `VAL_COF` ,
                `VAL_COF_TYPE` ,
                `ROWSERIE` ,
                `PREF_EXPORT`
";

        // dans les tables
        $dbSearch->from = "
FROM " . $this->table_name . "
";

        $dbSearch->where = "WHERE 1";

        // dans l'ordre
        if ($a_data['daff'] == "") $a_data['daff'] = "0";
        if ($a_data['sens_tri'] == "") $a_data['sens_tri'] = "ASC";
        if ($a_data['col_tri'] == "") $a_data['col_tri'] = $this->table_name . ".username";

        $dbSearch->groupby = "";

        // --------------=======================----------------
        $dbSearch->infoQuery();
        // --------------=======================----------------
        $dbSearch->integreData($a_data);
        // --------------=======================----------------
        if (isset($_GET['export'])) {
            $dbSearch->execNoLimit();
        }
        else {
            $dbSearch->exec();
        }

        return $dbSearch;
    }

    function autoLogin ()
    {
        // deconnexion demandee
        if (isset($_POST['submitLoginDisconnect'])) {
            $this->logout();
        }

        // tentative de connexion automatique
        if ((! isset($_SESSION['userConnect']->user_id) or empty($_SESSION['userConnect']->user_id))
                and issetNotEmpty($_COOKIE["username"])
                and issetNotEmpty($_COOKIE["pass"])) {

            $this->load('c', "
                WHERE username ='" . Db_Escape_String($_COOKIE["username"]) . "'
                AND password='" . Db_Escape_String($_COOKIE["pass"]) . "'
                AND level<98
                ");

            if (1 == $this->dbSelect->nbLineResult) {
                $_SESSION['userConnect'] = $this->dbSelect->a_dataQuery[0];
                $this->addNbConnect();
            }
            else {
                setcookie("pass", "", time() - 3600);
            }
        }
        // connexion demandee
        else if (isset($_POST['submitLoginConnect'])) {

            // -----------
            // absence de cookie indique la non acceptation des cookies par le
            // navigateur
            if (isset($_COOKIE['PHPSESSID'])) {
                // -----------
                $this->load('c', "WHERE username ='" . Db_Escape_String($_POST['user_login']) . "'");

                if (1 == $this->dbSelect->nbLineResult) {

                    $mdp_user = md5($_POST['user_password']);

                    if ($mdp_user and ($this->password == md5($_POST['user_password']))) {
                        if ($this->level < 98) {

                            $_SESSION['userConnect'] = $this->dbSelect->a_dataQuery[0];

                            $this->addNbConnect();

                            // défini les paramètres de cookie
                            setcookie("username", $this->username, time() + 31104000, "/");
                            // connexion automatique "se souvenir de moi"
                            if (isset($_POST['chkvisit'])) {
                                setcookie("pass", $this->password, time() + 31104000, "/");
                            } else {
                                setcookie("pass", $this->password, time() + 3600, "/"); // on mémorise pour 1h
                            }
                            $test = strpos($_SERVER['HTTP_REFERER'],"inscription?act=post" );
                            // on retourne à la page d'origine de la connexion
                            if (strpos($_SERVER['HTTP_REFERER'],"inscription?act=post" ) ) { 
                                header('Location: '.BDO_URL);
                            } else {
                                header('Location: '.$_SERVER['HTTP_REFERER']);
                                
                            }
                        }
                        else {
                            $this->error = 'Compte bloqué';
                        }
                    }
                    else {
                        $this->error = 'Mot de passe de connexion invalide';
                    }
                }
                else {
                    $this->error = 'Identifiant de connexion inconnu';
                }
            }
            else {
                $this->error = 'Vous devez accepter les cookies pour vous connecter.';
            }
        }
            // connexion realise par openid username
        else if ((! isset($_SESSION['userConnect']->user_id) or empty($_SESSION['userConnect']->user_id))
                and isset($_SESSION['userConnect']->username)){
            $this->load('c', "
                WHERE username ='" . Db_Escape_String($_SESSION['userConnect']->username) . "'
                AND level<98
                ORDER BY nb_connect
                ");

            if (1 <= $this->dbSelect->nbLineResult) {
                $_SESSION['userConnect'] = $this->dbSelect->a_dataQuery[0];
                $this->addNbConnect();
            }
            else {
                $this->error = $_SESSION['userConnect']->username . ' -> pseudo inconnu';
            }

        }
        // connexion realise par openid email
        else if ((! isset($_SESSION['userConnect']->user_id) or empty($_SESSION['userConnect']->user_id))
                and isset($_SESSION['userConnect']->email)){
            $this->load('c', "
                WHERE email ='" . Db_Escape_String($_SESSION['userConnect']->email) . "'
                AND level<98
                ORDER BY nb_connect
                ");

            if (1 <= $this->dbSelect->nbLineResult) {
                $_SESSION['userConnect'] = $this->dbSelect->a_dataQuery[0];
                $this->addNbConnect();
            }
            else {
                $this->error = $_SESSION['userConnect']->email . ' -> Email inconnu';
            }

        }
        // connexion réalisé par appel d'API avec un token
        else if (isset($_GET["API_TOKEN"])) {
            if ($this->verifyToken($_GET["API_TOKEN"])) {
                $_SESSION['userConnect'] = $this->dbSelect->a_dataQuery[0];
            } else {
                header("HTTP/1.1 401 Unauthorized");
            }
            
            
        }
        if ($this->error) {
            $this->guest();
        }

        foreach ((array) $_SESSION['userConnect'] as $var => $value) {
            $this->$var = $value;
        }
        return $this;
    }

    public function addNbConnect ()
    {
        $a_data = array(
                "user_id" => $this->user_id,
                "nb_connect" => ($this->nb_connect + 1),
                "last_connect" => date("Y-m-d H:i:s")
        );

        $this->set_dataPaste($a_data);
        $this->update();
    }

    public function guest ()
    {
        // niveau d'acces publique
        unset($_SESSION['user']);
        unset($_SESSION['userConnect']);

        $user = new stdClass();
        $user->level = 5;
        $user->user_id = null;
        $user->username = null;
        $_SESSION['userConnect'] = $user;
    }

    public function logout ()
    {
        $this->guest();

        // on efface le cookie
        if (isset($_COOKIE["pass"])) unset($_COOKIE["pass"]);
        setcookie("pass", "", time() + 3600, "/");

        header("Location:" . BDO_URL);
        Bdo_Cfg::quit();
    }

    public static function minAccesslevel ($level = 5)
    {
        if (! isset($_SESSION['userConnect']->level) or ($_SESSION['userConnect']->level > $level)) {
            return false;
        }
        return true;
    }

    /**
     *
     * @return boolean
     */
    public function isAllowed ($resPriv)
    {
        // acces super administrateur
        if (User::minAccesslevel(0)) {
            return true;
        }
        // connaitre les roles
        if (empty($a_idAclRole)) {

            // tableau des roles
            $a_idAclRole[] = $_SESSION['userConnect']->level;
        }

        $acl = Bdo_Cfg::getVar('acl');

        $a_respriv = explode('.', $resPriv);
        $resource = $a_respriv[0];
        $privilege = $a_respriv[1];

        if (! $acl->isResourcePrivilege($resource, $privilege)) {
            return false;
        }

        foreach ($a_idAclRole as $id_acl_role) {
            //echo '<br/>'.$id_acl_role.'-'.$resource.'-'.$privilege;
            if ($acl->isAllowed($id_acl_role, $resource, $privilege)) {
                // echo ' OK';
                return true;
            }
        }

        return false;
    }

    public function is_openCollection ()
    {
        if ($this->OPEN_COLLEC != 'Y') {
            return false;
        }
        else {
            return true;
        }
    }

    public function setForumAccount($username,$password, $email) {
        $connexion = Db_connect(array(
                'login' => FORUM_DB_USER,
                'password' => FORUM_DB_PWD,
                'sid' => FORUM_DB_SID,
                'server' => FORUM_DB_HOST));

        if ($connexion === false) {
            echo "Forum : connexion impossible. Seul le compte bdovore a été créé.<br/>";
            return;
        }


        $username = Db_Escape_String($username);
        $email = Db_Escape_String($email);

        $verif = "SELECT count(*) AS nb FROM bb3_users WHERE username='" . $username . "'";
        $result = Db_query($verif,$connexion);
        $o = Db_fetch_object($result);
        if ($o->nb == 0) {
            $query = "SELECT MAX(user_id) AS total FROM bb3_users";
            $result = Db_query($query,$connexion);
            $o = Db_fetch_object($result);
            $id = $o->total + 1;
            $query = "INSERT INTO bb3_users (
                    user_id , username,username_clean, user_password, user_email, user_regdate, group_id, user_style, user_options, user_allow_viewemail ) VALUES (
                    $id, '" . $username . "', '" . $username . "', '" . md5($password) . "', '" . $email . "'," . time() . ", 545, 2, 230207,0
                    )";
            $result = Db_query($query,$connexion);
            if ($result) {
                 $query = "INSERT INTO `bb3_user_group` (
                        `group_id` ,
                        `user_id` ,
                        `group_leader` ,
                        `user_pending`
                        )
                        VALUES (
                        '545', $id, '0', '0'
                        )";
                 $result = Db_query($query,$connexion);
                 
            }
        }
    }

    public static function deleteAllDataForUser($profile_user_id) {
        /*
         * Suppression de toute les données d'un utilisateur
         */

        $user_id = intval($profile_user_id);

        Db_query("UPDATE `bd_edition` SET `USER_ID`=NULL WHERE `user_id`='" . $user_id . "'");
        Db_query("UPDATE `bd_edition` SET `VALIDATOR`=NULL WHERE `VALIDATOR`='" . $user_id . "'");
        Db_query("UPDATE `newsletter` SET `USR_CREA`=NULL WHERE `USR_CREA`='" . $user_id . "'");
        Db_query("UPDATE `newsletter` SET `USR_MODIF`=NULL WHERE `USR_MODIF`='" . $user_id . "'");
        Db_query("UPDATE `users_alb_prop` SET `VALIDATOR`=NULL WHERE `VALIDATOR`='" . $user_id . "'");

        // dans tout les cas
        Db_query("DELETE FROM `serie_comment` WHERE `user_id`='" . $user_id . "'");
        Db_query("DELETE FROM `users_album` WHERE `user_id`='" . $user_id . "'");
        Db_query("DELETE FROM `users_exclusions` WHERE `user_id`='" . $user_id . "'");
        Db_query("DELETE FROM `users_list_aut` WHERE `user_id`='" . $user_id . "'");
        Db_query("DELETE FROM `users_list_carre` WHERE `user_id`='" . $user_id . "'");
        Db_query("DELETE FROM `users_comment` WHERE `USER_ID`='" . $user_id . "'");
        Db_query("DELETE FROM `users_alb_prop` WHERE `USER_ID`='" . $user_id . "'");
        Db_query("DELETE FROM `users` WHERE `user_id`='" . $user_id . "'");
    }

    public function getUserList($search,$exactmatch=false,$max=10) {

        $username = Db_Escape_String($search);

        if ($exactmatch)
            $where = " WHERE username LIKE '" . $username . "'";
        else
            $where = " WHERE username LIKE '%" . $username . "%'";

        $orderby = " ORDER BY username";
        $limit = " LIMIT 0, " . intval($max);

        return $this->load("c", $where . $orderby . $limit);

    }

    public function countUserBy($type, $id) {


        $query = '';
        switch ($type) {
            case "edition" :
                {
                    $query = "
                SELECT COUNT(DISTINCT(ua.user_id)) as nbr
                FROM
                    users_album ua
                WHERE
                    ua.id_edition=" . intval($id) . "
                ";
                    break;
                }
            case "tome" :
                {
                    $query = "
                SELECT COUNT(DISTINCT(ua.user_id)) as nbr
                FROM
                    users_album ua
                    INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
                WHERE
                    en.id_tome=" . intval($id) . "
                ";
                    break;
                }
            case "collection" :
                {
                    $query = "
                SELECT COUNT(DISTINCT(ua.user_id)) as nbr
                FROM
                    users_album ua
                    INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
                WHERE
                    en.id_collection=" . intval($id) . "
                ";
                    break;
                }
            case "editeur" :
                {
                    $query = "
                SELECT COUNT(DISTINCT(ua.user_id)) as nbr
                FROM
                    users_album ua
                    INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
                    INNER JOIN bd_collection c ON en.id_collection = c.id_collection

                WHERE
                    c.id_editeur=" . intval($id) . "
                ";
                    break;
                }
            case "serie" :
                {
                    $query = "
                SELECT COUNT(DISTINCT(ua.user_id)) as nbr
                FROM
                    users_album ua
                    INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
                    INNER JOIN bd_tome t ON t.id_tome = en.id_tome
                WHERE
                    t.id_serie=" . intval($id) . "
                ";
                    break;
                }
            case "genre" :
                {
                    $query = "
                SELECT COUNT(DISTINCT(ua.user_id)) as nbr
                FROM
                    users_album ua
                    INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
                    INNER JOIN bd_tome t ON t.id_tome = en.id_tome
                    INNER JOIN bd_serie s ON t.id_serie = s.id_serie
                WHERE
                    s.id_genre=" . intval($id) . "
                ";
                    break;
                }
            case "auteur" :
                {
                    $query = "
                SELECT COUNT(DISTINCT(ua.user_id)) as nbr
                FROM
                    users_album ua
                    INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
                    INNER JOIN bd_tome t ON t.id_tome = en.id_tome
                WHERE
                    t.id_scenar = " . intval($id) . "
                    or t.id_dessin = " . intval($id) . "
                    or t.id_scenar_alt = " . intval($id) . "
                    or t.id_dessin_alt = " . intval($id) . "
                    or t.id_color = " . intval($id) . "
                    or t.id_color_alt = " . intval($id) . "
                ";
                    break;
                }
            case "tomeComment" :
                {
                    $query = "
                SELECT COUNT(DISTINCT(uc.user_id)) as nbr
                FROM
                    users_comment uc
                WHERE
                    uc.id_tome=" . intval($id) . "
                ";
                    break;
                }
            case "serieComment" :
                {
                    $query = "
                SELECT COUNT(DISTINCT(sc.user_id)) as nbr
                FROM
                    serie_comment sc
                WHERE
                    sc.id_serie=" . intval($id) . "
                ";
                    break;
                }
        }
        if (! empty ( $query )) {
                    $result = Db_query( $query );
            $o = Db_fetch_object($result);
            return $o->nbr;
        }
        return false;
    }

    public function getToken($username, $password) {
         $this->load('c', "WHERE username ='" . Db_Escape_String($username) . "'");
         $error = "";
        if (1 == $this->dbSelect->nbLineResult) {

            $mdp_user = md5($password);

            if ($mdp_user and ($this->password == md5($password))) {
                if ($this->level < 98) {

                   $API_TOKEN = uniqid($prefix=$this->user_id."-");
                   $this->DATE_TOKEN =date('Y-m-d H:i:s');
                   $a_data = array(
                       "user_id" => $this->user_id,
                        "API_TOKEN" => md5($API_TOKEN) ,
                        "DATE_TOKEN" =>  date('Y-m-d H:i:s')
                    );

                    $this->set_dataPaste($a_data);
                   $this->update();
                }
                else {
                    $error = 'Compte bloqué';
                }
            }
            else {
                $error = 'Identifiant ou Mot de passe de connexion invalide';
            }
        }
        else {
            $error = 'Identifiant ou Mot de passe de connexion invalide';
        }
        
        return (array("Token" => $API_TOKEN, "Error" => $error));
    }
    
    public function verifyToken ($token) {
        $tab = explode("-", $token, 2);
        $user_id = $tab[0];
        $this->load('c', "WHERE user_id =" . Db_Escape_String($user_id) . "");
         if (1 == $this->dbSelect->nbLineResult) {
             if (md5($token) == $this->API_TOKEN) {
                 if ($this->DATE_TOKEN ) {
                     // todo : rendre le token temporaire
                     return ($user_id);
                 } else {
                     $this->error = "Token périmé";
                 }
             } else {
                 $this->error = "Token invalide";
             }
         } else {
              $this->error = "Utilisateur inconnu";
         }
    }
}
